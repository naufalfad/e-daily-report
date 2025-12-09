// resources/js/pages/staf/dashboard.js

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
        console.log("Data JSON:", data);

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

    // Tidak tersedia → tetap aman
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
     * 5. AKTIVITAS TERKINI
     * =======================================================*/
    const listContainer = document.getElementById("aktivitas-list");
    if (listContainer) listContainer.innerHTML = "";

    const aktivitas = data.aktivitas_terbaru || [];

    if (listContainer) {
        if (aktivitas.length === 0) {
            listContainer.innerHTML =
                '<li class="text-sm text-slate-500">Belum ada aktivitas terbaru.</li>';
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
                <li class="flex items-start gap-3">
                    <div class="h-8 w-8 rounded-[10px] flex items-center justify-center ${tone}">
                        <img src="/assets/icon/${iconName}" class="h-5 w-5 opacity-90">
                    </div>

                    <div class="flex-1">
                        <div class="text-[13px] font-medium leading-snug truncate" style="max-width: 250px;"
                            title="${item.deskripsi_aktivitas}">
                            ${item.deskripsi_aktivitas}
                        </div>
                        <div class="flex justify-between mt-[2px]">
                            <span class="text-xs text-slate-500 capitalize">${statusLabel}</span>
                            <span class="text-xs text-slate-500 whitespace-nowrap">${tanggalFormatted}</span>
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
        if (e) e.preventDefault(); // Mencegah link reload halaman
        const modal = document.getElementById('modal-all-draft');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Matikan scroll body utama
        }
    }

    window.closeModalDraft = function () {
        const modal = document.getElementById('modal-all-draft');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = ''; // Hidupkan kembali scroll body
        }
    }

    // Tutup modal dengan tombol ESC keyboard
    document.addEventListener('keydown', function (event) {
        if (event.key === "Escape") {
            closeModalDraft();
        }
    });

    /* =======================================================
    * RENDER LIST DRAFT (LOGIKA ANDA)
    * =======================================================*/

    // Asumsi: variable 'data' sudah didapat dari fetch sebelumnya.
    const draft = data.draft_limit || [];   // Data sedikit (misal 3)
    const draftTerbaru = data.draft_terbaru || []; // Data banyak (semua)

    // 1. RENDER LIST LUAR (draft-list)
    const draftContainer = document.getElementById("draft-list");
    if (draftContainer) {
        draftContainer.innerHTML = "";
        if (draft.length === 0) {
            draftContainer.innerHTML = '<li class="text-sm text-slate-500">Belum ada draft.</li>';
        } else {
            draft.forEach(item => {
                // Gunakan fungsi helper untuk generate HTML agar tidak duplikasi kode
                draftContainer.insertAdjacentHTML('beforeend', generateDraftItemHtml(item));
            });
        }
    }

    // 2. RENDER LIST DALAM MODAL (draft-terbaru)
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

    // Helper Function: Supaya HTML item konsisten antara list luar dan dalam modal
    function generateDraftItemHtml(item) {
        const dateObj = new Date(item.updated_at);
        const tanggalFormatted = dateObj.toLocaleDateString('id-ID', {
            day: 'numeric', month: 'long', year: 'numeric'
        });

        return `
        <li class="rounded-xl bg-[#F1F5F9] px-3 py-2.5 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="font-medium leading-tight text-[15px] truncate" title="${item.deskripsi_aktivitas}">
                    ${item.deskripsi_aktivitas}
                </div>
                <div class="text-xs text-slate-500 mt-[2px] leading-tight">
                    Disimpan: ${tanggalFormatted}
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="window.location.href='/staf/input-lkh/${item.id}'"
                    class="rounded-[6px] bg-emerald-600 text-white text-[13px] px-3 py-[4px] shadow-sm hover:brightness-95">
                    Lanjutkan
                </button>
                <button type="button" onclick="deleteDraft('${item.id}')"
                    class="rounded-[6px] bg-[#B6241C] text-white text-[13px] px-3 py-[4px] shadow-sm hover:bg-rose-600/80">
                    Hapus
                </button>
            </div>
        </li>
        `;
    }

    /* =======================================================
     * 7. GRAFIK — FIXED VERSION (ANTI ERROR CANVAS)
     * =======================================================*/
    const canvas = document.getElementById("kinerjaBulananChart");

    // Pastikan data grafik ada
    const aktivitasAll = data.grafik_aktivitas || [];

    if (canvas) {
        // --- PERBAIKAN UTAMA DI SINI ---
        // Cek apakah canvas ini sudah punya chart instance dari Chart.js
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }
        // -------------------------------

        // Proses Data Grafik
        let monthlySkp = Array(12).fill(0);
        let monthlyNonSkp = Array(12).fill(0);
        let monthlyApproved = Array(12).fill(0);
        let monthlyRejected = Array(12).fill(0);

        aktivitasAll.forEach(item => {
            const dateObj = new Date(item.tanggal_laporan);
            const month = dateObj.getMonth();

            // Hitung SKP dan Non SKP
            if (item.skp_rencana_id !== null && item.skp_rencana_id !== "null" && item.status !== "draft") {
                // SKP yang valid
                monthlySkp[month]++;
            }
            else if ((item.skp_rencana_id === null || item.skp_rencana_id === "null") && item.status !== "draft") {
                // Bukan SKP
                monthlyNonSkp[month]++;
            }

            // Hitungan status
            if (item.status === "rejected") {
                monthlyRejected[month]++;
            } else if (item.status === "approved") {
                monthlyApproved[month]++;
            }
        });

        const ctx = canvas.getContext("2d");
        const gradientTotal = ctx.createLinearGradient(0, 0, 0, 260);
        gradientTotal.addColorStop(0, "rgba(30, 64, 175, 0.25)");
        gradientTotal.addColorStop(1, "rgba(30, 64, 175, 0)");

        // Buat Chart Baru
        new Chart(ctx, {
            type: "line",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                datasets: [
                    {
                        label: "Laporan SKP",
                        data: monthlySkp,
                        borderColor: "#1E40AF",
                        backgroundColor: gradientTotal,
                        pointBackgroundColor: "#1E40AF",
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: "Laporan Non SKP",
                        data: monthlyNonSkp,
                        borderColor: "#f8be00ff",
                        backgroundColor: gradientTotal,
                        pointBackgroundColor: "#f8be00ff",
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
                    },
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
    }

    /* =======================================================
     * 7. DELETE DRAFT GLOBAL
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