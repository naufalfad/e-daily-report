console.log("STAF LOG FILE LOADED!");

import { authFetch } from "../../utils/auth-fetch";

export function logActivityStaf() {
    return {
        // === STATE ===
        items: [],
        isLoading: false,
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0
        },

        // Filter State (Backend Friendly)
        filter: {
            search: '',
            month: new Date().getMonth() + 1, // Default bulan ini
            year: new Date().getFullYear()    // Default tahun ini
        },

        // === INIT ===
        initLog() {
            console.log("INIT LOG STAF - Server Side");
            this.fetchData();
        },

        // === CORE FUNCTION: FETCH DATA ===
        async fetchData(page = 1) {
            this.isLoading = true;

            // 1. Siapkan Query Params
            const params = new URLSearchParams({
                page: page,
                per_page: 15,
                month: this.filter.month,
                year: this.filter.year,
                search: this.filter.search
            });

            try {
                // 2. Request ke Backend
                const response = await authFetch(`/api/log-aktivitas?${params.toString()}`);
                const result = await response.json();

                // 3. Update State
                if (page === 1) {
                    this.items = result.data;
                } else {
                    this.items = [...this.items, ...result.data];
                }

                this.pagination.current_page = result.current_page;
                this.pagination.last_page = result.last_page;
                this.pagination.total = result.total;

            } catch (error) {
                console.error("Gagal memuat log staf:", error);
            } finally {
                this.isLoading = false;
            }
        },

        // === ACTIONS ===
        applyFilter() {
            this.fetchData(1);
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
window.logActivityStaf = logActivityStaf;