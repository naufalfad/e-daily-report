<?php

namespace App\Enums;

enum NotificationType: string
{
    // 1. LKH
    case REMINDER_LKH = 'reminder_lkh';
    case LKH_APPROVED = 'lkh_approved';
    case LKH_REJECTED = 'lkh_rejected';

    case LKH_NEW_SUBMISSION = 'lkh_new_submission';
    case LKH_UPDATE_SUBMISSION = 'lkh_update_submission';

    // 2. SKP
    case SKP_SUBMITTED = 'skp_submitted';
    case SKP_APPROVED  = 'skp_approved';
    case SKP_REJECTED  = 'skp_rejected';

    // 3. Umum
    case PENGUMUMAN  = 'pengumuman';
    case INFO_SYSTEM = 'info_system';

    // ===== HUMAN TITLE =====
    public function title(): string
    {
        return match($this) {
            self::REMINDER_LKH => 'Pengingat LKH',
            self::LKH_APPROVED => 'LKH Anda Disetujui',
            self::LKH_REJECTED => 'LKH Anda Ditolak',

            self::LKH_NEW_SUBMISSION => 'Pengajuan LKH Baru',
            self::LKH_UPDATE_SUBMISSION => 'Perubahan LKH Bawahan',

            self::SKP_SUBMITTED => 'Pengajuan SKP Baru',
            self::SKP_APPROVED  => 'SKP Disetujui',
            self::SKP_REJECTED  => 'SKP Ditolak',

            self::PENGUMUMAN   => 'Pengumuman Baru',
            self::INFO_SYSTEM  => 'Informasi Sistem',
            default             => 'Notifikasi',
        };
    }

    // ===== HUMAN MESSAGE DEFAULT =====
    public function defaultMessage(): string
    {
        return match($this) {
            self::LKH_NEW_SUBMISSION =>
                'Ada laporan baru dari bawahan Anda.',
            self::LKH_UPDATE_SUBMISSION =>
                'Bawahan Anda melakukan perubahan pada laporan sebelumnya.',

            self::LKH_APPROVED =>
                'LKH Anda telah dinilai dan disetujui.',
            self::LKH_REJECTED =>
                'LKH Anda ditolak. Silakan perbaiki dan ajukan kembali.',

            self::SKP_SUBMITTED =>
                'Terdapat pengajuan SKP baru dari bawahan.',
            self::SKP_APPROVED =>
                'SKP Anda telah disetujui.',
            self::SKP_REJECTED =>
                'SKP Anda ditolak. Cek alasannya ya.',

            default => 'Anda memiliki notifikasi baru.'
        };
    }
}