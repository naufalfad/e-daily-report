// =========================
// GLOBAL STYLE & LOGIN
// =========================
import '../css/app.css';
import '../js/pages/login.js';

// =========================
// GLOBAL UTILITY
// =========================
import './global/loader.js';
import './global/notification.js';
import './utils/auth-fetch';

// Chart global (boleh)
import Chart from 'chart.js/auto';
window.Chart = Chart;

// =========================
// NOTIFIKASI GLOBAL FIX
// =========================

document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('btn-logout');

    // =========================
    // 1. LOGOUT FIX
    // =========================
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
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

    // Badge counter
    const count = notifications.length;
    notifBadge.textContent = count > 9 ? "9+" : count;

    // Render notif
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
