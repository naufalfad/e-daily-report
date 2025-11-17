<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKabupaten extends Model
{
    use HasFactory;
    protected $table = 'master_kabupaten';
    protected $guarded = [];
    public $timestamps = false;

    // --- PENTING UNTUK STRING PK ---
    public $incrementing = false;
    protected $keyType = 'string';
    // --------------------------------
    
    public function provinsi()
    {
        return $this->belongsTo(MasterProvinsi::class, 'provinsi_id', 'id');
    }

    public function kecamatan()
    {
        return $this->hasMany(MasterKecamatan::class, 'kabupaten_id', 'id');
    }
}