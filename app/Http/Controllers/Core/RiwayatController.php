<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\RiwayatService;
use Illuminate\Support\Carbon;

class RiwayatController extends Controller
{
    /**
     * Halaman Riwayat untuk STAF.
     */
    public function indexStaf(Request $request)
    {
        return $this->handleIndex($request, 'staf', 'staf.riwayat-lkh');
    }

    /**
     * Halaman Riwayat untuk PENILAI.
     */
    public function indexPenilai(Request $request)
    {
        return $this->handleIndex($request, 'penilai', 'penilai.riwayat');
    }

    /**
     * Logika utama untuk menentukan response JSON atau View.
     */
    private function handleIndex(Request $request, string $role, string $viewName)
    {
        // 1. Jika request datang dari Javascript (AJAX/Fetch) -> Return JSON
        if ($request->ajax() || $request->wantsJson()) {
            $user = auth()->user();
            
            // Tentukan tanggal (Input User atau Default Bulan Ini)
            $dates = $this->getFilterDates($request);

            // Ambil data dari Service dengan range tanggal yang pasti
            // Default perPage = 10 (dari Service)
            $data = RiwayatService::getRiwayat(
                $user,
                $role,
                $request->mode, 
                $dates['from'],
                $dates['to']
            );

            // Return JSON Paginator (data, current_page, last_page, total, links)
            return response()->json($data);
        }

        // 2. Jika request biasa (Buka di browser) -> Return Halaman HTML
        return view($viewName);
    }

    /**
     * Export Riwayat Laporan ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $role = $request->role ?? 'staf';

        // 1. Normalisasi Input Tanggal
        $dates = $this->getFilterDates($request);

        // [MODIFIKASI] Ambil data untuk PDF (Limit Besar / Semua)
        // Kita set 9999 agar terambil semua dalam satu halaman PDF
        $riwayatPaginator = RiwayatService::getRiwayat(
            $user,
            $role,
            $request->mode,
            $dates['from'],
            $dates['to'],
            9999 // Limit besar untuk PDF
        );

        // Ambil item dari paginator (karena service return object paginator)
        $items = $riwayatPaginator->items(); 

        // 2. Format Label Periode untuk Header PDF
        $fromLabel = Carbon::parse($dates['from'])->translatedFormat('d F Y');
        $toLabel   = Carbon::parse($dates['to'])->translatedFormat('d F Y');
        
        $periode = "$fromLabel s/d $toLabel";

        return Pdf::loadView('pdf.riwayat', [
            'items'   => $items, // Pass array items, bukan object paginator
            'user'    => $user,
            'from'    => $fromLabel,
            'to'      => $toLabel,
            'role'    => $role,
            'periode' => $periode,
        ])->setPaper('a4', 'landscape')
          ->stream('riwayat-lkh.pdf');
    }

    /**
     * Helper: Menentukan rentang tanggal filter.
     */
    private function getFilterDates(Request $request)
    {
        if ($request->from_date && $request->to_date) {
            return [
                'from' => $request->from_date,
                'to'   => $request->to_date
            ];
        }

        // Default: Bulan Ini
        return [
            'from' => Carbon::now()->startOfMonth()->toDateString(),
            'to'   => Carbon::now()->endOfMonth()->toDateString()
        ];
    }
}