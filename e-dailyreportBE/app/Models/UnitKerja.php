<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    use HasFactory;

    // Tentukan nama tabel karena tidak jamak (bukan UnitKerjas)
    protected $table = 'unit_kerja';
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(UnitKerja::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(UnitKerja::class, 'parent_id');
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