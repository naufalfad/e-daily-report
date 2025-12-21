<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class BidangController extends Controller
{
    /**
     * Tampilkan halaman & Data JSON
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Eager Load 'unitKerja' agar tidak N+1 Query
            $data = Bidang::with('unitKerja')
                ->withCount('users') // Hitung jumlah pegawai di bidang ini
                ->latest()
                ->get();

            return response()->json(['data' => $data]);
        }

        // Ambil data Unit Kerja untuk isi Dropdown (Select Option)
        $unitKerjas = UnitKerja::orderBy('nama_unit', 'asc')->get();

        return view('admin.master.bidang.index', compact('unitKerjas'));
    }

    /**
     * Simpan Data Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nama_bidang'   => 'required|string|max:255',
        ], [
            'unit_kerja_id.required' => 'Unit Kerja induk wajib dipilih.',
            'nama_bidang.required'   => 'Nama Bidang wajib diisi.',
        ]);

        Bidang::create([
            'unit_kerja_id' => $request->unit_kerja_id,
            'nama_bidang'   => $request->nama_bidang,
        ]);

        return response()->json(['message' => 'Bidang berhasil ditambahkan.']);
    }

    /**
     * Update Data
     */
    public function update(Request $request, $id)
    {
        $bidang = Bidang::findOrFail($id);

        $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nama_bidang'   => 'required|string|max:255',
        ]);

        $bidang->update([
            'unit_kerja_id' => $request->unit_kerja_id,
            'nama_bidang'   => $request->nama_bidang,
        ]);

        return response()->json(['message' => 'Bidang berhasil diperbarui.']);
    }

    /**
     * Hapus Data (Strict Mode)
     */
    public function destroy($id)
    {
        $bidang = Bidang::withCount('users')->findOrFail($id);

        // Cek apakah ada pegawai di bidang ini?
        if ($bidang->users_count > 0) {
            return response()->json([
                'message' => 'Gagal hapus! Masih ada pegawai di bidang ini.'
            ], 422);
        }

        $bidang->delete(); // Soft Delete

        return response()->json(['message' => 'Bidang berhasil dihapus.']);
    }
}