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
        'skp_rencana_id',
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
        'komentar_validasi',
        'waktu_validasi',
        'master_kelurahan_id',
        'is_luar_lokasi',
        'lokasi',
        'atasan_id',
        'mode_lokasi',
        'lokasi_teks',
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'waktu_validasi' => 'datetime',
    ];

    // Hubungan ke Pengguna
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // [BENAR] Relasi ke Rencana (Pengganti SKP)
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

    public function scopeWaitingReview(Builder $query): void
    {
        $query->where('status', 'waiting_review');
    }

    protected $appends = ['lat', 'lng'];

    public function getLatAttribute()
    {
        if (!$this->lokasi) return null;
        return (float) \DB::selectOne("SELECT ST_Y(lokasi) AS lat FROM laporan_harian WHERE id = ?", [$this->id])->lat;
    }

    public function getLngAttribute()
    {
        if (!$this->lokasi) return null;
        return (float) \DB::selectOne("SELECT ST_X(lokasi) AS lng FROM laporan_harian WHERE id = ?", [$this->id])->lng;
    }

}
