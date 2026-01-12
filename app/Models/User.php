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
        'is_active' => 'boolean',
    ];

    protected $spatialFields = [];

    protected $appends = ['foto_profil_url'];

    // Eager load standar
    protected $with = ['atasan', 'jabatan', 'bidang'];

    // =========================================================================
    // HELPER LOGIC HIERARKI (NEW FEATURE - PHASE 3)
    // =========================================================================

    /**
     * Cek apakah user berada di level Sub-Bidang (Anak).
     * Return: boolean
     */
    public function isDiSubBidang()
    {
        // Pastikan relasi bidang ada, lalu cek parent_id atau level
        return $this->bidang && ($this->bidang->parent_id !== null || $this->bidang->level === 'sub_bidang');
    }

    /**
     * Ambil Bidang Induk dari user ini.
     * Jika user di Sub-Bidang -> return Parent-nya.
     * Jika user di Bidang -> return Bidang itu sendiri.
     */
    public function getBidangInduk()
    {
        if (!$this->bidang) return null;

        if ($this->isDiSubBidang()) {
            return $this->bidang->parent;
        }

        return $this->bidang;
    }

    /**
     * Logic cerdas untuk mengambil Tupoksi yang BOLEH dilihat/dikerjakan user.
     * Skenario: Staf Sub-Bidang bisa melihat tupoksi Sub-Bidang (Utama) + Tupoksi Bidang (Opsional/Tugas Tambahan).
     */
    public function getAvailableTupoksiIds()
    {
        $ids = [];
        
        // 1. Ambil ID bidang user saat ini (Entah itu Induk atau Anak)
        if ($this->bidang_id) {
            $ids[] = $this->bidang_id;
        }

        // 2. Jika dia ada di sub-bidang, ambil juga ID induknya
        // (Asumsi: Staf kadang mengerjakan tugas umum bidang)
        if ($this->isDiSubBidang()) {
            $induk = $this->getBidangInduk();
            if ($induk) {
                $ids[] = $induk->id;
            }
        }

        return $ids;
    }

    // =========================================================================
    // EXISTING LOGIC
    // =========================================================================

    public function getFotoProfilUrlAttribute()
    {
        return $this->foto_profil
            ? Storage::disk('public')->url($this->foto_profil)
            : asset('assets/man.png');
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

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

    public function skpTarget()
    {
        return $this->hasMany(SkpTarget::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }
}