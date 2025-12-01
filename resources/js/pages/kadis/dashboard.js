import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", async function () {
    const setText = (id, value = "-") => {
        const el = document.getElementById(id);
        if (el) el.innerText = value;
    };

    const token = localStorage.getItem("auth_token");
    const headers = { Accept: "application/json" };
    if (token) headers["Authorization"] = "Bearer " + token;

    let data;
    try {
        const res = await fetch(`/e-daily-report/api/dashboard/kadis`, {
            method: "GET",
            headers,
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        data = await res.json();
        console.log("DATA KADIS:", data);
    } catch (err) {
        console.error("Gagal mengambil data dashboard Kadis:", err);
        return;
    }

    /* =======================
 * 0. BANNER & PROFIL
 * =======================*/

const u = data.user_info || {};

setText("banner-nama", u.name ?? "-");
setText("profile-nama", u.name ?? "-");
setText("profile-nip", u.nip ?? "-");
setText("profile-daerah", u.daerah ?? "-");

setText("profile-jabatan", u.jabatan ?? "-");
setText("profile-dinas", u.unit ?? "-");
setText("profile-alamat", u.alamat ?? "-");

    /* =======================
     * 1. Statistik Angka
     * =======================*/
    const s = data.statistik || {};

    setText("stat-total-hari-ini", s.total_hari_ini ?? 0);
    setText("stat-menunggu", s.total_menunggu ?? 0);
    setText("stat-disetujui", s.total_disetujui ?? 0);
    setText("stat-ditolak", s.total_ditolak ?? 0);

    /* =======================
     * 2. Persentase
     * =======================*/
    setText("rate-total-hari-ini", `↑ ${s.rate_total ?? 0}% dari kemarin`);
    setText("rate-menunggu", "⚠ Perlu perhatian"); // FIXED
    setText("rate-disetujui", `↑ ${s.rate_disetujui ?? 0}% Approval Rate`);
    setText("rate-ditolak", `↓ ${s.rate_ditolak ?? 0}% Rejection Rate`);

    /* =======================
     * 3. Aktivitas Terkini
     * =======================*/
    const list = document.getElementById("aktivitas-list");
    list.innerHTML = "";

    if (!data.aktivitas_terbaru?.length) {
        list.innerHTML = `<li class="text-sm text-slate-500">Belum ada aktivitas terbaru.</li>`;
    } else {
        data.aktivitas_terbaru.forEach((item) => {
            const dateObj = new Date(item.tanggal_laporan);
            const tanggal = dateObj.toLocaleDateString("id-ID", {
                day: "numeric",
                month: "long",
                year: "numeric",
            });

            let tone, icon, label;
            if (item.status === "approved") {
                tone = "bg-[#128C60]/50";
                icon = "approve.svg";
                label = "Disetujui";
            } else if (item.status === "rejected") {
                tone = "bg-[#B6241C]/50";
                icon = "reject.svg";
                label = "Ditolak";
            } else {
                tone = "bg-[#D8A106]/50";
                icon = "pending.svg";
                label = "Menunggu Review";
            }

            list.insertAdjacentHTML(
                "beforeend",
                `
                <li class="flex items-start gap-3">
                    <div class="h-8 w-8 rounded-[10px] flex items-center justify-center ${tone}">
                        <img src="/assets/icon/${icon}" class="h-5 w-5">
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-medium truncate">${item.deskripsi_aktivitas}</div>

                        <div class="flex justify-between mt-[2px]">
                            <span class="text-xs text-slate-500">${label}</span>
                            <span class="text-xs text-slate-500 whitespace-nowrap">${tanggal}</span>
                        </div>
                    </div>
                </li>
            `
            );
        });
    }

    /* =======================
     * 4. GRAFIK Kadis (FULL STYLE)
     * =======================*/

    if (window.kadisChart) {
        try {
            window.kadisChart.destroy();
        } catch (e) {}
    }

    const grafikAll = data.grafik || [];

    const monthlyTotal = Array(12).fill(0);
    const monthlyApproved = Array(12).fill(0);
    const monthlyRejected = Array(12).fill(0);

    grafikAll.forEach((item) => {
        const m = new Date(item.tanggal_laporan).getMonth();
        monthlyTotal[m]++;

        if (item.status === "approved") monthlyApproved[m]++;
        else if (item.status.includes("reject")) monthlyRejected[m]++;
    });

    const canvas = document.getElementById("kinerjaBulananChart");

    if (canvas) {
        const ctx = canvas.getContext("2d");

        // ===== MULTI GRADIENT EXACT LIKE OLD STYLE =====
        const gradTotal = ctx.createLinearGradient(0, 0, 0, 300);
        gradTotal.addColorStop(0, "rgba(30, 64, 175, 0.35)");
        gradTotal.addColorStop(1, "rgba(30, 64, 175, 0)");

        const gradApproved = ctx.createLinearGradient(0, 0, 0, 300);
        gradApproved.addColorStop(0, "rgba(18, 140, 96, 0.25)");
        gradApproved.addColorStop(1, "rgba(18, 140, 96, 0)");

        const gradRejected = ctx.createLinearGradient(0, 0, 0, 300);
        gradRejected.addColorStop(0, "rgba(182, 36, 28, 0.25)");
        gradRejected.addColorStop(1, "rgba(182, 36, 28, 0)");

        window.kadisChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: [
                    "Jan",
                    "Feb",
                    "Mar",
                    "Apr",
                    "Mei",
                    "Jun",
                    "Jul",
                    "Agu",
                    "Sep",
                    "Okt",
                    "Nov",
                    "Des",
                ],
                datasets: [
                    {
                        label: "Total Laporan",
                        data: monthlyTotal,
                        borderColor: "#1E40AF",
                        backgroundColor: gradTotal,
                        pointBackgroundColor: "#1E40AF",
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: "Laporan Diterima",
                        data: monthlyApproved,
                        borderColor: "#128C60",
                        backgroundColor: gradApproved,
                        pointBackgroundColor: "#128C60",
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: "Laporan Ditolak",
                        data: monthlyRejected,
                        borderColor: "#B6241C",
                        backgroundColor: gradRejected,
                        pointBackgroundColor: "#B6241C",
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: { usePointStyle: true },
                    },
                },
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });
    }
});
