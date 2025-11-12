<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AngelSourceLabs\LaravelSpatial\Eloquent\SpatialTrait; // <-- 1. IMPORT TRAIT BARU

class LaporanHarian extends Model
{
    use HasFactory, SpatialTrait; // <-- 2. GUNAKAN TRAIT BARU

    protected $table = 'laporan_harian';
    protected $guarded = [];

    // 3. TENTUKAN KOLOM SPATIAL ANDA DI SINI
    protected $spatial = [
        'lokasi',
    ];

    // == RELASI UTAMA ==
    
    public function pegawai()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function skp()
    {
        return $this->belongsTo(Skp::class, 'skp_id');
    }

    public function bukti()
    {
        return $this->hasMany(LkhBukti::class, 'laporan_id');
    }
    
    // == RELASI LOKASI MANUAL ==
    
    public function kelurahan()
    {
        return $this->belongsTo(MasterKelurahan::class, 'master_kelurahan_id', 'id');
    }
}