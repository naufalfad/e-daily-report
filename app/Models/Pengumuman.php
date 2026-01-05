<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    /**
     * Menggunakan guarded kosong sesuai preferensi sebelumnya.
     * Pastikan validasi di Controller diperketat untuk keamanan.
     */
    protected $guarded = [];

    /**
     * Relasi ke User (Kreator Pengumuman)
     * Information Expert: Mengetahui siapa yang membuat pengumuman.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_creator');
    }

    /**
     * Relasi ke Unit Kerja
     * Jika unit_kerja_id terisi namun bidang_id null, 
     * secara logis bisa dianggap pengumuman global unit.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * [BARU] Relasi ke Bidang (Divisi)
     * Digunakan untuk fitur isolasi pengumuman per divisi.
     * Jika bidang_id bernilai NULL, maka pengumuman bersifat UMUM.
     */
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    /**
     * Helper Scope untuk mengecek apakah pengumuman bersifat umum.
     */
    public function isUmum(): bool
    {
        return is_null($this->bidang_id);
    }
}