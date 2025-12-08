console.log("KADIS LOG FILE LOADED!");

import { authFetch } from "../../utils/auth-fetch";

export function logActivityKadis() {
    return {
        allItems: [],
        filteredItems: [],
        filter: { from: "", to: "" },

        initLog() {
            console.log("INIT LOG KADIS");

            authFetch("/api/log-aktivitas")
                .then(r => r.json())
                .then(res => {

                    // FORMAT BALIK KE AWAL, TIDAK SAMA DENGAN KABID/PENILAI
                    this.allItems = res.data.map(i => ({
                        id: i.id,
                        tanggal: i.timestamp?.split(" ")[0], // YYYY-MM-DD
                        waktu: i.timestamp?.split(" ")[1],   // HH:MM:SS
                        aktivitas: i.deskripsi_aktivitas ?? "-",
                        deskripsi: "-", // kadis tdk punya detail
                        tipe: "system",
                        timestamp_fixed: i.timestamp?.replace(" ", "T")
                    }));

                    this.filteredItems = this.allItems;
                })
                .catch(err => console.error("ERR LOG:", err));
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

        formatDate(dateString) {
            if (!dateString) return "-";
            return new Date(dateString).toLocaleDateString("id-ID", {
                day: "numeric",
                month: "short",
                year: "numeric",
            });
        },

        formatTime(v) {
            if (!v) return "-";
            return v.substring(0, 5); // ambil HH:MM saja
        }
    };
}

window.logActivityKadis = logActivityKadis;
