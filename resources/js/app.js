// resources/js/app.js

// Global fetch interceptor to handle CSRF tokens, Authorization headers, and AJAX indicators automatically.
const { fetch: originalFetch } = window;
window.fetch = async (resource, config = {}) => {
    // Check if the URL is local/relative or matches the current origin to avoid sending headers to third-party APIs
    let isLocalRequest = false;
    try {
        const urlObj = new URL(resource, window.location.origin);
        isLocalRequest = urlObj.origin === window.location.origin;
    } catch (e) {
        isLocalRequest = false;
    }

    if (isLocalRequest) {
        const headers = config.headers instanceof Headers 
            ? config.headers 
            : new Headers(config.headers || {});

        // 1. Add X-Requested-With header to mark request as AJAX (ensuring JSON responses from Laravel)
        if (!headers.has('X-Requested-With')) {
            headers.set('X-Requested-With', 'XMLHttpRequest');
        }

        // 2. Automatically add CSRF Token from meta tag
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfTokenMeta) {
            const tokenVal = csrfTokenMeta.getAttribute('content');
            if (tokenVal && !headers.has('X-CSRF-TOKEN')) {
                headers.set('X-CSRF-TOKEN', tokenVal);
            }
        }

        // 3. Automatically add Authorization Bearer Token if stored in localStorage
        const authToken = localStorage.getItem('auth_token');
        if (authToken && !headers.has('Authorization')) {
            headers.set('Authorization', `Bearer ${authToken}`);
        }

        config.headers = headers;
    }

    const response = await originalFetch(resource, config);

    if (isLocalRequest && response.status === 401) {
        // Prevent redirect loop if already on login page
        if (!window.location.pathname.startsWith('/login')) {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
    }

    return response;
};

// =========================
// GLOBAL STYLE & LOGIN
// =========================
import '../css/app.css';
import '../js/pages/login.js';
import '../js/profile-modal.js'

// =========================
// 1. IMPORT ENGINE ALPINE (WAJIB UNTUK MODAL)
// =========================
import Alpine from 'alpinejs';

// [TAMBAHAN BARU] Import Plugin Collapse
import collapse from '@alpinejs/collapse';
Alpine.plugin(collapse); // Daftarkan plugin

import MapInput from './components/map-input.js';

// =========================
// 2. IMPORT LOGIKA HALAMAN ADMIN
// =========================
// Import fungsi logika halaman manajemen pegawai (Eksisting)
import { manajemenPegawaiData } from './pages/admin/manajemen-pegawai.js';
// Import fungsi logika halaman akun pengguna (Eksisting)
import { akunPenggunaData } from './pages/admin/akun-pengguna.js';
// [PERBAIKAN UTAMA] Import fungsi logika halaman pengaturan sistem
import { systemSettingsData } from './pages/admin/setting-sistem.js';
import { penilaiMapData } from './pages/penilai/peta-aktivitas.js';
import { stafMapData } from './pages/staf/peta-aktivitas.js';
import { kadisMapData } from './pages/kadis/peta-aktivitas.js';
import { riwayatDataPenilai } from './pages/penilai/riwayat.js';
import { riwayatDataStaf} from './pages/staf/riwayat.js';
import { logActivityKadis } from './pages/kadis/log-aktivitas.js';
import { logActivityDataAdmin } from './pages/admin/log-aktivitas.js';
import { riwayatCore } from './public/riwayat-core.js';


// =========================
// 3. REGISTRASI GLOBAL (Fix: Menggunakan Alpine.data untuk Robustness)
// =========================
window.MapInput = MapInput;
window.Alpine = Alpine;

// Ganti SEMUA penugasan 'window.xxx = xxx' menjadi Alpine.data()
Alpine.data('manajemenPegawaiData', manajemenPegawaiData); 
Alpine.data('akunPenggunaData', akunPenggunaData);
Alpine.data('systemSettingsData', systemSettingsData);
Alpine.data('penilaiMapData', penilaiMapData);
Alpine.data('stafMapData', stafMapData); // FIX UTAMA: Registrasi sebagai komponen Alpine
Alpine.data('kadisMapData', kadisMapData);
Alpine.data('riwayatDataPenilai', riwayatDataPenilai);
Alpine.data('riwayatDataStaf', riwayatDataStaf);
Alpine.data('logActivityKadis', logActivityKadis);
Alpine.data('logActivityDataAdmin', logActivityDataAdmin);
Alpine.data('riwayatCore', riwayatCore);


