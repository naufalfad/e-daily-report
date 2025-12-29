<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // [PENTING] Import SoftDeletes

class Jabatan extends Model
{
    use HasFactory, SoftDeletes; // [PENTING] Aktifkan trait SoftDeletes

    protected $table = 'jabatan';
    
    // Kita gunakan guarded ['id'] agar 'unit_kerja_id' dan 'nama_jabatan' bisa diisi (Mass Assignment)
    protected $guarded = ['id'];

    // =========================================================================
    // RELASI UTAMA (STRUKTUR BARU)
    // =========================================================================

    /**
     * Relasi ke Bapak (Unit Kerja)
     * Logic: Jabatan ini spesifik milik Unit Kerja tertentu.
     * Contoh: 'Sekretaris' milik 'Dinas Pendidikan' (ID: 101)
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Relasi ke User (Pegawai)
     * Logic: Siapa saja pegawai yang sedang menduduki jabatan ini?
     */
    public function users()
    {
        return $this->hasMany(User::class, 'jabatan_id');
    }

    // =========================================================================
    // LOGIC & SCOPE (HELPER QUERY)
    // =========================================================================

    /**
     * Scope untuk memfilter atau mencari jabatan 'Kepala Bidang' secara dinamis.
     * Berguna untuk fitur Skoring Kinerja Bidang.
     * * Cara Pakai: Jabatan::kepalaBidang()->get();
     */
    public function scopeKepalaBidang($query)
    {
        // Menggunakan LOWER agar pencarian case-insensitive (huruf besar/kecil dianggap sama)
        return $query->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%kepala bidang%']);
    }
}