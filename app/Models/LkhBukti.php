<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LkhBukti extends Model
{
    use HasFactory;

    protected $table = 'lkh_bukti';
    protected $guarded = ['id'];

    // [PENTING] Tambahkan ini agar 'file_url' muncul di JSON response
    protected $appends = ['file_url'];

    /**
     * Accessor untuk membuat atribut 'file_url' secara otomatis.
     * Frontend (riwayat-core.js) menggunakan property ini untuk menampilkan preview.
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            // Gunakan Storage facade untuk generate URL publik yang benar
            // Pastikan Anda sudah menjalankan: php artisan storage:link
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }
}