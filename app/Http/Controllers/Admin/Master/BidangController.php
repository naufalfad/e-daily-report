<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BidangController extends Controller
{
    /**
     * Tampilkan halaman & Data JSON dengan Pagination & Search
     */
    public function index(Request $request)
    {
        // 1. Jika request via AJAX (fetch JS), kirim JSON Paginasi
        if ($request->ajax()) {
            
            // Inisialisasi Query 
            // Load 'unitKerja' dan 'parent' (untuk melihat induknya jika dia sub bidang)
            // Hitung jumlah pegawai ('users') dan jumlah sub bidang ('children')
            $query = Bidang::with(['unitKerja', 'parent'])
                ->withCount(['users', 'children']);

            // 2. Logic Pencarian (Server-side Search)
            if ($request->filled('search')) {
                $search = $request->input('search');
                
                $query->where(function($q) use ($search) {
                    $q->where('nama_bidang', 'ilike', "%{$search}%")
                      // Cari berdasarkan nama Unit Kerja
                      ->orWhereHas('unitKerja', function($qUnit) use ($search) {
                          $qUnit->where('nama_unit', 'ilike', "%{$search}%");
                      })
                      // Cari berdasarkan nama Induk Bidang
                      ->orWhereHas('parent', function($qParent) use ($search) {
                          $qParent->where('nama_bidang', 'ilike', "%{$search}%");
                      });
                });
            }

            // 3. Sorting & Pagination
            $perPage = $request->input('per_page', 10);
            $data = $query->latest()->paginate($perPage);

            return response()->json($data);
        }

        // 4. Kirim Data Statis untuk Dropdown Unit Kerja
        $unitKerjas = UnitKerja::orderBy('nama_unit', 'asc')->get();

        return view('admin.master.bidang.index', compact('unitKerjas'));
    }

    /**
     * Simpan Data Baru
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nama_bidang'   => 'required|string|max:255',
            'level'         => ['required', Rule::in([Bidang::LEVEL_BIDANG, Bidang::LEVEL_SUB_BIDANG])],
        ], [
            'unit_kerja_id.required' => 'Unit Kerja wajib dipilih.',
            'level.required'         => 'Tingkatan (Level) bidang wajib dipilih.',
        ]);

        // 2. Validasi Logika Hierarki (Conditional Validation)
        $parentId = null;

        if ($request->level === Bidang::LEVEL_SUB_BIDANG) {
            // Jika Sub Bidang, Parent ID Wajib diisi
            $request->validate([
                'parent_id' => [
                    'required',
                    'exists:bidang,id',
                    // CUSTOM LOGIC: Pastikan Parent yang dipilih ada di Unit Kerja yang sama
                    function ($attribute, $value, $fail) use ($request) {
                        $parent = Bidang::find($value);
                        if ($parent && $parent->unit_kerja_id != $request->unit_kerja_id) {
                            $fail('Induk Bidang harus berasal dari Unit Kerja yang sama.');
                        }
                        // Pastikan Parent yang dipilih levelnya adalah 'bidang' (bukan sub_bidang lagi/nesting berlebih)
                        if ($parent && $parent->level !== Bidang::LEVEL_BIDANG) {
                             $fail('Induk yang dipilih harus berlevel Bidang, bukan Sub Bidang.');
                        }
                    },
                ]
            ], [
                'parent_id.required' => 'Induk Bidang wajib dipilih untuk Sub Bidang.',
            ]);

            $parentId = $request->parent_id;
        } 
        // Jika level 'bidang', $parentId tetap null (default)

        // 3. Simpan Data
        Bidang::create([
            'unit_kerja_id' => $request->unit_kerja_id,
            'nama_bidang'   => $request->nama_bidang,
            'level'         => $request->level,
            'parent_id'     => $parentId,
        ]);

        return response()->json(['message' => 'Data Bidang berhasil ditambahkan.']);
    }

    /**
     * Update Data
     */
    public function update(Request $request, $id)
    {
        $bidang = Bidang::findOrFail($id);

        // 1. Validasi Input Dasar
        $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nama_bidang'   => 'required|string|max:255',
            'level'         => ['required', Rule::in([Bidang::LEVEL_BIDANG, Bidang::LEVEL_SUB_BIDANG])],
        ]);

        // 2. Validasi Logika Hierarki
        $parentId = null;

        if ($request->level === Bidang::LEVEL_SUB_BIDANG) {
            $request->validate([
                'parent_id' => [
                    'required',
                    'exists:bidang,id',
                    // Validasi: Tidak boleh memilih diri sendiri sebagai parent
                    function ($attribute, $value, $fail) use ($id) {
                        if ($value == $id) {
                            $fail('Bidang tidak bisa menjadi induk bagi dirinya sendiri.');
                        }
                    },
                    // Validasi Konsistensi Unit Kerja
                    function ($attribute, $value, $fail) use ($request) {
                        $parent = Bidang::find($value);
                        if ($parent && $parent->unit_kerja_id != $request->unit_kerja_id) {
                            $fail('Induk Bidang harus berasal dari Unit Kerja yang sama.');
                        }
                    },
                ]
            ]);
            $parentId = $request->parent_id;
        } else {
            // Jika diubah jadi 'bidang', pastikan dia tidak punya parent
            $parentId = null;
        }

        // 3. Update Data
        $bidang->update([
            'unit_kerja_id' => $request->unit_kerja_id,
            'nama_bidang'   => $request->nama_bidang,
            'level'         => $request->level,
            'parent_id'     => $parentId,
        ]);

        return response()->json(['message' => 'Data Bidang berhasil diperbarui.']);
    }

    /**
     * Hapus Data (Strict Mode)
     */
    public function destroy($id)
    {
        // Load count users dan count children (sub bidang)
        $bidang = Bidang::withCount(['users', 'children'])->findOrFail($id);

        // Validasi 1: Masih ada pegawai?
        if ($bidang->users_count > 0) {
            return response()->json([
                'message' => 'Gagal hapus! Masih ada pegawai aktif di bidang ini.'
            ], 422);
        }

        // Validasi 2: Masih ada sub bidang? (Jika ini adalah induk)
        if ($bidang->children_count > 0) {
            return response()->json([
                'message' => 'Gagal hapus! Bidang ini masih memiliki Sub Bidang di bawahnya. Hapus/pindahkan Sub Bidang terlebih dahulu.'
            ], 422);
        }

        $bidang->delete(); // Soft Delete

        return response()->json(['message' => 'Bidang berhasil dihapus.']);
    }

    /**
     * API Internal: Ambil daftar Induk Bidang berdasarkan Unit Kerja
     * Digunakan oleh Dropdown Dependent di Modal (AJAX)
     */

    public function getParents(Request $request)
    {
        // Validasi ringan memastikan unit_kerja_id dikirim
        $request->validate([
            'unit_kerja_id' => 'required|numeric'
        ]);

        // Logic: 
        // 1. Ambil bidang yang berada di unit kerja yang dipilih.
        // 2. Filter hanya yang level-nya 'bidang' (karena Sub Bidang tidak boleh punya anak).
        $parents = Bidang::where('unit_kerja_id', $request->unit_kerja_id)
            ->where('level', Bidang::LEVEL_BIDANG) 
            ->orderBy('nama_bidang', 'asc')
            ->get(['id', 'nama_bidang']); // Optimization: Hanya ambil kolom yang dibutuhkan

        return response()->json($parents);
    }
}