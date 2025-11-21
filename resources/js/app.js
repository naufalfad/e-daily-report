import '../css/app.css';

// HAPUS atau KOMENTARI baris-baris di bawah ini agar tidak double-load
// karena file-file ini sudah dipanggil via @vite() di masing-masing file Blade.
// import './pages/login.js';
// import './pages/staf/input-lkh.js';
// import './pages/penilai/validasi-laporan.js';
// import './pages/penilai/pengumuman.js';  <-- INI BIANG KEROKNYA TUANKU
// import './pages/admin/manajemen-pegawai.js';
// import './pages/admin/akun-pengguna.js';

// =============================================================================
// GLOBAL LOGIC (Berjalan di semua halaman)
// =============================================================================

// 1. Logika Sidebar & Modal Profil
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

    // Klik area gelap di luar card -> tutup
    if (profileModal) {
        profileModal.addEventListener('click', (e) => {
            if (e.target === profileModal) {
                closeProfileModal();
            }
        });
    }

    // Tekan ESC -> tutup
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && profileModal && !profileModal.classList.contains('hidden')) {
            closeProfileModal();
        }
    });
});

// 2. Logika Global Logout
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan ID tombol logout di sidebar sesuai ('btn-logout' atau di dalam form)
    // Jika menggunakan form submit biasa (seperti di kode sidebar sebelumnya),
    // kode di bawah ini mungkin tidak terpakai, tapi disiapkan untuk AJAX logout.
    const logoutBtn = document.getElementById('btn-logout'); 

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault(); 

            if(!confirm('Apakah Paduka yakin ingin keluar?')) return;

            const token = localStorage.getItem('auth_token');

            try {
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
                sessionStorage.setItem('logout_message', 'Anda berhasil logout, silakan login ulang.');
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                
                // Redirect via Javascript window location (bukan fetch)
                // Pastikan route logout di web.php menangani session destroy juga
                document.getElementById('logout-form').submit(); 
                // Atau jika full AJAX: window.location.href = '/login';
            }
        });
    }
    
    // Menampilkan pesan logout (Flash Message via JS)
    const logoutMessage = sessionStorage.getItem('logout_message');
    if (logoutMessage) {
        // Cari elemen alert di halaman login (jika ada)
        const authMessageEl = document.getElementById('auth-message');
        if (authMessageEl) {
            authMessageEl.textContent = logoutMessage;
            authMessageEl.classList.remove('hidden');
            setTimeout(() => {
                authMessageEl.classList.add('hidden');
            }, 5000);
        }
        sessionStorage.removeItem('logout_message');
    }
});