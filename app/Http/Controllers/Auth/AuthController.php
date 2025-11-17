<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi input dari Frontend
        // Kita ubah rule validasi: Frontend mengirim 'username', bukan 'email'.
        $validator = Validator::make($request->all(), [
            'username' => 'required|string', // Input field dari form login
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Tentukan tipe kredensial (Fleksibilitas Cerdas)
        // Logika: Jika input seperti email, kita cek ke kolom 'email'.
        // Jika tidak (angka/string biasa), kita anggap itu NIP dan cek ke kolom 'nip'.
        $loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

        // 3. Susun kredensial untuk Auth::attempt
        $credentials = [
            $loginType => $request->username,
            'password' => $request->password
        ];

        // 4. Eksekusi Login
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Kredensial tidak valid (NIP/Email atau Password salah)'], 401);
        }

        // 5. Ambil data User setelah login berhasil
        // Kita cari user berdasarkan kolom yang dipakai login tadi ($loginType)
        $user = User::with(['roles', 'unitKerja', 'jabatan', 'atasan'])
                    ->where($loginType, $request->username)
                    ->firstOrFail();

        // 6. Buat Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    public function user(Request $request)
    {
        // Return user dengan relasi terkini
        return response()->json(
            $request->user()->load(['roles', 'unitKerja', 'jabatan', 'atasan'])
        );
    }
}