<?php

declare(strict_types=1);

namespace App\Actions\Lkh;

use App\Http\Requests\Lkh\StoreLkhRequest;
use App\Models\LaporanHarian;
use App\Models\LkhBukti;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Added for logging
use Exception;

/**
 * Class SubmitLkhAction
 *
 * Bertanggung jawab penuh atas proses persistensi Laporan Kinerja Harian.
 * Mengenkapsulasi logika Transaksi Database, Kalkulasi Geospasial, dan Upload File.
 *
 * Pattern: Command / Action Pattern.
 */
class SubmitLkhAction
{
    /**
     * Dependency Injection untuk Service Notifikasi.
     * Mengurangi Coupling langsung ke implementasi notifikasi.
     */
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Eksekusi logika penyimpanan LKH.
     *
     * @param StoreLkhRequest $request Data yang sudah divalidasi (Safe Data).
     * @param User $user Actor yang melakukan submit.
     * @return LaporanHarian
     * @throws Exception Jika terjadi kegagalan transaksi.
     */
    public function execute(StoreLkhRequest $request, User $user): LaporanHarian
    {
        // 1. Spatial Logic & Business Rule Check
        // Menentukan apakah pegawai berada di luar radius kantor atau tidak.
        $latitude = (float) $request->validated('latitude');
        $longitude = (float) $request->validated('longitude');
        
        $isLuarLokasi = $this->checkIsLuarLokasi($latitude, $longitude);

        // 2. Database Transaction Wrapper
        // Menjamin Atomicity: Semua data tersimpan, atau tidak sama sekali.
        DB::beginTransaction();

        try {
            // A. Persiapkan Data Laporan
            // UPDATED: 'address_auto' sudah ada di request->safe() karena sudah di whitelist di Request Validation
            $data = $request->safe()->except(['bukti', 'latitude', 'longitude']);
            
            // Enrich data dengan logic bisnis
            $data['user_id'] = $user->id;
            $data['atasan_id'] = $user->atasan_id; // Chain of Responsibility: Atasan langsung
            $data['is_luar_lokasi'] = $isLuarLokasi;
            
            // [FIX] Paksa status menjadi 'waiting_review' agar masuk ke list validasi atasan
            // Kecuali jika user eksplisit minta 'draft' (jika nanti fitur draft diaktifkan di FE)
            // Tapi sesuai diskusi sebelumnya, tombol kirim = waiting_review.
            if ($request->input('status') !== 'draft') {
                $data['status'] = 'waiting_review';
            }
            
            // PostGIS Geometry Insertion using Raw SQL
            // ST_MakePoint(lng, lat): Urutan PostGIS adalah Longitude dulu, baru Latitude.
            // ST_SetSRID(..., 4326): Mengatur koordinat sistem ke WGS 84 (Standar GPS).
            // Validasi: Pastikan lat/lng bukan 0 jika providernya GPS (walaupun request validation sudah handle)
            if ($latitude != 0 && $longitude != 0) {
                $data['lokasi'] = DB::raw("ST_SetSRID(ST_MakePoint({$longitude}, {$latitude}), 4326)");
            }

            // B. Insert Laporan Harian (Aggregate Root)
            /** @var LaporanHarian $lkh */
            $lkh = LaporanHarian::create($data);

            // C. Handle File Uploads (Child Entities)
            if ($request->hasFile('bukti')) {
                $this->handleBuktiUpload($request->file('bukti'), $lkh);
            }

            // D. Trigger Notification (Side Effect)
            // Kondisi ini akan bernilai TRUE jika status = waiting_review
            if ($lkh->status === 'waiting_review' && $lkh->atasan_id) {
                // Menggunakan try-catch internal agar error notifikasi tidak membatalkan transaksi utama
                try {
                    $this->notificationService->notifyAtasan($lkh);
                } catch (Exception $e) {
                    // Log error notifikasi, tapi biarkan proses submit berlanjut (Non-blocking)
                    Log::error("Gagal kirim notifikasi LKH ID {$lkh->id}: " . $e->getMessage());
                }
            }

            // E. Commit Transaction
            DB::commit();

            return $lkh;

        } catch (Exception $e) {
            // F. Rollback jika ada error fatal
            DB::rollBack();
            
            // Log error untuk debugging
            Log::error("Submit LKH Failed: " . $e->getMessage());

            // Re-throw exception agar bisa ditangkap oleh Handler atau Controller untuk return response 500
            throw $e;
        }
    }

    /**
     * Menghitung jarak Haversine untuk menentukan apakah koordinat berada di luar radius kantor.
     * Penerapan prinsip Information Expert (Action ini tahu cara menghitung).
     */
    private function checkIsLuarLokasi(float $latUser, float $lngUser): bool
    {
        // Defensive: Ambil config dengan nilai default aman jika config hilang
        // Koordinat Kantor Pemerintahan Mimika (contoh) atau default Jakarta
        $officeLat = (float) config('services.office.latitude', -6.175392);
        $officeLng = (float) config('services.office.longitude', 106.827153);
        $radiusAllowed = (int) config('services.office.radius', 100); // meter

        // Jika koordinat user 0/null (fallback case), anggap luar lokasi
        if ($latUser === 0.0 || $lngUser === 0.0) {
            return true;
        }

        // Rumus Haversine (Jarak dalam Meter)
        $earthRadius = 6371000;
        $dLat = deg2rad($latUser - $officeLat);
        $dLng = deg2rad($lngUser - $officeLng);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($officeLat)) * cos(deg2rad($latUser)) *
             sin($dLng / 2) * sin($dLng / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance > $radiusAllowed;
    }

    /**
     * Menangani loop upload file bukti.
     * Memisahkan logic file handling agar method execute tidak terlalu gemuk.
     *
     * @param array $files Array of UploadedFile
     * @param LaporanHarian $lkh Parent Model
     */
    private function handleBuktiUpload(array $files, LaporanHarian $lkh): void
    {
        foreach ($files as $file) {
            // Generate nama file unik & aman
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store file ke disk 'public' folder 'bukti_lkh'
            $path = $file->storeAs('bukti_lkh', $filename, 'public');

            // Insert ke tabel lkh_bukti
            LkhBukti::create([
                'laporan_id' => $lkh->id,
                'file_path' => $path,
                'file_name_original' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientOriginalExtension(),
            ]);
        }
    }
}