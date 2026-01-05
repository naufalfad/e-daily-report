/**
 * Riwayat Core Module (ES Module)
 * --------------------------------------------------------------------------
 * Mengelola logika state management untuk halaman Riwayat LKH.
 * Digunakan secara global oleh role: Staf & Penilai.
 *
 * @param {string} role - Role pengguna saat ini ('staf' atau 'penilai')
 * @param {string} baseUrl - (Opsional) URL endpoint fetch data. Default: Current URL.
 */
export function riwayatCore(role, baseUrl = window.location.href) {
    return {
        // ===========================================
        // STATE VARIABLES
        // ===========================================
        
        // Expose variabel 'role' agar bisa dibaca oleh HTML
        role: role, 

        items: [],
        loading: false,

        // [BARU] Pagination State
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0,
            from: 0,
            to: 0
        },

        // Filter State
        filter: {
            from: '',
            to: '',
            // Default 'mine' jika role penilai, null jika staf
            mode: role === 'penilai' ? 'mine' : null
        },

        // Modal Detail State
        open: false,
        modalData: null,

        // Modal Bukti (Evidence) State
        openBukti: false,
        daftarBukti: [],

        // Preview File State
        showPreview: false,
        selectedBukti: null,

        // ===========================================
        // INITIALIZATION
        // ===========================================
        initPage() {
            // console.log(`Riwayat Core initialized for role: ${this.role}`);
            
            // Load data halaman 1 saat pertama dibuka
            this.fetchData(1);
        },

        setDefaultDate() {
            const date = new Date();
            const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
            const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
            const format = (d) => d.toISOString().split('T')[0];

            this.filter.from = format(firstDay);
            this.filter.to = format(lastDay);
        },

        // ===========================================
        // DATA FETCHING & FILTERING
        // ===========================================
        
        // [MODIFIKASI] Terima parameter page (default 1)
        async fetchData(page = 1) {
            this.loading = true;

            try {
                // Bangun Query Parameters
                const params = new URLSearchParams();
                
                // [BARU] Kirim parameter page ke backend
                params.append('page', page);

                if (this.filter.from) params.append('from_date', this.filter.from);
                if (this.filter.to) params.append('to_date', this.filter.to);

                if (this.role === 'penilai' && this.filter.mode) {
                    params.append('mode', this.filter.mode);
                }

                const response = await fetch(`${baseUrl}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                // Cek validitas JSON
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Server tidak mengembalikan JSON. Cek Controller Anda.");
                }

                if (!response.ok) throw new Error('Gagal memuat data');

                const data = await response.json();

                // [MODIFIKASI] Handle Struktur Laravel Paginator
                // Laravel return: { data: [...], current_page: 1, ... }
                this.items = data.data || []; 

                // Simpan Meta Data Pagination
                this.pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    total: data.total,
                    from: data.from,
                    to: data.to
                };

            } catch (error) {
                console.error('Error fetching data:', error);
                this.items = [];
                // Reset pagination on error
                this.pagination = { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
            } finally {
                this.loading = false;
            }
        },

        filterData() {
            // Saat filter berubah, reset ke halaman 1
            this.fetchData(1);
        },

        // [BARU] Fungsi Navigasi Halaman
        changePage(page) {
            // Validasi agar tidak request halaman yang tidak valid
            if (page >= 1 && page <= this.pagination.last_page) {
                this.fetchData(page);
            }
        },

        // ===========================================
        // EXPORT FUNCTIONALITY
        // ===========================================
        exportPdf() {
            const params = new URLSearchParams();

            if (this.filter.from) params.append('from_date', this.filter.from);
            if (this.filter.to) params.append('to_date', this.filter.to);

            params.append('role', this.role);

            if (this.role === 'penilai') {
                params.append('mode', this.filter.mode);
            }

            const exportUrl = `/riwayat/export-pdf?${params.toString()}`;
            window.open(exportUrl, '_blank');
        },

        // ===========================================
        // UI HELPERS (FORMATTERS)
        // ===========================================
        formatDate(dateString) {
            if (!dateString) return '-';
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            try {
                return new Date(dateString).toLocaleDateString('id-ID', options);
            } catch (e) {
                return dateString;
            }
        },

        getLokasi(data) {
            if (data.is_luar_lokasi) {
                return data.lokasi_manual_text || 'Luar Lokasi (Manual)';
            }
            return 'Di Kantor / WFH Terdata';
        },

        statusBadgeHtml(status) {
            switch (status) {
                case 'approved':
                    return `<span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-emerald-200 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                Disetujui
                            </span>`;
                case 'rejected':
                    return `<span class="bg-rose-100 text-rose-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-rose-200 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                Ditolak
                            </span>`;
                case 'waiting_review':
                    return `<span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-amber-200 flex items-center gap-1">
                                <svg class="w-3 h-3 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Menunggu
                            </span>`;
                default:
                    return `<span class="bg-slate-100 text-slate-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-slate-200">Draft</span>`;
            }
        },

        // ===========================================
        // MODAL & EVIDENCES HANDLERS
        // ===========================================
        openModal(item) {
            this.modalData = item;
            this.open = true;
        },

        viewBukti(buktiList) {
            this.daftarBukti = buktiList || [];
            this.openBukti = true;
        },

        getFileType(url) {
            if (!url) return 'other';
            const extension = url.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) return 'image';
            if (['pdf'].includes(extension)) return 'pdf';
            if (['mp4', 'webm', 'ogg', 'mov'].includes(extension)) return 'video';

            return 'other';
        },

        preview(bukti) {
            this.selectedBukti = bukti;
            this.showPreview = true;
        },

        editLaporan(id) {
            window.location.href = `/staf/input-lkh/${id}`;
        }
    };
}