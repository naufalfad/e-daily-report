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
import { riwayatData } from './pages/penilai/riwayat.js';
window.riwayatData = riwayatData;
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
import './utils/auth-fetch';
import Chart from 'chart.js/auto';
window.Chart = Chart;

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

        const notifBadge = document.getElementById("notif-badge");
    const notifList = document.getElementById("notif-list");

    if (!notifBadge || !notifList) return;

    // ===========================
    // 1. DUMMY / BACKEND DATA
    // ===========================
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

    // ===========================
    // 2. MAP ICON NOTIF
    // ===========================
    const iconMap = {
        success: {
            bg: "bg-[#0E7A4A]/10",
            icon: "/assets/icon/notif-success.svg"
        },
        warning: {
            bg: "bg-[#FACC15]/10",
            icon: "/assets/icon/notif-warning.svg"
        },
        danger: {
            bg: "bg-[#DC2626]/10",
            icon: "/assets/icon/notif-danger.svg"
        }
    };

    // ===========================
    // 3. UPDATE BADGE
    // ===========================
    const count = notifications.length;
    notifBadge.textContent = count > 9 ? "9+" : count;

    // ===========================
    // 4. RENDER NOTIFIKASI
    // ===========================
    notifications.forEach(n => {
        const icon = iconMap[n.type] ?? iconMap["warning"];

        const html = `
            <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 transition">

                <div class="w-10 h-10 rounded-full ${icon.bg} flex items-center justify-center">
                    <img src="${icon.icon}" class="w-5 h-5">
                </div>

                <div class="flex-1">
                    <div class="text-[13px] font-semibold text-slate-800">${n.title}</div>
                    <p class="text-[12px] text-slate-500">${n.message}</p>
                    <span class="text-[11px] text-slate-400">${n.date}</span>
                </div>
            </div>
        `;

        notifList.insertAdjacentHTML("beforeend", html);
    });
});