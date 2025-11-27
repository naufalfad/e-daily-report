import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/login.js',
                
                // Admin & Kadis
                'resources/js/pages/admin/dashboard.js',
                'resources/js/pages/kadis/dashboard.js',
                
                // Penilai
                'resources/js/pages/penilai/dashboard.js',
                'resources/js/pages/penilai/validasi-laporan.js',
                'resources/js/pages/penilai/pengumuman.js',
                'resources/js/pages/penilai/input-skp.js',
                'resources/js/pages/penilai/skoring-kinerja.js',
                // [PENTING] Tambahkan JS Riwayat Penilai jika belum ada
                'resources/js/pages/penilai/riwayat.js', 

                // Staf
                'resources/js/pages/staf/dashboard.js',
                // [PERBAIKAN UTAMA] Tambahkan JS Riwayat Staf di sini
                'resources/js/pages/staf/riwayat.js', 
            ],
            refresh: true,
        }),
    ],
});