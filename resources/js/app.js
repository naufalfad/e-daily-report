import '../css/app.css';
import './pages/login.js';
import './pages/staf/input-lkh.js';

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