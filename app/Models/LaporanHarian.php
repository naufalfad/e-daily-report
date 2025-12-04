<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class LaporanHarian extends Model
{
    use HasFactory;

    protected $table = 'laporan_harian';

    protected $fillable = [
        'user_id',
        'skp_id',
        'tupoksi_id',
        'jenis_kegiatan',
        'tanggal_laporan',
        'waktu_mulai',
        'waktu_selesai',
        'deskripsi_aktivitas',
        'output_hasil_kerja',
        'volume',
        'satuan',
        'status',
        
        // [PERBAIKAN UTAMA] Menggunakan nama kolom dari Database Migration
        'komentar_validasi', // sebelumnya 'catatan_penilai' atau 'komentar_validasi'
        'waktu_validasi',    // sebelumnya 'validated_at'
        
        'master_kelurahan_id',
        'is_luar_lokasi',
        'lokasi',
        'atasan_id', // Tetap menggunakan atasan_id sebagai penilai awal
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        // [PERBAIKAN] Menggunakan nama kolom yang benar
        'waktu_validasi' => 'datetime',
    ];

    // Hubungan ke Pengguna (Pembuat Laporan)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rencana()
    {
        return $this->belongsTo(\App\Models\SkpRencana::class, 'skp_rencana_id');
    }

    // Hubungan ke Tupoksi
    public function tupoksi(): BelongsTo
    {
        return $this->belongsTo(Tupoksi::class, 'tupoksi_id');
    }

    // Hubungan ke Bukti LKH
    public function bukti(): HasMany
    {
        return $this->hasMany(LkhBukti::class, 'laporan_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    // Hubungan ke Atasan (Relasi yang digunakan sebagai penilai/validator di DB)
    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }
    
    // Accessor untuk mendapatkan status laporan yang lebih deskriptif
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting_review' => 'Menunggu Review',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'draft' => 'Draft',
            default => 'Unknown',
        };
    }

    // Query Scope untuk memfilter yang menunggu review
    public function scopeWaitingReview(Builder $query): void
    {
        $query->where('status', 'waiting_review');
    }
}