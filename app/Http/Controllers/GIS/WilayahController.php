<?php

namespace App\Http\Controllers\GIS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\MasterProvinsi;
use App\Models\MasterKabupaten;
use App\Models\MasterKecamatan;
use App\Models\MasterKelurahan;

class WilayahController extends Controller
{
    /**
     * Get All Provinsi
     * Digunakan untuk dropdown standar wilayah
     */
    public function provinsi(): JsonResponse
    {
        $data = MasterProvinsi::select('id', 'nama')
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json($data);
    }

    /**
     * Get Kabupaten by Provinsi ID
     */
    public function kabupaten(Request $request): JsonResponse
    {
        $request->validate([
            'provinsi_id' => 'required|string'
        ]);
        
        $data = MasterKabupaten::where('provinsi_id', $request->provinsi_id)
            ->select('id', 'provinsi_id', 'nama')
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json($data);
    }

    /**
     * Get Kecamatan by Kabupaten ID (API 1 Roadmap)
     * Digunakan untuk filter awal di modal peta
     */
    public function kecamatan(Request $request): JsonResponse
    {
        $request->validate([
            'kabupaten_id' => 'required|string'
        ]);
        
        $data = MasterKecamatan::where('kabupaten_id', $request->kabupaten_id)
            ->select('id', 'kabupaten_id', 'nama')
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json($data);
    }

    /**
     * Get Kelurahan by Kecamatan ID (API 2 Roadmap)
     * CRITICAL: Mengembalikan data latitude & longitude untuk fitur Geotagging
     * Frontend akan menggunakan lat/long dari response ini untuk fungsi map.flyTo()
     */
    public function kelurahan(Request $request): JsonResponse
    {
        $request->validate([
            'kecamatan_id' => 'required|string'
        ]);
        
        // Optimasi: Kita select kolom spesifik. 
        // Latitude & Longitude WAJIB ada untuk fitur fallback strategy peta.
        $data = MasterKelurahan::where('kecamatan_id', $request->kecamatan_id)
            ->select(['id', 'kecamatan_id', 'nama', 'latitude', 'longitude']) 
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json($data);
    }
}