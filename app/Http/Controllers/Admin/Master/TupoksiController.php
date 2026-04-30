<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\Tupoksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TupoksiController extends Controller
{
    /**
     * Tampilkan halaman daftar Tupoksi dengan Server-Side Pagination & Search
     */
    public function index(Request $request)
    {
        // 1. Handle Request AJAX (API Fetching)
        if ($request->ajax() || $request->wantsJson()) {
            
            // Inisialisasi Query dengan Eager Loading relasi
            $query = Tupoksi::with('bidang');

            // A. Filter Dropdown Bidang (Spesifik)
            if ($request->filled('bidang_id')) {
                $query->where('bidang_id', $request->input('bidang_id'));
            }

            // B. Global Search (Cari di uraian tugas ATAU nama bidang)
            if ($request->filled('search')) {
                $search = $request->input('search');

                $query->where(function ($q) use ($search) {
                    $q->where('uraian_tugas', 'ILIKE', "%{$search}%")
                      ->orWhereHas('bidang', function ($subQ) use ($search) {
                          $subQ->where('nama_bidang', 'ILIKE', "%{$search}%");
                      });
                });
            }

            // C. Sorting & Pagination Dinamis (Standardisasi Kontrak API)
            // Menangkap parameter kontrol state dari Front-End
            $perPage = $request->input('limit', 10);
            $sortBy = $request->input('sort', 'created_at'); 
            $sortDir = $request->input('dir', 'desc');

            // Eksekusi Paginasi di level Database Engine
            $paginator = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

            return response()->json($paginator);
        }

        // 2. Load View & Data Dropdown (Initial DOM Render)
        $bidangs = Bidang::orderBy('nama_bidang', 'ASC')->get();
        return view('admin.master.tupoksi.index', compact('bidangs'));
    }

    /**
     * Menyimpan data Tupoksi baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bidang_id'    => 'required|exists:bidang,id',
            'uraian_tugas' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        Tupoksi::create($request->only('bidang_id', 'uraian_tugas'));

        return response()->json(['status' => 'success', 'message' => 'Data Tupoksi berhasil disimpan.']);
    }

    /**
     * Memperbarui data Tupoksi
     */
    public function update(Request $request, $id)
    {
        $tupoksi = Tupoksi::find($id);
        if (!$tupoksi) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'bidang_id'    => 'required|exists:bidang,id',
            'uraian_tugas' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $tupoksi->update($request->only('bidang_id', 'uraian_tugas'));

        return response()->json(['status' => 'success', 'message' => 'Data Tupoksi berhasil diperbarui.']);
    }

    /**
     * Menghapus data Tupoksi (Strict Mode)
     */
    public function destroy($id)
    {
        $tupoksi = Tupoksi::find($id);
        if (!$tupoksi) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        // Proteksi logika: Cek keterkaitan dengan entitas transaksi (Laporan Harian)
        if ($tupoksi->laporanHarian()->exists()) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Gagal! Tupoksi ini sudah digunakan dalam Laporan Harian pegawai.'
            ], 409);
        }

        $tupoksi->delete(); // Eksekusi penghapusan
        
        return response()->json(['status' => 'success', 'message' => 'Data Tupoksi berhasil dihapus.']);
    }
}