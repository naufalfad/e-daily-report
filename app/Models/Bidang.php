<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use HasFactory;

    protected $table = 'bidang';
    protected $guarded = [];

    // Milik Unit Kerja mana?
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    // [BARU] 1 Bidang punya banyak Tupoksi
    public function tupoksi()
    {
        return $this->hasMany(Tupoksi::class, 'bidang_id');
    }

    // 1 Bidang punya banyak Pegawai
    public function users()
    {
        return $this->hasMany(User::class, 'bidang_id');
    }
}