<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // [PENTING] Import SoftDeletes

class Bidang extends Model
{
    use HasFactory, SoftDeletes; // [PENTING] Aktifkan fitur Soft Deletes

    protected $table = 'bidang';

    // Menggunakan guarded agar fleksibel (mass assignment aman selama di-handle controller)
    protected $guarded = [];

    // =========================================================================
    // RELASI HIERARKI (STRUKTUR ORGANISASI)
    // =========================================================================

    /**
     * Relasi ke Bapak (Unit Kerja)
     * Logic: Setiap Bidang PASTI berada di bawah 1 Unit Kerja.
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Relasi ke Anak (Tupoksi)
     * Logic: 1 Bidang memiliki banyak Tupoksi (Tugas Pokok & Fungsi).
     */
    public function tupoksi()
    {
        return $this->hasMany(Tupoksi::class, 'bidang_id');
    }

    /**
     * Relasi ke User (Pegawai)
     * Logic: 1 Bidang menaungi banyak Pegawai Staf.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'bidang_id');
    }

    // =========================================================================
    // LOGIC KHUSUS (SKORING & ANALISA)
    // =========================================================================

    /**
     * Relasi Bidang ke Kepala Bidang (Kabid) penanggung jawab.
     * Logika dinamis untuk mencari jabatan Kepala Bidang tanpa hardcoding ID user.
     * Berguna untuk fitur Skoring Kinerja Bidang.
     */
    public function kepalaBidang()
    {
        // Mencari satu user di bidang ini yang jabatannya mengandung kata 'Kepala Bidang'
        return $this->hasOne(User::class, 'bidang_id')
            ->whereHas('jabatan', function ($query) {
                // Menggunakan LOWER agar pencarian case-insensitive (huruf besar/kecil dianggap sama)
                $query->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%kepala bidang%']);
            });
    }
}