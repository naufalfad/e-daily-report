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

    /**
     * Kolom yang boleh diisi secara massal.
     * Sesuai struktur tabel: bidang_id (FK) dan uraian_tugas.
     */
    protected $fillable = [
        'bidang_id',
        'uraian_tugas',
    ];

    /**
     * Relasi: Satu Tupoksi milik satu Bidang.
     * Foreign Key: bidang_id
     */
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    /**
     * Relasi: Satu Tupoksi bisa memiliki banyak Laporan Harian.
     * Ini digunakan untuk proteksi saat penghapusan (Integrity Check).
     */
    public function laporanHarian(): HasMany
    {
        return $this->hasMany(LaporanHarian::class, 'tupoksi_id');
    }
}