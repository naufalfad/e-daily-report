<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SkpRencana; // Model Parent Baru
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;

class SkpController extends Controller
{
    /**
     * GET List SKP (Rencana)
     * Menampilkan daftar rencana kerja beserta target-targetnya.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil Rencana beserta Target-nya (Eager Loading 'targets')
        $query = SkpRencana::with('targets')
            ->where('user_id', $user->id)
            ->latest();

        // Filter Tahun (Opsional)
        if ($request->has('year')) {
            $query->whereYear('periode_awal', $request->year);
        }

        $data = $query->get();

        return response()->json([
            'message' => 'Data SKP berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * STORE SKP Baru (Parent + Children Transaction)
     * Menerima input Header Rencana dan Array Target sekaligus.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Kompleks (Header + Array Targets)
        $validator = Validator::make($request->all(), [
            // Validasi Header (Rencana)
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
            'rhk_intervensi' => 'required|string', // Input Manual RHK Atasan
            'rencana_hasil_kerja' => 'required|string', // Input Manual RHK Sendiri

            // Validasi Detail Targets (Array of Objects)
            'targets' => 'required|array|min:1', // Minimal ada 1 target
            'targets.*.jenis_aspek' => 'required|in:Kuantitas,Kualitas,Waktu,Biaya',
            'targets.*.indikator' => 'required|string',
            'targets.*.target' => 'required|integer',
            'targets.*.satuan' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $rencana = SkpRencana::create([
                'user_id' => Auth::id(),
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'rhk_intervensi' => $request->rhk_intervensi,
                'rencana_hasil_kerja' => $request->rencana_hasil_kerja,
            ]);

            foreach ($request->targets as $item) {
                $rencana->targets()->create([
                    'jenis_aspek' => $item['jenis_aspek'],
                    'indikator' => $item['indikator'],
                    'target' => $item['target'],
                    'satuan' => $item['satuan'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Rencana SKP berhasil dibuat',
                'data' => $rencana->load('targets')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // PENTING: Log error ke file storage/logs/laravel.log
            Log::error('Gagal Simpan SKP: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan Server',
                'debug_error' => $e->getMessage() // Kirim pesan error ke frontend supaya bisa dibaca di Swal
            ], 500);
        }
    }

    /**
     * SHOW Detail SKP
     */
    public function show($id)
    {
        $rencana = SkpRencana::with('targets')
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$rencana)
            return response()->json(['message' => 'Data tidak ditemukan'], 404);

