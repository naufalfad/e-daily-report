<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User; 
use App\Models\SystemSetting;
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
                return response()->json(['message' => 'Kredensial tidak valid (Pengguna tidak ditemukan)'], 401);
            }

            // 3. Cek password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Kredensial tidak valid (Password salah)'], 401);
            }

            // ======================================================================
            // 4. SECURITY GATE 1: CEK STATUS SUSPEND (is_active)
            // ======================================================================
            if ($user->is_active === false) {
                return response()->json([
                    'message' => 'Akun Anda dinonaktifkan (Suspend). Silakan hubungi Administrator.',
                    'status_code' => 403 
                ], 403);
            }
            
            // ======================================================================
            // 5. SECURITY GATE 2: CEK MAINTENANCE MODE [PERBAIKAN UTAMA]
            // ======================================================================
            $isMaintenance = SystemSetting::where('setting_key', 'maintenance_mode')
                                            ->where('setting_value', '1')
                                            ->exists();

            if ($isMaintenance) {
                if (!$user->hasRole('Super Admin')) {
                    // [PERBAIKAN] Tambahkan URL Redirect ke Response 403
                    return response()->json([
                        'message' => 'Sistem sedang dalam mode pemeliharaan. Akses dibatasi untuk Administrator.',
                        'status_code' => 403,
                        'redirect_url' => route('maintenance') // Kirim URL ke Frontend
                    ], 403);
                }
            }
            
            // 6. Jika Lolos -> Login manual dan buat token
            Auth::login($user);

            $user->load(['roles', 'unitKerja', 'jabatan', 'atasan']);
            $token = $user->createToken('auth_token')->plainTextToken;

            // 7. Respon sukses
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
        // Pengecekan is_active untuk sesi yang sudah ada
        if ($request->user() && $request->user()->is_active === false) {
             return response()->json(['message' => 'Sesi dinonaktifkan.'], 403);
        }
        
        return response()->json(
            $request->user()->load(['roles', 'unitKerja', 'jabatan', 'atasan'])
        );
    }

    /**
     * Alias/helper untuk user data.
     */
    public function me(Request $request)
    {
        // Pengecekan is_active untuk sesi yang sudah ada
        if ($request->user() && $request->user()->is_active === false) {
             return response()->json(['message' => 'Sesi dinonaktifkan.'], 403);
        }
        
        return response()->json($request->user());
    }
}