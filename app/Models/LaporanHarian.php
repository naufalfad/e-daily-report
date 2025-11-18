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
        'catatan_penilai',
        'master_kelurahan_id',
        'is_luar_lokasi',
        'lokasi',
        // 'validator_id' DIHAPUS
        'validated_at',
        'atasan_id', // Tetap menggunakan atasan_id sebagai penilai awal
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'validated_at' => 'datetime',
    ];

    // Hubungan ke Pengguna (Pembuat Laporan)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Hubungan ke SKP (Jika kegiatan terkait SKP)
    public function skp(): BelongsTo
    {
        return $this->belongsTo(Skp::class, 'skp_id');
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

    // Hubungan ke Atasan (Relasi yang digunakan sebagai penilai/validator di DB)
    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }
    
    // RELASI VALIDATOR DIHAPUS KARENA KOLOM TIDAK ADA
    // public function validator(): BelongsTo { ... }

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