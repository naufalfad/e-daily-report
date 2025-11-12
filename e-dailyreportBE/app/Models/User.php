<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use AngelSourceLabs\LaravelSpatial\Eloquent\SpatialTrait; // <-- 1. IMPORT TRAIT BARU

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SpatialTrait; // <-- 2. GUNAKAN TRAIT BARU

    protected $guarded = []; // Izinkan mass assignment

    protected $hidden = [
        'password',
    ];

    // == RELASI ORGANISASI & PERAN ==

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    // == RELASI LKH & SKP ==

    public function skp()
    {
        return $this->hasMany(Skp::class, 'user_id');
    }

    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class, 'user_id');
    }

    public function laporanValidasi()
    {
        return $this->hasMany(LaporanHarian::class, 'validator_id');
    }
    
    // == RELASI PENDUKUNG ==

    public function pengumumanDibuat()
    {
        return $this->hasMany(Pengumuman::class, 'user_id_creator');
    }

    public function notifikasiDiterima()
    {
        return $this->hasMany(Notifikasi::class, 'user_id_recipient');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}