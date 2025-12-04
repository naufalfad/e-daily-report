<?php

namespace App\Services;

use App\Models\LaporanHarian;

class RiwayatService
{
    public static function getRiwayat($user, $role, $mode, $from = null, $to = null)
    {
        $query = LaporanHarian::with(['user.unitKerja', 'validator', 'atasan'])
            ->orderBy('tanggal_laporan', 'desc')
            ->whereIn('status', ['approved', 'rejected']);   // ⬅ WAJIB ADA


        // === FILTER ROLE STAF ===
        if ($role === 'staf') {
            $query->where('user_id', $user->id);
        }

        // === FILTER ROLE PENILAI ===
        if ($role === 'penilai') {
            if ($mode === 'mine') {
                $query->where('user_id', $user->id);
            } else {
                $query->where('atasan_id', $user->id);
            }
        }

        // === FILTER TANGGAL ===
        if ($from) {
            $query->whereDate('tanggal_laporan', '>=', $from);
        }

        if ($to) {
            $query->whereDate('tanggal_laporan', '<=', $to);
        }

        return $query->get();
    }
}