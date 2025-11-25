<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Skp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SkpController extends Controller
{
    /**
     * 1. READ (List SKP milik User yang sedang login)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Skp::where('user_id', $userId);

        if ($request->has('year')) {
            $query->whereYear('periode_mulai', $request->year);
        }

        $skp = $query->latest()->get();

        return response()->json([
            'message' => 'List SKP berhasil diambil',
            'data' => $skp
        ]);
    }

    /**
     * 2. CREATE (Input SKP Baru - Akumulasi Target)
     */
    public function store(Request $request)
    {
        // Validasi: 'satuan' dihapus
        $validator = Validator::make($request->all(), [
            'nama_skp'        => 'required|string|max:255',
            'periode_mulai'   => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            
            // Rencana aksi & Indikator tetap ada sebagai deskripsi target
            'rencana_aksi'    => 'required|string', 
            'indikator'       => 'required|string', 
            
            // Target hanya angka (akumulasi)
            'target'          => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $skp = Skp::create([
                'user_id'         => Auth::id(),
                'nama_skp'        => $request->nama_skp,
                'periode_mulai'   => $request->periode_mulai,
                'periode_selesai' => $request->periode_selesai,
                'rencana_aksi'    => $request->rencana_aksi,
                'indikator'       => $request->indikator,
                'target'          => $request->target,
                // 'satuan' dihapus
            ]);

            return response()->json([
                'message' => 'SKP berhasil dibuat',
                'data'    => $skp
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat SKP', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. SHOW (Detail 1 SKP)
     */
    public function show($id)
    {
        $skp = Skp::where('user_id', Auth::id())->find($id);

        if (!$skp) {
            return response()->json(['message' => 'SKP tidak ditemukan atau bukan milik Anda'], 404);
        }

        return response()->json(['data' => $skp]);
    }

    /**
     * 4. UPDATE (Edit SKP)
     */
    public function update(Request $request, $id)
    {
        $skp = Skp::where('user_id', Auth::id())->find($id);

        if (!$skp) {
            return response()->json(['message' => 'SKP tidak ditemukan atau akses ditolak'], 403);
        }

        // Validasi Update: 'satuan' dihapus
        $validator = Validator::make($request->all(), [
            'nama_skp'        => 'sometimes|required|string|max:255',
            'periode_mulai'   => 'sometimes|required|date',
            'periode_selesai' => 'sometimes|required|date|after_or_equal:periode_mulai',
            'rencana_aksi'    => 'sometimes|required|string',
            'indikator'       => 'sometimes|required|string',
            'target'          => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update data (Eloquent akan otomatis mengabaikan field yang tidak ada di request)
        $skp->update($request->except(['satuan'])); 

        return response()->json([
            'message' => 'SKP berhasil diperbarui',
            'data'    => $skp
        ]);
    }

    /**
     * 5. DESTROY (Hapus SKP)
     */
    public function destroy($id)
    {
        $skp = Skp::where('user_id', Auth::id())->find($id);

        if (!$skp) {
            return response()->json(['message' => 'SKP tidak ditemukan atau akses ditolak'], 403);
        }

        $skp->delete();

        return response()->json(['message' => 'SKP berhasil dihapus']);
    }

    /**
     * 6. HALAMAN SKORING KINERJA (View)
     * Perbaikan: Hybrid Logic (Hierarki + History Validasi)
     */
    public function skoringKinerja()
    {
        $userId = Auth::id();

        // --- 1. CARI ID BAWAHAN (HIERARKI) ---
        // Kita pakai cara manual level 1-3 biar lebih 'bandel' daripada fungsi rekursif
        $hierarchyIds = collect([]);

        // Level 1: Bawahan Langsung (Anak)
        $level1 = \App\Models\User::where('atasan_id', $userId)->pluck('id');
        $hierarchyIds = $hierarchyIds->merge($level1);

        // Level 2: Bawahan dari Bawahan (Cucu) - misal untuk Kabid/Kaban
        if ($level1->isNotEmpty()) {
            $level2 = \App\Models\User::whereIn('atasan_id', $level1)->pluck('id');
            $hierarchyIds = $hierarchyIds->merge($level2);

            // Level 3: Cicit - misal untuk Kaban/Kadis
            if ($level2->isNotEmpty()) {
                $level3 = \App\Models\User::whereIn('atasan_id', $level2)->pluck('id');
                $hierarchyIds = $hierarchyIds->merge($level3);
            }
        }

        // --- 2. CARI ID BAWAHAN (HISTORY LAPORAN) ---
        // INI PENTING: Ambil juga user yang pernah kirim laporan ke kita (yg kita validasi)
        // Jadi kalau struktur data user salah, tapi dia lapor ke kita, tetap dianggap bawahan.
        $reportingIds = \App\Models\LaporanHarian::where('atasan_id', $userId)
                            ->distinct()
                            ->pluck('user_id');

        // --- 3. GABUNGKAN SEMUA ID (UNIQUE) ---
        $allSubordinateIds = $hierarchyIds->merge($reportingIds)->unique()->values();

        // --- 4. QUERY DATA FINAL ---
        $query = \App\Models\User::with(['unitKerja', 'jabatan', 'roles']) // Roles plural aman
                    ->whereIn('id', $allSubordinateIds);

        // Ambil data untuk kalkulasi
        $allSubordinates = $query->get();

        // --- LOGIC PERHITUNGAN (SIMULASI) ---
        $processedData = $allSubordinates->map(function($pegawai) {
            // Nanti ganti ini dengan real query sum(skp) dll
            $pegawai->skor_skp = rand(75, 95); 
            $pegawai->skor_lkh = rand(70, 98);
            $pegawai->total_nilai = ($pegawai->skor_skp * 0.6) + ($pegawai->skor_lkh * 0.4);
            
            if ($pegawai->total_nilai >= 90) $pegawai->predikat = 'Sangat Baik';
            elseif ($pegawai->total_nilai >= 80) $pegawai->predikat = 'Baik';
            elseif ($pegawai->total_nilai >= 70) $pegawai->predikat = 'Cukup';
            else $pegawai->predikat = 'Kurang';

            $pegawai->nama_unit = $pegawai->unitKerja->nama ?? '-';
            return $pegawai;
        });

        // --- DATA VISUALISASI ---
        $totalPegawai = $processedData->count();
        $avgScore = $totalPegawai > 0 ? $processedData->avg('total_nilai') : 0;
        $countSangatBaik = $processedData->where('predikat', 'Sangat Baik')->count();
        $countPerluPembinaan = $processedData->whereIn('predikat', ['Kurang', 'Sangat Kurang'])->count();

        $chartData = [
            'predikat' => [
                'labels' => ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'],
                'data' => [
                    $processedData->where('predikat', 'Sangat Baik')->count(),
                    $processedData->where('predikat', 'Baik')->count(),
                    $processedData->where('predikat', 'Cukup')->count(),
                    $processedData->where('predikat', 'Kurang')->count(),
                ]
            ],
            'unit' => [
                'labels' => $processedData->pluck('nama_unit')->unique()->values()->all(),
                'data' => $processedData->groupBy('nama_unit')->map->avg('total_nilai')->values()->all(),
            ]
        ];

        // --- PAGINATION MANUAL ---
        $page = request()->get('page', 1);
        $perPage = 10;
        $dataPegawai = new \Illuminate\Pagination\LengthAwarePaginator(
            $processedData->forPage($page, $perPage),
            $totalPegawai,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('penilai.skoring-kinerja', compact(
            'dataPegawai', 
            'totalPegawai', 
            'avgScore', 
            'countSangatBaik', 
            'countPerluPembinaan',
            'chartData'
        ));
    }
}