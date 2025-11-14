<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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
        // Eager Load relasi agar hemat query
        $query = User::with(['unitKerja', 'jabatan', 'roles', 'atasan']);

        // Filter Pencarian (Nama / NIP)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%");
            });
        }

        // Filter per Unit Kerja (Opsional)
        if ($request->has('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        // Urutkan dari yang paling baru dibuat
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
            'jabatan_id'    => 'required|exists:jabatan,id',
            'role_id'       => 'required|exists:roles,id',
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'nip'           => $request->nip,
                'password'      => Hash::make($request->password),
                'unit_kerja_id' => $request->unit_kerja_id,
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
                // 'foto_profil' bisa ditambahkan nanti jika ada upload file
            ]);

            // Assign Role
            $user->roles()->attach($request->role_id);

            DB::commit();

            return response()->json([
                'message' => 'Pegawai berhasil didaftarkan',
                'data'    => $user->load('roles')
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
        $user = User::with(['unitKerja', 'jabatan', 'roles', 'atasan', 'bawahan'])->findOrFail($id);
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
            // Perhatikan: unique ignore ID user yang sedang diedit
            'email'         => 'required|email|unique:users,email,'.$id,
            'nip'           => 'nullable|string|unique:users,nip,'.$id,
            
            // Password nullable (hanya diisi jika ingin ganti password)
            'password'      => 'nullable|string|min:6',
            
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'role_id'       => 'required|exists:roles,id',
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            DB::beginTransaction();

            // Update data dasar
            $userData = [
                'name'          => $request->name,
                'email'         => $request->email,
                'nip'           => $request->nip,
                'unit_kerja_id' => $request->unit_kerja_id,
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
            ];

            // Cek apakah password diganti?
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Update Role (Gunakan sync agar role lama terhapus & diganti yang baru)
            if ($request->has('role_id')) {
                $user->roles()->sync([$request->role_id]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Data pegawai berhasil diperbarui',
                'data'    => $user->load('roles')
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
        // Jangan biarkan user menghapus dirinya sendiri
        if (auth()->id() == $id) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun sendiri!'], 403);
        }

        try {
            $user = User::findOrFail($id);
            
            // Hapus user
            // Note: Karena di migration kita pakai ON DELETE CASCADE/SET NULL
            // Data di tabel lain (LKH, user_roles) aman terhapus/terupdate otomatis.
            $user->delete();

            return response()->json(['message' => 'Pegawai berhasil dihapus']);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus', 'error' => $e->getMessage()], 500);
        }
    }
}