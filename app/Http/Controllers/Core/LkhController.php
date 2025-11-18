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
        
        // FIX: Hapus eager loading 'validator'
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
     * 2. CREATE LKH
     */
    public function store(Request $request)
    {
        $validAktivitas = 'Rapat,Pelayanan Publik,Penyusunan Dokumen,Kunjungan Lapangan,Lainnya';
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }

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


            // Simpan LKH [FIX: Menyimpan atasan_id]
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

            // --- UPLOAD KE MINIO ---
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

            DB::commit();

            if ($user->atasan_id) {
                NotificationService::send(
                    $user->atasan_id,
                    'lkh_new_submission',
                    'Pegawai ' . $user->name . ' mengirim laporan: ' . $request->jenis_kegiatan,
                    $lkh->id
                );
            }

            return response()->json([
                'message' => 'Laporan Harian berhasil dikirim',
                'is_luar_lokasi' => $isLuarLokasi,
                'data' => $lkh->load(['bukti', 'tupoksi'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengirim laporan', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 3. SHOW DETAIL LKH (Akses dengan ID laporan)
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // FIX: Defensive check for routing crash (riwayat string)
        if (!is_numeric($id)) {
             return response()->json(['message' => 'ID Laporan tidak valid atau URL salah.'], 400);
        }

        // FIX: Hapus eager loading 'validator'
        $lkh = LaporanHarian::with(['tupoksi', 'skp', 'bukti', 'user.bidang', 'user.jabatan', 'atasan']) 
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id) // Laporan miliknya
                      ->orWhere('atasan_id', $user->id); // Laporan yang ditujukan kepadanya
            })
            ->find($id);

        if (!$lkh) return response()->json(['message' => 'Laporan tidak ditemukan'], 404);

        return response()->json(['data' => $lkh]);
    }


    /**
     * Mengambil Riwayat LKH berdasarkan Role dan Filter (Untuk Halaman Riwayat)
     */
    public function getRiwayat(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }
        
        // FIX: Hapus eager loading 'validator'
        $query = LaporanHarian::with([
            'tupoksi', 
            'user:id,name', 
            'atasan:id,name', // Hanya relasi 'atasan' yang digunakan sebagai penilai
            'bukti'
        ]);
        
        $mode = $request->input('mode', 'mine'); 
        
        // FIX UTAMA: Mengganti $user->hasRole() dengan cek relasi roles() bawaan
        $isPenilai = $user->roles()->pluck('nama_role')->contains('Penilai'); 

        // --- LOGIKA FILTER MODE BERDASARKAN ROLE ---
        if ($isPenilai && $mode === 'subordinates') {
            $query->where('atasan_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }
        
        // --- LOGIKA FILTER TANGGAL (Selalu Aktif) ---
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

        foreach ($lkh->bukti as $file) {
            Storage::disk('minio')->delete($file->file_path);
        }
        
        $lkh->delete(); 

        return response()->json(['message' => 'Laporan berhasil dihapus']);
    }
}