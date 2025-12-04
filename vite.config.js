import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    base: '/',

    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/login.js',

                // Admin
                'resources/js/pages/admin/dashboard.js',
                'resources/js/pages/admin/manajemen-pegawai.js',
                'resources/js/pages/admin/log-aktivitas.js',
                'resources/js/pages/admin/akun-pengguna.js',
                'resources/js/pages/admin/setting-sistem.js',

                // Kadis
                'resources/js/pages/kadis/dashboard.js',
                'resources/js/pages/kadis/log-aktivitas.js',
                'resources/js/pages/kadis/validasi-laporan.js',

                // Penilai
                'resources/js/pages/penilai/dashboard.js',
                'resources/js/pages/penilai/validasi-laporan.js',
                'resources/js/pages/penilai/pengumuman.js',
                'resources/js/pages/penilai/input-skp.js',
                'resources/js/pages/penilai/skoring-kinerja.js',
                'resources/js/pages/penilai/input-lkh.js',
                'resources/js/pages/penilai/riwayat.js',
                'resources/js/pages/penilai/peta-aktivitas.js',
                'resources/js/pages/penilai/log-aktivitas.js',

                // Staf
                'resources/js/pages/staf/dashboard.js',
                'resources/js/pages/staf/input-skp.js',
                'resources/js/pages/staf/peta-aktivitas.js',
                'resources/js/pages/staf/log-aktivitas.js',
                'resources/js/pages/staf/input-lkh.js',
                'resources/js/pages/staf/riwayat.js',

                'resources/js/profile-modal.js',
            ],
            refresh: true,
        }),
    ],
});
