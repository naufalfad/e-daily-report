<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    /**
     * Tampilkan halaman & Data JSON
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Jabatan::with('unitKerja')
                ->withCount('users') // Hitung jumlah pegawai yang menjabat
                ->latest()
                ->get();

            return response()->json(['data' => $data]);
        }

        // Ambil data Unit Kerja untuk dropdown
        $unitKerjas = UnitKerja::orderBy('nama_unit', 'asc')->get();

        return view('admin.master.jabatan.index', compact('unitKerjas'));
    }

    /**
     * Simpan Data Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nama_jabatan'  => 'required|string|max:255',
        ], [
            'unit_kerja_id.required' => 'Unit Kerja wajib dipilih.',
            'nama_jabatan.required'  => 'Nama Jabatan wajib diisi.',
        ]);

        Jabatan::create([
            'unit_kerja_id' => $request->unit_kerja_id,
            'nama_jabatan'  => $request->nama_jabatan,
        ]);

        return response()->json(['message' => 'Jabatan berhasil ditambahkan.']);
    }

    /**
     * Update Data
     */
    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nama_jabatan'  => 'required|string|max:255',
        ]);

        $jabatan->update([
            'unit_kerja_id' => $request->unit_kerja_id,
            'nama_jabatan'  => $request->nama_jabatan,
        ]);

        return response()->json(['message' => 'Jabatan berhasil diperbarui.']);
    }

    /**
     * Hapus Data (Strict Mode)
     */
    public function destroy($id)
    {
        $jabatan = Jabatan::withCount('users')->findOrFail($id);

        // Cek apakah ada pegawai yang sedang menjabat?
        if ($jabatan->users_count > 0) {
            return response()->json([
                'message' => 'Gagal hapus! Masih ada pegawai dengan jabatan ini.'
            ], 422);
        }

        $jabatan->delete(); // Soft Delete

        return response()->json(['message' => 'Jabatan berhasil dihapus.']);
    }
}