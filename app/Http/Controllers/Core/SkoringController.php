<?php

namespace App\Http\Controllers\Core;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;

class SkoringController extends Controller
{
    public function exportPdf()
    {
        // [FIX LOGIC] Ambil User Fresh dari DB + Load Relasi UnitKerja & Bidang
        // Jangan cuma auth()->user(), karena relasi unitKerja belum tentu ke-load
        $atasan = User::with(['unitKerja', 'bidang', 'jabatan'])
                    ->find(auth()->id());

        // Ambil bawahan (Logic tetap sama, sudah benar)
        $bawahan = User::where('atasan_id', $atasan->id)
            ->with('unitKerja') // Ini untuk tabel bawahan
            ->get()
            ->map(function ($item) {
                $item->total_lkh = $item->lkh()->count();
                $item->acc_lkh = $item->lkh()->where('status', 'approved')->count();
                $item->skor = $item->total_lkh > 0
                    ? round(($item->acc_lkh / $item->total_lkh) * 100)
                    : 0;

                $item->predikat = $item->skor >= 90 ? 'Sangat Baik' :
                    ($item->skor >= 75 ? 'Baik' :
                        ($item->skor >= 60 ? 'Cukup' : 'Kurang'));

                return $item;
            });

        // Statistik
        $avgScore = $bawahan->avg('skor') ?? 0;
        $pembinaan = $bawahan->where('skor', '<', 60)->count();

        // Render PDF
        $pdf = PDF::loadView('pdf.skoring-kinerja', [
            'atasan' => $atasan,
            'bawahan' => $bawahan,
            'avgScore' => $avgScore,
            'pembinaan' => $pembinaan
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan Skoring Kinerja.pdf');
    }

}