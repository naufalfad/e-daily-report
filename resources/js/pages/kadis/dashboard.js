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
        // Endpoint ini sekarang mengembalikan JSON struktur baru (User Info + Grafik Data per Bidang)
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
    setText("profile-unit", u.unit_kerja ?? "-"); // Perhatikan key JSON 'unit_kerja'
    setText("profile-alamat", u.alamat ?? "-");
    
    // Set Foto Profil jika ada
    const imgEl = document.getElementById("profile-foto");
    if (imgEl && u.foto) {
        imgEl.src = u.foto;
    }

    /* =========================================================================
     * 2. GENERATE GRAFIK PER BIDANG (DYNAMIC LOOP)
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

        // Palette Warna untuk membedakan tiap bidang (Aesthetic Touch)
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
            // Ambil warna berdasarkan index (looping jika bidang > 6)
            const theme = colors[index % colors.length];
            const canvasId = `chart-bidang-${bidang.id_bidang}`;
            const totalKinerja = bidang.data_bulanan.reduce((a, b) => a + b, 0);

            // 1. Inject HTML Struktur Card ke Container
            const cardHtml = `
                <div class="bg-white p-5 rounded-[20px] border border-slate-200 shadow-sm flex flex-col h-[320px]">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm leading-tight mb-1">${bidang.nama_bidang}</h3>
                            <p class="text-xs text-slate-500">Kinerja Tahun ${tahunPeriode}</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs text-slate-400">Total Approved</span>
                            <span class="block text-lg font-bold text-slate-800">${formatNumber(totalKinerja)}</span>
                        </div>
                    </div>
                    
                    <div class="flex-1 w-full relative min-h-0">
                        <canvas id="${canvasId}"></canvas>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', cardHtml);

            // 2. Render Chart.js
            const ctx = document.getElementById(canvasId).getContext("2d");
            
            // Buat Gradient effect
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, theme.bg.replace('0.1', '0.4')); // Lebih tebal di atas
            gradient.addColorStop(1, "rgba(255, 255, 255, 0)");

            new Chart(ctx, {
                type: "line",
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                    datasets: [{
                        label: 'LKH Disetujui',
                        data: bidang.data_bulanan, // Array [10, 20, 0, ...]
                        borderColor: theme.border,
                        backgroundColor: gradient,
                        borderWidth: 2,
                        pointBackgroundColor: "#fff",
                        pointBorderColor: theme.border,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.4 // Garis melengkung halus (Smooth Curve)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }, // Hide legend agar bersih
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#1e293b',
                            bodyColor: '#1e293b',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return `Total: ${context.parsed.y} Laporan`;
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