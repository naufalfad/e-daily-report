<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SkpRencana; // Model Parent Baru
use App\Models\SkpTarget;  // Model Child Baru
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            'periode_awal'   => 'required|date',
            'periode_akhir'  => 'required|date|after_or_equal:periode_awal',
            'rhk_intervensi' => 'required|string', // Input Manual RHK Atasan
            'rencana_hasil_kerja' => 'required|string', // Input Manual RHK Sendiri
            
            // Validasi Detail Targets (Array of Objects)
            'targets'        => 'required|array|min:1', // Minimal ada 1 target
            'targets.*.jenis_aspek' => 'required|in:Kuantitas,Kualitas,Waktu,Biaya',
            'targets.*.indikator'   => 'required|string',
            'targets.*.target'      => 'required|integer',
            'targets.*.satuan'      => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Gunakan Transaksi DB: Semua tersimpan atau tidak sama sekali
            DB::beginTransaction();

            // 2. Simpan Header (Tabel skp_rencana)
            $rencana = SkpRencana::create([
                'user_id' => Auth::id(),
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'rhk_intervensi' => $request->rhk_intervensi,
                'rencana_hasil_kerja' => $request->rencana_hasil_kerja,
            ]);

            // 3. Simpan Detail (Tabel skp_target)
            // Looping array targets dari request frontend
            foreach ($request->targets as $item) {
                $rencana->targets()->create([
                    'jenis_aspek' => $item['jenis_aspek'],
                    'indikator'   => $item['indikator'],
                    'target'      => $item['target'],
                    'satuan'      => $item['satuan'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Rencana SKP berhasil dibuat',
                'data' => $rencana->load('targets')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan SKP',
                'error' => $e->getMessage()
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

        if (!$rencana) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        return response()->json(['data' => $rencana]);
    }

    /**
     * UPDATE SKP
     * Strategi: Update Header -> Hapus Target Lama -> Insert Target Baru
     */
    public function update(Request $request, $id)
    {
        $rencana = SkpRencana::where('user_id', Auth::id())->find($id);
        if (!$rencana) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        // Validasi
        $validator = Validator::make($request->all(), [
            'periode_awal'   => 'required|date',
            'periode_akhir'  => 'required|date|after_or_equal:periode_awal',
            'rhk_intervensi' => 'required|string',
            'rencana_hasil_kerja' => 'required|string',
            'targets'        => 'required|array|min:1',
            'targets.*.jenis_aspek' => 'required|in:Kuantitas,Kualitas,Waktu,Biaya',
            'targets.*.indikator'   => 'required|string',
            'targets.*.target'      => 'required|integer',
            'targets.*.satuan'      => 'required|string|max:50',
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
                    'indikator'   => $item['indikator'],
                    'target'      => $item['target'],
                    'satuan'      => $item['satuan'],
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
        if (!$rencana) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        try {
            $rencana->delete(); // Cascade delete akan menghapus targets otomatis
            
            return response()->json(['message' => 'SKP berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus', 'error' => $e->getMessage()], 500);
        }
    }
    
    // --- Helper / Placeholder untuk fitur Skoring (jika diperlukan nanti) ---
    /**
     * API: Data Skoring Kinerja Bawahan
     * Logic: Ambil bawahan -> Ambil SKP Aktif -> Hitung % Realisasi LKH Approved
     */
    public function getSkoringData(Request $request)
    {
        $user = Auth::user();
        
        // 1. Cari Bawahan (User yang atasan_id-nya adalah user login)
        $bawahan = \App\Models\User::where('atasan_id', $user->id)->get();

        $data = $bawahan->map(function($staff) {
            // 2. Ambil Rencana SKP Terbaru milik staf ini
            $rencana = SkpRencana::with(['targets' => function($q) {
                            $q->where('jenis_aspek', 'Kuantitas');
                        }])
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

            // 4. Hitung Realisasi (Sum Volume LKH yang Approved)
            $realisasi = \App\Models\LaporanHarian::where('skp_rencana_id', $rencana->id)
                ->where('status', 'approved')
                ->sum('volume');

            // 5. Hitung Persentase
            $persen = $targetAngka > 0 ? round(($realisasi / $targetAngka) * 100) : 0;

            return [
                'id' => $staff->id,
                'nama' => $staff->name,
                'nip' => $staff->nip,
                'foto' => $staff->foto_profil_url ?? asset('images/default-user.png'),
                'rhk' => $rencana->rencana_hasil_kerja,
                'target' => $targetAngka,
                'realisasi' => $realisasi,
                'satuan' => $satuan,
                'capaian' => $persen,
                'status' => 'Aktif'
            ];
        });

        return response()->json(['data' => $data]);
    }
}
