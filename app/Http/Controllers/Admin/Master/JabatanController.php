<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    /**
     * Tampilkan halaman & Data JSON dengan Pagination
     */
    public function index(Request $request)
    {
        // 1. Jika request via AJAX (fetch JS), kirim JSON Paginasi
        if ($request->ajax()) {
            
            // Inisialisasi Query + Eager Load 'unitKerja' + Count 'users'
            $query = Jabatan::with('unitKerja')
                ->withCount('users');

            // 2. Logic Pencarian (Server-side Search)
            // Mencari di Nama Jabatan ATAU Nama Unit Kerja (Relasi)
            if ($request->filled('search')) {
                $search = $request->input('search');
                
                $query->where(function($q) use ($search) {
                    $q->where('nama_jabatan', 'ilike', "%{$search}%")
                      ->orWhereHas('unitKerja', function($qUnit) use ($search) {
                          $qUnit->where('nama_unit', 'ilike', "%{$search}%");
                      });
                });
            }

            // 3. Sorting & Pagination
            $perPage = $request->input('per_page', 10);
            
            $data = $query->latest()
                          ->paginate($perPage);

            return response()->json($data);
        }

        // 4. Jika request browser biasa, kirim Data Dropdown untuk Modal
        // Data ini tetap dikirim via View karena statis dan digunakan untuk form Create/Edit
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