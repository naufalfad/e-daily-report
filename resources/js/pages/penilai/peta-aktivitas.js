// resources/js/pages/penilai/peta-aktivitas.js

export function penilaiMapData() {
    return {

        map: null,
        markersLayer: null,
        allActivities: [],
        filter: {
            from: '',
            to: ''
        },

        showModal: false,
        selectedActivity: null,
        loading: false, // State loading

        initMap() {
            this.$nextTick(() => {
                // 1. Init Map (Default View: Timika, Papua Tengah)
                this.map = L.map('map', { zoomControl: true })
                    .setView([-4.5467, 136.8833], 13);

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

                // 4. Load Data Awal
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

        // ---------------- LOGIC DATA (SERVER SIDE) ----------------
        loadData() {
            this.loading = true;

            // Build URL dengan Filter
            let url = '/api/staf-aktivitas'; // Endpoint khusus Penilai (Lihat bawahan)
            const params = [];

            if (this.filter.from) params.push(`from_date=${this.filter.from}`);
            if (this.filter.to) params.push(`to_date=${this.filter.to}`);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            fetch(url, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        console.error("API error:", data);
                        return;
                    }
                    this.allActivities = data.data;
                    this.loadMarkers(data.data);
                })
                .catch(err => console.error("Gagal memuat data:", err))
                .finally(() => {
                    this.loading = false;
                });
        },

        loadMarkers(data) {
            this.markersLayer.clearLayers();

            if (data.length === 0) return;

            const latlngs = [];

            data.forEach(act => {
                // Validasi koordinat
                if (!act.lat || !act.lng) return;

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
                                "${act.deskripsi && act.deskripsi.length > 50 ? act.deskripsi.substring(0, 50) + '...' : (act.deskripsi || '-')}"
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
                    radius: 8,
                    fillColor: color,
                    color: '#FFF',
                    weight: 2,
                    fillOpacity: 0.9
                })
                    .bindPopup(popupContent)
                    .addTo(this.markersLayer);
                
                latlngs.push([act.lat, act.lng]);
            });

            // Auto Zoom ke area marker jika ada data
            if (latlngs.length > 0) {
                this.map.fitBounds(latlngs, { padding: [50, 50] });
            }
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

        // ---------------- FILTER ACTION ----------------
        applyFilter() {
            // Panggil API ulang dengan parameter tanggal
            this.loadData();
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
        },
        exportMap() {

            const osmTemp = L.tileLayer(
                "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
                { maxZoom: 20 }
            );

            let activeGoogleLayer = null;

            this.map.eachLayer(layer => {
                if (layer._url?.includes("google")) {
                    activeGoogleLayer = layer;
                    this.map.removeLayer(layer);
                }
            });

            osmTemp.addTo(this.map);

            Swal.fire({
                title: "Export Peta?",
                text: "Peta aktivitas akan diproses menjadi PDF.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#1C7C54",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Export",
            }).then((result) => {

                if (!result.isConfirmed) {
                    if (activeGoogleLayer) activeGoogleLayer.addTo(this.map);
                    this.map.removeLayer(osmTemp);
                    return;
                }

                const mapEl = document.getElementById("map");

                html2canvas(mapEl, {
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: "#ffffff",
                }).then((canvas) => {

                    const imgData = canvas.toDataURL("image/png");

                    fetch("/export-map", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                            "Authorization": "Bearer " + localStorage.getItem("auth_token")
                        },
                        body: JSON.stringify({ image: imgData }),
                    })
                    .then(res => res.blob())
                    .then(blob => {

                        // === BUKA TAB BARU DENGAN PDF ===
                        const pdfUrl = URL.createObjectURL(blob);
                        window.open(pdfUrl, "_blank");

                    });

                });
            });
        },
    }
}

// Global Registration
window.penilaiMapData = penilaiMapData;