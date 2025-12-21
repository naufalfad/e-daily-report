console.log("ADMIN LOG FILE LOADED!"); // Debugging flag
import { authFetch } from "../../utils/auth-fetch";

export function logActivityDataAdmin() {
    return {
        // === STATE ===
        items: [], // Data log
        isLoading: false,
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0
        },
        
        // Filter State (Sesuai Backend Baru)
        filter: {
            search: '',
            month: new Date().getMonth() + 1, // Default bulan ini
            year: new Date().getFullYear(),   // Default tahun ini
            role: '' // Opsional jika admin ingin filter role
        },

        // === INIT ===
        initLog() {
            console.log("INIT LOG ADMIN - Server Side");
            this.fetchData(); // Load data pertama kali
        },

        // === CORE FUNCTION: FETCH DATA FROM SERVER ===
        async fetchData(page = 1) {
            this.isLoading = true;
            
            // 1. Siapkan Query Parameters
            const params = new URLSearchParams({
                page: page, // Halaman yang diminta
                per_page: 15, // Limit per halaman
                month: this.filter.month,
                year: this.filter.year,
                search: this.filter.search,
                role: this.filter.role
            });

            try {
                // 2. Request ke Backend (yang sudah direfactor)
                const response = await authFetch(`/api/log-aktivitas?${params.toString()}`);
                const result = await response.json();

                // 3. Update State
                if (page === 1) {
                    // Jika filter/search baru, ganti semua data
                    this.items = result.data;
                } else {
                    // Jika load more/next page, gabungkan data (Infinite Scroll Style)
                    this.items = [...this.items, ...result.data];
                }

                // 4. Update Pagination Meta
                this.pagination.current_page = result.current_page;
                this.pagination.last_page = result.last_page;
                this.pagination.total = result.total;

            } catch (error) {
                console.error("Gagal memuat log:", error);
            } finally {
                this.isLoading = false;
            }
        },

        // === ACTIONS ===
        
        // Dipanggil saat tombol "Terapkan" ditekan
        applyFilter() {
            this.fetchData(1); // Reset ke halaman 1
        },

        // Dipanggil saat tombol "Load More" ditekan
        loadMore() {
            if (this.pagination.current_page < this.pagination.last_page) {
                this.fetchData(this.pagination.current_page + 1);
            }
        },

        // Reset Filter ke Default
        resetFilter() {
            this.filter.search = '';
            this.filter.month = new Date().getMonth() + 1;
            this.filter.year = new Date().getFullYear();
            this.filter.role = '';
            this.fetchData(1);
        },

        // === HELPERS (FORMATTING) ===
        formatDate(v) {
            if (!v) return "-";
            // Menggunakan format backend jika ada, atau fallback ke JS Date
            return new Date(v).toLocaleDateString("id-ID", {
                day: "numeric", month: "short", year: "numeric"
            });
        },

        formatTime(v) {
            if (!v) return "-";
            return new Date(v).toLocaleTimeString("id-ID", {
                hour: "2-digit", minute: "2-digit"
            });
        }
    }
}

// Register global agar bisa dipanggil x-data="logActivityData()"
window.logActivityDataAdmin = logActivityDataAdmin;