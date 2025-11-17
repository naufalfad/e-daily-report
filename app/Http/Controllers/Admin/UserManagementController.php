<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bidang; // [BARU] Import Model Bidang
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * 1. READ (List Semua Pegawai)
     */
    public function index(Request $request)
    {
        // [UPDATE] Tambahkan 'bidang' ke eager load agar datanya muncul di JSON
        $query = User::with(['unitKerja', 'bidang', 'jabatan', 'roles', 'atasan']);

        // Filter Pencarian (Nama / NIP)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%");
            });
        }

        // Filter per Unit Kerja
        if ($request->has('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        // [BARU] Filter per Bidang (Opsional, jika Admin ingin lihat pegawai bidang tertentu saja)
        if ($request->has('bidang_id')) {
            $query->where('bidang_id', $request->bidang_id);
        }

        $users = $query->latest()->paginate(10);
        
        return response()->json($users);
    }

    /**
     * 2. CREATE (Tambah Pegawai Baru)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'nip'           => 'nullable|string|unique:users,nip',
            'password'      => 'required|string|min:6',
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id', // [BARU] Wajib pilih bidang
            'jabatan_id'    => 'required|exists:jabatan,id',
            'role_id'       => 'required|exists:roles,id',
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // [BARU] Validasi Konsistensi: Pastikan Bidang yang dipilih BENAR anak dari Unit Kerja yang dipilih
        // Jangan sampai Unit "Bapenda" tapi Bidangnya "IGD" (milik RSUD)
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

            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'nip'           => $request->nip,
                'password'      => Hash::make($request->password),
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id, // [BARU] Simpan bidang
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
            ]);

            // Assign Role
            $user->roles()->attach($request->role_id);

            DB::commit();

            return response()->json([
                'message' => 'Pegawai berhasil didaftarkan',
                'data'    => $user->load(['roles', 'bidang'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mendaftar', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 3. SHOW (Detail 1 Pegawai)
     */
    public function show($id)
    {
        // [UPDATE] Load bidang
        $user = User::with(['unitKerja', 'bidang', 'jabatan', 'roles', 'atasan', 'bawahan'])->findOrFail($id);
        return response()->json($user);
    }

    /**
     * 4. UPDATE (Edit Data Pegawai)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,'.$id,
            'nip'           => 'nullable|string|unique:users,nip,'.$id,
            'password'      => 'nullable|string|min:6',
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id', // [BARU]
            'jabatan_id'    => 'required|exists:jabatan,id',
            'role_id'       => 'required|exists:roles,id',
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // [BARU] Validasi Konsistensi lagi saat update
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

            $userData = [
                'name'          => $request->name,
                'email'         => $request->email,
                'nip'           => $request->nip,
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id, // [BARU] Update bidang
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            if ($request->has('role_id')) {
                $user->roles()->sync([$request->role_id]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Data pegawai berhasil diperbarui',
                'data'    => $user->load(['roles', 'bidang'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 5. DESTROY (Hapus Pegawai)
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