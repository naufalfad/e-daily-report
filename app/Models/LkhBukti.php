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

    protected $appends = ['file_url'];

    // Mengembalikan URL MinIO yang bisa diakses browser
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }
}