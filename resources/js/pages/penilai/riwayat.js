// resources/js/pages/penilai/riwayat.js

import { authFetch } from "../../utils/auth-fetch";
import { showToast } from "../../global/notification";

// =========================================================
// GLOBAL HELPERS UNTUK ALPINE.JS & HTML RENDERER
// =========================================================

// --- 1. Helper Status ---
window.createStatusBadge = function (status) {
    const styles = {
        'waiting_review': 'bg-amber-50 text-amber-600 border border-amber-200',
        'approved': 'bg-emerald-50 text-emerald-600 border border-emerald-200',
        'rejected': 'bg-rose-50 text-rose-600 border border-rose-200',
        'draft': 'bg-slate-50 text-slate-500 border border-slate-200'
    };
    const labels = {
        'waiting_review': 'Menunggu',
        'approved': 'Disetujui',
        'rejected': 'Ditolak',
        'draft': 'Draft'
    };
    const cls = styles[status] || styles['draft'];
    const lbl = labels[status] || status;
    return `<span class="px-2.5 py-1 text-[10px] uppercase font-bold rounded-full ${cls}">${lbl}</span>`;
};

window.statusBadgeHtml = function (status) {
    return window.createStatusBadge(status);
};

// --- 2. Helper Kategori Lokasi ---
window.getKategoriText = function (kat) {
    switch (kat) {
        case "WFO": return "WFO";
        case "WFH": return "WFH";
        case "WFA": return "WFA";
        case "DL": return "Dinas Luar";
        default: return kat || "WFO";
    }
};

window.createKategoriBadge = function (kat) {
    const safeKat = kat || 'WFO';
    let css = '';

    switch (safeKat) {
        case "WFO": css = "bg-emerald-50 border-emerald-200 text-emerald-700"; break;
        case "WFH": css = "bg-blue-50 border-blue-200 text-blue-700"; break;
        case "WFA": css = "bg-indigo-50 border-indigo-200 text-indigo-700"; break;
        case "DL": css = "bg-purple-50 border-purple-200 text-purple-700"; break;
        default: css = "bg-slate-50 border-slate-200 text-slate-700"; break;
    }

    return `<span class="rounded-md border ${css} text-[10px] font-extrabold px-2 py-0.5 tracking-wider">${window.getKategoriText(safeKat)}</span>`;
};

window.kategoriBadgeHtml = function (kat) {
    return window.createKategoriBadge(kat);
};


// =========================================================
// ALPINE COMPONENT CORE
// =========================================================

