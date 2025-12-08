<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bidang;
use App\Models\Role; // [BARU] Perlu model Role untuk default role
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * [HR DOMAIN] List Data Pegawai
     * Menampilkan data pegawai beserta struktur jabatannya.
     */
    public function index(Request $request)
    {
        $query = User::with(['unitKerja', 'bidang', 'jabatan', 'roles', 'atasan']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%");
                // Note: Pencarian username dihapus agar fokus pada identitas pegawai (NIP/Nama)
            });
        }

        // Filter Unit Kerja & Bidang (Tetap dipertahankan untuk kebutuhan HR)
        if ($request->has('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        if ($request->has('bidang_id')) {
            $query->where('bidang_id', $request->bidang_id);
        }

        $users = $query->latest()->paginate(10);
        
        return response()->json($users);
    }

    /**
     * [HR DOMAIN] Create Pegawai Baru
     * - Input: Data Diri & Struktur Jabatan.
     * - Logic: Otomatis buat akun dengan Username=NIP & Password=NIP.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data Kepegawaian (HR)
        // Password & Username TIDAK divalidasi dari request karena auto-generate.
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'nip'           => 'required|string|unique:users,nip', // NIP Wajib & Unik
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'atasan_id'     => 'nullable|exists:users,id',
            // 'role_id' dihapus dari request, kita set default 'Staf'
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
                
                // Set Kredensial Otomatis
                'username'      => $defaultUsername, 
                'password'      => $defaultPassword,
                'email'         => null, // Email opsional

                // Struktur Organisasi
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id,
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
            ]);

            // 5. Assign Default Role (Staf)
            // Kita cari Role 'Staf', jika tidak ada, ambil role pertama (fallback)
            $roleStaf = Role::where('nama_role', 'Staf')->first();
            $roleId = $roleStaf ? $roleStaf->id : 1; // Default ID 1 jika Staf tidak ditemukan

            $user->roles()->attach($roleId);

            DB::commit();

            return response()->json([
                'message' => 'Pegawai berhasil didaftarkan. Akun login otomatis dibuat (Username & Password = NIP).',
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
     * - Security: DILARANG memperbarui password, username, atau role di sini.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validasi hanya field HR
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'nip'           => 'required|string|unique:users,nip,'.$id, // Ignore current ID
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'bidang_id'     => 'required|exists:bidang,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'atasan_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // Validasi Bidang vs Unit Kerja (Konsistensi Data)
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

            // Update Data Profil & Struktur Saja
            $user->update([
                'name'          => $request->name,
                'nip'           => $request->nip,
                'unit_kerja_id' => $request->unit_kerja_id,
                'bidang_id'     => $request->bidang_id,
                'jabatan_id'    => $request->jabatan_id,
                'atasan_id'     => $request->atasan_id,
                // SECURITY: Username & Password tidak disentuh di sini
            ]);

            // NOTE: Update Role dihapus dari sini. 
            // Jika pegawai naik jabatan (ganti role), itu dilakukan di modul Akun Pengguna.

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
     * Menghapus data pegawai sekaligus mematikan akunnya.
     */
    public function destroy($id)
    {
        if (auth()->id() == $id) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun sendiri!'], 403);
        }

        try {
            $user = User::findOrFail($id);
            
            // Logic tambahan: Cek apakah user punya laporan penting? 
            // Untuk saat ini kita soft delete atau force delete sesuai kebutuhan.
            // Di sini kita pakai standard delete.
            $user->delete();
            
            return response()->json(['message' => 'Pegawai berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus', 'error' => $e->getMessage()], 500);
        }
    }
}