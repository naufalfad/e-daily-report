console.log("PENILAI LOG FILE LOADED!");
import { authFetch } from "../../utils/auth-fetch";

export function logActivityPenilai() {
    return {
        allItems: [],
        filteredItems: [],
        filter: { from: "", to: "" },

        initLog() {
            console.log("INIT LOG PENILAI");
            authFetch("/api/log-aktivitas")
                .then(r => r.json())
                .then(res => {
                    this.allItems = res.data.map(i => ({
                        ...i,
                        timestamp_fixed: i.timestamp?.replace(" ", "T")
                    }));
                    this.filteredItems = this.allItems;
                });
        },

        filterData() {
            let from = this.filter.from ? new Date(this.filter.from) : null;
            let to = this.filter.to ? new Date(this.filter.to) : null;

            if (from) from.setHours(0, 0, 0, 0);
            if (to) to.setHours(23, 59, 59, 999);

            this.filteredItems = this.allItems.filter(it => {
                const t = new Date(it.timestamp_fixed);
                if (from && t < from) return false;
                if (to && t > to) return false;
                return true;
            });
        },

        formatDate(v) {
            if (!v) return "-";
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

window.logActivityPenilai = logActivityPenilai;
