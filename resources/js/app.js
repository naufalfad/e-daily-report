import '../css/app.css';
import './pages/login.js';
import './pages/staf/input-lkh.js';
import './pages/penilai/validasi-laporan.js';
import './pages/penilai/pengumuman.js';
import './pages/admin/manajemen-pegawai.js';
import './pages/admin/akun-pengguna.js';

// Logika Global untuk Logout
document.addEventListener('DOMContentLoaded', function() {
    // Ubah selector menjadi id="btn-logout" yang sudah diatur di sidebar.blade.php
    const logoutBtn = document.getElementById('btn-logout'); 

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault(); 

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
                // === PENAMBAHAN LOGIKA PESAN SUKSES & REDIRECT ===
                
                // 1. Simpan pesan sukses ke sessionStorage sebelum redirect
                const successMessage = 'Anda berhasil logout, silakan login ulang.';
                sessionStorage.setItem('logout_message', successMessage);
                
                // 2. Hapus data otentikasi lokal
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                
                // 3. Redirect ke halaman login
                window.location.href = '/login';
            }
        });
    }
    
    // LOGIKA PENAMPILAN PESAN (Harus ditambahkan di script yang berjalan di halaman login)
    // Walaupun ini seharusnya ada di resources/js/pages/login.js, 
    // saya masukkan ke sini sebagai referensi kode yang harus ditambahkan.
    const logoutMessage = sessionStorage.getItem('logout_message');
    
    if (logoutMessage) {
        // Asumsi: Ada elemen di halaman login (misalnya alert box atau toast) dengan id 'auth-message'
        const authMessageEl = document.getElementById('auth-message');
        
        if (authMessageEl) {
            authMessageEl.textContent = logoutMessage;
            authMessageEl.classList.remove('hidden'); // Tampilkan pesan
            // Opsional: Tambahkan logika untuk menghilangkan pesan setelah beberapa detik
            setTimeout(() => {
                authMessageEl.classList.add('hidden');
            }, 5000);
        } else {
            // Fallback jika tidak ada elemen HTML yang sesuai di halaman login
            alert(logoutMessage); 
        }

        // Hapus pesan dari storage setelah ditampilkan
        sessionStorage.removeItem('logout_message');
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
