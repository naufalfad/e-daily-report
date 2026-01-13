<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\Tupoksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TupoksiController extends Controller
{
    public function index(Request $request)
    {
        // 1. Handle Request AJAX
        if ($request->ajax() || $request->wantsJson()) {
            $query = Tupoksi::with('bidang');

            // A. Filter Dropdown Bidang (Jika ada input bidang_id)
            if ($request->has('bidang_id') && !empty($request->bidang_id)) {
                $query->where('bidang_id', $request->bidang_id);
            }

            // B. Global Search (Cari di uraian tugas ATAU nama bidang)
            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('uraian_tugas', 'ILIKE', "%{$search}%")
                    ->orWhereHas('bidang', function ($subQ) use ($search) {
                        $subQ->where('nama_bidang', 'ILIKE', "%{$search}%");
                    });
                });
            }

            // Default sort: Bidang dulu, baru ID
            $data = $query->orderBy('bidang_id', 'asc')
                          ->orderBy('id', 'desc')
                          ->paginate(10);

            return response()->json($data);
        }

        // 2. Load View & Data Dropdown
        $bidangs = Bidang::orderBy('nama_bidang', 'ASC')->get();
        return view('admin.master.tupoksi.index', compact('bidangs'));
    }

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

    public function destroy($id)
    {
        $tupoksi = Tupoksi::find($id);
        if (!$tupoksi) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        if ($tupoksi->laporanHarian()->exists()) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal! Tupoksi ini sudah digunakan dalam Laporan Harian pegawai.'
            ], 409);
        }

        $tupoksi->delete();
        return response()->json(['status' => 'success', 'message' => 'Data Tupoksi berhasil dihapus.']);
    }
}