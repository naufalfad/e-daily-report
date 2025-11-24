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
import './global/notification.js';

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

    /* ================================
    NOTIFIKASI GLOBAL (DASHBOARD)
    ================================ */

    // Template bikin 1 notifikasi
    function createNotif({ type = 'info', title = '', message = '' }) {

        const colors = {
            success: {
                icon: '/assets/icon/check-circle-green.svg',
                bg: 'bg-white',
                border: 'border-green-500'
            },
            error: {
                icon: '/assets/icon/error-circle-red.svg',
                bg: 'bg-white',
                border: 'border-red-500'
            },
            warning: {
                icon: '/assets/icon/warning-circle-yellow.svg',
                bg: 'bg-white',
                border: 'border-yellow-500'
            },
            info: {
                icon: '/assets/icon/info-circle-blue.svg',
                bg: 'bg-white',
                border: 'border-blue-500'
            }
        };

        const cfg = colors[type] || colors.info;

        const wrapper = document.createElement('div');
        wrapper.className = `
            notif-item shadow-lg border-l-4 ${cfg.border} ${cfg.bg}
            w-[360px] p-4 rounded-md flex gap-3 animate-slideIn
        `;
        wrapper.innerHTML = `
            <img src="${cfg.icon}" class="w-6 h-6" />
            <div class="flex-1">
                <div class="font-semibold text-sm">${title}</div>
                <div class="text-xs text-slate-600">${message}</div>
            </div>
        `;

        // auto-remove 4s
        setTimeout(() => {
            wrapper.classList.add('animate-slideOut');
            setTimeout(() => wrapper.remove(), 300);
        }, 4000);

        return wrapper;
    }

    // Kontainer notifikasi (otomatis)
    function ensureNotifContainer() {
        let c = document.getElementById('notif-container');
        if (!c) {
            c = document.createElement('div');
            c.id = 'notif-container';
            c.className = `
                fixed top-20 right-10 z-[9990]
                flex flex-col gap-3 pointer-events-none
            `;
            document.body.appendChild(c);
        }
        return c;
    }

    // API untuk munculin notifikasi
    window.pushNotif = function ({ type = 'info', title = '', message = '' }) {
        const container = ensureNotifContainer();
        container.appendChild(createNotif({ type, title, message }));
    };

});
