<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * * Menambahkan metadata lokasi untuk keperluan Audit Trail (Anti-Fraud).
     */
    public function up(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            // 1. Location Provider
            // Menyimpan sumber data koordinat.
            // Tipe: String(20) cukup untuk menampung enum value ('gps_device', 'manual_pin', 'search_result').
            // Default: 'manual_pin' (Asumsi terburuk: jika sistem gagal detect, dianggap manual).
            if (!Schema::hasColumn('laporan_harian', 'location_provider')) {
                $table->string('location_provider', 20)
                    ->default('manual_pin') 
                    ->after('lokasi_teks') // Placement agar rapi di GUI Database
                    ->comment('Origin: gps_device, manual_pin, search_result');
            }

            // 2. Location Accuracy
            // Menyimpan radius akurasi GPS dalam meter.
            // Tipe: Decimal(8,2) dipilih daripada Float untuk presisi angka pasti tanpa floating-point error.
            if (!Schema::hasColumn('laporan_harian', 'location_accuracy')) {
                $table->decimal('location_accuracy', 8, 2)
                    ->nullable()
                    ->after('location_provider')
                    ->comment('GPS accuracy radius in meters (null if manual)');
            }

            // 3. Address Auto (System Generated)
            // Menyimpan alamat hasil reverse geocoding oleh sistem.
            // Tipe: Text (karena alamat bisa panjang).
            // Tujuan: Sebagai pembanding (validator) terhadap 'lokasi_teks' yang diinput manual user.
            if (!Schema::hasColumn('laporan_harian', 'address_auto')) {
                $table->text('address_auto')
                    ->nullable()
                    ->after('location_accuracy')
                    ->comment('System generated reverse geocoding for audit comparison');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropColumn([
                'location_provider',
                'location_accuracy',
                'address_auto'
            ]);
        });
    }
};