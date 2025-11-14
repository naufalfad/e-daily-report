<?php

namespace App\Http\Controllers\GIS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterProvinsi;
use App\Models\MasterKabupaten;
use App\Models\MasterKecamatan;
use App\Models\MasterKelurahan;

class WilayahController extends Controller
{
    // GET /api/provinsi
    public function provinsi()
    {
        return MasterProvinsi::all();
    }

    // GET /api/kabupaten?provinsi_id=...
    public function kabupaten(Request $request)
    {
        // Validasi: pastikan provinsi_id dikirim
        $request->validate(['provinsi_id' => 'required|string']);
        
        return MasterKabupaten::where('provinsi_id', $request->provinsi_id)->get();
    }

    // GET /api/kecamatan?kabupaten_id=...
    public function kecamatan(Request $request)
    {
        $request->validate(['kabupaten_id' => 'required|string']);
        
        return MasterKecamatan::where('kabupaten_id', $request->kabupaten_id)->get();
    }

    // GET /api/kelurahan?kecamatan_id=...
    public function kelurahan(Request $request)
    {
        $request->validate(['kecamatan_id' => 'required|string']);
        
        return MasterKelurahan::where('kecamatan_id', $request->kecamatan_id)->get();
    }
}