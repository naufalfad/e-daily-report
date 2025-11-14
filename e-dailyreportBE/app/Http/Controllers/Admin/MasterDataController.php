<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    // Mengambil semua roles (Dropdown)
    public function getRoles() { return response()->json(Role::all()); }
    
    // Mengambil semua jabatan (Dropdown)
    public function getJabatan() { return response()->json(Jabatan::all()); }
    
    // Mengambil semua unit kerja (Dropdown)
    public function getUnitKerja() { return response()->json(UnitKerja::all()); }
    
    // Mengambil user untuk dijadikan atasan (Dropdown)
    public function getCalonAtasan() { return response()->json(User::select('id','name','nip')->get()); }
}