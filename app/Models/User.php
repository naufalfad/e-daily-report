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

    // ======================================================================
    // OVERRIDE LOGIN FIELD
    // ======================================================================
    /**
     * Laravel default uses email, override to use 'username' for login.
     */
    public function username()
    {
        return 'username';
    }

    // ======================================================================
    // ACCESSORS
    // ======================================================================
    public function getFotoProfilUrlAttribute()
    {
        if ($this->foto_profil) {
            return Storage::disk('minio')->url($this->foto_profil);
        }
        return null; 
    }
    
    public function getTupoksiTersediaAttribute()
    {
        if ($this->bidang) {
            return Tupoksi::where('bidang_id', $this->bidang_id)->get();
        }
        return collect(); // Kembalikan koleksi kosong jika tidak punya bidang
    }

    // ======================================================================
    // RELASI ORGANISASI & HIERARKI
    // ======================================================================
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    // ======================================================================
    // RELASI CORE BUSINESS
    // ======================================================================
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

    // ======================================================================
    // RELASI PENDUKUNG
    // ======================================================================
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
