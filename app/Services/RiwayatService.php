<?php

namespace App\Services;

use App\Models\LaporanHarian;

class RiwayatService
{
    /**
     * Mengambil data riwayat laporan dengan paginasi.
     *
     * @param mixed $user User object yang sedang login
     * @param string $role Role pengguna (staf, penilai)
     * @param string|null $mode Mode tampilan (mine = saya, subordinates = bawahan)
     * @param string|null $from Filter tanggal awal
     * @param string|null $to Filter tanggal akhir
     * @param int $perPage Jumlah item per halaman (Default: 10)
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getRiwayat($user, $role, $mode, $from = null, $to = null, $perPage = 10)
    {
        // 1. Optimasi Query (Eager Loading dengan Partial Select)
        $query = LaporanHarian::with([
            // User: Ambil ID, Nama, NIP, dan UnitKerjaID
            'user:id,name,nip,unit_kerja_id', 
            
            // Nested Relation: Unit Kerja
            'user.unitKerja:id,nama_unit', 

            // Validator & Atasan
            'validator:id,name,nip',
            'atasan:id,name,nip',

            // Relasi Bukti (Penting untuk tombol Lampiran)
            'bukti'
        ])
        ->orderBy('tanggal_laporan', 'desc')
        ->whereIn('status', ['approved', 'rejected', 'waiting_review']); // Filter Status Arsip


        // 2. Filter Berdasarkan Role & Mode
        
        // === CASE A: ROLE STAF ===
        if ($role === 'staf') {
            $query->where('user_id', $user->id);
        }

        // === CASE B: ROLE PENILAI ===
        if ($role === 'penilai') {
            if ($mode === 'subordinates') {
                // Tampilkan laporan bawahan
                $query->where('atasan_id', $user->id);
            } else {
                // Default: Tampilkan riwayat kinerja pribadi
                $query->where('user_id', $user->id);
            }
        }

        // 3. Filter Tanggal
        if ($from) {
            $query->whereDate('tanggal_laporan', '>=', $from);
        }

        if ($to) {
            $query->whereDate('tanggal_laporan', '<=', $to);
        }

        // [MODIFIKASI UTAMA]
        // Menggunakan paginate() alih-alih get() untuk memecah data per halaman.
        // Output kini berupa Object LengthAwarePaginator.
        return $query->paginate($perPage);
    }
}