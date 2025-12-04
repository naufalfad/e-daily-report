<?php

namespace App\Http\Controllers\Core;
use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\RiwayatService;

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

            // Ambil laporan yang tampil di halaman
            $query->where('user_id', $user->id);

            // Jika UI TIDAK sedang melakukan filter tanggal
            // Maka PDF harus mengikuti UI: hanya ambil data yang sedang ditampilkan
            if (!$request->from_date && !$request->to_date) {

                // Cari tanggal paling baru yang tampil pada UI
                $latestDate = LaporanHarian::where('user_id', $user->id)
                    ->orderBy('tanggal_laporan', 'desc')
                    ->value('tanggal_laporan');

                // Batasi hanya pada tanggal itu saja
                if ($latestDate) {
                    $query->whereDate('tanggal_laporan', $latestDate);
                }
            }
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

        $riwayat = RiwayatService::getRiwayat(
            $user,
            $role,
            $request->mode,
            $request->from_date,
            $request->to_date
        );

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