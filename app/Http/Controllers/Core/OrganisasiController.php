<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class OrganisasiController extends Controller
{
    /**
     * Mengambil seluruh pohon struktur organisasi Bapenda.
     * Dimulai dari Kepala Badan (yang 'atasan_id' nya null DAN bukan admin).
     */
    public function getTree(Request $request)
    {
        // Kita cari user yang merupakan pimpinan tertinggi (Kaban)
        // Berdasarkan seeder, kita bisa cari Kaban via email atau NIP-nya.
        // Opsi 1: Cari Kaban (Darius)
        $rootUser = User::where('email', 'darius.rain@bapenda.go.id')
                        ->orWhere('nip', '197301032007011031')
                        ->first();

        // Opsi 2: Cari siapa saja yang tidak punya atasan (kecuali admin)
        if (!$rootUser) {
            $rootUser = User::whereNull('atasan_id')
                            ->whereHas('roles', function($q) {
                                $q->where('nama_role', '!=', 'Super Admin');
                            })
                            ->first();
        }

        if (!$rootUser) {
            return response()->json(['message' => 'User pimpinan (root) tidak ditemukan.'], 404);
        }

        // Ambil data user tersebut, DAN panggil relasi 'bawahanRecursif'
        // yang sudah kita buat di Model User.
        $hierarki = User::where('id', $rootUser->id)
                        ->with(['jabatan', 'bidang', 'bawahanRecursif'])
                        ->first();

        return response()->json($hierarki);
    }
}