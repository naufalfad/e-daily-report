<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    /**
     * Menampilkan halaman daftar Unit Kerja dengan Server-Side Pagination & Search.
     */
    public function index(Request $request)
    {
        // Jika request via AJAX (API Fetching dari JS), kirim JSON Meta-Object
        if ($request->ajax()) {
            
            // 1. Inisialisasi Query & Eager Loading Count (untuk performa)
            $query = UnitKerja::query()
                ->withCount(['bidang', 'jabatan', 'users']);

            // 2. Implementasi Logic Pencarian (Server-side Search)
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                // Menggunakan ilike untuk kompatibilitas PostgreSQL (case-insensitive)
                $query->where('nama_unit', 'ilike', "%{$search}%"); 
            }

            // 3. Sorting & Pagination (Standardisasi Kontrak API)
            // Menangkap parameter limit, sort, dan dir dari Front-End
            $perPage = $request->input('limit', 10);
            $sortBy = $request->input('sort', 'created_at');
            $sortDir = $request->input('dir', 'desc');
            
            // Eksekusi Paginasi di level Database Engine
            $paginator = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

            return response()->json($paginator);
        }

        // Jika request biasa (browser load), tampilkan kerangka HTML
        return view('admin.master.unit-kerja.index');
    }

    /**
     * Menyimpan Unit Kerja Baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_unit' => 'required|string|max:255|unique:unit_kerja,nama_unit',
        ], [
            'nama_unit.required' => 'Nama Unit Kerja wajib diisi.',
            'nama_unit.unique'   => 'Nama Unit Kerja sudah ada.',
        ]);

        UnitKerja::create([
            'nama_unit' => $request->nama_unit
        ]);

        return response()->json(['message' => 'Unit Kerja berhasil ditambahkan.']);
    }

    /**
     * Update Unit Kerja.
     */
    public function update(Request $request, $id)
    {
        $unit = UnitKerja::findOrFail($id);

        $request->validate([
            'nama_unit' => 'required|string|max:255|unique:unit_kerja,nama_unit,' . $id,
        ]);

        $unit->update([
            'nama_unit' => $request->nama_unit
        ]);

        return response()->json(['message' => 'Unit Kerja berhasil diperbarui.']);
    }

    /**
     * Hapus Unit Kerja (Strict Mode).
     */
    public function destroy($id)
    {
        // Proteksi Logika: Verifikasi relasi sebelum eksekusi penghapusan
        $unit = UnitKerja::withCount(['bidang', 'jabatan', 'users'])->findOrFail($id);

        // [LOGIC STRICT] Cek apakah ada anak (Sub-Entitas)?
        if ($unit->bidang_count > 0 || $unit->jabatan_count > 0 || $unit->users_count > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal hapus! Unit ini masih memiliki Bidang, Jabatan, atau Pegawai terkait.'
            ], 422);
        }

        $unit->delete(); // Soft Delete

        return response()->json(['message' => 'Unit Kerja berhasil dihapus.']);
    }
}