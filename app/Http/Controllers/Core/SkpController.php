<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Skp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SkpController extends Controller
{
    // --- CRUD METHODS (STANDAR) ---

    public function index(Request $request)
    {
        $query = Skp::where('user_id', Auth::id());
        if ($request->has('year')) $query->whereYear('periode_mulai', $request->year);
        return response()->json(['message' => 'List SKP berhasil diambil', 'data' => $query->latest()->get()]);
    }

	public function store(Request $request)
    {
        // 1. Definisikan Rule Validasi
        $validator = Validator::make($request->all(), [
            'nama_skp'        => 'required|string|max:255',
            'periode_mulai'   => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            'rencana_aksi'    => 'required|string', 
            'indikator'       => 'required|string', 
            'target'          => 'required|integer|min:1',
            // Hapus 'satuan' jika memang sudah didrop dari DB, atau tambahkan jika perlu
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            // [PERBAIKAN DISINI]
            // Gunakan $validator->validated() agar HANYA data yang valid yang diambil.
            // Data sampah seperti '/api/skp' akan otomatis terbuang.
            $validatedData = $validator->validated();
            
            // Tambahkan User ID
            $validatedData['user_id'] = Auth::id();

            // Create menggunakan data bersih
            $skp = Skp::create($validatedData);

            return response()->json(['message' => 'SKP berhasil dibuat', 'data' => $skp], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat SKP', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $skp = Skp::where('user_id', Auth::id())->find($id);
        return $skp ? response()->json(['data' => $skp]) : response()->json(['message' => 'SKP tidak ditemukan'], 404);
    }

    public function update(Request $request, $id)
    {
        $skp = Skp::where('user_id', Auth::id())->find($id);
        if (!$skp) return response()->json(['message' => 'SKP tidak ditemukan'], 403);

        $skp->update($request->except(['satuan'])); 
        return response()->json(['message' => 'SKP berhasil diperbarui', 'data' => $skp]);
    }

    public function destroy($id)
    {
        $skp = Skp::where('user_id', Auth::id())->find($id);
        if (!$skp) return response()->json(['message' => 'SKP tidak ditemukan'], 403);
        $skp->delete();
        return response()->json(['message' => 'SKP berhasil dihapus']);
    }

    // --- FITUR UTAMA: SKORING KINERJA ---

    /**
     * Halaman Skoring Kinerja (DATA BAWAHAN ONLY)
     * Menggunakan Logika Unit Kerja & Tingkat Jabatan
     */
    public function skoringKinerja(Request $request)
    {
        // A. JIKA REQUEST AJAX (API JSON UNTUK JS)
        if ($request->ajax() || $request->wantsJson()) {
            
            $user = Auth::user()->load(['jabatan', 'unitKerja']);
            $jabatanName = strtolower($user->jabatan->nama_jabatan ?? '');
            
            // Query Dasar
            $query = User::query()
                        ->where('id', '!=', $user->id)
                        ->where('unit_kerja_id', $user->unit_kerja_id);

            // --- LOGIKA HIERARKI (Tetap Sama) ---
            if (str_contains($jabatanName, 'kepala badan') || 
                str_contains($jabatanName, 'kepala dinas') || 
                str_contains($jabatanName, 'sekretaris')) {
                // All unit
            } elseif (str_contains($jabatanName, 'kepala bidang')) {
                $query->where(function($q) use ($user) {
                    $q->where('atasan_id', $user->id)
                      ->orWhereIn('atasan_id', function($sq) use ($user) { 
                          $sq->select('id')->from('users')->where('atasan_id', $user->id);
                      });
                });
            } elseif (str_contains($jabatanName, 'kepala sub')) {
                $query->where('atasan_id', $user->id);
            } else {
                $query->where('id', 0);
            }

            $allSubordinates = $query->with(['unitKerja', 'jabatan', 'laporanHarian'])
                                     ->orderBy('name', 'asc')
                                     ->get();

            // --- [PERBAIKAN LOGIKA DISINI] ---
            $listBawahan = $allSubordinates->map(function($pegawai) {
                
                // 1. Ambil Laporan yang statusnya VALID saja (Bukan Draft)
                // Filter di level Collection Laravel (karena data sudah di-eager load)
                $lkhValid = $pegawai->laporanHarian->filter(function ($lkh) {
                    return in_array($lkh->status, ['approved', 'rejected', 'waiting_review']);
                });

                // 2. Hitung Total yang menjadi Pembagi (Hanya Approved, Rejected, Waiting)
                $totalLkh = $lkhValid->count();

                // 3. Hitung yang Disetujui
                $approvedLkh = $lkhValid->where('status', 'approved')->count();
                
                // 4. Hitung Skor
                // Sekarang 'Draft' tidak akan membuat skor menjadi kecil
                $skor = $totalLkh > 0 
                    ? round(($approvedLkh / $totalLkh) * 100, 1) 
                    : 0;

                $predikat = match(true) {
                    $skor >= 90 => 'Sangat Baik',
                    $skor >= 80 => 'Baik',
                    $skor >= 70 => 'Cukup',
                    default => 'Kurang'
                };

                return [
                    'id' => $pegawai->id,
                    'name' => $pegawai->name,
                    'jabatan' => $pegawai->jabatan->nama_jabatan ?? '-',
                    'unit_kerja' => $pegawai->unitKerja->nama_unit ?? '-',
                    'total_lkh' => $totalLkh, // Ini sekarang angka Murni (Tanpa Draft)
                    'approved_lkh' => $approvedLkh,
                    'total_nilai' => $skor,
                    'predikat' => $predikat,
                    'avatar_url' => $pegawai->foto_profil 
                        ? asset('storage/' . $pegawai->foto_profil) 
                        : asset('assets/icon/avatar.png')
                ];
            });

            return response()->json([
                'status' => 'success',
                'viewer_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'jabatan' => $user->jabatan->nama_jabatan,
                    'role_detected' => $jabatanName
                ],
                'kinerja_bawahan' => $listBawahan 
            ]);
        }

        return view('penilai.skoring-kinerja');
    }
}
