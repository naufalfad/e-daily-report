<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKecamatan extends Model
{
    use HasFactory;

    protected $table = 'master_kecamatan';
    
    // Menggunakan guarded empty array agar mass assignment fleksibel
    protected $guarded = [];
    
    public $timestamps = false;

    // --- KONFIGURASI PRIMARY KEY STRING (PENTING) ---
    // Kode wilayah Indonesia formatnya string (contoh: '94.01')
    public $incrementing = false;
    protected $keyType = 'string';
    // ------------------------------------------------

    /**
     * Relasi ke Kabupaten (Parent)
     */
    public function kabupaten()
    {
        return $this->belongsTo(MasterKabupaten::class, 'kabupaten_id', 'id');
    }

    /**
     * Relasi ke Kelurahan (Children)
     */
    public function kelurahan()
    {
        return $this->hasMany(MasterKelurahan::class, 'kecamatan_id', 'id');
    }
}