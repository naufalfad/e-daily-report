<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaporanHarian;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    /**
     * Logic Pusat Filter Data Berdasarkan Role
     */
    private function getQuery($request)
    {
        $user = Auth::user();
        
        // 1. Validasi Tanggal (Wajib)
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        // 2. Query Dasar
        $query = LaporanHarian::with(['user', 'skp'])
            ->whereBetween('tanggal_laporan', [$request->start_date, $request->end_date]);

        // 3. Filter Hak Akses (Security)
        // Jika Pegawai Biasa -> Hanya data sendiri
        if ($user->roles->contains('nama_role', 'Pegawai')) {
            $query->where('user_id', $user->id);
        }
        // Jika Penilai -> Bisa pilih user tertentu ATAU semua bawahan
        elseif ($user->roles->contains('nama_role', 'Penilai')) {
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            } else {
                // Ambil semua bawahan
                $bawahanIds = User::where('atasan_id', $user->id)->pluck('id');
                $query->whereIn('user_id', $bawahanIds);
            }
        }
        // Jika Admin/Kadis -> Bebas (bisa filter user_id jika mau)
        else {
             if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }
        
        return $query;
    }

    public function exportExcel(Request $request)
    {
        // Ambil data matang dari getQuery
        $data = $this->getQuery($request)->get();
        
        // Pass data ke Class Export
        return Excel::download(new LaporanExport($data), 'laporan_kinerja.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getQuery($request)->get();

        $pdf = Pdf::loadView('pdf.laporan_harian', [
            'data' => $data,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date
        ]);

        // Download PDF
        return $pdf->download('laporan_kinerja.pdf');
    }
}