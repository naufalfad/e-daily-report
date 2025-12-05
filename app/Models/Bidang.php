<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Import Models yang dibutuhkan
use App\Models\User;
use App\Models\Jabatan; 

class Bidang extends Model
{
    use HasFactory;

    protected $table = 'bidang';
    protected $guarded = [];

    // Milik Unit Kerja mana?
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    // [BARU] 1 Bidang punya banyak Tupoksi
    public function tupoksi()
    {
        return $this->hasMany(Tupoksi::class, 'bidang_id');
    }

    // 1 Bidang punya banyak Pegawai (Staf)
    public function users()
    {
        return $this->hasMany(User::class, 'bidang_id');
    }

    // =========================================================
    // TAHAP 1.3 FIX: RELASI SKORING PER BIDANG (Penanggung Jawab)
    // =========================================================

    /**
     * Relasi Bidang ke Kepala Bidang (Kabid) penanggung jawab.
     * Logika dinamis untuk mencari jabatan Kepala Bidang tanpa hardcoding ID.
     */
    public function kepalaBidang()
    {
        // Mencari satu user di bidang ini yang jabatannya adalah 'Kepala Bidang'
        return $this->hasOne(User::class, 'bidang_id')
                    ->whereHas('jabatan', function ($query) {
                        // FIX: Mengubah kolom 'nama' menjadi 'nama_jabatan'
                        $query->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%kepala bidang%']);
                    });
    }
}