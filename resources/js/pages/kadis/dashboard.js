// resources/js/pages/kadis/dashboard.js
import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", async function () {

    /* =======================================================
     * 0. HELPER ANTI ERROR NULL
     * =======================================================*/
    function setText(id, value = "-") {
        const el = document.getElementById(id);
        if (el) el.innerText = value;
    }

    function setHTML(id, value = "") {
        const el = document.getElementById(id);
        if (el) el.innerHTML = value;
    }

    /* =======================================================
     * 1. TOKEN
     * =======================================================*/
    const token = localStorage.getItem("auth_token");

    const headers = { "Accept": "application/json" };
    if (token) headers["Authorization"] = "Bearer " + token;

    /* =======================================================
     * 2. FETCH API DASHBOARD KADIS
     * =======================================================*/
    let data;

    try {
        const res = await fetch("/api/kadis/dashboard", {
            method: "GET",
            headers: headers
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        data = await res.json();
        console.log("Dashboard Kadis:", data);

    } catch (err) {
        console.error("Gagal mengambil data:", err);
        return;
    }

    /* =======================================================
     * 3. PROFIL KADIS
     * =======================================================*/
    const uInfo = data.user_info || {};

    // Banner
    setText("banner-nama", uInfo.name ?? "Kadis");

    // Card Profil
    setText("profile-nama", uInfo.name ?? "-");
    setText("profile-nip", uInfo.nip ?? "-");
    setText("profile-alamat", uInfo.alamat ?? "-");
    setText("profile-email", uInfo.email ?? "-");
    setText("profile-telepon", uInfo.no_telp ?? "-");
    setText("profile-jabatan", uInfo.jabatan ?? "-");
    setText("profile-dinas", uInfo.unit ?? "-");

    /* =======================================================
     * 4. STATISTIK RINGKAS
     * =======================================================*/
    if (data.statistik_skp) {
        const s = data.statistik_skp;

        setText("stat-val-1", s.total_laporan_hari_ini);
        setText("stat-val-2", s.total_waiting);
        setText("stat-val-3", s.total_diterima);
        setText("stat-desc-3", s.persen_diterima + "% Approval Rate");
        setText("stat-val-4", s.total_ditolak);
        setText("stat-desc-4", s.persen_ditolak + "% Rejection Rate");
    }

    /* =======================================================
     * 5. AKTIVITAS TERKINI (Kabid → Kadis)
     * =======================================================*/
    const listContainer = document.getElementById("aktivitas-list");
    if (listContainer) listContainer.innerHTML = "";

    const aktivitas = data.aktivitas_terbaru || [];

    if (listContainer) {
        if (aktivitas.length === 0) {
            listContainer.innerHTML = `<li class="text-sm text-slate-500">Belum ada aktivitas terbaru.</li>`;
        } else {
            aktivitas.forEach(item => {

                const tgl = new Date(item.tanggal_laporan).toLocaleDateString("id-ID", {
                    day: "numeric",
                    month: "long",
                    year: "numeric"
                });

                let tone = 'bg-slate-200';
                let icon = 'pending.svg';
                let statusLabel = item.status;

                if (item.status === "approved") {
                    tone = 'bg-[#128C60]/50';
                    icon = 'approve.svg';
                    statusLabel = 'Disetujui';
                } else if (item.status === "rejected") {
                    tone = 'bg-[#B6241C]/50';
                    icon = 'reject.svg';
                    statusLabel = 'Ditolak';
                } else {
                    tone = 'bg-[#D8A106]/50';
                    icon = 'pending.svg';
                    statusLabel = 'Menunggu Validasi';
                }

                const HTML = `
                    <li class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-[10px] flex items-center justify-center ${tone}">
                            <img src="/assets/icon/${icon}" class="h-5 w-5">
                        </div>

                        <div class="flex-1">
                            <div class="text-[15px] font-medium leading-snug">${item.deskripsi_aktivitas}</div>

                            <div class="flex justify-between mt-[2px]">
                                <span class="text-xs text-slate-500">${statusLabel}</span>
                                <span class="text-xs text-slate-500 whitespace-nowrap">${tgl}</span>
                            </div>
                        </div>
                    </li>
                `;

                listContainer.insertAdjacentHTML("beforeend", HTML);
            });
        }
    }

    /* =======================================================
     * 6. DRAFT LAPORAN KABID
     * =======================================================*/
    const draftContainer = document.getElementById("draft-list");
    if (draftContainer) draftContainer.innerHTML = "";

    const draft = data.draft_terbaru || [];

    if (draftContainer) {
        if (draft.length === 0) {
            draftContainer.innerHTML = `<li class="text-sm text-slate-500">Tidak ada draft.</li>`;
        } else {
            draft.forEach(item => {

                const tgl = new Date(item.updated_at).toLocaleDateString("id-ID", {
                    day: "numeric",
                    month: "long",
                    year: "numeric"
                });

                const HTML = `
                <li class="rounded-xl bg-[#F1F5F9] px-3 py-2.5 flex items-start justify-between gap-4">

                    <div class="flex-1 min-w-0">
                        <div class="font-medium leading-tight text-[15px] truncate">${item.deskripsi_aktivitas}</div>
                        <div class="text-xs text-slate-500 mt-[2px] leading-tight">
                            Disimpan: ${tgl}
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button 
                            onclick="window.location.href='/kadis/validasi-laporan/${item.id}'"
                            class="rounded-[6px] bg-emerald-600 text-white text-[13px] px-3 py-[4px] shadow-sm hover:brightness-95">
                            Lanjutkan
                        </button>
                        <button 
                            type="button" 
                            onclick="deleteDraft('${item.id}')"
                            class="rounded-[6px] bg-[#B6241C] text-white text-[13px] px-3 py-[4px] shadow-sm hover:bg-rose-600/80">
                            Hapus
                        </button>
                    </div>
                </li>
                `;

                draftContainer.insertAdjacentHTML("beforeend", HTML);
            });
        }
    }

    /* =======================================================
     * 7. GRAFIK AKTIVITAS KABID → KADIS
     * =======================================================*/
    let chartKinerja = window.chartKinerjaKadis || null;

    const grafik = data.grafik_aktivitas || [];

    let monthlyTotal = Array(12).fill(0);
    let monthlyApproved = Array(12).fill(0);
    let monthlyRejected = Array(12).fill(0);

    grafik.forEach(item => {
        const m = new Date(item.tanggal_laporan).getMonth();

        monthlyTotal[m]++;

        if (item.status === "approved") monthlyApproved[m]++;
        else if (item.status === "rejected") monthlyRejected[m]++;
    });

    const canvas = document.getElementById("kinerjaBulananChart");

    if (canvas) {
        const ctx = canvas.getContext("2d");

        if (chartKinerja) chartKinerja.destroy();

        const gradientTotal = ctx.createLinearGradient(0, 0, 0, 260);
        gradientTotal.addColorStop(0, "rgba(30, 64, 175, 0.25)");
        gradientTotal.addColorStop(1, "rgba(30, 64, 175, 0)");

        chartKinerja = new Chart(ctx, {
            type: "line",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
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
                        label: "Diterima",
                        data: monthlyApproved,
                        borderColor: "#128C60",
                        pointBackgroundColor: "#128C60",
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: "Ditolak",
                        data: monthlyRejected,
                        borderColor: "#B6241C",
                        pointBackgroundColor: "#B6241C",
                        fill: false,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "bottom" }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        window.chartKinerjaKadis = chartKinerja;
    }

});

/* =======================================================
 * 8. DELETE DRAFT
 * =======================================================*/
window.deleteDraft = async function (id) {

    if (!confirm("Hapus draft ini?")) return;

    const token = localStorage.getItem("auth_token");

    try {
        const res = await fetch(`/api/lkh/${id}`, {
            method: "DELETE",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Accept": "application/json"
            }
        });

        if (!res.ok) {
            alert("Gagal menghapus!");
            return;
        }

        alert("Draft berhasil dihapus!");
        window.location.reload();

    } catch (err) {
        alert("Kesalahan koneksi server.");
    }
};
