<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Throwable;

class AuthController extends Controller
{
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

            // 4. Login manual (untuk session, optional)
            Auth::login($user);

            // 5. Buat token API (Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            // 6. Muat relasi yang dibutuhkan
            $user->load(['roles', 'unitKerja', 'jabatan', 'atasan']);

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

    public function logout(Request $request)
    {
        // Hapus token saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        // Kembalikan user dengan relasi
        $user = $request->user()->load(['roles', 'unitKerja', 'jabatan', 'atasan']);
        return response()->json($user);
    }

    // Optional: alias untuk /me
    public function user(Request $request)
    {
        return $this->me($request);
    }
}
