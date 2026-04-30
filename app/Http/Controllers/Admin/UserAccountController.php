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
     * Menampilkan data akun dengan fitur Server-Side Pagination, Search, dan Filter Role.
     */
    public function index(Request $request)
    {
        // 1. Deteksi Request AJAX (API Fetching)
        if ($request->ajax()) {
            
            // Eager Load Roles & Unit Kerja (Optimasi Query & Mencegah N+1)
            $query = User::with(['roles', 'unitKerja:id,nama_unit']);

            // Filter Search (Berdasarkan Username, Nama, atau NIP)
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('username', 'ilike', "%{$search}%")
                      ->orWhere('name', 'ilike', "%{$search}%")
                      ->orWhere('nip', 'ilike', "%{$search}%");
                });
            }

            // Filter Relasi Role (Mengecek ke tabel Many-to-Many)
            if ($request->filled('role_id')) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('id', $request->input('role_id'));
                });
            }

            // Filter Status Boolean (Active/Suspend)
            if ($request->filled('status')) {
                $isActive = $request->input('status') === 'active';
                $query->where('is_active', $isActive);
            }

            // Integrasi Paginator: Mengambil param dari FE dan merender metadata
            $perPage = $request->input('limit', 10);
            $sortBy = $request->input('sort', 'created_at');
            $sortDir = $request->input('dir', 'desc');

            $paginator = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

            return response()->json($paginator);
        }

        // 2. Mengirim Referensi Data ke Front-End (DOM Initial Load)
        $roles = Role::orderBy('nama_role', 'asc')->get();
        
        return view('admin.akun-pengguna', compact('roles'));
    }

    /**
     * [IT DOMAIN] Update Credentials (Username & Password)
     * Endpoint: PATCH /api/admin/akun/{id}/credentials
     */
    public function updateCredentials(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|alpha_dash|unique:users,username,'.$id,
            'password' => 'nullable|string|min:6|confirmed', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $user->username = $request->username;

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

        // Proteksi Diri: Mencegah Admin yang login menghilangkan role admin-nya sendiri
        if ($user->id === auth()->id() && $user->hasRole('Admin')) {
            // Asumsi method hasRole() tersedia di Trait model User Anda
             return response()->json(['message' => 'Tindakan Ditolak: Anda tidak dapat mengubah hak akses admin pada akun yang sedang Anda gunakan.'], 403);
        }

        try {
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
            return response()->json(['message' => 'Tindakan Ditolak: Anda tidak dapat menangguhkan akun Anda sendiri!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            $user->is_active = $request->is_active;
            $user->save();

            $statusText = $request->is_active ? 'diaktifkan' : 'dinonaktifkan (Suspend)';

            return response()->json([
                'message' => "Status akun berhasil {$statusText}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengubah status akun.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}