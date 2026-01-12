<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bidang;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * [HR DOMAIN] List Data Pegawai
     * Menampilkan data pegawai beserta struktur jabatannya dengan Pagination & Search.
     */
    public function index(Request $request)
    {
        // 1. Deteksi Request AJAX untuk Pagination (Server-side Datatables)
        if ($request->ajax()) {
            
            // Eager Loading relasi yang dibutuhkan
            $query = User::with(['unitKerja', 'bidang', 'jabatan', 'roles', 'atasan']);

            // 3. Advanced Filtering
            
            // Filter Pencarian Teks (Nama atau NIP)
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('nip', 'ilike', "%{$search}%");
                });
            }

            // Filter Dropdown Unit Kerja
            if ($request->filled('unit_kerja_id')) {
                $query->where('unit_kerja_id', $request->input('unit_kerja_id'));
            }

            // Filter Dropdown Bidang
            if ($request->filled('bidang_id')) {
                $bidangId = $request->input('bidang_id');
                // Logic Cerdas: Jika user filter "Bidang X" (Induk), 
                // tampilkan juga pegawai di "Sub Bidang X1", "Sub Bidang X2", dst.
                $bidang = Bidang::find($bidangId);
                if ($bidang) {
                    if ($bidang->parent_id === null) {
                        // Ini adalah Induk, ambil id dia dan id anak-anaknya
                        $ids = $bidang->children->pluck('id')->push($bidang->id);
                        $query->whereIn('bidang_id', $ids);
                    } else {
                        // Ini adalah Sub-Bidang, filter spesifik
                        $query->where('bidang_id', $bidangId);
                    }
                }
            }

            // 4. Pagination & Sorting Standard
            $totalRecords = User::count();
            $filteredRecords = $query->count();
            
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir', 'asc');
            
            $columns = ['id', 'name', 'nip', 'jabatan_id', 'bidang_id', 'is_active', 'id']; 
            $orderBy = $columns[$orderColumnIndex] ?? 'created_at';

            $data = $query->orderBy($orderBy, $orderDir)
                          ->skip($start)
                          ->take($limit)
                          ->get();

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        // =====================================================================
        // PHASE 4: UPDATE DATA FETCHING
        // Mengambil data master untuk dropdown di Modal (View)
        // =====================================================================

        $unitKerja = UnitKerja::all();
        
        // [UPDATED CODE]
        // Mengambil Bidang yang dikelompokkan: Induk -> Children
        // Ini memanfaatkan Scope 'induk' dan relasi 'children' yang dibuat di Tahap 3
        $bidang = Bidang::induk()
            ->with('children')
            ->orderBy('nama_bidang', 'asc')
            ->get();
            
        $jabatan = Jabatan::all();
        $roles = Role::all();
        
        // Ambil list pegawai untuk dropdown "Atasan Langsung"
        // Hanya ambil nama & NIP untuk efisiensi
        $pegawaiList = User::select('id', 'name', 'nip', 'jabatan_id')->get(); 

        return view('admin.manajemen-pegawai', compact('unitKerja', 'bidang', 'jabatan', 'roles', 'pegawaiList'));
    }

    /**
     * Store Pegawai Baru
     */
    public function store(Request $request)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'nip'           => 'required|string|unique:users,nip',
            'email'         => 'nullable|email|unique:users,email',
            'username'      => 'required|string|unique:users,username',
            'password'      => 'required|string|min:6',
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'role'          => 'required|exists:roles,name',
            'atasan_id'     => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validasi Logic: Pastikan Bidang ada di Unit Kerja yang dipilih
        $bidang = Bidang::find($request->bidang_id);
        if ($bidang && $bidang->unit_kerja_id != $request->unit_kerja_id) {
            return response()->json([
               'errors' => ['bidang_id' => ['Bidang tidak sesuai dengan Unit Kerja yang dipilih.']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Buat User
            // Note: bidang_id sekarang bisa berisi ID Induk (untuk Kabid) atau ID Anak (untuk Staf)
            $user = User::create([
                'name'          => $request->name,
                'nip'           => $request->nip,
                'email'         => $request->email,
                'username'      => $request->username,
                'password'      => Hash::make($request->password),
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id, 
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
                'is_active'     => true
            ]);

            // Assign Role
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->roles()->attach($role);
            }

            DB::commit();

            return response()->json(['message' => 'Pegawai berhasil ditambahkan']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan data', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update Profil Pegawai
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'nip'           => 'required|string|unique:users,nip,'.$id,
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'atasan_id'     => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bidang = Bidang::find($request->bidang_id);
        if ($bidang && $bidang->unit_kerja_id != $request->unit_kerja_id) {
            return response()->json([
               'errors' => ['bidang_id' => ['Bidang tidak sesuai dengan Unit Kerja yang dipilih.']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user->update([
                'name'          => $request->name,
                'nip'           => $request->nip,
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id,
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
            ]);
            
            // Update Role jika ada di request
            if ($request->has('role')) {
                $role = Role::where('name', $request->role)->first();
                if ($role) {
                     $user->roles()->sync([$role->id]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Data profil pegawai berhasil diperbarui',
                'data'    => $user->load(['roles', 'bidang'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete Pegawai
     */
    public function destroy($id)
    {
        if (auth()->id() == $id) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun sendiri!'], 403);
        }

        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'Pegawai berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus', 'error' => $e->getMessage()], 500);
        }
    }
}