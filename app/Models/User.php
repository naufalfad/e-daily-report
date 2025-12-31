<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use AngelSourceLabs\LaravelSpatial\Eloquent\SpatialTrait;
use Illuminate\Support\Facades\Storage;

use App\Models\Jabatan;
use App\Models\Bidang;
use App\Models\LaporanHarian; // Pastikan Model LaporanHarian ter-import

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
        'is_active' => 'boolean',
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
            : asset('assets/man.png'); // FIX: Default avatar path disesuaikan
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
    
    // laporan yang harus divalidasi oleh user sebagai atasan
    public function laporanValidasi()
    {
        return $this->hasMany(LaporanHarian::class, 'atasan_id');
    }
    
    // [FIXED] Relasi SKP diarahkan ke SkpRencana (Model Baru)
    public function skp()
    {
        return $this->hasMany(SkpRencana::class, 'user_id');
    }

    /**
     * Relasi LKH (Laporan Harian) yang dibuat oleh user
     */
    public function lkh()
    {
        return $this->hasMany(LaporanHarian::class, 'user_id');
    }

    // ---------------------------------------------------------------------
    // Unit kerja / jabatan / bidang (TAHAP 1.2: Relasi Dasar Skoring)
    // ---------------------------------------------------------------------
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Relasi Bidang. Penting untuk mengelompokkan user dalam perhitungan skoring per Bidang.
     */
    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    /**
     * Relasi Jabatan. Penting untuk mengidentifikasi Kepala Bidang secara dinamis.
     */
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

    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class, 'user_id');
    }
}