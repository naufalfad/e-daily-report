<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aktivitas extends Model
{
    use HasFactory;

    protected $table = 'aktivitas';

    protected $fillable = [
        'user_id',
        'deskripsi_aktivitas',
        'output_hasil_kerja',
        'jenis_kegiatan',
        'tanggal_laporan',
        'waktu_mulai',
        'waktu_selesai',
        'lat',
        'lng',
        'lokasi',
        'status',
        'is_luar_lokasi',
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'is_luar_lokasi' => 'boolean',
    ];

    // ==========================
    // RELASI
    // ==========================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ==========================
    // ACCESSOR UNTUK LAT / LNG
    // ==========================

    public function getLatAttribute($val)
    {
        return (float) $val;
    }

    public function getLngAttribute($val)
    {
        return (float) $val;
    }

    // ==========================
    // ACCESSOR UNTUK STATUS LABEL
    // ==========================

    public function getStatusLabelAttribute()
    {
        return [
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'waiting_review' => 'Menunggu Validasi'
        ][$this->status] ?? 'Tidak Diketahui';
    }
}
