// resources/js/pages/staf/peta-aktivitas.js

document.addEventListener('alpine:init', () => {
    Alpine.data('mapPageData', () => ({

        map: null,
        markersLayer: null,
        allActivities: [],
        filter: {
            from: '',
            to: ''
        },

        showModal: false,
        selectedActivity: null,

        initMap() {
            this.$nextTick(() => {
                // 1. Init Map
                this.map = L.map('map', { zoomControl: true })
                    .setView([-4.557, 136.885], 13);

                // 2. Tile Layers
                const googleRoadmap = L.tileLayer(
                    "https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}",
                    { attribution: "Google Maps", maxZoom: 20 }
                );

                const googleSatelit = L.tileLayer(
                    "https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}",
                    { attribution: "Google Satelit", maxZoom: 22 }
                );

                const baseLayers = {
                    "Google Maps": googleRoadmap,
                    "Google Satelit": googleSatelit
                };

                L.control.layers(baseLayers).addTo(this.map);
                googleRoadmap.addTo(this.map);

                // 3. Layer Markers
                this.markersLayer = L.layerGroup().addTo(this.map);

                // 4. Load Data
                this.loadData();
                this.initDatePickers();

                // 5. Resize Observer
                new ResizeObserver(() =>
                    this.map.invalidateSize()
                ).observe(document.getElementById('map'));

                // 6. BRIDGING FUNCTION
                window.openActivityDetail = (id) => {
                    this.openModal(id);
                };
            });
        },

        // ---------------- LOGIC DATA ----------------
        loadData() {
            fetch('/data/peta-aktivitas.json')
                .then(res => res.json())
                .then(data => {
                    this.allActivities = data;
                    this.loadMarkers(data);
                })
                .catch(err => console.error("Gagal memuat data:", err));
        },

        loadMarkers(data) {
            this.markersLayer.clearLayers();

            data.forEach(act => {
                // Penentuan warna status
                let color = '#f59e0b';
                let bgColorStatus = '#fffbeb';
                let statusLabel = 'Menunggu';

                if (act.status === 'approved') {
                    color = '#22c55e';
                    bgColorStatus = '#dcfce7';
                    statusLabel = 'Disetujui';
                } else if (act.status === 'rejected') {
                    color = '#ef4444';
                    bgColorStatus = '#fee2e2';
                    statusLabel = 'Ditolak';
                }

                // Popup content HTML
                const popupContent = `
                    <div style="padding: 12px 10px; min-width: 260px;">
                        
                        <div style="margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                            <strong style="font-size:14px; color:#1C7C54; display:block; line-height:1.3; margin-bottom:2px;">
                                ${act.kegiatan}
                            </strong>
                            <div style="display:flex; align-items:center; gap:4px; font-size:11px; color:#64748b;">
                                <span>👤 ${act.user}</span>
                                <span>•</span>
                                <span style="color:#0E7A4A; font-weight:500;">${act.kategori_aktivitas}</span>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div style="display:flex; gap:10px; font-size:11px; color:#475569; margin-bottom:6px;">
                                <span style="display:flex; align-items:center; gap:3px;">📅 ${act.tanggal}</span>
                                <span style="display:flex; align-items:center; gap:3px;">⏰ ${act.waktu}</span>
                            </div>
                            <p style="font-size:12px; line-height:1.5; color:#334155; margin:0; font-style:italic; background:#f8fafc; padding:6px; border-radius:4px; border-left: 3px solid ${color};">
                                "${act.deskripsi.length > 50 ? act.deskripsi.substring(0,50)+'...' : act.deskripsi}"
                            </p>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:8px; border-top:1px dashed #e2e8f0;">
                            <span style="font-size:10px; font-weight:600; color:${color}; background:${bgColorStatus}; padding:2px 8px; border-radius:10px; border:1px solid ${color}40;">
                                ${statusLabel}
                            </span>

                            <button onclick="window.openActivityDetail(${act.id})"
                               style="cursor: pointer; border: none; display: inline-block; background-color: #0E7A4A; color: #ffffff; padding: 5px 12px; font-size: 11px; font-weight: 500; border-radius: 6px; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.1);"
                               onmouseover="this.style.backgroundColor='#0a5c38'"
                               onmouseout="this.style.backgroundColor='#0E7A4A'"
                            >
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                `;

                L.circleMarker([act.lat, act.lng], {
                    radius: 7,
                    fillColor: color,
                    color: '#FFF',
                    weight: 2,
                    fillOpacity: 0.9
                })
                .bindPopup(popupContent)
                .addTo(this.markersLayer);
            });
        },

        // ---------------- MODAL ----------------
        openModal(id) {
            const found = this.allActivities.find(item => item.id == id);
            if (found) {
                this.selectedActivity = found;
                this.showModal = true;
            }
        },

        closeModal() {
            this.showModal = false;
            setTimeout(() => {
                this.selectedActivity = null;
            }, 300);
        },

        // ---------------- FILTER ----------------
        applyFilter() {
            const from = this.filter.from ? new Date(this.filter.from) : null;
            const to = this.filter.to ? new Date(this.filter.to) : null;

            if (from) from.setHours(0, 0, 0, 0);
            if (to) to.setHours(23, 59, 59, 999);

            const filtered = this.allActivities.filter(act => {
                const actDate = new Date(act.tanggal);
                if (from && actDate < from) return false;
                if (to && actDate > to) return false;
                return true;
            });

            this.loadMarkers(filtered);
        },

        // ---------------- DATEPICKER ----------------
        initDatePickers() {
            this.$nextTick(() => {
                ['tgl_dari', 'tgl_sampai'].forEach(id => {
                    const input = document.getElementById(id);
                    const btn = document.getElementById(id + '_btn');
                    if (input && btn) {
                        btn.addEventListener('click', () => {
                            try { input.showPicker(); }
                            catch (e) { input.focus(); }
                        });
                    }
                });
            });
        }
    }));
});