export function riwayatDataPenilai(role) {
    const TOKEN = localStorage.getItem("auth_token");
    const BASE_URL = "/api/lkh/riwayat";

    return {
        role: role,
        items: [],
        loading: false,

        // State Modal Detail Laporan
        open: false,
        modalData: null,

        // State Modal Bukti
        openBukti: false,
        daftarBukti: [],
        showPreview: false,
        selectedBukti: null,

        // Filter Data
        filter: {
            from: "",
            to: "",
            mode: "mine",
        },

        // ===============================
        // ACTIONS
        // ===============================

        editLaporan(id) {
            if (!id) return;
            window.location.href = `/penilai/input-laporan/${id}`;
        },

        exportPdf() {
            Swal.fire({
                title: "Export PDF?",
                text: "Apakah Anda yakin ingin mengekspor riwayat laporan ini ke PDF?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#155FA6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Export",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = `/riwayat/export-pdf?role=${this.role}&mode=${this.filter.mode}`;

                    if (this.filter.from) url += `&from_date=${this.filter.from}`;
                    if (this.filter.to) url += `&to_date=${this.filter.to}`;

                    window.open(url, "_blank");
                }
            });
        },

        // ===============================
        // HELPERS & FORMATTING
        // ===============================

        formatDate(isoString) {
            if (!isoString) return "-";
            try {
                const datePart = isoString.split("T")[0];
                return new Date(datePart).toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                });
            } catch (e) {
                return isoString;
            }
        },

        getLokasi(item) {
            return (
                item.lokasi_manual_text ||
                (item.is_luar_lokasi ? "Luar Kantor (GPS)" : "Dalam Kantor (GPS)")
            );
        },

        getFileType(url) {
            if (!url) return "other";
            const ext = url.split(".").pop().toLowerCase();

            if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) return "image";
            if (ext === "pdf") return "pdf";
            if (["mp4", "mov", "webm"].includes(ext)) return "video";
            return "other";
        },

        // ===============================
        // DATA FETCHING
        // ===============================

        async initPage() {
            await this.fetchData();
            this.initDatePickers();
        },

        async fetchData() {
            this.loading = true;
            this.items = [];

            let url = BASE_URL + `?role=${this.role}`;

            if (this.role === "penilai") {
                url += `&mode=${this.filter.mode}`;
            }

            if (this.filter.from) url += `&from_date=${this.filter.from}`;
            if (this.filter.to) url += `&to_date=${this.filter.to}`;

            try {
                const response = await fetch(url, {
                    headers: {
                        Authorization: `Bearer ${TOKEN}`,
                        Accept: "application/json",
                    },
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(`Gagal memuat data. Status: ${response.status}. Pesan: ${errorData.message || "Unknown Error"}`);
                }

                const data = await response.json();

                // Menghandle format data Pagination dari Laravel
                if (data.data && Array.isArray(data.data.data)) {
                    this.items = data.data.data;
                } else if (data.data && Array.isArray(data.data)) {
                    this.items = data.data;
                } else {
                    this.items = [];
                }

                console.log("Data Load Success:", this.items);
            } catch (e) {
                console.error("Gagal memuat data riwayat LKH:", e);
            } finally {
                this.loading = false;
            }
        },

        async filterData() {
            await this.fetchData();
        },

        // ===============================
        // MODALS
        // ===============================

        async openModal(item) {
            this.modalData = null;
            this.open = true;

            try {
                const res = await fetch(`/api/lkh/${item.id}`, {
                    headers: {
                        Authorization: `Bearer ${TOKEN}`,
                        Accept: "application/json",
                    },
                });

                const data = await res.json();
                this.modalData = data.data;

            } catch (e) {
                console.error("Gagal load detail LKH:", e);
            }
        },

        viewBukti(buktiArray) {
            const bukti = this.normalizeBukti(buktiArray);

            if (bukti.length > 0) {
                this.daftarBukti = bukti;
                this.openBukti = true;
            } else {
                Swal.fire({
                    icon: "info",
                    title: "Tidak Ada Bukti",
                    text: "Laporan ini tidak memiliki dokumen lampiran.",
                    confirmButtonColor: "#155FA6",
                });
            }
        },

        preview(b) {
            this.selectedBukti = b;
            this.showPreview = true;
        },

        // ===============================
        // UI COMPONENTS
        // ===============================

        initDatePickers() {
            ["tgl_dari", "tgl_sampai"].forEach((id) => {
                const input = document.getElementById(id);
                const btn = document.getElementById(id + "_btn");

                if (!input || !btn) return;

                btn.addEventListener("click", () => {
                    try { input.showPicker(); }
                    catch { input.focus(); }
                });
            });
        },

        normalizeBukti(buktiArray) {
            if (!buktiArray) return [];

            if (typeof buktiArray === "string") {
                try {
                    buktiArray = JSON.parse(buktiArray);
                } catch (e) {
                    console.error("Gagal parse bukti:", buktiArray);
                    return [];
                }
            }

            if (!Array.isArray(buktiArray)) {
                return [];
            }

            return buktiArray
                .map((bukti) => {
                    if (typeof bukti === "string") {
                        return { file_url: `/storage/uploads/bukti/${bukti}` };
                    }
                    if (bukti.path) {
                        return { file_url: `/storage/${bukti.path}` };
                    }
                    if (bukti.file_url) {
                        return bukti;
                    }
                    return null;
                })
                .filter(Boolean);
        },
    };
}

// Global Registration agar bisa dipanggil via x-data di Blade
window.riwayatCore = riwayatDataPenilai;