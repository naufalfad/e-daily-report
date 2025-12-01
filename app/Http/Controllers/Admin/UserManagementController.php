<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bidang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['unitKerja', 'bidang', 'jabatan', 'roles', 'atasan']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%")
                  ->orWhere('username', 'ilike', "%{$search}%"); // Cari by username juga
            });
        }

        if ($request->has('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        if ($request->has('bidang_id')) {
            $query->where('bidang_id', $request->bidang_id);
        }

        $users = $query->latest()->paginate(10);
        
        return response()->json($users);
    }

    public function store(Request $request)
    {
        // [PERBAIKAN] Hapus validasi email, Ganti ke Username
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:50|unique:users,username', // Wajib Username
            'nip'           => 'nullable|string|unique:users,nip',
            'password'      => 'required|string|min:6',
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'role_id'       => 'required|exists:roles,id',
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

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
                'username'      => $request->username, // Simpan Username
                'email'         => null, // Email dikosongkan atau opsional
                'nip'           => $request->nip,
                'password'      => Hash::make($request->password),
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id,
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
            ]);

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

    public function show($id)
    {
        $user = User::with(['unitKerja', 'bidang', 'jabatan', 'roles', 'atasan', 'bawahan'])->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:50|unique:users,username,'.$id, // Unique ignore ID
            'nip'           => 'nullable|string|unique:users,nip,'.$id,
            'password'      => 'nullable|string|min:6',
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'role_id'       => 'required|exists:roles,id',
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            DB::beginTransaction();

            $userData = [
                'name'          => $request->name,
                'username'      => $request->username, // Update Username
                'nip'           => $request->nip,
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id,
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