        return response()->json(['data' => $rencana]);
    }

    /**
     * UPDATE SKP
     * Strategi: Update Header -> Hapus Target Lama -> Insert Target Baru
     */
    public function update(Request $request, $id)
    {
        $rencana = SkpRencana::where('user_id', Auth::id())->find($id);
        if (!$rencana)
            return response()->json(['message' => 'Data tidak ditemukan'], 404);

        // Validasi
        $validator = Validator::make($request->all(), [
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
            'rhk_intervensi' => 'required|string',
            'rencana_hasil_kerja' => 'required|string',
            'targets' => 'required|array|min:1',
            'targets.*.jenis_aspek' => 'required|in:Kuantitas,Kualitas,Waktu,Biaya',
            'targets.*.indikator' => 'required|string',
            'targets.*.target' => 'required|integer',
            'targets.*.satuan' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Update Header
            $rencana->update([
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'rhk_intervensi' => $request->rhk_intervensi,
                'rencana_hasil_kerja' => $request->rencana_hasil_kerja,
            ]);

            // 2. Reset Target (Hapus semua child lama)
            $rencana->targets()->delete();

            // 3. Insert Target Baru
            foreach ($request->targets as $item) {
                $rencana->targets()->create([
                    'jenis_aspek' => $item['jenis_aspek'],
                    'indikator' => $item['indikator'],
                    'target' => $item['target'],
                    'satuan' => $item['satuan'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Rencana SKP berhasil diperbarui',
                'data' => $rencana->load('targets')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui SKP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE SKP
     */
    public function destroy($id)
    {
        $rencana = SkpRencana::where('user_id', Auth::id())->find($id);
        if (!$rencana)
            return response()->json(['message' => 'Data tidak ditemukan'], 404);

        try {
            $rencana->delete(); // Cascade delete akan menghapus targets otomatis

            return response()->json(['message' => 'SKP berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus', 'error' => $e->getMessage()], 500);
        }
    }

    // --- Helper / Placeholder untuk fitur Skoring (jika diperlukan nanti) ---
    /**
     * API: Data Skoring Kinerja (Revisi: Unit Kerja diutamakan)
     * Rumus: (Approved / (Total Uploaded - Draft)) * 100
     */
    public function getSkoringData(Request $request)
    {
        $user = Auth::user();

        // 1. Cari Bawahan (User yang atasan_id-nya adalah user login)
        $bawahan = \App\Models\User::where('atasan_id', $user->id)->get();

        $data = $bawahan->map(function ($staff) {
            // 2. Ambil Rencana SKP Terbaru milik staf ini
            $rencana = SkpRencana::with([
                'targets' => function ($q) {
                    $q->where('jenis_aspek', 'Kuantitas');
                }
            ])
                ->where('user_id', $staff->id)
                ->latest()
                ->first();

            if (!$rencana) {
                return [
                    'id' => $staff->id,
                    'nama' => $staff->name,
                    'nip' => $staff->nip,
                    'status' => 'Belum buat SKP',
                    'capaian' => 0
                ];
            }

            // 3. Ambil Target Kuantitas
            $targetObj = $rencana->targets->first();
            $targetAngka = $targetObj ? $targetObj->target : 0;
            $satuan = $targetObj ? $targetObj->satuan : '-';

            // 3. Hitung Pembilang: Total LKH yang DISETUJUI (Approved)
            $totalApproved = \App\Models\LaporanHarian::where('user_id', $staff->id)
                ->where('status', 'approved')
                ->count();

            // 4. Kalkulasi Persentase
            $skor = $totalSubmitted > 0 
                ? round(($totalApproved / $totalSubmitted) * 100) 
                : 0;

            // 5. Tentukan Predikat
            $predikat = 'Kurang';
            if ($skor >= 90) $predikat = 'Sangat Baik';
            else if ($skor >= 75) $predikat = 'Baik';
            else if ($skor >= 60) $predikat = 'Cukup';

            // 6. Return Data Structure
            return [
                'id' => $staff->id,
                'nama' => $staff->name,
                'nip'  => $staff->nip,
                'foto' => $staff->foto_profil_url ?? asset('assets/icon/avatar.png'),
                
                // [FIX] Masukkan UNIT KERJA ke key 'rhk' 
                // Karena JS di frontend (skoring-kinerja.js) merender kolom ke-2 menggunakan key 'rhk'
                'rhk' => $staff->unitKerja->nama_unit ?? 'Non-Unit', 
                
                // Statistik LKH
                'target' => $totalSubmitted,   // Total yang diajukan
                'realisasi' => $totalApproved, // Total yang di-approve
                'satuan' => 'LKH',
                
                // Hasil Akhir
                'capaian' => $skor,
                'predikat' => $predikat
            ];
        });

        return response()->json([
            'message' => 'Data skoring kinerja berhasil diambil',
            'data' => $data
        ]);
    }

    public function exportPdf()
    {
        $user = auth()->user();

        $skp = SkpRencana::where('user_id', $user->id)
            ->with('targets')
            ->get();

        $penilai = User::find($user->atasan_id);

        $periode_awal = $skp->first()?->periode_awal ?? null;
        $periode_akhir = $skp->first()?->periode_akhir ?? null;

        $pdf = Pdf::loadView('pdf.skp-export', [
            'user' => $user,
            'penilai' => $penilai,
            'skp' => $skp,
            'periode_awal' => $periode_awal,
            'periode_akhir' => $periode_akhir,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('SKP_' . $user->name . '.pdf');
    }

}