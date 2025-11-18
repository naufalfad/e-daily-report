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
<<<<<<< HEAD

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
        'validator_id',
        'validated_at',
        // FIX UTAMA: Menambahkan 'atasan_id' ke fillable untuk Mass Assignment
        'atasan_id', 
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'validated_at' => 'datetime',
    ];

    // Hubungan ke Pengguna (Pembuat Laporan)
    public function user(): BelongsTo
=======

    protected $guarded = ['id'];

    protected $spatialFields = [
        'lokasi',
    ];

    // ---------------------------------------------------------------------
    // Relasi langsung ke pegawai (pembuat laporan)
    // ---------------------------------------------------------------------
    public function user()
>>>>>>> 481dcfad944b7faa883dd86b2af594d5749aa9a5
    {
        return $this->belongsTo(User::class, 'user_id');
    }

<<<<<<< HEAD
    // Hubungan ke SKP (Jika kegiatan terkait SKP)
    public function skp(): BelongsTo
=======
    // ---------------------------------------------------------------------
    // Relasi laporan SKP
    // ---------------------------------------------------------------------
    public function skp()
>>>>>>> 481dcfad944b7faa883dd86b2af594d5749aa9a5
    {
        return $this->belongsTo(Skp::class, 'skp_id');
    }

<<<<<<< HEAD
    // Hubungan ke Tupoksi
    public function tupoksi(): BelongsTo
    {
        return $this->belongsTo(Tupoksi::class, 'tupoksi_id');
    }

    // Hubungan ke Bukti LKH
    public function bukti(): HasMany
=======
    // ---------------------------------------------------------------------
    // Relasi bukti laporan (foto, file)
    // ---------------------------------------------------------------------
    public function bukti()
>>>>>>> 481dcfad944b7faa883dd86b2af594d5749aa9a5
    {
        return $this->hasMany(LkhBukti::class, 'laporan_id');
    }

<<<<<<< HEAD
    // Hubungan ke Atasan (Relasi baru untuk kolom 'atasan_id')
    public function atasan(): BelongsTo
=======
    // ---------------------------------------------------------------------
    // Relasi ke atasan yang memvalidasi
    // ---------------------------------------------------------------------
    public function atasan()
>>>>>>> 481dcfad944b7faa883dd86b2af594d5749aa9a5
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }
<<<<<<< HEAD
    
    // Hubungan ke Penilai/Validator (untuk kolom 'validator_id')
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
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
=======
}
>>>>>>> 481dcfad944b7faa883dd86b2af594d5749aa9a5
