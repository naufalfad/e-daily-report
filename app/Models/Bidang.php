<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bidang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bidang';

    // Kolom yang boleh diisi (Mass Assignment)
    // Saya tambahkan parent_id dan level agar seeder/controller aman
    protected $fillable = [
        'unit_kerja_id', 
        'nama_bidang', 
        'parent_id', 
        'level'
    ];

    // =========================================================================
    // 1. RELASI HIERARKI (NEW FEATURE - SOTK STRUCTURE)
    // =========================================================================

    /**
     * Relasi ke Atasan (Induk)
     * Contoh: Sub Bidang Pendataan (Anak) -> Bidang Pajak (Bapak)
     */
    public function parent()
    {
        return $this->belongsTo(Bidang::class, 'parent_id');
    }

    /**
     * Relasi ke Bawahan (Anak)
     * Contoh: Bidang Pajak (Bapak) -> [Sub Bidang Pendataan, Sub Bidang Penetapan...]
     */
    public function children()
    {
        return $this->hasMany(Bidang::class, 'parent_id');
    }

    /**
     * Scope Query: Hanya ambil Bidang Induk (Top Level)
     * Cara pakai: Bidang::induk()->get();
     */
    public function scopeInduk($query)
    {
        return $query->whereNull('parent_id')->orWhere('level', 'bidang');
    }

    /**
     * Scope Query: Hanya ambil Sub Bidang (Anak)
     * Cara pakai: Bidang::anak()->get();
     */
    public function scopeAnak($query)
    {
        return $query->whereNotNull('parent_id')->orWhere('level', 'sub_bidang');
    }

    // =========================================================================
    // 2. RELASI EXISTING
    // =========================================================================

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function tupoksi()
    {
        return $this->hasMany(Tupoksi::class, 'bidang_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'bidang_id');
    }

    public function kepalaBidang()
    {
        return $this->hasOne(User::class, 'bidang_id')
            ->whereHas('jabatan', function ($query) {
                $query->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%kepala bidang%'])
                      ->orWhereRaw('LOWER(nama_jabatan) LIKE ?', ['%kabid%']);
            });
    }
}