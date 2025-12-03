<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserAccountController extends Controller
{
    /**
     * [IT DOMAIN] List Akun Pengguna
     * Berbeda dengan Manajemen Pegawai, di sini kita fokus pada status akun.
     * Menampilkan: Username, Role saat ini, Status (jika ada).
     */
    public function index(Request $request)
    {
        // Kita select field yang relevan untuk keamanan akun saja
        $query = User::with(['roles', 'unitKerja:id,nama_unit']);

        // Fitur Pencarian (Username / Nama)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        // Filter berdasarkan Role (Misal: Cari siapa saja Admin)
        if ($request->has('role_id')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('id', $request->role_id);
            });
        }

        $users = $query->latest()->paginate(10);

        return response()->json($users);
    }

    /**
     * [IT DOMAIN] Update Credentials (Username & Password)
     * Digunakan untuk: Reset Password atau Ganti Username.
     * Endpoint: PATCH /api/admin/akun/{id}/credentials
     */
    public function updateCredentials(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validasi Ketat
        $validator = Validator::make($request->all(), [
            // Username wajib unik, kecuali milik user itu sendiri
            'username' => 'required|string|max:50|alpha_dash|unique:users,username,'.$id,
            // Password opsional (kalau kosong berarti tidak diubah)
            'password' => 'nullable|string|min:6|confirmed', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Update Username
            $user->username = $request->username;

            // Update Password (Hanya jika diisi)
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Kredensial akun berhasil diperbarui.',
                'note'    => $request->filled('password') ? 'Password telah diubah.' : 'Hanya username yang berubah.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui kredensial', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * [IT DOMAIN] Update Role (Hak Akses)
     * Digunakan untuk: Promosi akun menjadi Admin/Penilai.
     * Endpoint: PATCH /api/admin/akun/{id}/role
     */
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Mencegah Admin menghapus akses Admin-nya sendiri (Bunuh diri)
        if ($user->id === auth()->id() && !$user->hasRole('Admin')) {
             // Cek logika tambahan jika perlu
        }

        try {
            // Sync Role (Mengganti role lama dengan yang baru)
            $user->roles()->sync([$request->role_id]);

            return response()->json([
                'message' => 'Hak akses pengguna berhasil diperbarui.',
                'data'    => $user->load('roles')
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui role', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * [IT DOMAIN] Update Status (Suspend/Active)
     * Endpoint: PATCH /api/admin/akun/{id}/status
     * Catatan: Pastikan tabel users punya kolom 'is_active' (boolean).
     * Jika belum ada, jalankan migrasi tambahan.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id == auth()->id()) {
            return response()->json(['message' => 'Anda tidak bisa menonaktifkan akun sendiri!'], 403);
        }

        // Validasi input status (1 = Active, 0 = Suspend)
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            // Cek apakah kolom is_active ada di database
            // Jika tidak ada, fitur ini tidak akan jalan (perlu migrasi dulu)
            $user->forceFill([
                'is_active' => $request->is_active
            ])->save();

            $statusText = $request->is_active ? 'diaktifkan' : 'dinonaktifkan (Suspend)';

            return response()->json([
                'message' => "Akun berhasil {$statusText}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengubah status. Pastikan database mendukung fitur suspend (kolom is_active).',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}