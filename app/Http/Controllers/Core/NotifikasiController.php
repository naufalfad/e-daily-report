<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;
use App\Enums\NotificationType;

class NotifikasiController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Ambil mentah dulu
        $notifRaw = Notifikasi::with('related')
            ->where('user_id_recipient', $userId)
            ->latest()
            ->limit(50)
            ->get();

        // =============================
        // GROUPING AKUMULASI LKH BARU
        // =============================
        $grouped = $notifRaw->groupBy(fn($n) => $n->tipe_notifikasi);

        $finalList = collect();

        foreach ($grouped as $type => $items) {

            $enumType = NotificationType::tryFrom($type);

            // Jika enum tidak dikenal → skip
            if (!$enumType) continue;

            // === CASE 1: AKUMULASI LKH MASUK ===
            if ($type === NotificationType::LKH_NEW_SUBMISSION->value) {

                $total = $items->count();
                $latest = $items->first();

                $finalList->push([
                    'id'            => $latest->id,
                    'tipe'          => $type,
                    'title'         => "Ada $total Laporan Menunggu Validasi",
                    'pesan'         => "Terdapat $total laporan yang baru diajukan bawahan Anda dan menunggu untuk divalidasi.",
                    'created_at'    => $latest->created_at,
                    'redirect_url'  => "/penilai/validasi-lkh",
                ]);

                continue;
            }

            // === CASE 2: BUKAN AKUMULASI ===
            foreach ($items as $item) {

                $finalList->push([
                    'id'            => $item->id,
                    'tipe'          => $type,
                    'title'         => $enumType->title(),
                    'pesan'         => $item->pesan ?: $enumType->defaultMessage(),
                    'created_at'    => $item->created_at,
                    'redirect_url'  => $this->resolveRedirect($item),
                ]);
            }
        }

        return response()->json([
            'unread_count' => Notifikasi::where('user_id_recipient', $userId)->where('is_read', false)->count(),
            'data'         => $finalList->sortByDesc('created_at')->values()
        ]);
    }

    private function resolveRedirect($item)
    {
        if (!$item->related_type) return "#";

        return match ($item->related_type) {
            'App\Models\LaporanHarian' =>
                Auth::user()->hasRole('Atasan')
                    ? "/penilai/validasi-lkh/{$item->related_id}"
                    : "/pegawai/riwayat-lkh/{$item->related_id}",

            'App\Models\Pengumuman' => "/dashboard/pengumuman/{$item->related_id}",
            'App\Models\Skp' => "/pegawai/skp/{$item->related_id}",
            default => "#",
        };
    }
}