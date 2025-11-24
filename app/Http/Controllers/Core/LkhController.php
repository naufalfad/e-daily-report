<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\LkhBukti;
use App\Models\Tupoksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService; 
use App\Enums\NotificationType; 
use Carbon\Carbon; // Tambahan untuk formatting tanggal di pesan

class LkhController extends Controller
{
    /**
     * Mengambil Data Referensi untuk Form Input
     */
    public function getReferensi(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }

        $listTupoksi = [];
        if ($user->bidang_id) {
            $listTupoksi = Tupoksi::where('bidang_id', $user->bidang_id)
                ->select('id', 'uraian_tugas')
                ->get();
        }

        $jenisAktivitas = [
            'Rapat',
            'Pelayanan Publik',
            'Penyusunan Dokumen',
            'Kunjungan Lapangan',
            'Lainnya'
        ];

        return response()->json([
            'tupoksi' => $listTupoksi,
            'jenis_aktivitas' => $jenisAktivitas,
            'user_bidang_info' => $user->bidang ? $user->bidang->nama_bidang : 'User belum memiliki bidang'
        ]);
    }

    /**
     * 1. LIST LKH (Digunakan untuk Halaman Input LKH Staf)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        $query = LaporanHarian::with(['tupoksi', 'skp', 'bukti'])
            ->where('user_id', $userId);

        if ($request->has('tanggal')) {
            $query->whereDate('tanggal_laporan', $request->tanggal);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest('tanggal_laporan')->paginate(10);

        return response()->json($data);
    }

    /**
     * 2. CREATE LKH (BEST PRACTICE VERSION)
     */
    public function store(Request $request)
    {
        $validAktivitas = 'Rapat,Pelayanan Publik,Penyusunan Dokumen,Kunjungan Lapangan,Lainnya';
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }

        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'tupoksi_id'        => 'required|exists:tupoksi,id',
            'jenis_kegiatan'    => 'required|in:' . $validAktivitas, 
            'skp_id'            => 'nullable|exists:skp,id',
            'tanggal_laporan'   => 'required|date',
            'waktu_mulai'       => 'required',
            'waktu_selesai'     => 'required|after:waktu_mulai',
            'deskripsi_aktivitas'=> 'required|string',
            'output_hasil_kerja'=> 'required|string',
            'volume'            => 'required|integer|min:1',
            'satuan'            => 'required|string|max:50',
            'latitude'          => 'nullable|numeric|required_without:master_kelurahan_id',
            'longitude'         => 'nullable|numeric|required_without:master_kelurahan_id',
            'master_kelurahan_id'=> 'nullable|exists:master_kelurahan,id|required_without:latitude',
            'bukti.*'           => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            // 2. Mulai Transaksi Database (Atomic Operation)
            DB::beginTransaction();

            $finalLat = $request->latitude;
            $finalLng = $request->longitude;
            
            // --- Logika Geofencing ---
            $officeLat = config('services.office.lat');
            $officeLng = config('services.office.lng');
            $radius    = config('services.office.radius');
            $isLuarLokasi = true;

            if ($officeLat && $officeLng && $finalLat && $finalLng) {
                $distanceQuery = DB::selectOne("
                    SELECT ST_DistanceSphere(
                        ST_Point(?, ?), 
                        ST_Point(?, ?)  
                    ) as distance
                ", [$finalLng, $finalLat, $officeLng, $officeLat]);

                if ($distanceQuery->distance <= $radius) {
                    $isLuarLokasi = false;
                }
            }

            // 3. Simpan Data LKH Utama
            $lkh = LaporanHarian::create([
                'user_id'            => $user->id,
                'skp_id'             => $request->skp_id,
                'tupoksi_id'         => $request->tupoksi_id,
                'jenis_kegiatan'     => $request->jenis_kegiatan,
                'tanggal_laporan'    => $request->tanggal_laporan,
                'waktu_mulai'        => $request->waktu_mulai,
                'waktu_selesai'      => $request->waktu_selesai,
                'deskripsi_aktivitas'=> $request->deskripsi_aktivitas,
                'output_hasil_kerja' => $request->output_hasil_kerja,
                'volume'             => $request->volume,
                'satuan'             => $request->satuan,
                'status'             => 'waiting_review',
                'master_kelurahan_id'=> $request->master_kelurahan_id,
                'is_luar_lokasi'     => $isLuarLokasi,
                'atasan_id'          => $user->atasan_id,
                'lokasi' => ($finalLat && $finalLng) ? DB::raw("ST_SetSRID(ST_MakePoint({$finalLng}, {$finalLat}), 4326)") : null
            ]);

            // 4. Upload Bukti ke MinIO
            if ($request->hasFile('bukti')) {
                foreach ($request->file('bukti') as $file) {
                    $path = $file->store('lkh_bukti', 'minio'); 
                    LkhBukti::create([
                        'laporan_id'         => $lkh->id,
                        'file_path'          => $path,
                        'file_name_original' => $file->getClientOriginalName(),
                        'file_type'          => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            // 5. Kirim Notifikasi (DI DALAM TRANSAKSI)
            // Jika ini error, maka create LKH di atas ikut ter-rollback otomatis
            if ($user->atasan_id) {
                // Formatting tanggal agar lebih humanis
                $tglIndo = Carbon::parse($request->tanggal_laporan)->format('d/m/Y');
                
                NotificationService::send(
                    $user->atasan_id,
                    NotificationType::LKH_NEW_SUBMISSION->value, 
                    "Pegawai {$user->name} mengajukan LKH baru kegiatan '{$request->jenis_kegiatan}' untuk tanggal {$tglIndo}.",
                    $lkh // Object untuk Polymorphic Redirect
                );
            }

            // 6. Commit Transaksi (Simpan Permanen)
            DB::commit();

            return response()->json([
                'message' => 'Laporan Harian berhasil dikirim',
                'is_luar_lokasi' => $isLuarLokasi,
                'data' => $lkh->load(['bukti', 'tupoksi'])
            ], 201);

        } catch (\Exception $e) {
            // 7. Rollback jika ada error apapun (DB atau Notif)
            DB::rollBack();
            
            // Opsional: Clean up file MinIO jika sudah terlanjur terupload (Advanced)
            // if (isset($path)) Storage::disk('minio')->delete($path);

            return response()->json(['message' => 'Gagal mengirim laporan', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 3. SHOW DETAIL LKH
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!is_numeric($id)) {
             return response()->json(['message' => 'ID Laporan tidak valid.'], 400);
        }

        $lkh = LaporanHarian::with(['tupoksi', 'skp', 'bukti', 'user.bidang', 'user.jabatan', 'atasan']) 
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id) // Laporan miliknya
                      ->orWhere('atasan_id', $user->id); // Laporan bawahan
            })
            ->find($id);

        if (!$lkh) return response()->json(['message' => 'Laporan tidak ditemukan'], 404);

        return response()->json(['data' => $lkh]);
    }


    /**
     * Mengambil Riwayat LKH
     */
    public function getRiwayat(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User belum login'], 401);
        }
        
        $query = LaporanHarian::with([
            'tupoksi', 
            'user:id,name', 
            'atasan:id,name',
            'bukti'
        ]);
        
        $mode = $request->input('mode', 'mine'); 
        $isPenilai = $user->roles()->pluck('nama_role')->contains('Penilai'); 

        // Filter Mode
        if ($isPenilai && $mode === 'subordinates') {
            $query->where('atasan_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }
        
        // Filter Tanggal
        if ($request->filled('from_date')) {
            $query->whereDate('tanggal_laporan', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal_laporan', '<=', $request->to_date);
        }

        $data = $query->latest('tanggal_laporan')->paginate(15);
        
        return response()->json($data);
    }
    
    /**
     * 4. DELETE LKH
     */
    public function destroy($id)
    {
        $lkh = LaporanHarian::where('user_id', Auth::id())->find($id);

        if (!$lkh) return response()->json(['message' => 'Laporan tidak ditemukan'], 404);

        if ($lkh->status === 'approved') {
            return response()->json(['message' => 'Laporan yang sudah disetujui tidak bisa dihapus'], 403);
        }

        // Hapus file fisik di MinIO
        foreach ($lkh->bukti as $file) {
            Storage::disk('minio')->delete($file->file_path);
        }
        
        $lkh->delete(); 

        return response()->json(['message' => 'Laporan berhasil dihapus']);
    }
}