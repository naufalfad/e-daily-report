<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tupoksi extends Model
{
    use HasFactory;

    protected $table = 'tupoksi';

    protected $fillable = [
        'bidang_id',
        'uraian_tugas',
    ];

    // Hubungan ke Bidang
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    // Hubungan ke Laporan Harian (DITAMBAHKAN UNTUK KELENGKAPAN)
    public function laporanHarian(): HasMany
    {
        return $this->hasMany(LaporanHarian::class, 'tupoksi_id');
    }
}