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
}