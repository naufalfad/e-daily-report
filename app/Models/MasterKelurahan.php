<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKelurahan extends Model
{
    use HasFactory;

    protected $table = 'master_kelurahan';
    
    protected $guarded = [];
    
    public $timestamps = false;

    // --- KONFIGURASI PRIMARY KEY STRING ---
    public $incrementing = false;
    protected $keyType = 'string';
    // --------------------------------------

    /**
     * Type Casting
     * Memastikan latitude & longitude dikembalikan sebagai angka (double/float)
     * agar Javascript tidak perlu parsing manual dari string.
     */
    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    /**
     * Relasi ke Kecamatan (Parent)
     */
    public function kecamatan()
    {
        return $this->belongsTo(MasterKecamatan::class, 'kecamatan_id', 'id');
    }
    
    /**
     * Relasi ke Laporan Harian
     * Digunakan untuk menarik laporan berdasarkan lokasi kelurahan
     */
    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class, 'master_kelurahan_id', 'id');
    }
}