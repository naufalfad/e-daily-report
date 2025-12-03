<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $role = $request->role ?? 'staf';

        $query = LaporanHarian::with(['user.unitKerja', 'validator', 'atasan'])
            ->orderBy('tanggal_laporan', 'desc')
            ->whereIn('status', ['approved', 'rejected']); // ⬅ FILTER HANYA YANG SUDAH DIVERIFIKASI

        // Filter role
        if ($role === 'staf') {
            $query->where('user_id', $user->id);
        }

        if ($role === 'penilai') {
            if ($request->mode === 'mine') {
                $query->where('user_id', $user->id);
            } else {
                $query->where('atasan_id', $user->id);
            }
        }

        // Filter tanggal
        if ($request->from_date) {
            $query->whereDate('tanggal_laporan', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('tanggal_laporan', '<=', $request->to_date);
        }

        $riwayat = $query->get();

        // Jika tidak pakai filter, tampilkan "Semua Tanggal"
        $from = $request->from_date ?: 'Semua Tanggal';
        $to = $request->to_date ?: 'Semua Tanggal';

        $periode = ($request->from_date || $request->to_date)
            ? ($from . ' s/d ' . $to)
            : 'Semua Tanggal';

        return Pdf::loadView('pdf.riwayat', [
            'items' => $riwayat,
            'user' => $user,
            'from' => $from,
            'to' => $to,
            'role' => $role,
            'periode' => $periode, // ⬅ DITAMBAHKAN
        ])->setPaper('a4', 'landscape')
            ->stream('riwayat-lkh.pdf');
    }

}