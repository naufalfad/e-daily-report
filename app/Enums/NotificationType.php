<?php

namespace App\Enums;

enum NotificationType: string
{
    // 1. Terkait LKH (Laporan Kinerja Harian)
    case REMINDER_LKH = 'reminder_lkh';
    case LKH_APPROVED = 'lkh_approved';
    case LKH_REJECTED = 'lkh_rejected';
    
    // 2. Terkait SKP (Sasaran Kinerja Pegawai)
    case SKP_SUBMITTED = 'skp_submitted';
    case SKP_APPROVED  = 'skp_approved';
    case SKP_REJECTED  = 'skp_rejected';

    // 3. Umum
    case PENGUMUMAN  = 'pengumuman';
    case INFO_SYSTEM = 'info_system';

    /**
     * Helper untuk mendapatkan label yang manusiawi (Opsional)
     */
    public function label(): string
    {
        return match($this) {
            self::REMINDER_LKH => 'Pengingat LKH',
            self::LKH_APPROVED => 'LKH Disetujui',
            self::LKH_REJECTED => 'LKH Ditolak',
            self::PENGUMUMAN   => 'Pengumuman',
            default            => 'Notifikasi',
        };
    }
}