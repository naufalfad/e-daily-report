// resources/js/pages/penilai/riwayat.js

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

        // Filter Data
        filter: {
            from: "",
            to: "",
            // Default "mine". Dropdown di UI akan mengubah ini jadi "subordinates"
            mode: "mine",
        },

        // ===============================
        // ACTIONS
        // ===============================

        // Edit Laporan (Jika status Draft/Rejected)
        editLaporan(id) {
            if (!id) return;
            // Arahkan ke halaman input dengan ID laporan (disesuaikan dengan nama file/route)
            window.location.href = `/penilai/input-laporan/${id}`;
        },

        // Export PDF sesuai Filter & Mode saat ini
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
                    // Gunakan mode dinamis dari filter (mine/subordinates)
                    let url = `/riwayat/export-pdf?role=${this.role}&mode=${this.filter.mode}`;

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

        statusText(status) {
            const texts = {
                approved: "Diterima",
                rejected: "Ditolak",
                draft: "Draft",
                waiting_review: "Menunggu",
            };
            return texts[status] || "Menunggu";
        },

        statusBadgeClass(status) {
            const classes = {
                approved:
                    "rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-medium px-2.5 py-0.5",
                rejected:
                    "rounded-full bg-rose-100 text-rose-700 text-[11px] font-medium px-2.5 py-0.5",
                draft: "rounded-full bg-slate-200 text-slate-600 text-[11px] font-medium px-2.5 py-0.5",
                waiting_review:
                    "rounded-full bg-amber-100 text-amber-700 text-[11px] font-medium px-2.5 py-0.5",
            };
            // Default ke waiting_review (kuning) jika status tidak dikenali
            return classes[status] || classes["waiting_review"];
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

            // Build URL dengan parameter role & mode dinamis
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
                    throw new Error(
                        `Gagal memuat data. Status: ${response.status
                        }. Pesan: ${errorData.message || "Unknown Error"}`
                    );
                }

                const data = await response.json();
                this.items = data.data || [];
            } catch (e) {
                console.error("Gagal memuat data riwayat LKH:", e);
                // Opsional: Tampilkan notifikasi error kecil
            } finally {
                this.loading = false;
            }
        },

        // Trigger ulang fetch saat filter berubah
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

                console.log("MODAL DATA FULL =", this.modalData);
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

        normalizeBukti(buktiArray) {
            if (!buktiArray) return [];

            // Jika bukti berupa STRING JSON -> parse dulu
            if (typeof buktiArray === "string") {
                try {
                    buktiArray = JSON.parse(buktiArray);
                } catch (e) {
                    console.error("Gagal parse bukti:", buktiArray);
                    return [];
                }
            }

            // Jika hasil parse bukan array -> jadikan array
            if (!Array.isArray(buktiArray)) {
                return [];
            }

            // Normalisasi setiap item
            return buktiArray
                .map((bukti) => {
                    // Jika string (nama file)
                    if (typeof bukti === "string") {
                        return {
                            file_url: `/storage/uploads/bukti/${bukti}`,
                        };
                    }

                    // Jika object { path: ... }
                    if (bukti.path) {
                        return {
                            file_url: `/storage/${bukti.path}`,
                        };
                    }

                    // Jika sudah ada file_url
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
window.riwayatDataPenilai = riwayatDataPenilai;
