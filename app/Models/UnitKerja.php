<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    use HasFactory;

    protected $table = 'unit_kerja';
    protected $guarded = [];

    // Self-referencing (Jika OPD punya sub-unit/cabang dinas, opsional)
    public function parent()
    {
        return $this->belongsTo(UnitKerja::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(UnitKerja::class, 'parent_id');
    }

    // [BARU] 1 Unit Kerja punya banyak Bidang
    public function bidang()
    {
        return $this->hasMany(Bidang::class, 'unit_kerja_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'unit_kerja_id');
    }
    
    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'unit_kerja_id');
    }
}