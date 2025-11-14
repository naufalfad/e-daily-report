<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AngelSourceLabs\LaravelSpatial\Eloquent\SpatialTrait;

class LaporanHarian extends Model
{
    use HasFactory, SpatialTrait;

    protected $table = 'laporan_harian';
    protected $guarded = ['id'];

    // Konfigurasi PostGIS (Nama kolom yang tipe datanya GEOMETRY/GEOGRAPHY)
    protected $spatialFields = [
        'lokasi', 
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke SKP (Bisa Null jika Non-SKP)
    public function skp()
    {
        return $this->belongsTo(Skp::class)->withDefault([
            'nama_skp' => 'Non-SKP / Tugas Tambahan'
        ]);
    }

    // Relasi ke Bukti (Foto/PDF)
    public function bukti()
    {
        return $this->hasMany(LkhBukti::class, 'laporan_id');
    }
    
    // Relasi ke Validator (Atasan)
    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}