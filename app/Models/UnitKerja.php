<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // [PENTING] Import SoftDeletes

class UnitKerja extends Model
{
    use HasFactory, SoftDeletes; // [PENTING] Aktifkan trait SoftDeletes

    protected $table = 'unit_kerja';
    
    // Menggunakan guarded agar semua kolom bisa diisi (mass assignable)
    protected $guarded = [];

    // =========================================================================
    // RELASI HIERARKI (Self-Referencing)
    // =========================================================================
    
    // Relasi ke Induk (Misal: UPTD Puskesmas -> Induknya Dinas Kesehatan)
    public function parent()
    {
        return $this->belongsTo(UnitKerja::class, 'parent_id');
    }

    // Relasi ke Anak (Misal: Dinas Kesehatan -> Anaknya UPTD Puskesmas A, B, C)
    public function children()
    {
        return $this->hasMany(UnitKerja::class, 'parent_id');
    }

    // =========================================================================
    // RELASI UTAMA (Sesuai Struktur Baru)
    // =========================================================================

    // [Step B] 1 Unit Kerja punya banyak Bidang
    public function bidang()
    {
        return $this->hasMany(Bidang::class, 'unit_kerja_id');
    }

    // [Step C] 1 Unit Kerja punya banyak Jabatan (BARU)
    // Ini implementasi dari logic: "Jabatan milik Unit Kerja"
    public function jabatan()
    {
        return $this->hasMany(Jabatan::class, 'unit_kerja_id');
    }

    // =========================================================================
    // RELASI LAINNYA
    // =========================================================================

    public function users()
    {
        return $this->hasMany(User::class, 'unit_kerja_id');
    }
    
    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'unit_kerja_id');
    }
}