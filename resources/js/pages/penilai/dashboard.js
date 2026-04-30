// resources/js/pages/penilai/dashboard.js

import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", async function () {

    /* =======================================================
     * 0. PROTECT ID ELEMENT (ANTI ERROR NULL)
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
     * 1. TOKEN & HEADER
     * =======================================================*/
    const token = localStorage.getItem("auth_token");

    const headers = { "Accept": "application/json" };
    if (token) headers["Authorization"] = "Bearer " + token;

    /* =======================================================
     * 2. FETCH API
     * =======================================================*/
    let data;

    try {
        const res = await fetch("/api/dashboard/stats", {
            method: "GET",
            headers: headers
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        data = await res.json();
        console.log("Data JSON Dashboard Penilai:", data);

    } catch (err) {
        console.error("Gagal mengambil data API:", err);
        return;
    }

    /* =======================================================
     * 3. BANNER + PROFIL
     * =======================================================*/
    const uInfo = data.user_info || {};

    setText("banner-nama", uInfo.name ?? "User");
    setText("profile-nama", uInfo.name ?? "-");
    setText("profile-jabatan", uInfo.jabatan ?? "-");
    setText("profile-dinas", uInfo.unit ?? "-");

    setText("profile-nip", uInfo.nip);
    setText("profile-alamat", uInfo.alamat);
    setText("profile-email", uInfo.email);
    setText("profile-telepon", uInfo.no_telp);

    if (data.skoring_utama) {
        setText("profile-target", data.skoring_utama.target_tahunan + " Dokumen");
    }

    /* =======================================================
     * 4. STATISTIK RINGKAS
     * =======================================================*/
    if (data.statistik_skp) {
        const s = data.statistik_skp;

        setText("stat-val-1", s.total_skp);
        setText("stat-val-2", s.total_non_skp);
        setText("stat-val-3", s.total_diterima);
        setText("stat-desc-3", s.persen_diterima + "% Dari total diterima");

        setText("stat-val-4", s.total_ditolak);
        setText("stat-desc-4", s.persen_ditolak + "% Dari total ditolak");
    }

    /* =======================================================
     * 5. AKTIVITAS TERKINI (UI DIPERBARUI)
     * =======================================================*/
    const listContainer = document.getElementById("aktivitas-list");
    if (listContainer) listContainer.innerHTML = "";

    const aktivitas = data.aktivitas_terbaru || [];

    if (listContainer) {
        if (aktivitas.length === 0) {
            listContainer.innerHTML =
                '<li class="text-sm text-slate-500 text-center py-4">Belum ada aktivitas terbaru.</li>';
        } else {
            aktivitas.forEach(item => {
                const dateObj = new Date(item.tanggal_laporan);
                const tanggalFormatted = dateObj.toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'long', year: 'numeric'
                });

                let tone = 'bg-slate-200';
                let iconName = 'pending.svg';
                let statusLabel = item.status;

                if (item.status === 'approved') {
                    tone = 'bg-[#128C60]/50';
                    iconName = 'approve.svg';
                    statusLabel = 'Disetujui';
                } else if (item.status === 'rejected' || item.status.includes('reject')) {
                    tone = 'bg-[#B6241C]/50';
                    iconName = 'reject.svg';
                    statusLabel = 'Ditolak';
                } else if (item.status === 'waiting_review') {
                    tone = 'bg-[#D8A106]/50';
                    iconName = 'pending.svg';
                    statusLabel = 'Menunggu Review';
                }

                const htmlItem = `
                <li class="flex items-start gap-3 border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                    <div class="h-8 w-8 rounded-[10px] flex items-center justify-center shrink-0 ${tone}">
                        <img src="/assets/icon/${iconName}" class="h-5 w-5 opacity-90">
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-bold text-slate-800 leading-snug truncate" 
                            title="${item.deskripsi_aktivitas}">
                            ${item.deskripsi_aktivitas}
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-[11px] font-medium text-slate-500 capitalize">${statusLabel}</span>
                            <span class="text-[10px] text-slate-400 whitespace-nowrap">${tanggalFormatted}</span>
                        </div>
                    </div>
                </li>
                `;

                listContainer.insertAdjacentHTML('beforeend', htmlItem);
            });
        }
    }

    /* =======================================================
     * LOGIKA MODAL (BUKA / TUTUP)
     * =======================================================*/
    window.openModalDraft = function (e) {
        if (e) e.preventDefault();
        const modal = document.getElementById('modal-all-draft');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    window.closeModalDraft = function () {
        const modal = document.getElementById('modal-all-draft');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === "Escape") {
            closeModalDraft();
        }
    });

    /* =======================================================
     * 6. RENDER LIST DRAFT
     * =======================================================*/
    const draft = data.draft_limit || [];
    const draftTerbaru = data.draft_terbaru || [];

    const draftContainer = document.getElementById("draft-list");
    if (draftContainer) {
        draftContainer.innerHTML = "";
        if (draft.length === 0) {
            draftContainer.innerHTML = '<li class="text-sm text-slate-500 text-center py-4">Belum ada draft.</li>';
        } else {
            draft.forEach(item => {
                draftContainer.insertAdjacentHTML('beforeend', generateDraftItemHtml(item));
            });
        }
    }

    const draftTerbaruContainer = document.getElementById("draft-terbaru");
    if (draftTerbaruContainer) {
        draftTerbaruContainer.innerHTML = "";
        if (draftTerbaru.length === 0) {
            draftTerbaruContainer.innerHTML = '<li class="text-sm text-slate-500 text-center py-4">Belum ada draft tersimpan.</li>';
        } else {
            draftTerbaru.forEach(item => {
                draftTerbaruContainer.insertAdjacentHTML('beforeend', generateDraftItemHtml(item));
            });
        }
    }

    function generateDraftItemHtml(item) {
        const dateObj = new Date(item.updated_at);
        const tanggalFormatted = dateObj.toLocaleDateString('id-ID', {
            day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        return `
        <li class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 flex items-center justify-between gap-4 hover:shadow-sm transition-all">
            <div class="flex-1 min-w-0">
                <div class="font-bold leading-tight text-[13px] text-slate-800 truncate" title="${item.deskripsi_aktivitas}">
                    ${item.deskripsi_aktivitas || 'Laporan Tanpa Deskripsi'}
                </div>
                <div class="text-[11px] font-medium text-slate-400 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tersimpan: ${tanggalFormatted}
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="window.location.href='/penilai/input-laporan/${item.id}'"
                    class="rounded-lg bg-[#1C7C54] text-white font-bold text-[12px] px-3 py-1.5 shadow-sm hover:bg-[#166443] transition-colors">
                    Lanjutkan
                </button>
                <button type="button" onclick="deleteDraft('${item.id}')"
                    class="rounded-lg bg-white border border-rose-200 text-rose-600 font-bold text-[12px] px-3 py-1.5 shadow-sm hover:bg-rose-50 hover:border-rose-300 transition-colors">
                    Hapus
                </button>
            </div>
        </li>
        `;
    }

    /* =======================================================
     * 7. GRAFIK KINERJA BULANAN (LINE CHART)
     * =======================================================*/
    const canvas = document.getElementById("kinerjaBulananChart");
    const aktivitasAll = data.grafik_aktivitas || [];

    if (canvas) {
        const existingChart = Chart.getChart(canvas);
        if (existingChart) existingChart.destroy();

        let monthlySkp = Array(12).fill(0);
        let monthlyNonSkp = Array(12).fill(0);
        let monthlyApproved = Array(12).fill(0);
        let monthlyRejected = Array(12).fill(0);

        aktivitasAll.forEach(item => {
            const dateObj = new Date(item.tanggal_laporan);
            const month = dateObj.getMonth();

            if (item.skp_rencana_id !== null && item.skp_rencana_id !== "null" && item.status !== "draft") {
                monthlySkp[month]++;
            } else if ((item.skp_rencana_id === null || item.skp_rencana_id === "null") && item.status !== "draft") {
                monthlyNonSkp[month]++;
            }

            if (item.status === "rejected") {
                monthlyRejected[month]++;
            } else if (item.status === "approved") {
                monthlyApproved[month]++;
            }
        });

        const ctx = canvas.getContext("2d");
        const gradientTotal = ctx.createLinearGradient(0, 0, 0, 260);
        gradientTotal.addColorStop(0, "rgba(28, 124, 84, 0.2)"); // Mengikuti tema Emerald
        gradientTotal.addColorStop(1, "rgba(28, 124, 84, 0)");

        new Chart(ctx, {
            type: "line",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                datasets: [
                    {
                        label: "Laporan SKP",
                        data: monthlySkp,
                        borderColor: "#1C7C54",
                        backgroundColor: gradientTotal,
                        pointBackgroundColor: "#1C7C54",
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: "Laporan Non SKP",
                        data: monthlyNonSkp,
                        borderColor: "#3B82F6",
                        pointBackgroundColor: "#3B82F6",
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: "Diterima",
                        data: monthlyApproved,
                        borderColor: "#10B981",
                        borderDash: [5, 5],
                        pointBackgroundColor: "#10B981",
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: "Ditolak",
                        data: monthlyRejected,
                        borderColor: "#EF4444",
                        borderDash: [5, 5],
                        pointBackgroundColor: "#EF4444",
                        fill: false,
                        tension: 0.4
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "bottom", labels: { usePointStyle: true, boxWidth: 6 } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                },
                interaction: { mode: 'index', intersect: false }
            }
        });
    }

    /* =======================================================
     * 8. GRAFIK DISTRIBUSI LOKASI (DONUT CHART) - [NEW]
     * =======================================================*/
    const canvasLokasi = document.getElementById("lokasiChart");
    if (canvasLokasi && data.distribusi_lokasi) {

        const existingLokasiChart = Chart.getChart(canvasLokasi);
        if (existingLokasiChart) existingLokasiChart.destroy();

        const ctxLokasi = canvasLokasi.getContext("2d");
        const dist = data.distribusi_lokasi;

        const totalLokasi = (dist.WFO || 0) + (dist.WFH || 0) + (dist.WFA || 0) + (dist.DL || 0);

        new Chart(ctxLokasi, {
            type: "doughnut",
            data: {
                labels: ["WFO", "WFH", "WFA", "Dinas Luar"],
                datasets: [{
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
                        labels: { usePointStyle: true, padding: 20, font: { family: 'Poppins', size: 11 } }
                    },
                    tooltip: {
                        enabled: totalLokasi !== 0
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

                    var text = totalLokasi === 0 ? "0" : totalLokasi,
                        textX = Math.round((width - ctx.measureText(text).width) / 2),
                        textY = (height / 2) - 10;

                    ctx.fillText(text, textX, textY);

                    ctx.font = "normal " + (fontSize * 0.4) + "em Poppins";
                    ctx.fillStyle = "#64748b";
                    var text2 = "Total Laporan",
                        text2X = Math.round((width - ctx.measureText(text2).width) / 2),
                        text2Y = (height / 2) + 15;

                    ctx.fillText(text2, text2X, text2Y);
                    ctx.save();
                }
            }]
        });
    }

    /* =======================================================
     * 9. DELETE DRAFT GLOBAL
     * =======================================================*/
    window.deleteDraft = async function (id) {
        if (!confirm('Apakah Anda yakin ingin menghapus draft laporan ini?')) return;
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
                const json = await res.json();
                alert("Gagal menghapus: " + (json.message || "Error"));
                return;
            }
            alert("Draft berhasil dihapus!");
            window.location.reload();
        } catch (err) {
            alert("Terjadi kesalahan koneksi.");
        }
    }
});