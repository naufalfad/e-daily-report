// resources/js/pages/staf/peta-aktivitas.js

export function stafMapData() {
    return {

        map: null,
        markersLayer: null, 
        markerMap: {},      // Dictionary untuk pencarian marker by ID
        
        allActivities: [],
        filter: {
            from: '',
            to: ''
        },

        // State untuk marker lokasi pengguna (GPS)
        currentLocationMarker: null, 

        // State Modal Detail
        showModal: false,
        selectedActivity: null,

        // Loading State
        loading: false,

        initMap() {
            this.$nextTick(() => {
                // ------------------------------------------------------------------
                // 1. RESTORE POSISI TERAKHIR (Agar UX Konsisten)
                // ------------------------------------------------------------------
                const savedLat = sessionStorage.getItem('staf_map_lat');
                const savedLng = sessionStorage.getItem('staf_map_lng');
                const savedZoom = sessionStorage.getItem('staf_map_zoom');

                // Default: Timika (jika belum ada history)
                const initialLat = savedLat ? parseFloat(savedLat) : -4.5467;
                const initialLng = savedLng ? parseFloat(savedLng) : 136.8833;
                const initialZoom = savedZoom ? parseInt(savedZoom) : 13;

                // ------------------------------------------------------------------
                // 2. INISIALISASI PETA
                // ------------------------------------------------------------------
                this.map = L.map('map', { 
                    zoomControl: false, 
                    attributionControl: false
                }).setView([initialLat, initialLng], initialZoom);

                // Zoom Control di kanan bawah
                L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                // Layer Maps
                const googleRoadmap = L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", { maxZoom: 20 });
                const googleSatelite = L.tileLayer("https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", { maxZoom: 22 });

                const baseLayers = { "Peta Jalan": googleRoadmap, "Satelit": googleSatelite };
                L.control.layers(baseLayers, null, { position: 'bottomright' }).addTo(this.map);
                googleRoadmap.addTo(this.map);

                // ------------------------------------------------------------------
                // 3. KONFIGURASI MARKER CLUSTER (Tema Biru & Humanis)
                // ------------------------------------------------------------------
                this.markersLayer = L.markerClusterGroup({
                    zoomToBoundsOnClick: false, // Matikan zoom default -> Tampilkan List Popup
                    spiderfyOnMaxZoom: true,
                    spiderfyDistanceMultiplier: 2, // Jarak antar marker lega
                    showCoverageOnHover: false,
                    maxClusterRadius: 60,
                    
                    // Icon Cluster (Biru Monokromatik)
                    iconCreateFunction: function(cluster) {
                        var count = cluster.getChildCount();
                        var c = ' marker-cluster-';
                        
                        if (count < 10) { c += 'small'; } 
                        else if (count < 50) { c += 'medium'; } 
                        else { c += 'large'; }
                
                        return new L.DivIcon({ 
                            html: '<div><span>' + count + '</span></div>', 
                            className: 'marker-cluster-custom' + c, 
                            iconSize: new L.Point(40, 40) 
                        });
                    }
                });

                // ------------------------------------------------------------------
                // 4. EVENT LISTENER: KLIK CLUSTER -> LIST POPUP
                // ------------------------------------------------------------------
                this.markersLayer.on('clusterclick', (a) => {
                    const markers = a.layer.getAllChildMarkers();
                    const count = markers.length;

                    // Header Popup Humanis
                    let content = `
                        <div style="font-family: 'Poppins', sans-serif; width: 360px; overflow: hidden;">
                            
                            <div style="padding: 16px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between;">
                                <div>
                                    <h4 style="color: #0f172a; font-size: 15px; font-weight: 700; margin:0; line-height: 1.2;">
                                        Lokasi Aktivitas
                                    </h4>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 500;">
                                        Anda memiliki <b>${count}</b> laporan di titik ini
                                    </span>
                                </div>
                                <span style="background:#eff6ff; color:#3b82f6; font-size:10px; padding:4px 10px; border-radius:20px; font-weight:700; border: 1px solid #dbeafe;">
                                    CLUSTER
                                </span>
                            </div>

                            <ul style="list-style:none; padding: 0; margin:0; max-height: 320px; overflow-y: auto;">
                    `;

                    markers.forEach((marker, index) => {
                        const data = marker.options.customData; 
                        
                        if(data) {
                            // Status Badge Style
                            let statusBg = '#fff7ed'; 
                            let statusText = '#c2410c'; 
                            let statusLabel = 'Menunggu';
                            
                            if(data.status === 'approved') { 
                                statusBg = '#ecfdf5'; statusText = '#047857'; statusLabel = 'Disetujui'; 
                            } else if(data.status === 'rejected') { 
                                statusBg = '#fef2f2'; statusText = '#b91c1c'; statusLabel = 'Ditolak'; 
                            }

                            const borderStyle = index !== markers.length - 1 ? 'border-bottom: 1px solid #f1f5f9;' : '';

                            content += `
                                <li style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s; ${borderStyle}" 
                                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    
                                    <div style="flex: 1; padding-right: 12px; min-width: 0;">
                                        <div style="display:flex; justify-content: space-between; align-items:flex-start; margin-bottom: 4px;">
                                            <span style="font-weight: 700; font-size: 13px; color: #334155;">
                                                ${data.tanggal}
                                            </span>
                                            <span style="background:${statusBg}; color:${statusText}; font-size:9px; padding:2px 8px; border-radius:12px; font-weight:600; letter-spacing: 0.5px; text-transform: uppercase;">
                                                ${statusLabel}
                                            </span>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b; line-height: 1.4;">
                                            ${data.kegiatan.length > 45 ? data.kegiatan.substring(0, 45) + '...' : data.kegiatan}
                                        </div>
                                    </div>

                                    <button onclick="window.zoomToActivity(${data.id})" 
                                        style="flex-shrink: 0; background: white; color: #0ea5e9; border: 1px solid #e0f2fe; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.03);"
                                        onmouseover="this.style.background='#0ea5e9'; this.style.color='white'" 
                                        onmouseout="this.style.background='white'; this.style.color='#0ea5e9'">
                                        Lihat
                                    </button>
                                </li>
                            `;
                        }
                    });

                    content += `</ul></div>`;

                    L.popup({ 
                        offset: [0, -10], 
                        closeButton: true,
                        autoPan: true,
                        maxWidth: 400,
                        className: 'custom-cluster-popup' 
                    })
                    .setLatLng(a.latlng)
                    .setContent(content)
                    .openOn(this.map);
                });

                this.map.addLayer(this.markersLayer);

                // 5. Load Data & Observers
                this.loadData();
                this.initDatePickers();
                
                // Simpan posisi peta saat bergerak (Event Listener)
                this.map.on('moveend', () => {
                    const center = this.map.getCenter();
                    sessionStorage.setItem('staf_map_lat', center.lat);
                    sessionStorage.setItem('staf_map_lng', center.lng);
                    sessionStorage.setItem('staf_map_zoom', this.map.getZoom());
                });

                new ResizeObserver(() => {
                    this.map.invalidateSize();
                }).observe(document.querySelector('.map-container'));

                // 6. REGISTER GLOBAL FUNCTIONS
                window.openActivityDetail = (id) => this.openModal(id);
                
                // Helper Navigasi Cluster -> Marker
                window.zoomToActivity = (id) => this.handleZoomToId(id);
            });
        },

        // ------------------------------------------------------------------
        // ZOOM KE ITEM (SPIDERFY LOGIC)
        // ------------------------------------------------------------------
        handleZoomToId(id) {
            this.map.closePopup(); 
            const targetMarker = this.markerMap[id];

            if (targetMarker) {
                this.markersLayer.zoomToShowLayer(targetMarker, () => {
                    targetMarker.openPopup();
                });
            }
        },

        // ------------------------------------------------------------------
        // FITUR: GPS SAYA
        // ------------------------------------------------------------------
        zoomToCurrentLocation() {
            if (!navigator.geolocation) {
                Swal.fire({ icon: 'warning', title: 'Gagal', text: 'Browser tidak mendukung Geolocation.' });
                return;
            }

            this.loading = true; 

            if (this.currentLocationMarker) {
                this.map.removeLayer(this.currentLocationMarker);
                this.currentLocationMarker = null;
            }

            this.map.locate({ setView: true, maxZoom: 17, timeout: 10000, enableHighAccuracy: true })
            .on('locationfound', (e) => {
                this.loading = false;
                const latlng = e.latlng;
                
                const locationMarker = L.circleMarker(latlng, {
                    radius: 8, color: 'white', weight: 3, fillColor: '#3b82f6', fillOpacity: 1
                });

                const accuracyCircle = L.circle(latlng, e.accuracy, {
                    color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.15, weight: 1, interactive: false
                });

                this.currentLocationMarker = L.layerGroup([locationMarker, accuracyCircle]).addTo(this.map);
                
                locationMarker.bindPopup(`
                    <div style="text-align:center; font-family: 'Poppins', sans-serif; padding: 4px;">
                        <b style="color:#1e293b;">Lokasi Anda</b><br>
                        <span style="font-size:11px; color:#64748b;">Akurasi: ${Math.round(e.accuracy)}m</span>
                    </div>
                `).openPopup();
                
                setTimeout(() => {
                    if (this.currentLocationMarker) {
                        this.map.removeLayer(this.currentLocationMarker);
                        this.currentLocationMarker = null;
                    }
                }, 10000);
            })
            .on('locationerror', (e) => {
                this.loading = false;
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mendapatkan lokasi GPS.' });
            });
        },

        // ------------------------------------------------------------------
        // LOAD DATA
        // ------------------------------------------------------------------
        loadData() {
            this.loading = true;
            
            // Endpoint untuk Staf (Hanya data sendiri)
            let url = '/api/peta-aktivitas'; 
            
            const params = [];
            if (this.filter.from) params.push(`from_date=${this.filter.from}`);
            if (this.filter.to) params.push(`to_date=${this.filter.to}`);
            if (params.length > 0) url += '?' + params.join('&');

            fetch(url, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.allActivities = data.data;
                    this.loadMarkers(data.data);
                }
            })
            .catch(err => {
                console.error("Error loading data:", err);
            })
            .finally(() => { this.loading = false; });
        },

        loadMarkers(data) {
            this.markersLayer.clearLayers();
            this.markerMap = {}; 

            if (data.length === 0) return;
            
            if (this.currentLocationMarker) {
                this.map.removeLayer(this.currentLocationMarker);
                this.currentLocationMarker = null;
            }

            const latlngs = [];
            
            data.forEach(act => {
                if (!act.lat || !act.lng) return;

                // Warna Status
                let color = '#f59e0b'; // Amber
                let statusLabel = 'Menunggu';
                
                if (act.status === 'approved') {
                    color = '#10b981'; // Emerald
                    statusLabel = 'Disetujui';
                } else if (act.status === 'rejected') {
                    color = '#f43f5e'; // Rose
                    statusLabel = 'Ditolak';
                }

                // Popup Detail Single
                const popupContent = `
                    <div style="padding: 12px 8px; min-width: 240px; font-family:'Poppins',sans-serif;">
                        <div style="font-weight:700; color:#0f172a; font-size:14px; line-height:1.4; margin-bottom:8px;">
                            ${act.kegiatan}
                        </div>
                        <div style="font-size:12px; color:#64748b; margin-bottom:12px; border-left: 3px solid #cbd5e1; padding-left:10px;">
                            <div style="font-weight:600; color:#334155; margin-bottom:2px;">${act.tanggal}</div>
                            <div>Pukul ${act.waktu}</div>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; font-weight:600; color:${color}; background:${color}15; padding:4px 10px; border-radius:12px; border:1px solid ${color}30;">
                                ${statusLabel}
                            </span>
                            <button onclick="window.openActivityDetail(${act.id})"
                                style="background:#0f172a; color:white; border:none; padding:6px 14px; font-size:11px; border-radius:6px; cursor:pointer; font-weight:500; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                Detail
                            </button>
                        </div>
                    </div>
                `;

                const marker = L.circleMarker([act.lat, act.lng], {
                    radius: 7, 
                    fillColor: color,
                    color: '#ffffff', 
                    weight: 2,
                    fillOpacity: 1,
                    customData: act 
                })
                .bindPopup(popupContent);

                this.markersLayer.addLayer(marker);
                this.markerMap[act.id] = marker;
                
                latlngs.push([act.lat, act.lng]);
            });

            // Fit Bounds hanya jika belum ada posisi tersimpan
            if (latlngs.length > 0) {
                if (!sessionStorage.getItem('staf_map_lat')) {
                    this.map.fitBounds(latlngs, { padding: [50, 50], maxZoom: 15 });
                }
            }
        },

        // ------------------------------------------------------------------
        // UTILS
        // ------------------------------------------------------------------
        openModal(id) {
            const found = this.allActivities.find(item => item.id == id);
            if (found) {
                this.selectedActivity = found;
                this.showModal = true;
            }
        },
        closeModal() { 
            this.showModal = false; 
            setTimeout(() => { this.selectedActivity = null; }, 300); 
        },
        
        applyFilter() { this.loadData(); },
        
        initDatePickers() {
            this.$nextTick(() => {
                ['tgl_dari', 'tgl_sampai'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('click', () => el.showPicker ? el.showPicker() : el.focus());
                });
            });
        },
        
        // ---------- EXPORT ----------
        exportMap() {
            Swal.fire({
                title: "Export PDF",
                text: "Download laporan peta aktivitas Anda?",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#0ea5e9",
                confirmButtonText: "Ya, Download",
                cancelButtonText: "Batal",
                showLoaderOnConfirm: true, 
                preConfirm: () => {
                    const fromDate = this.filter.from || '';
                    const toDate = this.filter.to || '';
                    // Menggunakan endpoint global
                    let url = `/preview-map-pdf?from_date=${fromDate}&to_date=${toDate}`;
                    window.open(url, "_blank");
                    return true; 
                }
            });
        },
    }
}

window.stafMapData = stafMapData;