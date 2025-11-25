// resources/js/pages/staf/dashboard.js

import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", async function() {
    // Ubah token retrieval sesuai metode auth Anda.
    // Jika tes di browser yang sudah login via session, tidak perlu header Authorization
    const token = localStorage.getItem("auth_token");

    const headers = {
        "Accept": "application/json"
    };
    if (token) {
        headers["Authorization"] = "Bearer " + token;
    }

    try {
        // Panggil API
        const response = await fetch("http://127.0.0.1:8000/api/dashboard/stats", {
            method: "GET",
            headers: headers
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Debugging: lihat apa yang diterima
        console.log("Data JSON:", data);

        /* =======================================================
         * 1. BANNER + PROFIL
         * =======================================================*/
        // JSON: user_info -> {name, jabatan, unit}
        // Tidak ada: nip, email, telepon, alamat

        const uInfo = data.user_info || {};

        // Set Nama
        document.getElementById("banner-nama").innerText = uInfo.name || "User";
        document.getElementById("profile-nama").innerText = uInfo.name || "-";

        // Set Jabatan & Unit
        document.getElementById("profile-jabatan").innerText = uInfo.jabatan || "-";
        document.getElementById("profile-dinas").innerText = uInfo.unit || "-";

        // Set Placeholder untuk data yang tidak ada di JSON
        document.getElementById("profile-nip").innerText = uInfo.nip || "-"; // Tidak ada di JSON
        document.getElementById("profile-alamat").innerText = uInfo.alamat || "-"; // Tidak ada di JSON
        document.getElementById("profile-email").innerText = uInfo.email || "-"; // Tidak ada di JSON
        document.getElementById("profile-telepon").innerText = uInfo.no_telp || "-"; // Tidak ada di JSON

        // Menggunakan kolom "Alamat" di UI untuk menampilkan "Target Tahunan" dari skor
        if (data.skoring_utama) {
            document.getElementById("profile-target").innerText = data.skoring_utama.target_tahunan +
                " Dokumen";
        }

        /* =======================================================
         * 2. STATISTIK RINGKAS
         * =======================================================*/

        // Card 1: Total Laporan SKP (Dari statistik_skp.total_diajukan)
        if (data.statistik_skp) {
            document.getElementById("stat-val-1").innerText = data.statistik_skp.total_diajukan;

            // Card 3: Persen Diterima
            document.getElementById("stat-val-3").innerText = data.statistik_skp.total_diterima;
            document.getElementById("stat-desc-3").innerText = data.statistik_skp.persen_diterima +
                "% Dari total diterima";

            // Card 4: Persen Ditolak
            document.getElementById("stat-val-4").innerText = data.statistik_skp.total_ditolak;
            document.getElementById("stat-desc-4").innerText = data.statistik_skp.persen_ditolak +
                "% Dari total ditolak";
        }

        // Card 2: Realisasi Tahunan (Dari skoring_utama)
        if (data.skoring_utama) {
            document.getElementById("stat-val-2").innerText = data.skoring_utama.realisasi_tahunan;
            document.getElementById("stat-desc-2").innerText = data.skoring_utama.persen_capaian +
                "% Capaian";
        }

        /* =======================================================
         * 3. AKTIVITAS TERKINI
         * =======================================================*/
        const listContainer = document.getElementById("aktivitas-list");
        listContainer.innerHTML = ""; // Clear loading text

        const aktivitas = data.aktivitas_terbaru || [];

        if (aktivitas.length === 0) {
            listContainer.innerHTML =
                '<li class="text-sm text-slate-500">Belum ada aktivitas terbaru.</li>';
        } else {
            aktivitas.forEach(item => {
                const dateObj = new Date(item.tanggal_laporan);
                const tanggalFormatted = dateObj.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long', // Ganti 'numeric' jika ingin angka bulan (11), 'long' jika nama bulan (November)
                    year: 'numeric'
                });
                // Tentukan warna/icon berdasarkan status text
                let tone = 'bg-slate-200';
                let iconName = 'pending.svg'; // default
                let statusLabel = item.status;

                if (item.status === 'approved') {
                    tone = 'bg-[#128C60]/50'; // Hijau
                    iconName = 'approve.svg';
                    statusLabel = 'Disetujui';
                } else if (item.status === 'rejected' || item.status.includes('reject')) {
                    tone = 'bg-[#B6241C]/50'; // Merah
                    iconName = 'reject.svg';
                    statusLabel = 'Ditolak';
                } else if (item.status === 'waiting_review') {
                    tone = 'bg-[#D8A106]/50'; // Kuning
                    iconName = 'pending.svg';
                    statusLabel = 'Menunggu Review';
                }

                const htmlItem = `
                <li class="flex items-start gap-3">
                    <div class="h-8 w-8 rounded-[10px] flex items-center justify-center ${tone}">
                        <img src="/assets/icon/${iconName}" class="h-5 w-5 opacity-90" alt="">
                    </div>
                    <div class="flex-1">
                        <div class="text-[13px] font-medium leading-snug truncate overflow-hidden text-ellipsis whitespace-nowrap" 
                            style="max-width: 250px;"
                            title="${item.deskripsi_aktivitas}">
                            ${item.deskripsi_aktivitas}
                        </div>
                        <div class="flex justify-between mt-[2px]">
                            <span class="text-xs text-slate-500 capitalize">${statusLabel}</span>
                            <span class="text-xs text-slate-500 whitespace-nowrap">${tanggalFormatted}</span>
                        </div>
                    </div>
                </li>`;

                listContainer.insertAdjacentHTML('beforeend', htmlItem);
            });
        }

        /* =======================================================
        * 4. DAFTAR DRAFT
        * =======================================================*/
        const draftContainer = document.getElementById("draft-list");
        draftContainer.innerHTML = ""; // Clear loading

        const draft = data.draft_terbaru || [];

        if (draft.length === 0) {
            draftContainer.innerHTML =
                '<li class="text-sm text-slate-500">Belum ada draft.</li>';
        } else {
            draft.forEach(item => {

                // Format tanggal
                const dateObj = new Date(item.updated_at);
                const tanggalFormatted = dateObj.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });

                const htmlItem = `
                <li class="rounded-xl bg-[#F1F5F9] px-3 py-2.5 flex items-start justify-between gap-4">
                    
                    <!-- BAGIAN TEKS -->
                    <div class="flex-1 min-w-0"> 
                        <div class="font-medium leading-tight text-[15px] truncate"
                            title="${item.deskripsi_aktivitas}">
                            ${item.deskripsi_aktivitas}
                        </div>
                        <div class="text-xs text-slate-500 mt-[2px] leading-tight">
                            Disimpan: ${tanggalFormatted}
                        </div>
                    </div>

                    <!-- BAGIAN TOMBOL -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button 
                            onclick="window.location.href='/penilai/input-laporan/${item.id}'"
                            class="rounded-[6px] bg-emerald-600 text-white text-[13px] px-3 py-[4px] leading-none shadow-sm hover:brightness-95">
                            Lanjutkan
                        </button>
                        <button type="button" onclick="deleteDraft('${item.id}')"
                            class="rounded-[6px] bg-[#B6241C] text-white text-[13px] px-3 py-[4px] leading-none shadow-sm hover:bg-rose-600/80">
                            Hapus
                        </button>
                    </div>
                </li>
                `;
                draftContainer.insertAdjacentHTML('beforeend', htmlItem);
            });
        }

        /* =======================================================
        * 5. GRAFIK KINERJA BULANAN (Dari aktivitas_terbaru)
        * =======================================================*/
        // Ambil aktivitas terbaru
        const aktivitasAll = data.grafik_aktivitas || [];

        // Siapkan array 12 bulan, default 0
        let monthlyTotal = Array(12).fill(0);
        let monthlyApproved = Array(12).fill(0);
        let monthlyRejected = Array(12).fill(0);

        // Loop aktivitas dan kelompokkan ke bulan
        aktivitasAll.forEach(item => {
            const dateObj = new Date(item.tanggal_laporan);
            const month = dateObj.getMonth(); // 0-11

            monthlyTotal[month]++;

            if (item.status === "approved") {
                monthlyApproved[month]++;
            } 
            else if (item.status === "rejected" || item.status.includes("reject")) {
                monthlyRejected[month]++;
            }
            else if (item.status === "draft") {
                monthlyTotal[month]--;
            }
        });

        // Label bulan
        const labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul",
                        "Agu", "Sep", "Okt", "Nov", "Des"];

        // Render Chart
        const ctx = document.getElementById("kinerjaBulananChart").getContext("2d");

        // Gradient Total
        const gradientTotal = ctx.createLinearGradient(0, 0, 0, 260);
        gradientTotal.addColorStop(0, "rgba(30, 64, 175, 0.25)");
        gradientTotal.addColorStop(1, "rgba(30, 64, 175, 0.00)");

        new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Total Laporan",
                        data: monthlyTotal,
                        borderColor: "#1E40AF",
                        backgroundColor: gradientTotal,
                        pointBackgroundColor: "#1E40AF",
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: "Laporan Diterima",
                        data: monthlyApproved,
                        borderColor: "#128C60",
                        pointBackgroundColor: "#128C60",
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: "Laporan Ditolak",
                        data: monthlyRejected,
                        borderColor: "#B6241C",
                        pointBackgroundColor: "#B6241C",
                        fill: false,
                        tension: 0.3
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom"
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

    } catch (err) {
        console.error("Gagal mengambil data API:", err);
    }
});
// Fungsi Hapus Laporan Global
window.deleteDraft = async function(id) {
    // 1. Konfirmasi User
    if(!confirm('Apakah Anda yakin ingin menghapus draft laporan ini?')) {
        return;
    }

    const token = localStorage.getItem("auth_token");

    try {
        // 2. Kirim Request DELETE
        const response = await fetch(`/api/lkh/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        // 3. Cek Response
        if (response.ok) {
            alert('Draft berhasil dihapus!');
            window.location.reload(); 
        } else {
            const res = await response.json();
            alert('Gagal menghapus: ' + (res.message || 'Terjadi kesalahan'));
        }

    } catch (error) {
        console.error('Error saat menghapus:', error);
        alert('Terjadi kesalahan koneksi.');
    }
}