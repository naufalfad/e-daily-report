<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class BidangController extends Controller
{
    /**
     * Tampilkan halaman & Data JSON dengan Pagination & Search
     */
    public function index(Request $request)
    {
        // 1. Jika request via AJAX (fetch JS), kirim JSON Paginasi
        if ($request->ajax()) {
            
            // Inisialisasi Query + Eager Load 'unitKerja' agar efisien
            // + Hitung jumlah pegawai ('users') di bidang ini
            $query = Bidang::with('unitKerja')
                ->withCount('users');

            // 2. Logic Pencarian (Server-side Search)
            // Mencari di Nama Bidang ATAU Nama Unit Kerja (Relasi Parent)
            if ($request->filled('search')) {
                $search = $request->input('search');
                
                $query->where(function($q) use ($search) {
                    $q->where('nama_bidang', 'ilike', "%{$search}%")
                      ->orWhereHas('unitKerja', function($qUnit) use ($search) {
                          $qUnit->where('nama_unit', 'ilike', "%{$search}%");
                      });
                });
            }

            // 3. Sorting & Pagination
            // Default 10 data per halaman jika tidak diset oleh client
            $perPage = $request->input('per_page', 10);
            
            $data = $query->latest()
                          ->paginate($perPage);

            return response()->json($data);
        }

        // 4. Jika request browser biasa, kirim Data Dropdown untuk Modal Create/Edit
        // Data ini statis, cukup diload sekali saat halaman dibuka
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