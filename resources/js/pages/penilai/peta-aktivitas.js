// =====================================================
//   PETA AKTIVITAS — SCRIPT TERPISAH (Penilai)
// =====================================================

export function mapPageData() {
    return {
        map: null,
        markersLayer: null,
        allActivities: [],
        filter: {
            from: '',
            to: ''
        },

        // --------------------------
        // INIT MAP
        // --------------------------
        initMap() {
            this.$nextTick(() => {
                this.map = L.map('map', {
                    zoomControl: true
                }).setView([-4.557, 136.885], 13);

                const googleRoadmap = L.tileLayer(
                    "https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}",
                    { attribution: "Google Maps", maxZoom: 20 }
                );

                const googleSatelit = L.tileLayer(
                    "https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}",
                    { attribution: "Google Satelit", maxZoom: 22 }
                );

                L.control.layers(
                    {
                        "Google Maps": googleRoadmap,
                        "Google Satelit": googleSatelit
                    }
                ).addTo(this.map);

                googleRoadmap.addTo(this.map);

                this.markersLayer = L.layerGroup().addTo(this.map);

                this.loadData();
                this.initDatePickers();

                // Fix width/height resize
                new ResizeObserver(() => this.map.invalidateSize())
                    .observe(document.getElementById('map'));
            });
        },

        // --------------------------
        // LOAD DATA JSON
        // --------------------------
        loadData() {
            fetch('/data/peta-aktivitas.json')
                .then(res => res.json())
                .then(data => {
                    this.allActivities = data;
                    this.loadMarkers(this.allActivities);
                })
                .catch(err => console.error("Gagal muat data:", err));
        },

        // --------------------------
        // LOAD MARKERS
        // --------------------------
        loadMarkers(data) {
            this.markersLayer.clearLayers();

            data.forEach(act => {
                let color = '#f59e0b';
                if (act.status === 'approved') color = '#22c55e';
                else if (act.status === 'rejected') color = '#ef4444';

                L.circleMarker([act.lat, act.lng], {
                    radius: 7,
                    fillColor: color,
                    color: '#FFF',
                    weight: 2,
                    fillOpacity: 0.9
                })
                .bindPopup(`
                    <div style="font-size:13px;line-height:1.5;">
                        <strong style="font-size:14px;color:#1C7C54;">${act.kegiatan}</strong><br>
                        <strong>Pegawai:</strong> ${act.user}<br>
                        <strong>Status:</strong> ${act.status}
                    </div>
                `)
                .addTo(this.markersLayer);
            });
        },

        // --------------------------
        // FILTER
        // --------------------------
        applyFilter() {
            const from = this.filter.from ? new Date(this.filter.from) : null;
            const to   = this.filter.to   ? new Date(this.filter.to)   : null;

            if (from) from.setHours(0, 0, 0, 0);
            if (to)   to.setHours(23, 59, 59, 999);

            const filtered = this.allActivities.filter(act => {
                const date = new Date(act.tanggal_laporan);
                if (from && date < from) return false;
                if (to   && date > to)   return false;
                return true;
            });

            this.loadMarkers(filtered);
        },

        // --------------------------
        // DATE PICKER
        // --------------------------
        initDatePickers() {
            this.$nextTick(() => {
                ['tgl_dari', 'tgl_sampai'].forEach(id => {
                    const input = document.getElementById(id);
                    const btn   = document.getElementById(id + '_btn');
                    if (!input || !btn) return;

                    btn.addEventListener('click', () => {
                        try { input.showPicker(); }
                        catch { input.focus(); }
                    });
                });
            });
        }
    };
}