// =========================
// 4. NYALAKAN MESIN ALPINE (KUNCI UTAMA)
// =========================
Alpine.start();


// =========================
// GLOBAL UTILITY
// =========================
import './global/loader.js';
import './global/notification.js';
import './utils/auth-fetch';
import './public/riwayat-core.js';

// =========================
// STAF
// =========================
import './pages/staf/input-skp.js';
import './pages/staf/log-aktivitas.js';
import './pages/staf/peta-aktivitas.js'; 
import './pages/staf/pengumuman.js';

// =========================
// PENILAI
// =========================
import './pages/penilai/input-skp.js';
import './pages/penilai/log-aktivitas.js';
import './pages/penilai/pengumuman.js';
import './pages/admin/log-aktivitas.js';

// Chart global (boleh)
import Chart from 'chart.js/auto';
window.Chart = Chart;

// =========================
// NOTIFIKASI GLOBAL FIX (LOGIC BAWAAN)
// =========================
import '@fortawesome/fontawesome-free/css/all.min.css';


document.addEventListener('DOMContentLoaded', function () {

    const logoutBtn = document.getElementById('btn-logout');

    // =========================
    // 1. LOGOUT FIX
    // =========================
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: 'Sesi Anda akan diakhiri.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, keluar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then(async (result) => {

                if (!result.isConfirmed) return;

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
                    console.warn('Logout server gagal, lanjut logout lokal');
                }

                // Hapus session local
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');

                Swal.fire({
                    title: 'Berhasil Logout',
                    text: 'Anda telah keluar dari aplikasi.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });

                setTimeout(() => window.location.href = '/login', 900);
            });
        });
    }

    // =========================
    // LOGOUT SWEETALERT (BARU)
    // =========================
    const logoutForm = document.getElementById('logout-form');

    if (logoutBtn && logoutForm) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Yakin ingin logout?',
                text: 'Sesi Anda akan diakhiri.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1C7C54',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    logoutForm.submit();
                }
            });
        });
    }

    // =========================
    // 2. NOTIFIKASI GLOBAL FIX
    // =========================
    const notifBadge = document.getElementById("notif-badge");
    const notifList = document.getElementById("notif-list");

    if (!notifBadge || !notifList) return;

    // Dummy backend
    const notifications = [
        {
            type: "success",
            title: "Laporan Telah Diterima!",
            message: "Laporan harian Anda berhasil diverifikasi oleh Penilai.",
            date: "09 Nov 2025"
        },
        {
            type: "danger",
            title: "Laporan Ditolak!",
            message: "Laporan Anda ditolak. Silakan periksa kembali dan lengkapi datanya.",
            date: "09 Nov 2025"
        },
        {
            type: "warning",
            title: "Menunggu Verifikasi",
            message: "Laporan Anda sedang menunggu pengecekan Penilai.",
            date: "08 Nov 2025"
        }
    ];

    // =========================
    // 3. FIX iconMap TIDAK UNDEFINED
    // =========================
    const iconMap = {
        success: {
            bg: "bg-[#0E7A4A]/10",
            icon: "fas fa-check-circle text-[#0E7A4A]"
        },
        warning: {
            bg: "bg-[#FACC15]/10",
            icon: "fas fa-exclamation-triangle text-[#FACC15]"
        },
        danger: {
            bg: "bg-[#DC2626]/10",
            icon: "fas fa-times-circle text-[#DC2626]"
        }
    };

    // Badge counter
    const count = notifications.length;
    notifBadge.textContent = count > 9 ? "9+" : count;

    // Render notif
    notifications.forEach(n => {
        const icon = iconMap[n.type] ?? iconMap["warning"];

        const html = `
            <div class="flex items-start gap-1.5 p-2 rounded-none hover:bg-slate-50 transition">

                <div class="w-10 h-10 rounded-full ${icon.bg} flex items-center justify-center shrink-0">
                    <i class="${icon.icon} text-lg"></i>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-semibold text-slate-800 truncate">${n.title}</div>
                    <p class="text-[12px] text-slate-500 line-clamp-2">${n.message}</p>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">${n.date}</span>
                </div>
            </div>
        `;

        notifList.insertAdjacentHTML("beforeend", html);
    });
});