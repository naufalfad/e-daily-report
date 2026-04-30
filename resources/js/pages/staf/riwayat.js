// resources/js/pages/staf/riwayat.js

export function riwayatDataStaf(role) {
    const TOKEN = localStorage.getItem("auth_token");
    const BASE_URL = "/api/lkh/riwayat";

    return {
        role: role,
        items: [],
        loading: false,

        // State Modal Detail Laporan
        open: false,
        showPreview: false,
        selectedBukti: null,
        modalData: null,

        // State Modal Bukti
        openBukti: false,
        daftarBukti: [],

        // Filter (Mode otomatis 'mine' untuk staf)
        filter: {
            from: "",
            to: "",
            mode: "mine",
        },

        // ===============================
        // ACTIONS
        // ===============================

        // Edit Laporan (Khusus Staf: Draft atau Rejected)
        editLaporan(id) {
            if (!id) return;
            window.location.href = `/staf/input-lkh/${id}`;
        },

        // Export PDF
        exportPdf() {
            Swal.fire({
                title: "Export PDF?",
                text: "Apakah Anda yakin ingin mengekspor riwayat laporan ini ke PDF?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#1C7C54", // Disesuaikan dengan tema Emerald
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Export",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = `/riwayat/export-pdf?role=${this.role}&mode=mine`;

                    if (this.filter.from)
                        url += `&from_date=${this.filter.from}`;
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
                (item.is_luar_lokasi
                    ? "Luar Kantor (GPS)"
                    : "Dalam Kantor (GPS)")
            );
        },

        // --- NEW: Helper untuk Kategori Lokasi ---
        kategoriText(kat) {
            switch (kat) {
                case "WFO": return "WFO";
                case "WFH": return "WFH";
                case "WFA": return "WFA";
                case "DL": return "Dinas Luar";
                default: return kat || "WFO";
            }
        },

        kategoriBadgeClass(kat) {
            switch (kat) {
                case "WFO": return "rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-extrabold px-2 py-0.5 tracking-wider";
                case "WFH": return "rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-extrabold px-2 py-0.5 tracking-wider";
                case "WFA": return "rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-extrabold px-2 py-0.5 tracking-wider";
                case "DL": return "rounded-md bg-purple-50 border border-purple-200 text-purple-700 text-[10px] font-extrabold px-2 py-0.5 tracking-wider";
                default: return "rounded-md bg-slate-50 border border-slate-200 text-slate-700 text-[10px] font-extrabold px-2 py-0.5 tracking-wider";
            }
        },

        kategoriBadgeHtml(kat) {
            const safeKat = kat || 'WFO'; // Fallback data lama ke WFO
            return `<span class="${this.kategoriBadgeClass(safeKat)}">${this.kategoriText(safeKat)}</span>`;
        },

        // --- EXISITING: Helper untuk Status ---
        statusText(status) {
            switch (status) {
                case "approved": return "Diterima";
                case "rejected": return "Ditolak";
                case "draft": return "Draft";
                default: return "Menunggu";
            }
        },

        statusBadgeClass(status) {
            switch (status) {
                case "approved": return "rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold px-2.5 py-0.5";
                case "rejected": return "rounded-full bg-rose-100 text-rose-700 text-[11px] font-bold px-2.5 py-0.5";
                case "draft": return "rounded-full bg-slate-200 text-slate-600 text-[11px] font-bold px-2.5 py-0.5";
                default: return "rounded-full bg-amber-100 text-amber-700 text-[11px] font-bold px-2.5 py-0.5"; // waiting_review
            }
        },

        statusBadgeHtml(status) {
            return `<span class="${this.statusBadgeClass(status)}">${this.statusText(status)}</span>`;
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

            // Build URL
            let url = BASE_URL + `?role=${this.role}&mode=mine`;
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
                this.items = data.data || [];
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

        openModal(item) {
            this.modalData = item;
            this.open = true;
        },

        viewBukti(buktiArray) {
            if (buktiArray && buktiArray.length > 0) {
                this.daftarBukti = buktiArray;
                this.openBukti = true;
            } else {
                Swal.fire({
                    icon: "info",
                    title: "Tidak Ada Bukti",
                    text: "Laporan ini tidak memiliki lampiran bukti.",
                    confirmButtonColor: "#1C7C54",
                });
            }
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
                    try {
                        input.showPicker();
                    } catch {
                        input.focus();
                    }
                });
            });
        },

        getFileType(url) {
            if (!url) return "other";
            const ext = url.split(".").pop().toLowerCase();

            if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) return "image";
            if (ext === "pdf") return "pdf";
            if (["mp4", "mov", "webm"].includes(ext)) return "video";
            return "other";
        },

        preview(b) {
            this.selectedBukti = b;
            this.showPreview = true;
        },
    };
}

// Global Registration
window.riwayatDataStaf = riwayatDataStaf;