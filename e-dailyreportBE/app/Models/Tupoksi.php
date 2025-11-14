<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tupoksi extends Model
{
    use HasFactory;

    protected $table = 'tupoksi';
    protected $guarded = [];

    // Milik Bidang apa?
    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }
    
    // Tupoksi ini dipakai di laporan mana saja?
    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class, 'tupoksi_id');
    }
}