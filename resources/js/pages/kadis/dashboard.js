// resources/js/pages/kadis/dashboard.js

import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", async function () {
    // Helper untuk set text aman
    const setText = (id, value = "-") => {
        const el = document.getElementById(id);
        if (el) el.innerText = value;
    };

    // Helper untuk format angka
    const formatNumber = (num) => {
        return new Intl.NumberFormat('id-ID').format(num);
    };

    // 1. Fetch Data dari API Baru (getStatsKadis)
    const token = localStorage.getItem("auth_token");
    const headers = { Accept: "application/json" };
    if (token) headers["Authorization"] = "Bearer " + token;

    let data;
    try {
        // Endpoint ini sekarang mengembalikan JSON struktur baru (User Info + Grafik Data per Bidang + Distribusi Lokasi Global)
        const res = await fetch(`/api/dashboard/kadis`, {
            method: "GET",
            headers,
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        data = await res.json();
        console.log("DATA DASHBOARD KADIS (NEW):", data);
    } catch (err) {
        console.error("Gagal mengambil data dashboard:", err);
        return;
    }

    /* =========================================================================
     * 1. PROFIL & BANNER
     * Mapping data user_info ke elemen HTML
     * =========================================================================*/
    const u = data.user_info || {};

    // Header Welcome
    setText("banner-nama", u.name ?? "Kepala Dinas");

    // Card Profil Samping
    setText("profile-nama", u.name ?? "-");
    setText("profile-nip", u.nip ?? "-");
    setText("profile-jabatan", u.jabatan ?? "-");
    setText("profile-unit", u.unit_kerja ?? "-");
    setText("profile-alamat", u.alamat ?? "-");

    // Set Foto Profil jika ada
    const imgEl = document.getElementById("profile-foto");
    if (imgEl && u.foto) {
        imgEl.src = u.foto;
    }

    /* =========================================================================
     * 2. GRAFIK DISTRIBUSI LOKASI GLOBAL (DONUT CHART) - [NEW]
     * =========================================================================*/
    const canvasLokasi = document.getElementById("lokasiGlobalChart");
    if (canvasLokasi && data.distribusi_lokasi_global) {

        const existingLokasiChart = Chart.getChart(canvasLokasi);
        if (existingLokasiChart) existingLokasiChart.destroy();

        const ctxLokasi = canvasLokasi.getContext("2d");
        const dist = data.distribusi_lokasi_global;

        // Validasi jika semua data 0
        const totalLokasi = (dist.WFO || 0) + (dist.WFH || 0) + (dist.WFA || 0) + (dist.DL || 0);

        new Chart(ctxLokasi, {
            type: "doughnut",
            data: {
                labels: ["WFO", "WFH", "WFA", "Dinas Luar"],
                datasets: [{
                    // Jika total 0, beri data semu [1] agar chart abu-abu tetap tergambar
                    data: totalLokasi === 0 ? [1] : [dist.WFO || 0, dist.WFH || 0, dist.WFA || 0, dist.DL || 0],
                    backgroundColor: totalLokasi === 0 ? ["#F1F5F9"] : [
                        "#1C7C54", // Emerald WFO
                        "#3B82F6", // Blue WFH
                        "#6366F1", // Indigo WFA
                        "#A855F7"  // Purple DL
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { family: 'Poppins', size: 11 }
                        }
                    },
                    tooltip: {
                        enabled: totalLokasi !== 0 // Matikan tooltip jika kosong
                    }
                }
            },
            plugins: [{
                id: 'textCenter',
                beforeDraw: function (chart) {
                    var width = chart.width,
                        height = chart.height,
                        ctx = chart.ctx;

                    ctx.restore();
                    var fontSize = (height / 114).toFixed(2);
                    ctx.font = "bold " + fontSize + "em Poppins";
                    ctx.textBaseline = "middle";
                    ctx.fillStyle = "#1e293b";

                    var text = totalLokasi === 0 ? "0" : formatNumber(totalLokasi),
                        textX = Math.round((width - ctx.measureText(text).width) / 2),
                        textY = (height / 2) - 10;

                    ctx.fillText(text, textX, textY);

                    ctx.font = "normal " + (fontSize * 0.4) + "em Poppins";
                    ctx.fillStyle = "#64748b";
                    var text2 = "Laporan Aktif",
                        text2X = Math.round((width - ctx.measureText(text2).width) / 2),
                        text2Y = (height / 2) + 15;

                    ctx.fillText(text2, text2X, text2Y);
                    ctx.save();
                }
            }]
        });
    }

    /* =========================================================================
     * 3. GENERATE GRAFIK PER BIDANG (DYNAMIC LOOP)
     * =========================================================================*/
    const container = document.getElementById("grafik-bidang-container");
    const listBidang = data.grafik_data || [];
    const tahunPeriode = data.periode_tahun || new Date().getFullYear();

    if (container) {
        // Bersihkan container (loading state remove)
        container.innerHTML = "";

        if (listBidang.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center text-slate-500 py-10">Data bidang tidak ditemukan.</div>`;
        }

        // Palette Warna untuk membedakan tiap bidang
        const colors = [
            { border: "#3B82F6", bg: "rgba(59, 130, 246, 0.1)" }, // Blue
            { border: "#10B981", bg: "rgba(16, 185, 129, 0.1)" }, // Emerald
            { border: "#F59E0B", bg: "rgba(245, 158, 11, 0.1)" }, // Amber
            { border: "#8B5CF6", bg: "rgba(139, 92, 246, 0.1)" }, // Violet
            { border: "#EC4899", bg: "rgba(236, 72, 153, 0.1)" }, // Pink
            { border: "#6366F1", bg: "rgba(99, 102, 241, 0.1)" }, // Indigo
        ];

        // Looping untuk membuat Card Grafik
        listBidang.forEach((bidang, index) => {
            const theme = colors[index % colors.length];
            const canvasId = `chart-bidang-${bidang.id_bidang}`;
            const totalKinerja = bidang.data_bulanan.reduce((a, b) => a + b, 0);

            // Inject HTML Struktur Card ke Container
            const cardHtml = `
                <div class="bg-white p-5 rounded-[24px] border border-slate-200 shadow-sm flex flex-col h-[320px] transition-shadow hover:shadow-md">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm leading-tight mb-1">${bidang.nama_bidang}</h3>
                            <p class="text-xs text-slate-500">Kinerja Tahun ${tahunPeriode}</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Total Disetujui</span>
                            <span class="block text-xl font-extrabold text-slate-800">${formatNumber(totalKinerja)}</span>
                        </div>
                    </div>
                    
                    <div class="flex-1 w-full relative min-h-0">
                        <canvas id="${canvasId}"></canvas>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', cardHtml);

            // Render Chart.js
            const ctx = document.getElementById(canvasId).getContext("2d");

            // Buat Gradient effect
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, theme.bg.replace('0.1', '0.4'));
            gradient.addColorStop(1, "rgba(255, 255, 255, 0)");

            new Chart(ctx, {
                type: "line",
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                    datasets: [{
                        label: 'LKH Disetujui',
                        data: bidang.data_bulanan,
                        borderColor: theme.border,
                        backgroundColor: gradient,
                        borderWidth: 2,
                        pointBackgroundColor: "#fff",
                        pointBorderColor: theme.border,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.4 // Smooth Curve
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1e293b',
                            bodyColor: '#1e293b',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return `Total: ${formatNumber(context.parsed.y)} Laporan`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [2, 4], color: '#f1f5f9' },
                            ticks: { font: { size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        });
    }
});