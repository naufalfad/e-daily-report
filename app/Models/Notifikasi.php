<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;
    protected $table = 'notifikasi';
    protected $guarded = [];

    /**
     * RELASI POLYMORPHIC
     * Memungkinkan notifikasi terhubung dinamis ke model apa saja (LKH, SKP, Pengumuman, dll)
     */
    public function related()
    {
        return $this->morphTo();
    }
    
    public function recipient()
    {
        return $this->belongsTo(User::class, 'user_id_recipient');
    }
}