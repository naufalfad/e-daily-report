<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatan';
    protected $guarded = ['id'];

    // =========================================================
    // TAHAP 1.1.b FIX: SCOPE DINAMIS UNTUK MENCARI KEPALA BIDANG
    // =========================================================

    /**
     * Scope untuk memfilter atau mencari jabatan 'Kepala Bidang' secara dinamis.
     * Menggunakan kolom 'nama_jabatan' (asumsi konsisten dengan skema Bapenda).
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeKepalaBidang($query)
    {
        // FIX: Mengganti 'nama' menjadi 'nama_jabatan'
        return $query->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%kepala bidang%']);
    }

    /**
     * Relasi ke User (Pegawai)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'jabatan_id');
    }
}