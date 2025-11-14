<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PengumumanController extends Controller
{
    /**
     * 1. LIST PENGUMUMAN (Untuk Dashboard Pegawai)
     * Menampilkan pengumuman yang relevan dengan User yang login
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Logika: Tampilkan pengumuman yang dibuat untuk Unit Kerja saya, 
        // atau pengumuman Global (unit_kerja_id NULL)
        $query = Pengumuman::with('creator')
            ->where(function($q) use ($user) {
                $q->where('unit_kerja_id', $user->unit_kerja_id)
                  ->orWhereNull('unit_kerja_id'); // Global
            });

        $data = $query->latest()->paginate(5);

        return response()->json($data);
    }

    /**
     * 2. CREATE PENGUMUMAN (Khusus Atasan/Admin)
     */
    public function store(Request $request)
    {
        // Cek Role: Hanya Admin atau Penilai/Kadis yang boleh buat
        // (Logic sederhana: Staff biasa tidak boleh)
        $user = Auth::user();
        if ($user->roles->contains('nama_role', 'Pegawai') && count($user->roles) == 1) {
            return response()->json(['message' => 'Anda tidak memiliki akses membuat pengumuman'], 403);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi_pengumuman' => 'required|string',
            'unit_kerja_id' => 'nullable|exists:unit_kerja,id' // Null = Global
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $pengumuman = Pengumuman::create([
            'user_id_creator' => $user->id,
            'judul' => $request->judul,
            'isi_pengumuman' => $request->isi_pengumuman,
            'unit_kerja_id' => $request->unit_kerja_id, // Opsional: target spesifik unit
        ]);
        
        // TODO: Di sini nanti kita panggil fungsi Notifikasi Otomatis (Trigger)
        // Agar masuk ke notif bar semua pegawai terkait.

        return response()->json([
            'message' => 'Pengumuman berhasil dibuat',
            'data' => $pengumuman
        ], 201);
    }

    /**
     * 3. DELETE PENGUMUMAN
     */
    public function destroy($id)
    {
        $pengumuman = Pengumuman::where('user_id_creator', Auth::id())->find($id);
        
        if (!$pengumuman) return response()->json(['message' => 'Pengumuman tidak ditemukan'], 404);
        
        $pengumuman->delete();
        return response()->json(['message' => 'Pengumuman dihapus']);
    }
}