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

    protected $spatialFields = [
        'lokasi',
    ];

    // ---------------------------------------------------------------------
    // Relasi langsung ke pegawai (pembuat laporan)
    // ---------------------------------------------------------------------
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ---------------------------------------------------------------------
    // Relasi laporan SKP
    // ---------------------------------------------------------------------
    public function skp()
    {
        return $this->belongsTo(Skp::class)->withDefault([
            'nama_skp' => 'Non-SKP / Tugas Tambahan'
        ]);
    }

    // ---------------------------------------------------------------------
    // Relasi bukti laporan (foto, file)
    // ---------------------------------------------------------------------
    public function bukti()
    {
        return $this->hasMany(LkhBukti::class, 'laporan_id');
    }

    // ---------------------------------------------------------------------
    // Relasi ke atasan yang memvalidasi
    // ---------------------------------------------------------------------
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }
}
