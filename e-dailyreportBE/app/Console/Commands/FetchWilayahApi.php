<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http; // <-- Import HTTP Client
use App\Models\MasterProvinsi; // <-- Import model

class FetchWilayahApi extends Command
{
    protected $signature = 'app:fetch-wilayah-api';
    protected $description = 'Fetch data wilayah dari API Binderbyte dan simpan ke DB';

    public function handle()
    {
        $this->info('Memulai mengambil data Provinsi...');

        // (Ganti API Key Anda)
        $response = Http::get('https://api.binderbyte.com/wilayah/provinsi', [
        'api_key' => env('BINDERBYTE_API_KEY')
    ]);

        if ($response->successful()) {
            $provinsi = $response->json()['value'];

            foreach ($provinsi as $prov) {
                MasterProvinsi::updateOrCreate(
                    ['id' => $prov['id']], // Cari berdasarkan ID
                    ['nama' => $prov['name']]  // Update atau Create
                );
            }

            $this->info('Data Provinsi berhasil disimpan.');
            // TODO: Tambahkan logika untuk ambil Kabupaten, Kecamatan, Kelurahan
        } else {
            $this->error('Gagal mengambil data Provinsi.');
        }
    }
}