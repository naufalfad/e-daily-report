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
        $validator = Validator::make($request->all(), [
            'nama_skp'        => 'required|string|max:255',
            'periode_mulai'   => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            'rencana_aksi'    => 'required|string', 
            'indikator'       => 'required|string', 
            'target'          => 'required|integer|min:1',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        try {
            $skp = Skp::create(array_merge($request->all(), ['user_id' => Auth::id()]));
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
            
            // Query Dasar: Cari User di Unit Kerja yang sama, KECUALI diri sendiri
            // Ini filter pertama yang sangat penting (Organizational Unit Filter)
            $query = User::query()
                        ->where('id', '!=', $user->id)
                        ->where('unit_kerja_id', $user->unit_kerja_id);

            // --- LOGIKA HIERARKI (Sesuai Titah Paduka) ---
            
            // Skenario 1: KADIS / KABAN / SEKRETARIS (Level Pimpinan Puncak Unit)
            // Melihat SEMUA pegawai di Unit/Dinasnya
            if (str_contains($jabatanName, 'kepala badan') || 
                str_contains($jabatanName, 'kepala dinas') || 
                str_contains($jabatanName, 'sekretaris')) {
                // Tidak perlu filter atasan_id, karena sudah difilter by unit_kerja_id
            }
            
            // Skenario 2: KEPALA BIDANG (KABID)
            // Melihat Kasubid (Bawahan Langsung) & Staf di Bidangnya (Cucu Buah)
            elseif (str_contains($jabatanName, 'kepala bidang')) {
                $query->where(function($q) use ($user) {
                    $q->where('atasan_id', $user->id) // Level 1: Anak Buah Langsung
                      ->orWhereIn('atasan_id', function($sq) use ($user) { 
                          $sq->select('id')->from('users')->where('atasan_id', $user->id); // Level 2: Cucu Buah
                      });
                });
            }
            
            // Skenario 3: KASUBID / KASUBAG (Level Pengawas)
            // Hanya melihat Staf langsung di bawahnya
            elseif (str_contains($jabatanName, 'kepala sub')) {
                $query->where('atasan_id', $user->id);
            }
            
            // Skenario Default (Staf/Lainnya)
            // Tidak punya bawahan
            else {
                $query->where('id', 0); // Force result kosong
            }

            // Eksekusi Query & Load Relasi LKH untuk hitung skor
            $allSubordinates = $query->with(['unitKerja', 'jabatan', 'laporanHarian'])
                                     ->orderBy('name', 'asc')
                                     ->get();

            // --- MAPPING & CALCULATING SKOR ---
            $listBawahan = $allSubordinates->map(function($pegawai) {
                $totalLkh = $pegawai->laporanHarian->count();
                $approvedLkh = $pegawai->laporanHarian->where('status', 'approved')->count();
                
                // Rumus: (Disetujui / Total) * 100
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
                    'total_lkh' => $totalLkh,
                    'approved_lkh' => $approvedLkh,
                    'total_nilai' => $skor,
                    'predikat' => $predikat,
                    'avatar_url' => $pegawai->foto_profil 
                        ? asset('storage/' . $pegawai->foto_profil) 
                        : asset('assets/icon/avatar.png')
                ];
            });

            // --- OUTPUT JSON TERPISAH (SESUAI PERMINTAAN) ---
            return response()->json([
                'status' => 'success',
                // INFO 1: Data Diri (Hanya untuk Debug/Header)
                'viewer_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'jabatan' => $user->jabatan->nama_jabatan,
                    'role_detected' => $jabatanName
                ],
                // INFO 2: Data Bawahan (Ini yang dipakai tabel JS)
                'kinerja_bawahan' => $listBawahan 
            ]);
        }

        // B. JIKA REQUEST BROWSER (View HTML)
        return view('penilai.skoring-kinerja');
    }
}