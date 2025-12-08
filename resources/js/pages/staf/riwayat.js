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

        filter: {
            from: "",
            to: "",
            mode: role === "penilai" ? "subordinates" : "mine",
        },

        // Edit Laporan
        editLaporan(id) {
            if (!id) return;
            window.location.href = `/staf/input-lkh/${id}`;
        },

        // UTILS
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

        // [LOGICAL FIX 1] Penanganan Status Draft Eksplisit
        statusText(status) {
            switch (status) {
                case "approved":
                    return "Diterima";
                case "rejected":
                    return "Ditolak";
                case "draft":
                    return "Draft"; 
                case "waiting_review":
                    return "Menunggu";
            }
        },

        // [LOGICAL FIX 2] Warna Badge Khusus Draft
        statusBadgeClass(status) {
            switch (status) {
                case "approved":
                    return "rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-medium px-2.5 py-0.5";
                case "rejected":
                    return "rounded-full bg-rose-100 text-rose-700 text-[11px] font-medium px-2.5 py-0.5";
                case "draft":
                    // Abu-abu untuk draft agar terlihat beda dengan pending
                    return "rounded-full bg-slate-200 text-slate-600 text-[11px] font-medium px-2.5 py-0.5";
                default:
                    // Kuning untuk pending/waiting_review
                    return "rounded-full bg-amber-100 text-amber-700 text-[11px] font-medium px-2.5 py-0.5";
            }
        },

        statusBadgeHtml(status) {
            return `<span class="${this.statusBadgeClass(
                status
            )}">${this.statusText(status)}</span>`;
        },

        getLokasi(item) {
            return (
                item.lokasi_manual_text ||
                (item.is_luar_lokasi
                    ? "Luar Kantor (GPS)"
                    : "Dalam Kantor (GPS)")
            );
        },

        // INIT
        async initPage() {
            await this.fetchData();
            this.initDatePickers();
        },

        // FETCH
        async fetchData() {
            this.loading = true;
            this.items = [];

            let url = BASE_URL + `?role=${this.role}`;

            if (this.role === "penilai") url += `&mode=${this.filter.mode}`;
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

                // [LOGICAL FIX 3] Mengambil JSON response (sebelumnya hilang)
                const data = await response.json();
                
                this.items = data.data || [];
            } catch (e) {
                console.error("Gagal memuat data riwayat LKH:", e);
            } finally {
                this.loading = false;
            }
        },

        // FILTER
        async filterData() {
            await this.fetchData();
        },

        // MODAL
        openModal(item) {
            this.modalData = item;
            this.open = true;
        },

        // VIEW FILE
        viewBukti(buktiArray) {
            if (buktiArray && buktiArray.length > 0) {
                this.daftarBukti = buktiArray;
                this.openBukti = true;
            } else {
                alert("Tidak ada bukti yang tersedia.");
            }
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

                    if (this.filter.from)
                        url += `&from_date=${this.filter.from}`;
                    if (this.filter.to) 
                        url += `&to_date=${this.filter.to}`;

                    window.open(url, "_blank");
                }
            });
        },

        // DATEPICKERS
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