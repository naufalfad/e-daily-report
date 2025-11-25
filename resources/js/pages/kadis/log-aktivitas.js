// =====================================================
//   LOG AKTIVITAS — SCRIPT TERPISAH (Penilai & Staf)
// =====================================================

export function logActivityData() {
    return {
        allItems: [],
        filteredItems: [],
        filter: {
            from: '',
            to: ''
        },

        // ----- INIT -----
        initLog() {
            fetch('/data/log-aktivitas.json')
                .then(res => res.json())
                .then(data => {
                    this.allItems = data.sort((a, b) => {
                        return new Date(b.tanggal + ' ' + b.waktu)
                             - new Date(a.tanggal + ' ' + a.waktu);
                    });
                    this.filteredItems = this.allItems;
                })
                .catch(err => console.error('Gagal memuat log:', err));
        },

        // ----- FILTER -----
        filterData() {
            const from = this.filter.from ? new Date(this.filter.from) : null;
            const to   = this.filter.to   ? new Date(this.filter.to)   : null;

            if (from) from.setHours(0, 0, 0, 0);
            if (to)   to.setHours(23, 59, 59, 999);

            this.filteredItems = this.allItems.filter(item => {
                const itemDate = new Date(item.tanggal);
                if (from && itemDate < from) return false;
                if (to   && itemDate > to)   return false;
                return true;
            });
        },

        // ----- HELPER -----
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
    };
}
