<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\LkhBukti;
use App\Models\Tupoksi; // [BARU] Import Tupoksi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService; 

class LkhController extends Controller
{
    /**
     * [BARU] Mengambil Data Referensi untuk Form Input
     * Endpoint ini dipanggil saat User membuka halaman "Buat Laporan"
     */
    public function getReferensi(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }


        // 1. Ambil Tupoksi hanya milik Bidang user tersebut
        $listTupoksi = [];
        if ($user->bidang_id) {
            $listTupoksi = Tupoksi::where('bidang_id', $user->bidang_id)
                ->select('id', 'uraian_tugas')
                ->get();
        }

        // 2. List Jenis Aktivitas (Sesuai titah Paduka)
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
     * 1. LIST LKH
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        // [UPDATE] Muat relasi tupoksi
        $query = LaporanHarian::with(['tupoksi', 'skp', 'bukti', 'validator'])
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
     * 2. CREATE LKH (Update Integrasi Volume & Satuan)
     */
    public function store(Request $request)
    {
        // Daftar aktivitas yang valid (Whitelist)
        $validAktivitas = 'Rapat,Pelayanan Publik,Penyusunan Dokumen,Kunjungan Lapangan,Lainnya';

        $validator = Validator::make($request->all(), [
            'tupoksi_id'        => 'required|exists:tupoksi,id',
            // Validasi case-sensitive, pastikan FE mengirim string yang persis sama
            'jenis_kegiatan'    => 'required|in:' . $validAktivitas, 

            'skp_id'            => 'nullable|exists:skp,id',
            'tanggal_laporan'   => 'required|date',
            'waktu_mulai'       => 'required',
            'waktu_selesai'     => 'required|after:waktu_mulai',
            
            'deskripsi_aktivitas'=> 'required|string',
            'output_hasil_kerja'=> 'required|string',
            
            // [BARU] Validasi Volume & Satuan
            'volume'            => 'required|integer|min:1',
            'satuan'            => 'required|string|max:50',

            // Validasi Lokasi
            'latitude'          => 'nullable|numeric|required_without:master_kelurahan_id',
            'longitude'         => 'nullable|numeric|required_without:master_kelurahan_id',
            'master_kelurahan_id'=> 'nullable|exists:master_kelurahan,id|required_without:latitude',
            
            'bukti.*'           => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            DB::beginTransaction();

            // --- Logika Koordinat (TETAP SAMA) ---
            $finalLat = null;
            $finalLng = null;

            if ($request->latitude && $request->longitude) {
                $finalLat = $request->latitude;
                $finalLng = $request->longitude;
            } else if ($request->master_kelurahan_id) {
                $kelurahan = DB::table('master_kelurahan')
                    ->where('id', $request->master_kelurahan_id)
                    ->select('latitude', 'longitude')
                    ->first();
                
                if ($kelurahan && $kelurahan->latitude) {
                    $finalLat = $kelurahan->latitude;
                    $finalLng = $kelurahan->longitude;
                }
            }

            // --- Logika Geofencing (TETAP SAMA) ---
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

            // Simpan LKH [UPDATE UTAMA DI SINI]
            $lkh = LaporanHarian::create([
                'user_id'            => Auth::id(),
                'skp_id'             => $request->skp_id,
                'tupoksi_id'         => $request->tupoksi_id,
                'jenis_kegiatan'     => $request->jenis_kegiatan,
                'tanggal_laporan'    => $request->tanggal_laporan,
                'waktu_mulai'        => $request->waktu_mulai,
                'waktu_selesai'      => $request->waktu_selesai,
                'deskripsi_aktivitas'=> $request->deskripsi_aktivitas,
                'output_hasil_kerja' => $request->output_hasil_kerja,
                
                // [BARU] Simpan Volume & Satuan
                'volume'             => $request->volume,
                'satuan'             => $request->satuan,

                'status'             => 'waiting_review',
                'master_kelurahan_id'=> $request->master_kelurahan_id,
                'is_luar_lokasi'     => $isLuarLokasi,
                'lokasi' => ($finalLat && $finalLng) ? DB::raw("ST_SetSRID(ST_MakePoint({$finalLng}, {$finalLat}), 4326)") : null
            ]);

            // --- UPLOAD KE MINIO (TETAP SAMA) ---
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

            // Kirim Notif (TETAP SAMA)
            if ($request->user()->atasan_id) {
                NotificationService::send(
                    $request->user()->atasan_id,
                    'lkh_new_submission',
                    'Pegawai ' . $request->user()->name . ' mengirim laporan: ' . $request->jenis_kegiatan,
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
    
    public function show($id)
    {
        // [UPDATE] Cek Laporan milik user ybs ATAU user adalah atasan dari pelapor
        $user = Auth::user();
        $lkh = LaporanHarian::with(['tupoksi', 'skp', 'bukti', 'validator', 'user.bidang', 'user.jabatan'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id) // Laporan miliknya
                      ->orWhere('validator_id', $user->id); // Laporan yg dia validasi
            })
            ->find($id);
            
        // Jika tidak ditemukan, coba cari tahu apakah dia Atasan dari si pembuat laporan
        if (!$lkh) {
             $lkhCheck = LaporanHarian::find($id);
             if ($lkhCheck && $lkhCheck->user->atasan_id == $user->id) {
                 $lkh = $lkhCheck->load(['tupoksi', 'skp', 'bukti', 'validator', 'user.bidang', 'user.jabatan']);
             }
        }

        if (!$lkh) return response()->json(['message' => 'Laporan tidak ditemukan'], 404);

        return response()->json(['data' => $lkh]);
    }

    /**
     * 4. DELETE LKH (Hapus dari MinIO juga)
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