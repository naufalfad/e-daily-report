// =====================================================
//   RIWAYAT LKH — PENILAI (SCRIPT TERPISAH)
// =====================================================

import { authFetch } from "../../utils/auth-fetch";

export function riwayatData(role) {

    const APP_URL = window.APP_URL;
    const TOKEN = localStorage.getItem('auth_token');
    const BASE_URL = `${APP_URL}/api/lkh/riwayat`;

    return {
        role: role,
        items: [],
        loading: false,
        open: false,
        modalData: null,

        filter: {
            from: '',
            to: '',
            mode: (role === 'penilai' ? 'subordinates' : 'mine')
        },

        // ---------------------------
        // FORMAT UTILITIES
        // ---------------------------
        formatDate(isoString) {
            if (!isoString) return '-';
            try {
                const datePart = isoString.split('T')[0];
                return new Date(datePart).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
            } catch {
                return isoString;
            }
        },

        statusText(status) {
            switch (status) {
                case 'approved': return 'Diterima';
                case 'rejected': return 'Ditolak';
                default: return 'Menunggu';
            }
        },

        statusBadgeClass(status) {
            switch (status) {
                case 'approved':
                    return 'rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-medium px-2.5 py-0.5';
                case 'rejected':
                    return 'rounded-full bg-rose-100 text-rose-700 text-[11px] font-medium px-2.5 py-0.5';
                default:
                    return 'rounded-full bg-amber-100 text-amber-700 text-[11px] font-medium px-2.5 py-0.5';
            }
        },

        statusBadgeHtml(status) {
            return `<span class="${this.statusBadgeClass(status)}">${this.statusText(status)}</span>`;
        },

        getLokasi(item) {
            return item.lokasi_manual_text ||
                (item.is_luar_lokasi ? 'Luar Kantor (GPS)' : 'Dalam Kantor (GPS)');
        },

        // ---------------------------
        // INIT PAGE
        // ---------------------------
        async initPage() {
            await this.fetchData();
            this.initDatePickers();
        },

        // ---------------------------
        // FETCH DATA
        // ---------------------------
        async fetchData() {

            this.loading = true;
            this.items = [];

            let url = `${BASE_URL}?role=${this.role}`;

            if (this.role === 'penilai') {
                url += `&mode=${this.filter.mode}`;
            }

            if (this.filter.from) {
                url += `&from_date=${this.filter.from}`;
            }

            if (this.filter.to) {
                url += `&to_date=${this.filter.to}`;
            }

            try {
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${TOKEN}`,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const err = await response.json();
                    throw new Error(err.message || 'Gagal memuat data');
                }

                const data = await response.json();
                this.items = data.data || [];

            } catch (err) {
                console.error("ERROR Fetch Riwayat:", err);
            }

            this.loading = false;
        },

        // ---------------------------
        // FILTER DATA
        // ---------------------------
        filterData() {
            this.fetchData();
        },

        // ---------------------------
        // MODAL HANDLING
        // ---------------------------
        openModal(item) {
            this.modalData = item;
            this.open = true;
        },

        viewBukti(buktiArray) {
            if (buktiArray && buktiArray.length > 0 && buktiArray[0].file_url) {
                window.open(buktiArray[0].file_url, '_blank');
            } else {
                alert('Tidak ada bukti tersedia.');
            }
        },

        // ---------------------------
        // DATE PICKER
        // ---------------------------
        initDatePickers() {
            ['tgl_dari', 'tgl_sampai'].forEach(id => {
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
        }
    };
}
