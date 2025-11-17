<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProvinsi extends Model
{
    use HasFactory;
    protected $table = 'master_provinsi';
    protected $guarded = [];
    public $timestamps = false; // Tidak pakai timestamps

    // --- PENTING UNTUK STRING PK ---
    public $incrementing = false;
    protected $keyType = 'string';
    // --------------------------------

    public function kabupaten()
    {
        return $this->hasMany(MasterKabupaten::class, 'provinsi_id', 'id');
    }
}