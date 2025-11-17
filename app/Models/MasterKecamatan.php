<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKecamatan extends Model
{
    use HasFactory;
    protected $table = 'master_kecamatan';
    protected $guarded = [];
    public $timestamps = false;

    // --- PENTING UNTUK STRING PK ---
    public $incrementing = false;
    protected $keyType = 'string';
    // --------------------------------
    
    public function kabupaten()
    {
        return $this->belongsTo(MasterKabupaten::class, 'kabupaten_id', 'id');
    }

    public function kelurahan()
    {
        return $this->hasMany(MasterKelurahan::class, 'kecamatan_id', 'id');
    }
}