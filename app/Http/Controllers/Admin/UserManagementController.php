<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bidang;
use App\Models\Role;
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
        // 1. Deteksi Request AJAX untuk Pagination
        if ($request->ajax()) {
            
            // 2. Eager Loading (Wajib untuk performa dan data JSON)
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

            // Filter Dropdown Bidang (Opsional)
            if ($request->filled('bidang_id')) {
                $query->where('bidang_id', $request->input('bidang_id'));
            }

            // 4. Pagination
            $perPage = $request->input('per_page', 10);
            $users = $query->latest()->paginate($perPage);
            
            return response()->json($users);
        }

        // Jika request browser biasa, return view kosong (karena data di-load via AJAX)
        // Kita juga bisa mengirim data statis untuk dropdown filter di sini
        $unitKerjas = \App\Models\UnitKerja::orderBy('nama_unit', 'asc')->get();
        return view('admin.manajemen-pegawai', compact('unitKerjas'));
    }

    /**
     * [HR DOMAIN] Create Pegawai Baru
     * - Input: Data Diri & Struktur Jabatan.
     * - Logic: Otomatis buat akun dengan Username=NIP & Password=NIP.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data Kepegawaian (HR)
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'nip'           => 'required|string|unique:users,nip', // NIP Wajib & Unik
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // 2. Validasi Relasi Bidang & Unit Kerja
        $cekBidang = Bidang::where('id', $request->bidang_id)
                           ->where('unit_kerja_id', $request->unit_kerja_id)
                           ->exists();

        if (!$cekBidang) {
            return response()->json([
                'errors' => ['bidang_id' => ['Bidang tidak sesuai dengan Unit Kerja yang dipilih.']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 3. AUTO-GENERATE CREDENTIALS
            // Logic: Default Username & Password adalah NIP pegawai tersebut.
            $defaultUsername = $request->nip;
            $defaultPassword = Hash::make($request->nip); 

            // 4. Simpan Data Pegawai
            $user = User::create([
                'name'          => $request->name,
                'nip'           => $request->nip,
                'username'      => $defaultUsername, 
                'password'      => $defaultPassword,
                'email'         => null, 
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id,
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
                'is_active'     => true, // Default aktif
            ]);

            // 5. Assign Default Role (Staf)
            $roleStaf = Role::where('nama_role', 'Staf')->first();
            $roleId = $roleStaf ? $roleStaf->id : 1; 

            $user->roles()->attach($roleId);

            DB::commit();

            return response()->json([
                'message' => 'Pegawai berhasil didaftarkan.',
                'data'    => $user->load(['roles', 'bidang'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mendaftar', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show Detail Pegawai
     */
    public function show($id)
    {
        $user = User::with(['unitKerja', 'bidang', 'jabatan', 'roles', 'atasan', 'bawahan'])->findOrFail($id);
        return response()->json($user);
    }

    /**
     * [HR DOMAIN] Update Data Pegawai
     * - Logic: HANYA memperbarui data profil, jabatan, dan struktur.
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
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // Validasi Bidang vs Unit Kerja
        $cekBidang = Bidang::where('id', $request->bidang_id)
                           ->where('unit_kerja_id', $request->unit_kerja_id)
                           ->exists();

        if (!$cekBidang) {
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