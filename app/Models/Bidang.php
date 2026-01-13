<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bidang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bidang';

    // Konstanta Level
    public const LEVEL_BIDANG = 'bidang';
    public const LEVEL_SUB_BIDANG = 'sub_bidang';

    protected $fillable = [
        'unit_kerja_id', 
        'nama_bidang', 
        'parent_id', 
        'level'
    ];

    // =========================================================================
    // 1. RELASI DATA MASTER TUPOKSI (FOKUS TAHAP INI)
    // =========================================================================

    /**
     * Relasi: Satu Bidang memiliki banyak Tupoksi.
     * Tidak memandang apakah ini Bidang Induk atau Sub-Bidang,
     * selama ID-nya tercatat di tabel tupoksi, relasi ini akan bekerja.
     */
    public function tupoksi(): HasMany
    {
        return $this->hasMany(Tupoksi::class, 'bidang_id');
    }

    // =========================================================================
    // 2. RELASI HIERARKI (STRUKTUR ORGANISASI)
    // =========================================================================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Bidang::class, 'parent_id');
    }

    // =========================================================================
    // 3. SCOPES (LOGIKA QUERY)
    // =========================================================================

    public function scopeLevelBidang($query)
    {
        return $query->where('level', self::LEVEL_BIDANG);
    }

    public function scopeLevelSubBidang($query)
    {
        return $query->where('level', self::LEVEL_SUB_BIDANG);
    }

    // =========================================================================
    // 4. RELASI LAINNYA
    // =========================================================================

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'bidang_id');
    }

    public function kepalaBidang(): HasOne
    {
        return $this->hasOne(User::class, 'bidang_id')
            ->whereHas('jabatan', function ($query) {
                $query->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%kepala bidang%'])
                      ->orWhereRaw('LOWER(nama_jabatan) LIKE ?', ['%kabid%']);
            });
    }
}