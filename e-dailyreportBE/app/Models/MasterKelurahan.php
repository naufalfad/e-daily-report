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

    // --- PENTING UNTUK STRING PK ---
    public $incrementing = false;
    protected $keyType = 'string';
    // --------------------------------
    
    public function kecamatan()
    {
        return $this->belongsTo(MasterKecamatan::class, 'kecamatan_id', 'id');
    }
    
    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class, 'master_kelurahan_id', 'id');
    }
}