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

        // Filter Tahun (Opsional, jika ada request ?year=2025)
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
        // 1. Validasi Input Kompleks
        $validator = Validator::make($request->all(), [
            // Validasi Header (Rencana)
            'periode_awal'   => 'required|date',
            'periode_akhir'  => 'required|date|after_or_equal:periode_awal',
            'rhk_intervensi' => 'required|string', // Input Manual
            'rencana_hasil_kerja' => 'required|string', // Input Manual
            
            // Validasi Detail Targets (Array of Objects)
            'targets'        => 'required|array|min:1', // Minimal 1 target (biasanya Kuantitas)
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
     * (Cara paling aman untuk data dinamis agar ID child tidak konflik)
     */
    public function update(Request $request, $id)
    {
        $rencana = SkpRencana::where('user_id', Auth::id())->find($id);
        if (!$rencana) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        // Validasi (Sama dengan Store)
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
            // Karena kita set onDelete('cascade') di migrasi, 
            // menghapus parent otomatis menghapus children (targets) di DB.
            $rencana->delete(); 
            
            return response()->json(['message' => 'SKP berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus', 'error' => $e->getMessage()], 500);
        }
    }

    // --- HELPER untuk Penilaian (Opsional/Future Dev) ---
    public function skoringKinerja()
    {
        // Logika ini nanti disesuaikan dengan struktur baru jika fitur penilaian diaktifkan
        return response()->json(['message' => 'Fitur skoring sedang disesuaikan']);
    }
}