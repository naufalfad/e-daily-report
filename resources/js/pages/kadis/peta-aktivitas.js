// resources/js/pages/kadis/peta-aktivitas.js

export function kadisMapData() {
    return {

        map: null,
        markersLayer: null, 
        markerMap: {},      // Dictionary untuk pencarian cepat marker by ID
        
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
                // 1. RESTORE POSISI TERAKHIR (UX: Agar user tidak kehilangan konteks)
                // ------------------------------------------------------------------
                const savedLat = sessionStorage.getItem('kadis_map_lat');
                const savedLng = sessionStorage.getItem('kadis_map_lng');
                const savedZoom = sessionStorage.getItem('kadis_map_zoom');

                // Default ke koordinat tengah (contoh: Mimika) jika belum ada history
                const initialLat = savedLat ? parseFloat(savedLat) : -4.5467;
                const initialLng = savedLng ? parseFloat(savedLng) : 136.8833;
                const initialZoom = savedZoom ? parseInt(savedZoom) : 13;

                // ------------------------------------------------------------------
                // 2. INISIALISASI PETA
                // ------------------------------------------------------------------
                this.map = L.map('map', { 
                    zoomControl: false, // Kita pindahkan zoom control ke posisi custom
                    attributionControl: false
                }).setView([initialLat, initialLng], initialZoom);

                // Zoom Control di kanan bawah agar tidak menutupi card filter
                L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                // Layer Maps
                const googleRoadmap = L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", { maxZoom: 20 });
                const googleSatelite = L.tileLayer("https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", { maxZoom: 22 });

                const baseLayers = { "Peta Jalan": googleRoadmap, "Satelit": googleSatelite };
                L.control.layers(baseLayers, null, { position: 'bottomright' }).addTo(this.map);
                googleRoadmap.addTo(this.map);

                // ------------------------------------------------------------------
                // 3. KONFIGURASI MARKER CLUSTER (DENGAN TEMA BIRU & LEBIH LEGA)
                // ------------------------------------------------------------------
                this.markersLayer = L.markerClusterGroup({
                    zoomToBoundsOnClick: false, // PENTING: Matikan zoom klik default agar List Popup muncul
                    
                    // [OPTIMASI SPIDERFY] Membuat sebaran marker lebih luas agar tidak numpuk
                    spiderfyOnMaxZoom: true,
                    spiderfyDistanceMultiplier: 2, // Jarak antar marker saat menyebar (default 1)
                    
                    showCoverageOnHover: false, // Hilangkan area biru saat hover agar lebih bersih
                    maxClusterRadius: 60,       // Radius grouping sedikit diperbesar
                    
                    // Kustomisasi Icon Cluster (Sesuai CSS di Blade)
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
                // 4. EVENT LISTENER: KLIK CLUSTER -> MUNCULKAN LIST POPUP (LEBAR & HUMANIS)
                // ------------------------------------------------------------------
                this.markersLayer.on('clusterclick', (a) => {
                    const markers = a.layer.getAllChildMarkers();
                    const count = markers.length;

                    // [UPDATE] Tampilan Card Lebih Lebar (360px) & Header Humanis
                    let content = `
                        <div style="font-family: 'Poppins', sans-serif; width: 360px; overflow: hidden;">
                            
                            <div style="padding: 16px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between;">
                                <div>
                                    <h4 style="color: #0f172a; font-size: 15px; font-weight: 700; margin:0; line-height: 1.2;">
                                        Lokasi Padat
                                    </h4>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 500;">
                                        Terdapat <b>${count}</b> pegawai di titik ini
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
                            // Logic Styling Status (Badge Style)
                            let statusBg = '#fff7ed'; // Default Amber Light
                            let statusText = '#c2410c'; // Default Amber Dark
                            let statusLabel = 'Menunggu';
                            
                            if(data.status === 'approved') { 
                                statusBg = '#ecfdf5'; statusText = '#047857'; statusLabel = 'Disetujui'; 
                            } else if(data.status === 'rejected') { 
                                statusBg = '#fef2f2'; statusText = '#b91c1c'; statusLabel = 'Ditolak'; 
                            }

                            // Border bottom kecuali item terakhir
                            const borderStyle = index !== markers.length - 1 ? 'border-bottom: 1px solid #f1f5f9;' : '';

                            content += `
                                <li style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s; ${borderStyle}" 
                                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    
                                    <div style="flex: 1; padding-right: 12px; min-width: 0;">
                                        <div style="display:flex; justify-content: space-between; align-items:flex-start; margin-bottom: 4px;">
                                            <span style="font-weight: 700; font-size: 13px; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">
                                                ${data.user}
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

                    // Tampilkan Popup Leaflet
                    L.popup({ 
                        offset: [0, -10], 
                        closeButton: true,
                        autoPan: true,
                        maxWidth: 400, // Izinkan lebar maksimal lebih besar
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
                
                new ResizeObserver(() => {
                    this.map.invalidateSize();
                }).observe(document.querySelector('.map-container'));

                // 6. REGISTER FUNGSI GLOBAL
                window.openActivityDetail = (id) => this.openModal(id);
                window.approveActivity = (id) => this.confirmApprove(id);
                window.rejectActivity = (id) => this.handleReject(id);
                
                // Helper function kritis untuk navigasi dari List Cluster -> Marker
                window.zoomToActivity = (id) => this.handleZoomToId(id);
            });
        },

        // ------------------------------------------------------------------
        // FITUR: ZOOM KE ITEM DARI LIST CLUSTER (SPIDERFY LOGIC)
        // ------------------------------------------------------------------
        handleZoomToId(id) {
            // 1. Tutup popup list cluster agar tidak menghalangi
            this.map.closePopup(); 

            // 2. Cari marker berdasarkan ID
            const targetMarker = this.markerMap[id];

            if (targetMarker) {
                // 3. Zoom & Spiderfy otomatis
                this.markersLayer.zoomToShowLayer(targetMarker, () => {
                    // 4. Buka popup detail marker
                    targetMarker.openPopup();
                });
            } else {
                console.warn("Marker ID " + id + " tidak ditemukan.");
            }
        },

        // ------------------------------------------------------------------
        // FITUR: GEOLOCATION / GPS SAYA
        // ------------------------------------------------------------------
        zoomToCurrentLocation() {
            if (!navigator.geolocation) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Browser Tidak Mendukung',
                    text: 'Fitur Geolocation tidak didukung oleh browser Anda.'
                });
                return;
            }

            this.loading = true; 

            if (this.currentLocationMarker) {
                this.map.removeLayer(this.currentLocationMarker);
                this.currentLocationMarker = null;
            }

            this.map.locate({
                setView: true, 
                maxZoom: 17, 
                timeout: 10000, 
                enableHighAccuracy: true 
            })
            .on('locationfound', (e) => {
                this.loading = false;
                const latlng = e.latlng;
                
                // Visualisasi Marker GPS yang lebih modern
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
                Swal.fire({ icon: 'error', title: 'Gagal Akses GPS', text: 'Pastikan GPS aktif.' });
            });
        },

        // ------------------------------------------------------------------
        // LOGIC DATA: FETCH & CREATE MARKERS
        // ------------------------------------------------------------------
        loadData() {
            this.loading = true;
            
            let url = '/api/all-aktivitas'; 
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
                console.error("Error loading map data:", err);
                Swal.fire({icon: 'error', title: 'Kesalahan', text: 'Gagal memuat data peta.'});
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

                // Warna & Status Marker Individual
                let color = '#f59e0b'; // Amber
                let statusLabel = 'Menunggu';
                
                if (act.status === 'approved') {
                    color = '#10b981'; // Emerald
                    statusLabel = 'Disetujui';
                } else if (act.status === 'rejected') {
                    color = '#f43f5e'; // Rose
                    statusLabel = 'Ditolak';
                }

                // Popup Detail Marker (Single Item)
                const popupContent = `
                    <div style="padding: 12px 8px; min-width: 240px; font-family:'Poppins',sans-serif;">
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

                // Buat Marker
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

            // Fit Bounds Smart
            if (latlngs.length > 0) {
                if (!sessionStorage.getItem('kadis_map_lat')) {
                    this.map.fitBounds(latlngs, { padding: [50, 50], maxZoom: 15 });
                }
            }
        },

        // ------------------------------------------------------------------
        // ACTION HANDLERS
        // ------------------------------------------------------------------
        async sendValidation(id, status, reason) {
            const center = this.map.getCenter();
            sessionStorage.setItem('kadis_map_lat', center.lat);
            sessionStorage.setItem('kadis_map_lng', center.lng);
            sessionStorage.setItem('kadis_map_zoom', this.map.getZoom());

            Swal.fire({
                title: 'Menyimpan...', text: 'Mohon tunggu sebentar', allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });

            const payload = { 
                status: status, 
                komentar_validasi: reason || "", 
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            try {
                const response = await fetch(`/kadis/validasi-laporan/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok) throw new Error(data.message || 'Gagal validasi.');

                await Swal.fire({
                    icon: 'success', title: 'Berhasil!',
                    text: status === 'approved' ? 'Laporan disetujui.' : 'Laporan ditolak.',
                    timer: 1500, showConfirmButton: false
                });

                window.location.reload();

            } catch (error) {
                console.error("Validation Error:", error);
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message || 'Terjadi kesalahan sistem.' });
            }
        },

        confirmApprove(id) {
            this.closeModal();
            Swal.fire({
                title: 'Setujui Laporan?',
                text: 'Pastikan data laporan sudah sesuai.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.sendValidation(id, 'approved', '');
                } else {
                    this.openModal(id);
                }
            });
        },

        handleReject(id) {
            this.closeModal();
            Swal.fire({
                title: 'Tolak Laporan',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan',
                inputPlaceholder: 'Contoh: Lokasi tidak sesuai...',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Kirim Penolakan',
                cancelButtonText: 'Batal',
                inputValidator: (value) => { if (!value) return 'Wajib menyertakan alasan!' }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.sendValidation(id, 'rejected', result.value);
                } else {
                    this.openModal(id);
                }
            });
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
        
        exportMap() {
            Swal.fire({
                title: "Export PDF",
                text: "Memproses laporan peta aktivitas...",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#0ea5e9",
                confirmButtonText: "Ya, Download",
                cancelButtonText: "Batal",
                showLoaderOnConfirm: true, 
                preConfirm: () => {
                    const fromDate = this.filter.from || '';
                    const toDate = this.filter.to || '';
                    let url = `/preview-map-pdf?from_date=${fromDate}&to_date=${toDate}`;
                    window.open(url, "_blank");
                    return true; 
                }
            });
        },
    }
}

window.kadisMapData = kadisMapData;