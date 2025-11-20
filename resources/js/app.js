import '../css/app.css';
import './login.js';
import './pages/staf/dashboard.js';
import './pages/staf/input-lkh.js';
import './pages/staf/input-skp.js';
import './pages/staf/log-aktivitas.js';
import './pages/staf/peta-aktivitas.js';
import './pages/staf/riwayat-lkh.js';
import './pages/penilai/dashboard.js';
import './pages/penilai/input-lkh.js';
import './pages/penilai/input-skp.js';
import './pages/penilai/log-aktivitas.js';
import './pages/penilai/peta-aktivitas.js';
import './pages/penilai/riwayat.js';
import './pages/penilai/validasi-laporan.js';
import './pages/penilai/pengumuman.js';
import './pages/admin/dashboard.js';
import './pages/admin/manajemen-pegawai.js';
import './pages/admin/log-aktivitas.js';
import './pages/admin/akun-pengguna.js';
import './pages/admin/setting-sistem.js';
import './pages/kadis/dashboard.js';
import './pages/kadis/log-aktivitas.js';
import './pages/kadis/validasi-laporan.js';
import './global/loader.js';

// Logika Global untuk Logout
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('btn-logout');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault(); // Cegah link default

            // Konfirmasi (Opsional)
            if(!confirm('Apakah Paduka yakin ingin keluar?')) return;

            // Ambil token dari storage
            const token = localStorage.getItem('auth_token');

            try {
                // Panggil API Logout untuk invalidasi token di server
                if (token) {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                }
            } catch (error) {
                console.warn('Gagal logout di server, tetap lakukan logout lokal.', error);
            } finally {
                // Hapus sesi lokal & Redirect
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                window.location.href = '/login';
            }
        });
    }
});
