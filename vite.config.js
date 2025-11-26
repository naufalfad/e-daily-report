import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
    		'resources/css/app.css',
    		'resources/js/app.js',
   		'resources/js/pages/login.js',
		'resources/js/pages/admin/dashboard.js',
                'resources/js/pages/kadis/dashboard.js', // <-- Si Biang Kerok
                'resources/js/pages/penilai/dashboard.js',
                'resources/js/pages/staf/dashboard.js',  // <-- Yang Harusnya Jalan

		'resources/js/pages/penilai/validasi-laporan.js',
		'resources/js/pages/penilai/pengumuman.js',
	],
            refresh: true,
        }),
    ],
});
