<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; // Tambahan Helper String

class ProfileController extends Controller
{
    /**
     * Menampilkan Halaman Edit Profil
     * Memuat data user beserta relasi SK (Jabatan, Unit) dan ROLE.
     */
    public function edit()
    {
        // 1. Load User + Relasi Roles (PENTING)
        $user = Auth::user()->load(['jabatan', 'unitKerja', 'atasan', 'roles']);

        // 2. Logika Deteksi Role (Untuk Layout app.blade.php)
        // Ambil role pertama, jika tidak ada default ke 'staf'
        $rawRole = $user->roles->first()->nama_role ?? 'staf';
        
        // Ubah jadi slug lowercase (Contoh: "Kepala Dinas" -> "kepala-dinas")
        $role = Str::slug($rawRole);
        
        // Normalisasi khusus untuk layout (sesuai case di app.blade.php)
        if ($role === 'kepala-dinas') {
            $role = 'kadis';
        }

        // 3. Kirim variable $role ke View
        return view('auth.profile', compact('user', 'role'));
    }

    /**
     * Update Tab 1: Biodata (Foto, Kontak, Alamat)
     */
    public function updateBiodata(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
            'no_telp'     => 'nullable|string|max:20',
            'email'       => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'alamat'      => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $path = $request->file('foto_profil')->store('uploads/profil', 'public');
            $user->foto_profil = $path;
        }

        $user->no_telp = $request->no_telp;
        $user->email   = $request->email;
        $user->alamat  = $request->alamat;
        $user->save();

        return back()->with('success', 'Biodata berhasil diperbarui.');
    }

    /**
     * Update Tab 2: Akun (Username & Password)
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed', 
        ]);

        $user->username = $request->username;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($user->wasChanged('username')) {
            return back()->with('warning', 'Username diperbarui! Gunakan username <b>' . $request->username . '</b> untuk login selanjutnya.');
        }

        return back()->with('success', 'Pengaturan akun berhasil disimpan.');
    }
}