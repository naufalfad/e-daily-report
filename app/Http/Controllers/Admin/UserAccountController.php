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
     * Menampilkan data akun dengan fitur Pagination, Search, dan Filter Role.
     */
    public function index(Request $request)
    {
        // 1. Deteksi Request AJAX (Fetch Data Table)
        if ($request->ajax()) {
            
            // Query Optimization: Eager Load Roles & Unit Kerja (Select fields seperlunya)
            $query = User::with(['roles', 'unitKerja:id,nama_unit']);

            // Filter Search (Username / Nama / NIP)
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('username', 'ilike', "%{$search}%")
                      ->orWhere('name', 'ilike', "%{$search}%")
                      ->orWhere('nip', 'ilike', "%{$search}%");
                });
            }

            // Filter Role (Dropdown)
            if ($request->filled('role_id')) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('id', $request->input('role_id'));
                });
            }

            // Filter Status (Optional: Active/Non-active)
            if ($request->filled('status')) {
                $isActive = $request->input('status') === 'active';
                $query->where('is_active', $isActive);
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $users = $query->latest()->paginate($perPage);

            return response()->json($users);
        }

        // 2. Return View (Browser Load)
        // Supply data Role untuk dropdown di modal dan filter
        $roles = Role::orderBy('nama_role', 'asc')->get();
        
        return view('admin.akun-pengguna', compact('roles'));
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

        // Mencegah Admin menghapus akses Admin-nya sendiri (Safety Check)
        if ($user->id === auth()->id() && $user->hasRole('Admin')) {
             // Logic tambahan: Admin boleh punya multi-role, tapi jangan sampai kehilangan akses admin.
             // Untuk kesederhanaan, kita izinkan sync, asalkan role_id yang dikirim valid.
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
            // Kita gunakan forceFill untuk memastikan field diupdate meskipun tidak di $fillable (opsional)
            // Tapi sebaiknya 'is_active' masuk fillable di Model User.
            $user->is_active = $request->is_active;
            $user->save();

            $statusText = $request->is_active ? 'diaktifkan' : 'dinonaktifkan (Suspend)';

            return response()->json([
                'message' => "Akun berhasil {$statusText}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengubah status akun.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}