<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bidang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bidang';

    // Konstanta untuk standarisasi Level
    // Digunakan di Controller dan View untuk menghindari hardcode string 'bidang'/'sub_bidang'
    public const LEVEL_BIDANG = 'bidang';
    public const LEVEL_SUB_BIDANG = 'sub_bidang';

    // Kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'unit_kerja_id', 
        'nama_bidang', 
        'parent_id', // Field baru: Referensi ke ID Induk
        'level'      // Field baru: Tipe ('bidang' atau 'sub_bidang')
    ];

    // =========================================================================
    // 1. RELASI HIERARKI (NEW FEATURE - SOTK STRUCTURE)
    // =========================================================================

    /**
     * Relasi ke Atasan (Induk)
     * Contoh: Sub Bidang Pendataan (Anak) -> milik Bidang Pajak (Bapak)
     */
    public function parent()
    {
        return $this->belongsTo(Bidang::class, 'parent_id');
    }

    /**
     * Relasi ke Bawahan (Anak)
     * Contoh: Bidang Pajak (Bapak) -> memiliki [Sub Bidang Pendataan, Sub Bidang Penetapan...]
     */
    public function children()
    {
        return $this->hasMany(Bidang::class, 'parent_id');
    }

    /**
     * Scope Query: Hanya ambil Bidang dengan level 'bidang' (Calon Induk)
     * Cara pakai: Bidang::levelBidang()->get();
     */
    public function scopeLevelBidang($query)
    {
        return $query->where('level', self::LEVEL_BIDANG);
    }

    /**
     * Scope Query: Hanya ambil Bidang dengan level 'sub_bidang'
     */
    public function scopeLevelSubBidang($query)
    {
        return $query->where('level', self::LEVEL_SUB_BIDANG);
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