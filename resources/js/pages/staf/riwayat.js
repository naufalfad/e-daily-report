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
                confirmButtonColor: "#155FA6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Export",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = `/riwayat/export-pdf?role=${this.role}&mode=mine`;

                    if (this.filter.from)
                        url += `&from_date=${this.filter.from}`;
                    if (this.filter.to) 
                        url += `&to_date=${this.filter.to}`;

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
                case "approved":
                    return "rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-medium px-2.5 py-0.5";
                case "rejected":
                    return "rounded-full bg-rose-100 text-rose-700 text-[11px] font-medium px-2.5 py-0.5";
                case "draft":
                    return "rounded-full bg-slate-200 text-slate-600 text-[11px] font-medium px-2.5 py-0.5";
                default: // waiting_review
                    return "rounded-full bg-amber-100 text-amber-700 text-[11px] font-medium px-2.5 py-0.5";
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
                    throw new Error(
                        `Gagal memuat data. Status: ${response.status}. Pesan: ${errorData.message || "Unknown Error"}`
                    );
                }

                const data = await response.json();
                this.items = data.data || []; // Pastikan fallback array kosong

            } catch (e) {
                console.error("Gagal memuat data riwayat LKH:", e);
                // Opsional: alert("Gagal memuat data");
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
                    icon: 'info',
                    title: 'Tidak Ada Bukti',
                    text: 'Laporan ini tidak memiliki lampiran bukti.',
                    confirmButtonColor: '#155FA6'
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
    };
}

// Global Registration
window.riwayatDataStaf = riwayatDataStaf;