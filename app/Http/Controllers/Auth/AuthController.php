<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Pastikan User Model di-import
use Throwable;

class AuthController extends Controller
{
    /**
     * Menangani proses login API dan memberikan token Sanctum.
     */
    public function login(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $username = $request->username;

            // 2. Cari User berdasarkan username
            $user = User::where('username', $username)->first();

            if (!$user) {
                // Gunakan 401 Unauthorized untuk kegagalan kredensial
                return response()->json(['message' => 'Kredensial tidak valid (Pengguna tidak ditemukan)'], 401);
            }

            // 3. Cek password
            if (!Hash::check($request->password, $user->password)) {
                // Gunakan 401 Unauthorized untuk kegagalan password
                return response()->json(['message' => 'Kredensial tidak valid (Password salah)'], 401);
            }

            // ======================================================================
            // 4. SECURITY GATE: CEK STATUS AKTIF (Implementasi Suspend)
            // ======================================================================
            if ($user->is_active === false) {
                // Jika akun dinonaktifkan, blokir login dan kirim kode 403 Forbidden
                return response()->json([
                    'message' => 'Akun Anda dinonaktifkan (Suspend). Silakan hubungi Administrator.',
                    'status_code' => 403 
                ], 403);
            }
            
            // 5. Jika Lolos -> Login manual dan buat token
            Auth::login($user);

            $user->load(['roles', 'unitKerja', 'jabatan', 'atasan']);
            $token = $user->createToken('auth_token')->plainTextToken;

            // 6. Respon sukses
            return response()->json([
                'message' => 'Login berhasil',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'data' => $user
            ]);

        } catch (Throwable $e) {
            \Log::error("Authentication failed: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'Terjadi kesalahan internal saat mencoba login. Silakan coba lagi.',
                'debug' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Menghapus token Sanctum pengguna saat ini.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    /**
     * Mengambil data pengguna saat ini (untuk aplikasi web/client).
     */
    public function user(Request $request)
    {
        return response()->json(
            $request->user()->load(['roles', 'unitKerja', 'jabatan', 'atasan'])
        );
    }

    /**
     * Alias/helper untuk user data (bisa dihapus jika tidak digunakan, tapi kita biarkan).
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}