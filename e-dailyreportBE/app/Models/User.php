<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use AngelSourceLabs\LaravelSpatial\Eloquent\SpatialTrait; // Pastikan library ini terinstall
use Illuminate\Support\Facades\Storage; // <-- PENTING: Untuk MinIO

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SpatialTrait;

    /**
     * Kolom yang tidak boleh diisi secara mass assignment.
     * Kosongkan array agar semua kolom bisa diisi (unguarded).
     */
    protected $guarded = [];

    /**
     * Atribut yang harus disembunyikan saat return JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting tipe data otomatis.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Konfigurasi kolom Spatial (PostGIS).
     * Jika tabel users belum punya kolom geometry, biarkan array kosong.
     */
    protected $spatialFields = [];

    /**
     * Menambahkan atribut custom ke dalam JSON response otomatis.
     */
    protected $appends = ['foto_profil_url'];

    // ======================================================================
    // ACCESSORS (Atribut Tambahan)
    // ======================================================================

    /**
     * Mengubah path 'profil/namafile.jpg' menjadi URL lengkap MinIO
     * Contoh: http://127.0.0.1:9000/edaily-report/profil/namafile.jpg
     */
    public function getFotoProfilUrlAttribute()
    {
        if ($this->foto_profil) {
            // Pastikan disk 'minio' sudah dikonfigurasi di config/filesystems.php
            return Storage::disk('minio')->url($this->foto_profil);
        }
        
        // Return null atau URL avatar default jika tidak ada foto
        // return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
        return null; 
    }

    // ======================================================================
    // RELASI ORGANISASI & HIERARKI
    // ======================================================================

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Atasan langsung dari user ini.
     */
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    /**
     * Daftar bawahan yang melapor ke user ini.
     */
    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    /**
     * Role User (Super Admin, Pegawai, Penilai, Kadis)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    // ======================================================================
    // RELASI CORE BUSINESS (SKP & LKH)
    // ======================================================================

    /**
     * SKP yang dibuat oleh user ini.
     */
    public function skp()
    {
        return $this->hasMany(Skp::class, 'user_id');
    }

    /**
     * Laporan Harian yang dibuat oleh user ini.
     */
    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class, 'user_id');
    }

    /**
     * Laporan Harian yang divalidasi OLEH user ini (sebagai Atasan/Validator).
     */
    public function laporanValidasi()
    {
        return $this->hasMany(LaporanHarian::class, 'validator_id');
    }

    // ======================================================================
    // RELASI PENDUKUNG (Notif, Pengumuman, Log)
    // ======================================================================

    /**
     * Pengumuman yang DIBUAT oleh user ini.
     */
    public function pengumumanDibuat()
    {
        return $this->hasMany(Pengumuman::class, 'user_id_creator');
    }

    /**
     * Notifikasi yang DITERIMA oleh user ini.
     */
    public function notifikasiDiterima()
    {
        return $this->hasMany(Notifikasi::class, 'user_id_recipient');
    }

    /**
     * Log aktivitas yang dilakukan user ini.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}