import '../css/app.css';
import './pages/login.js';
import './pages/staf/input-lkh.js';
import './pages/penilai/validasi-laporan.js';
import './pages/penilai/pengumuman.js';
import './pages/admin/manajemen-pegawai.js';
import './pages/admin/akun-pengguna.js';

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


// ==================== TAMBAHAN: Sidebar toggle + Modal Profil ====================

document.addEventListener('DOMContentLoaded', () => {
    // Sidebar toggle (mobile)
    const sidebarToggle = document.getElementById('sb-toggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    // Modal Profil
    const openProfileBtn = document.getElementById('btn-open-profile-modal');
    const closeProfileBtn = document.getElementById('btn-close-profile-modal');
    const profileModal = document.getElementById('profile-modal');

    const openProfileModal = () => {
        if (!profileModal) return;
        profileModal.classList.remove('hidden');
        profileModal.classList.add('flex');
    };

    const closeProfileModal = () => {
        if (!profileModal) return;
        profileModal.classList.add('hidden');
        profileModal.classList.remove('flex');
    };

    if (openProfileBtn && profileModal) {
        openProfileBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openProfileModal();
        });
    }

    if (closeProfileBtn && profileModal) {
        closeProfileBtn.addEventListener('click', () => {
            closeProfileModal();
        });
    }

    // Klik area gelap di luar card => tutup
    if (profileModal) {
        profileModal.addEventListener('click', (e) => {
            if (e.target === profileModal) {
                closeProfileModal();
            }
        });
    }

    // Tekan ESC => tutup
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && profileModal && !profileModal.classList.contains('hidden')) {
            closeProfileModal();
        }
    });
});
