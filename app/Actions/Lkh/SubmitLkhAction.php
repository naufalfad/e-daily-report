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
            // UPDATED: 'kategori_lokasi' dan 'address_auto' otomatis terekstrak secara aman
            // karena sudah di-whitelist di layer Request Validation (StoreLkhRequest).
            $data = $request->safe()->except(['bukti', 'latitude', 'longitude']);
            
            // Enrich data dengan logic bisnis
            $data['user_id'] = $user->id;
            $data['atasan_id'] = $user->atasan_id; // Chain of Responsibility: Atasan langsung
            $data['is_luar_lokasi'] = $isLuarLokasi;
            
            // [FIX] Paksa status menjadi 'waiting_review' agar masuk ke list validasi atasan
            if ($request->input('status') !== 'draft') {
                $data['status'] = 'waiting_review';
            }
            
            // PostGIS Geometry Insertion using Raw SQL
            // ST_MakePoint(lng, lat): Urutan PostGIS adalah Longitude dulu, baru Latitude.
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
            if ($lkh->status === 'waiting_review' && $lkh->atasan_id) {
                try {
                    $this->notificationService->notifyAtasan($lkh);
                } catch (Exception $e) {
                    Log::error("Gagal kirim notifikasi LKH ID {$lkh->id}: " . $e->getMessage());
                }
            }

            // E. Commit Transaction
            DB::commit();

            return $lkh;

        } catch (Exception $e) {
            // F. Rollback jika ada error fatal
            DB::rollBack();
            Log::error("Submit LKH Failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Menghitung jarak Haversine untuk menentukan apakah koordinat berada di luar radius kantor.
     */
    private function checkIsLuarLokasi(float $latUser, float $lngUser): bool
    {
        $officeLat = (float) config('services.office.latitude', -6.175392);
        $officeLng = (float) config('services.office.longitude', 106.827153);
        $radiusAllowed = (int) config('services.office.radius', 100); // meter

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
     */
    private function handleBuktiUpload(array $files, LaporanHarian $lkh): void
    {
        foreach ($files as $file) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('bukti_lkh', $filename, 'public');

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