<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum LocationProvider
 *
 * Bertindak sebagai Information Expert untuk jenis penyedia lokasi.
 * Menggantikan hardcoded strings untuk type safety dan konsistensi data.
 */
enum LocationProvider: string
{
    // Case: Lokasi diambil langsung dari GPS Browser/Device (High Trust)
    case GPS_DEVICE = 'gps_device';

    // Case: User menggeser pin secara manual di peta (Medium Trust)
    case MANUAL_PIN = 'manual_pin';

    // Case: User mencari lokasi via Search Bar/Geocoding (Medium/Low Trust)
    case SEARCH_RESULT = 'search_result';

    /**
     * Mendapatkan label yang manusiawi untuk keperluan UI/Reporting.
     * Penerapan Pola: Information Expert (Enum tahu cara menampilkan dirinya).
     */
    public function label(): string
    {
        return match($this) {
            self::GPS_DEVICE => 'GPS Perangkat (Otomatis)',
            self::MANUAL_PIN => 'Pin Manual (Peta)',
            self::SEARCH_RESULT => 'Hasil Pencarian Alamat',
        };
    }

    /**
     * Helper untuk menentukan apakah provider ini dianggap "High Integrity".
     * Berguna untuk logic validasi atau flagging fraud di masa depan.
     */
    public function isHighIntegrity(): bool
    {
        return $this === self::GPS_DEVICE;
    }
}