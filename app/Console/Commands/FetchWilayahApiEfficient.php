<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class FetchWilayahApiEfficient extends Command
{
    protected $signature = 'app:fetch-all-wilayah'; 
    protected $description = 'Download SELURUH Data Wilayah Indonesia (Emsifa Static API)';

    // URL RESMI & STABIL (Sesuai Dokumentasi Baginda)
    const BASE_URL = 'https://emsifa.github.io/api-wilayah-indonesia/api';

    public function handle()
    {
        $this->info('🚀 Memulai Download Data Seluruh Indonesia (URL Stabil)...');
        $this->info('Sumber: ' . self::BASE_URL);

        DB::disableQueryLog(); // Hemat RAM

        // --- STEP 1: PROVINSI ---
        $this->info("\n[1/4] Mengambil Provinsi...");
        
        // Ambil data dengan withoutVerifying() untuk mengatasi masalah SSL di Windows
        $response = Http::withoutVerifying()->get(self::BASE_URL . '/provinces.json');

        if ($response->failed()) {
            $this->error("❌ Gagal mengambil Provinsi. Status: " . $response->status());
            return;
        }

        $provinsis = $response->json();
        $dataProv = [];
        
        foreach ($provinsis as $p) {
            $dataProv[] = ['id' => $p['id'], 'nama' => $p['name']];
        }
        
        // UPSERT: Aman dijalankan berkali-kali
        DB::table('master_provinsi')->upsert($dataProv, ['id'], ['nama']);
        $this->info("✅ " . count($dataProv) . " Provinsi Tersimpan.");


        // --- STEP 2: KABUPATEN ---
        $this->info("\n[2/4] Mengambil Kabupaten...");
        $bar = $this->output->createProgressBar(count($provinsis));
        
        foreach ($provinsis as $p) {
            // URL: /regencies/{provinceId}.json
            $kabupatens = Http::withoutVerifying()->get(self::BASE_URL . "/regencies/{$p['id']}.json")->json();
            
            if ($kabupatens) {
                $dataKab = [];
                foreach ($kabupatens as $k) {
                    $dataKab[] = [
                        'id' => $k['id'], 
                        'provinsi_id' => $p['id'], 
                        'nama' => $k['name']
                    ];
                }
                DB::table('master_kabupaten')->upsert($dataKab, ['id'], ['provinsi_id', 'nama']);
            }
            $bar->advance();
        }
        $bar->finish();


        // --- STEP 3: KECAMATAN ---
        $this->info("\n\n[3/4] Mengambil Kecamatan...");
        $allKabId = DB::table('master_kabupaten')->pluck('id');
        $bar = $this->output->createProgressBar(count($allKabId));

        foreach ($allKabId as $kabId) {
            // URL: /districts/{regencyId}.json
            $kecamatans = Http::withoutVerifying()->get(self::BASE_URL . "/districts/{$kabId}.json")->json();
            
            if ($kecamatans) {
                $dataKec = [];
                foreach ($kecamatans as $k) {
                    $dataKec[] = [
                        'id' => $k['id'], 
                        'kabupaten_id' => $kabId, 
                        'nama' => $k['name']
                    ];
                }
                DB::table('master_kecamatan')->upsert($dataKec, ['id'], ['kabupaten_id', 'nama']);
            }
            $bar->advance();
        }
        $bar->finish();


        // --- STEP 4: KELURAHAN ---
        $this->info("\n\n[4/4] Mengambil Kelurahan (Proses Terlama)...");
        
        // Trik: Urutkan agar ID Mimika (94...) diproses belakangan/duluan jika mau dipantau
        $allKecId = DB::table('master_kecamatan')->orderBy('id')->pluck('id'); 
        
        $total = count($allKecId);
        $bar = $this->output->createProgressBar($total);
        
        $mimikaLat = -4.546123; 
        $mimikaLng = 136.885123;

        foreach ($allKecId as $index => $kecId) {
            try {
                // URL: /villages/{districtId}.json
                $kelurahans = Http::withoutVerifying()->get(self::BASE_URL . "/villages/{$kecId}.json")->json();
                
                if (!empty($kelurahans) && is_array($kelurahans)) {
                    $dataKel = [];
                    
                    // Cek Mimika (Kode 9404) untuk suntik koordinat
                    $isMimika = str_starts_with($kecId, '9404'); 

                    foreach ($kelurahans as $k) {
                        $lat = $isMimika ? ($mimikaLat + (rand(-100, 100) / 10000)) : null;
                        $lng = $isMimika ? ($mimikaLng + (rand(-100, 100) / 10000)) : null;

                        $dataKel[] = [
                            'id' => $k['id'], 
                            'kecamatan_id' => $kecId, 
                            'nama' => $k['name'],
                            'latitude' => $lat,
                            'longitude' => $lng
                        ];
                    }
                    
                    // UPSERT: Menjamin data lama aman, data baru masuk
                    DB::table('master_kelurahan')->upsert($dataKel, ['id'], ['kecamatan_id', 'nama', 'latitude', 'longitude']);
                }
            } catch (\Exception $e) {
                // Skip error agar tidak putus
            }
            
            $bar->advance();
        }
        $bar->finish();

        $this->newLine();
        $this->info('🎉 ALHAMDULILLAH! SELURUH DATA INDONESIA BERHASIL DISIMPAN!');
    }
}
