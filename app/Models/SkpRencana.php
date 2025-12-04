<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkpRencana extends Model
{
    use HasFactory;

    // Nama tabel sesuai migrasi baru
    protected $table = 'skp_rencana';

    protected $fillable = [
        'user_id',
        'periode_awal',
        'periode_akhir',
        'rhk_intervensi',      // Input Manual RHK Atasan
        'rencana_hasil_kerja', // Input Manual RHK Pegawai
    ];

    protected $casts = [
        'periode_awal'  => 'date',
        'periode_akhir' => 'date',
    ];

    /**
     * Relasi ke Pemilik SKP
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Detail Target (One to Many)
     * Satu Rencana bisa punya banyak Target (Kuantitas, Waktu, Kualitas, dll)
     */
    public function targets(): HasMany
    {
        return $this->hasMany(SkpTarget::class, 'skp_rencana_id');
    }

    /**
     * Relasi ke Laporan Harian
     * LKH sekarang menginduk ke Rencana ini
     */
    public function laporanHarian(): HasMany
    {
        return $this->hasMany(LaporanHarian::class, 'skp_rencana_id');
    }
}