// resources/js/pages/kadis/peta-aktivitas.js

import axios from 'axios';

export function kadisMapData() {
    return {

        map: null,
        markersLayer: null,
        markerMap: {},

        allActivities: [],
        filter: {
            from: '',
            to: '',
            kategori: 'all' // [NEW] Default state untuk filter Kategori Lokasi
        },

        currentLocationMarker: null,

        // State Modal Detail Aktivitas
        showModal: false,
        selectedActivity: null,

        loading: false,

        // EXPORT PDF STATE MANAGEMENT
        isExporting: false,
        exportStatus: 'idle',
        exportMessage: '',
        exportTitle: '',
        exportBlobUrl: null,

        initMap() {
            this.$nextTick(() => {
                // 1. RESTORE POSISI TERAKHIR 
                const savedLat = sessionStorage.getItem('kadis_map_lat');
                const savedLng = sessionStorage.getItem('kadis_map_lng');
                const savedZoom = sessionStorage.getItem('kadis_map_zoom');

                const initialLat = savedLat ? parseFloat(savedLat) : -4.5467;
                const initialLng = savedLng ? parseFloat(savedLng) : 136.8833;
                const initialZoom = savedZoom ? parseInt(savedZoom) : 13;

                // 2. INISIALISASI PETA
                this.map = L.map('map', {
                    zoomControl: false,
                    attributionControl: false
                }).setView([initialLat, initialLng], initialZoom);

                L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                const googleRoadmap = L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", { maxZoom: 20 });
                const googleSatelite = L.tileLayer("https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", { maxZoom: 22 });

                const baseLayers = { "Peta Jalan": googleRoadmap, "Satelit": googleSatelite };
                L.control.layers(baseLayers, null, { position: 'bottomright' }).addTo(this.map);
                googleRoadmap.addTo(this.map);

                // 3. KONFIGURASI MARKER CLUSTER 
                this.markersLayer = L.markerClusterGroup({
                    zoomToBoundsOnClick: false,
                    spiderfyOnMaxZoom: true,
                    spiderfyDistanceMultiplier: 2,
                    showCoverageOnHover: false,
                    maxClusterRadius: 60,

                    iconCreateFunction: function (cluster) {
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

                // 4. EVENT LISTENER: KLIK CLUSTER
                this.markersLayer.on('clusterclick', (a) => {
                    const markers = a.layer.getAllChildMarkers();
                    const count = markers.length;

                    let content = `
                        <div style="font-family: 'Poppins', sans-serif; width: 360px; overflow: hidden;">
                            
                            <div style="padding: 16px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between;">
                                <div>
                                    <h4 style="color: #0f172a; font-size: 15px; font-weight: 700; margin:0; line-height: 1.2;">
                                        Lokasi Padat
                                    </h4>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 500;">
                                        Terdapat <b>${count}</b> laporan di titik ini
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

                        if (data) {
                            let statusBg = '#fff7ed';
                            let statusText = '#c2410c';
                            let statusLabel = 'Menunggu';

                            if (data.status === 'approved') {
                                statusBg = '#ecfdf5'; statusText = '#047857'; statusLabel = 'Disetujui';
                            } else if (data.status === 'rejected') {
                                statusBg = '#fef2f2'; statusText = '#b91c1c'; statusLabel = 'Ditolak';
                            }

                            // [NEW] Label Kategori Lokasi di Cluster Popup
                            const katLokasi = data.kategori_lokasi || 'WFO';

                            let buttonHtml = `
                                <button onclick="window.openActivityDetail(${data.id})" 
                                    style="flex-shrink: 0; background: white; color: #0ea5e9; border: 1px solid #e0f2fe; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.03);"
                                    onmouseover="this.style.background='#0ea5e9'; this.style.color='white'" 
                                    onmouseout="this.style.background='white'; this.style.color='#0ea5e9'">
                                    Lihat
                                </button>
                            `;

                            const borderStyle = index !== markers.length - 1 ? 'border-bottom: 1px solid #f1f5f9;' : '';

                            content += `
                                <li style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s; ${borderStyle}" 
                                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    
                                    <div style="flex: 1; padding-right: 12px; min-width: 0;">
                                        <div style="display:flex; justify-content: space-between; align-items:flex-start; margin-bottom: 4px;">
                                            <span style="font-weight: 700; font-size: 13px; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">
                                                ${data.user}
                                            </span>
                                            
                                            <div style="display:flex; gap: 4px;">
                                                <span style="background:#f1f5f9; color:#475569; font-size:9px; padding:2px 6px; border-radius:12px; font-weight:700; border:1px solid #e2e8f0;">${katLokasi}</span>
                                                <span style="background:${statusBg}; color:${statusText}; font-size:9px; padding:2px 8px; border-radius:12px; font-weight:600; letter-spacing: 0.5px; text-transform: uppercase;">
                                                    ${statusLabel}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div style="font-size: 11px; color: #64748b; line-height: 1.4;">
                                            ${data.kegiatan.length > 45 ? data.kegiatan.substring(0, 45) + '...' : data.kegiatan}
                                        </div>
                                    </div>

                                    ${buttonHtml}
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

                // 5. SETUP LAINNYA
                this.loadData();
                this.initDatePickers();

                this.map.on('moveend', () => {
                    const center = this.map.getCenter();
                    sessionStorage.setItem('kadis_map_lat', center.lat);
                    sessionStorage.setItem('kadis_map_lng', center.lng);
                    sessionStorage.setItem('kadis_map_zoom', this.map.getZoom());
                });

                new ResizeObserver(() => {
                    this.map.invalidateSize();
                }).observe(document.querySelector('.map-container'));

                // 6. REGISTER FUNGSI GLOBAL
                window.openActivityDetail = (id) => this.openModal(id);
                window.zoomToActivity = (id) => this.handleZoomToId(id);
            });
        },

        handleZoomToId(id) {
            this.map.closePopup();
            const targetMarker = this.markerMap[id];

            if (targetMarker) {
                this.markersLayer.zoomToShowLayer(targetMarker, () => {
                    targetMarker.openPopup();
                });
            } else {
                console.warn("Marker ID " + id + " tidak ditemukan.");
            }
        },

        zoomToCurrentLocation() {
            if (!navigator.geolocation) {
                Swal.fire({ icon: 'warning', title: 'Browser Tidak Mendukung', text: 'Fitur Geolocation tidak didukung oleh browser Anda.' });
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

                    const locationMarker = L.circleMarker(latlng, { radius: 8, color: 'white', weight: 3, fillColor: '#3b82f6', fillOpacity: 1 });
                    const accuracyCircle = L.circle(latlng, e.accuracy, { color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.15, weight: 1, interactive: false });

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
                    Swal.fire({ icon: 'error', title: 'Gagal Akses GPS', text: 'Pastikan GPS aktif.' });
                });
        },

        // ------------------------------------------------------------------
        // LOGIC DATA: FETCH & CREATE MARKERS (KADIS GLOBAL API)
        // ------------------------------------------------------------------
        loadData() {
            this.loading = true;

            let url = '/api/all-aktivitas';
            const params = [];

            if (this.filter.from) params.push(`from_date=${this.filter.from}`);
            if (this.filter.to) params.push(`to_date=${this.filter.to}`);

            // [NEW] Injeksi Kategori Lokasi ke Parameter URL
            if (this.filter.kategori && this.filter.kategori !== 'all') {
                params.push(`kategori_lokasi=${this.filter.kategori}`);
            }

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
                    console.error("Error loading map data:", err);
                    Swal.fire({ icon: 'error', title: 'Kesalahan', text: 'Gagal memuat data peta.' });
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

                let color = '#f59e0b';
                let statusLabel = 'Menunggu';

                if (act.status === 'approved') {
                    color = '#10b981';
                    statusLabel = 'Disetujui';
                } else if (act.status === 'rejected') {
                    color = '#f43f5e';
                    statusLabel = 'Ditolak';
                }

                // [NEW] Kategori Lokasi di Popup Single Marker
                const katLokasi = act.kategori_lokasi || 'WFO';

                const popupContent = `
                    <div style="padding: 12px 8px; min-width: 240px; font-family:'Poppins',sans-serif;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 4px;">
                            <span style="font-size:10px; font-weight:700; color:#475569; background:#f1f5f9; padding:2px 6px; border-radius:4px; border:1px solid #cbd5e1;">Kategori: ${katLokasi}</span>
                        </div>
                        <div style="font-weight:700; color:#0f172a; font-size:14px; line-height:1.4; margin-bottom:8px;">
                            ${act.kegiatan}
                        </div>
                        
                        <div style="font-size:12px; color:#64748b; margin-bottom:12px; border-left: 3px solid #cbd5e1; padding-left:10px;">
                            <div style="font-weight:600; color:#334155; margin-bottom:2px;">${act.user}</div>
                            <div>${act.tanggal} • ${act.waktu}</div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; font-weight:600; color:${color}; background:${color}15; padding:4px 10px; border-radius:12px; border:1px solid ${color}30;">
                                ${statusLabel}
                            </span>
                            
                            <button onclick="window.openActivityDetail(${act.id})"
                                style="background:#0f172a; color:white; border:none; padding:6px 14px; font-size:11px; border-radius:6px; cursor:pointer; font-weight:500; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                Detail Penuh
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

            if (latlngs.length > 0) {
                if (!sessionStorage.getItem('kadis_map_lat')) {
                    this.map.fitBounds(latlngs, { padding: [50, 50], maxZoom: 15 });
                }
            }
        },

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

        // ------------------------------------------------------------------
        // EXPORT MAP PDF: MENGIRIMKAN FILTER KATEGORI
        // ------------------------------------------------------------------
        async exportMap() {
            const result = await Swal.fire({
                title: 'Pilih Visualisasi Peta',
                text: 'Bagaimana data akan ditampilkan dalam laporan?',
                icon: 'question',
                input: 'radio',
                inputOptions: {
                    'heatmap': 'Peta Sebaran (Heatmap) - Intensitas',
                    'cluster': 'Peta Titik (Clustering) - Detail Lokasi'
                },
                inputValue: 'heatmap',
                showCancelButton: true,
                confirmButtonColor: '#1C7C54',
                confirmButtonText: 'Lanjutkan Export',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            const selectedMode = result.value;
            const fromDate = this.filter.from || '';
            const toDate = this.filter.to || '';
            const kat = this.filter.kategori || 'all'; // [NEW] Ambil filter kategori

            this.isExporting = true;
            this.exportStatus = 'loading';
            this.exportTitle = 'Export Data Laporan';
            this.exportMessage = 'Menghubungkan ke server peta...';
            this.exportBlobUrl = null;

            let visualText = selectedMode === 'heatmap' ? 'Heatmap Intensitas' : 'Titik Clustering';
            let progressSteps = [
                'Mengambil data aktivitas...',
                `Merender visualisasi: ${visualText}...`,
                'Menyusun dokumen PDF...',
                'Finishing...'
            ];
            let stepIndex = 0;

            const progressInterval = setInterval(() => {
                if (this.exportStatus === 'loading' && stepIndex < progressSteps.length) {
                    this.exportMessage = progressSteps[stepIndex];
                    stepIndex++;
                }
            }, 2500);

            // [FIX] Axios Get mengirim parameter mode & kategori_lokasi
            axios.get(`/preview-map-pdf`, {
                params: {
                    from_date: fromDate,
                    to_date: toDate,
                    mode: selectedMode,
                    kategori_lokasi: kat
                },
                responseType: 'blob',
                timeout: 60000
            })
                .then(response => {
                    clearInterval(progressInterval);

                    this.exportStatus = 'success';
                    this.exportTitle = 'Siap Diunduh!';
                    this.exportMessage = 'Dokumen PDF berhasil dibuat. Klik tombol di bawah untuk menutup.';

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    this.exportBlobUrl = url;

                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `Peta_Aktivitas_${selectedMode}_${new Date().getTime()}.pdf`);
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                })
                .catch(error => {
                    clearInterval(progressInterval);
                    this.exportStatus = 'error';
                    this.exportTitle = 'Gagal Export';

                    if (error.response && error.response.data instanceof Blob) {
                        const reader = new FileReader();
                        reader.onload = () => {
                            try {
                                const errorJson = JSON.parse(reader.result);
                                this.exportMessage = errorJson.message || 'Terjadi kesalahan pada server renderer.';
                            } catch (e) {
                                this.exportMessage = 'Terjadi kesalahan jaringan atau server timeout.';
                            }
                        };
                        reader.readAsText(error.response.data);
                    } else {
                        this.exportMessage = error.message || 'Service peta tidak merespon.';
                    }
                });
        },
    }
}

window.kadisMapData = kadisMapData;