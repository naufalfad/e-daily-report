<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use AngelSourceLabs\LaravelSpatial\Eloquent\SpatialTrait;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SpatialTrait;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'username_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $spatialFields = [];

    protected $appends = ['foto_profil_url'];

    // Eager load agar otomatis ikut saat query user
    protected $with = ['atasan', 'jabatan', 'bidang'];

    // ---------------------------------------------------------------------
    // Accessor foto profil
    // ---------------------------------------------------------------------
    public function getFotoProfilUrlAttribute()
    {
        return $this->foto_profil
            ? Storage::disk('public')->url($this->foto_profil)
            : asset('images/default-user.png');
    }

    // ---------------------------------------------------------------------
    // Hierarki organisasi
    // ---------------------------------------------------------------------
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    public function bawahanRecursif()
    {
        return $this->hasMany(User::class, 'atasan_id')
                    ->with(['jabatan', 'bidang', 'bawahanRecursif']);
    }

    // ---------------------------------------------------------------------
    // Relasi role
    // ---------------------------------------------------------------------
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    // ---------------------------------------------------------------------
    // Relasi ke core business
    // ---------------------------------------------------------------------
    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class, 'user_id');
    }

    // laporan yang harus divalidasi oleh user sebagai atasan
    public function laporanValidasi()
    {
        return $this->hasMany(LaporanHarian::class, 'atasan_id');
    }

    // ---------------------------------------------------------------------
    // Unit kerja / jabatan / bidang
    // ---------------------------------------------------------------------
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    // ---------------------------------------------------------------------
    // Relasi lain-lain
    // ---------------------------------------------------------------------
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

    public function hasRole($roleName)
    {
        return $this->roles()->where('nama_role', $roleName)->exists();
    }

}
