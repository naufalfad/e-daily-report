<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\LkhBukti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService; 

class LkhController extends Controller
{
    /**
     * 1. LIST LKH
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        $query = LaporanHarian::with(['skp', 'bukti', 'validator'])
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
     * 2. CREATE LKH (Upload ke MinIO)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'skp_id'            => 'nullable|exists:skp,id',
            'tanggal_laporan'   => 'required|date',
            'waktu_mulai'       => 'required',
            'waktu_selesai'     => 'required|after:waktu_mulai',
            'jenis_kegiatan'    => 'required|string',
            'deskripsi_aktivitas'=> 'required|string',
            'output_hasil_kerja'=> 'required|string',
            
            'latitude'          => 'nullable|numeric|required_without:master_kelurahan_id',
            'longitude'         => 'nullable|numeric|required_without:master_kelurahan_id',
            'master_kelurahan_id'=> 'nullable|exists:master_kelurahan,id|required_without:latitude',
            
            'bukti.*'           => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            DB::beginTransaction();

            // --- Logika Koordinat ---
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
                } else {
                    throw new \Exception("Data koordinat wilayah tidak ditemukan.");
                }
            }

            // --- Logika Geofencing ---
            $officeLat = config('services.office.lat');
            $officeLng = config('services.office.lng');
            $radius    = config('services.office.radius');
            $isLuarLokasi = true;

            if ($officeLat && $officeLng && $finalLat) {
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

            // Simpan LKH
            $lkh = LaporanHarian::create([
                'user_id'            => Auth::id(),
                'skp_id'             => $request->skp_id,
                'tanggal_laporan'    => $request->tanggal_laporan,
                'waktu_mulai'        => $request->waktu_mulai,
                'waktu_selesai'      => $request->waktu_selesai,
                'jenis_kegiatan'     => $request->jenis_kegiatan,
                'deskripsi_aktivitas'=> $request->deskripsi_aktivitas,
                'output_hasil_kerja' => $request->output_hasil_kerja,
                'status'             => 'waiting_review',
                'master_kelurahan_id'=> $request->master_kelurahan_id,
                'is_luar_lokasi'     => $isLuarLokasi,
                'lokasi' => DB::raw("ST_SetSRID(ST_MakePoint({$finalLng}, {$finalLat}), 4326)")

            ]);

            // --- UPLOAD KE MINIO ---
            if ($request->hasFile('bukti')) {
                foreach ($request->file('bukti') as $file) {
                    // [PERBAIKAN] Gunakan disk 'minio'
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

            // Kirim Notif
            if ($request->user()->atasan_id) {
                NotificationService::send(
                    $request->user()->atasan_id,
                    'lkh_new_submission',
                    'Pegawai ' . $request->user()->name . ' baru saja mengirim LKH baru.',
                    $lkh->id
                );
            }

            return response()->json([
                'message' => 'Laporan Harian berhasil dikirim',
                'is_luar_lokasi' => $isLuarLokasi,
                'data' => $lkh->load('bukti')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengirim laporan', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $lkh = LaporanHarian::with(['skp', 'bukti', 'validator', 'user'])
            ->where('user_id', Auth::id()) 
            ->find($id);

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

        // [PERBAIKAN] Hapus file fisik di MinIO
        foreach ($lkh->bukti as $file) {
            Storage::disk('minio')->delete($file->file_path);
        }
        
        $lkh->delete(); 

        return response()->json(['message' => 'Laporan berhasil dihapus']);
    }
}