console.log("KADIS LOG FILE LOADED!");

import { authFetch } from "../../utils/auth-fetch";

export function logActivityKadis() {
    return {
        // === STATE ===
        items: [], // Menyimpan data log
        isLoading: false,
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0
        },

        // Filter State (Sesuai Backend)
        filter: {
            search: '',
            month: new Date().getMonth() + 1, // Default bulan ini
            year: new Date().getFullYear()    // Default tahun ini
        },

        // === INIT ===
        initLog() {
            console.log("INIT LOG KADIS - Server Side");
            this.fetchData(); // Load data awal
        },

        // === CORE FUNCTION: FETCH DATA ===
        async fetchData(page = 1) {
            this.isLoading = true;

            // 1. Siapkan Parameter Query
            const params = new URLSearchParams({
                page: page,
                per_page: 15, // Limit per load
                month: this.filter.month,
                year: this.filter.year,
                search: this.filter.search
            });

            try {
                // 2. Request ke Backend
                const response = await authFetch(`/api/log-aktivitas?${params.toString()}`);
                const result = await response.json();

                // 3. Update State Data
                if (page === 1) {
                    this.items = result.data; // Reset data jika filter baru
                } else {
                    this.items = [...this.items, ...result.data]; // Append data (Load More)
                }

                // 4. Update Pagination Meta
                this.pagination.current_page = result.current_page;
                this.pagination.last_page = result.last_page;
                this.pagination.total = result.total;

            } catch (error) {
                console.error("Gagal memuat log kadis:", error);
            } finally {
                this.isLoading = false;
            }
        },

        // === ACTIONS ===
        applyFilter() {
            this.fetchData(1); // Reset ke halaman 1 saat filter
        },

        resetFilter() {
            this.filter.search = '';
            this.filter.month = new Date().getMonth() + 1;
            this.filter.year = new Date().getFullYear();
            this.fetchData(1);
        },

        loadMore() {
            if (this.pagination.current_page < this.pagination.last_page) {
                this.fetchData(this.pagination.current_page + 1);
            }
        }
    };
}

// Register Global Function
window.logActivityKadis = logActivityKadis;