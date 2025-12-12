// resources/js/pages/kadis/peta-aktivitas.js

export function kadisMapData() {
    return {

        map: null,
        markersLayer: null,
        allActivities: [],
        filter: {
            from: '',
            to: ''
        },

        // [KRITIS] State untuk marker lokasi pengguna saat ini (sementara)
        currentLocationMarker: null, 

        // State Modal Detail
        showModal: false,
        selectedActivity: null,

        // Loading State
        loading: false,

        initMap() {
            this.$nextTick(() => {
                // 1. RESTORE ZOOM & POSISI (Agar nyaman saat reload)
                const savedLat = sessionStorage.getItem('kadis_map_lat');
                const savedLng = sessionStorage.getItem('kadis_map_lng');
                const savedZoom = sessionStorage.getItem('kadis_map_zoom');

                const initialLat = savedLat ? parseFloat(savedLat) : -4.5467;
                const initialLng = savedLng ? parseFloat(savedLng) : 136.8833;
                const initialZoom = savedZoom ? parseInt(savedZoom) : 13;

                // Bersihkan storage
                sessionStorage.removeItem('kadis_map_lat');
                sessionStorage.removeItem('kadis_map_lng');
                sessionStorage.removeItem('kadis_map_zoom');

                // Init Map
                this.map = L.map('map', { zoomControl: true }).setView([initialLat, initialLng], initialZoom);

                const googleRoadmap = L.tileLayer(
                    "https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}",
                    { attribution: "Google Maps", maxZoom: 20 }
                );

                const googleSatelite = L.tileLayer(
                    "https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}",
                    { 
                        attribution: "Google Satelite", 
                        maxZoom: 22 
                    }
                );
               const baseLayers = {
                    "Google Maps": googleRoadmap,
                    "Google Satelite": googleSatelite,
                };

                L.control.layers(baseLayers).addTo(this.map);
                googleRoadmap.addTo(this.map);

                // 3. Layer Markers
                this.markersLayer = L.layerGroup().addTo(this.map);

                // 4. Load Data Awal
                this.loadData();
                this.initDatePickers();

                new ResizeObserver(() => this.map.invalidateSize()).observe(document.getElementById('map'));

                // Register Global Functions
                window.openActivityDetail = (id) => this.openModal(id);
                window.approveActivity = (id) => this.confirmApprove(id);
                window.rejectActivity = (id) => this.handleReject(id);
            });
        },

        // ---------------- FITUR: ZOOM TO CURRENT GPS LOCATION ----------------
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

            // [BEST PRACTICE] 1. HAPUS MARKER LAMA SEBELUM MEMBUAT YANG BARU
            if (this.currentLocationMarker) {
                this.map.removeLayer(this.currentLocationMarker);
                this.currentLocationMarker = null;
            }

            // Gunakan metode locate Leaflet
            this.map.locate({
                setView: true, 
                maxZoom: 16,  
                timeout: 10000, 
                enableHighAccuracy: true 
            })
            .on('locationfound', (e) => {
                this.loading = false;
                const latlng = e.latlng;
                
                // 1. Buat Marker Penanda SEMENTARA
                const locationMarker = L.circleMarker(latlng, {
                    radius: 10,
                    color: '#FFF',
                    weight: 2,
                    fillColor: '#00BFFF', 
                    fillOpacity: 1
                }).addTo(this.map);

                // 2. Tambahkan Circle Akurasi
                const accuracyCircle = L.circle(latlng, e.accuracy, {
                    color: '#00BFFF',
                    fillColor: '#00BFFF',
                    fillOpacity: 0.1,
                    weight: 1,
                    interactive : false
                }).addTo(this.map);

                // 3. Simpan referensi layer group ke state
                this.currentLocationMarker = L.layerGroup([locationMarker, accuracyCircle]);
                
                locationMarker.bindPopup(`Anda di sini (Akurasi: ${Math.round(e.accuracy)} meter)`).openPopup();
                
                // 4. Hapus penanda secara otomatis setelah 5 detik (Memberi efek sementara)
                setTimeout(() => {
                    if (this.currentLocationMarker) {
                        this.map.removeLayer(this.currentLocationMarker);
                        this.currentLocationMarker = null;
                    }
                }, 5000);

            })
            .on('locationerror', (e) => {
                this.loading = false;
                let errorMessage = 'Gagal mendapatkan lokasi GPS.';
                
                if (e.code === 1) {
                    errorMessage = 'Akses lokasi ditolak oleh browser. Mohon izinkan akses GPS.';
                } else if (e.code === 2) {
                    errorMessage = 'Lokasi tidak tersedia atau sinyal lemah.';
                } else if (e.code === 3) {
                    errorMessage = 'Timeout mencari lokasi. Coba lagi.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Lokasi Gagal',
                    text: errorMessage
                });
            });
        },
        // ---------------- END FITUR BARU ----------------

        // ---------------- LOGIC DATA (SERVER SIDE) ----------------
        loadData() {
            this.loading = true;
            
            // [ENDPOINT KADIS] Mengambil Semua Aktivitas (Difilter di Controller berdasarkan unit_kerja_id)
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
            .catch(err => console.error("Gagal memuat data:", err))
            .finally(() => { this.loading = false; });
        },

        loadMarkers(data) {
            this.markersLayer.clearLayers();
            if (data.length === 0) return;
            
            // [BARU] 2. Hapus marker lokasi pengguna (jika ada) saat data aktivitas baru dimuat
            if (this.currentLocationMarker) {
                this.map.removeLayer(this.currentLocationMarker);
                this.currentLocationMarker = null;
            }

            const latlngs = [];
            
            // ... (Logika loadMarkers lainnya tidak diubah) ...
            data.forEach(act => {
                if (!act.lat || !act.lng) return;

                // Logic Warna Marker
                let color = '#f59e0b'; // Kuning (Waiting)
                let statusLabel = 'Menunggu';
                
                if (act.status === 'approved') {
                    color = '#22c55e'; // Hijau
                    statusLabel = 'Disetujui';
                } else if (act.status === 'rejected') {
                    color = '#ef4444'; // Merah
                    statusLabel = 'Ditolak';
                }

                // Popup Map (Hanya tombol Detail)
                const actionButton = `
                    <div style="margin-top:12px; padding-top:8px; border-top:1px dashed #e2e8f0; text-align:right;">
                        <button onclick="window.openActivityDetail(${act.id})"
                            style="background:#0E7A4A; color:white; border:none; padding:6px 14px; font-size:11px; border-radius:6px; cursor:pointer; font-weight:600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            Lihat Detail
                        </button>
                    </div>
                `;

                const popupContent = `
                    <div style="padding: 5px; min-width: 240px;">
                        <strong style="color:#1C7C54; display:block; margin-bottom:4px; font-size:13px;">${act.kegiatan}</strong>
                        <div style="font-size:11px; color:#64748b; margin-bottom:8px;">
                            👤 <b>${act.user}</b> <br>
                            📅 ${act.tanggal} • ⏰ ${act.waktu}
                        </div>
                        <span style="font-size:10px; font-weight:600; color:${color}; background:${color}20; padding:2px 8px; border-radius:10px; border:1px solid ${color}40;">
                            ${statusLabel}
                        </span>
                        ${actionButton}
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

            if (latlngs.length > 0 && !sessionStorage.getItem('kadis_map_lat')) {
                this.map.fitBounds(latlngs, { padding: [50, 50] });
            }
        },

        // ---------------- ACTION HANDLERS (VALIDASI & EXPORT) ----------------
        // ... (Fungsi sendValidation, confirmApprove, handleReject, dll. tidak diubah)
        async sendValidation(id, status, reason) {
            // ... (Logic sendValidation tidak diubah) ...
            // A. Simpan Posisi Peta (Key khusus Kadis)
            const center = this.map.getCenter();
            sessionStorage.setItem('kadis_map_lat', center.lat);
            sessionStorage.setItem('kadis_map_lng', center.lng);
            sessionStorage.setItem('kadis_map_zoom', this.map.getZoom());

            // B. Loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });

            const payload = { 
                status: status, 
                komentar_validasi: reason || "", // Ensure string
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            try {
                // [ENDPOINT KADIS]
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

                if (!response.ok) {
                    throw new Error(data.message || JSON.stringify(data.errors) || 'Gagal memproses validasi.');
                }

                // D. Sukses
                await Swal.fire({
                    icon: 'success',
                    title: status === 'approved' ? 'Disetujui!' : 'Ditolak!',
                    text: 'Data berhasil diperbarui. Halaman akan dimuat ulang.',
                    timer: 1500,
                    showConfirmButton: false
                });

                // E. Reload
                window.location.reload();

            } catch (error) {
                console.error("Validation Error:", error);
                
                let errorMsg = error.message;
                try {
                    const parsedObj = JSON.parse(error.message);
                    if(parsedObj.komentar_validasi) errorMsg = parsedObj.komentar_validasi[0];
                } catch(e) {}

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memproses',
                    text: errorMsg || 'Terjadi kesalahan pada sistem.'
                });
            }
        },

        confirmApprove(id) {
            // ... (Logic confirmApprove tidak diubah) ...
            this.closeModal();

            Swal.fire({
                title: 'Setujui Laporan?',
                input: 'textarea',
                inputLabel: 'Catatan Validasi (Opsional)',
                inputPlaceholder: 'Berikan catatan untuk staf (boleh dikosongkan)...',
                inputAttributes: { 'aria-label': 'Catatan validasi' },
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const reason = result.value || ''; 
                    this.sendValidation(id, 'approved', reason);
                } else {
                    this.openModal(id);
                }
            });
        },

        handleReject(id) {
            // ... (Logic handleReject tidak diubah) ...
            this.closeModal();

            Swal.fire({
                title: 'Tolak Laporan',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan (Wajib)',
                inputPlaceholder: 'Tuliskan alasan penolakan disini...',
                inputAttributes: { 'aria-label': 'Alasan penolakan' },
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Kirim Penolakan',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) return 'Alasan penolakan wajib diisi!'
                }
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
                    const btn = document.getElementById(id + '_btn');
                    if (el && btn) btn.addEventListener('click', () => el.showPicker ? el.showPicker() : el.focus());
                });
            });
        },
        
        exportMap() {

            // Logika export diganti dengan panggilan GET request ke Headless Renderer.

            Swal.fire({
                title: "Export Peta Aktivitas?",
                text: "Laporan akan dibuat di server berdasarkan filter tanggal yang dipilih.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#1C7C54",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Proses Export",
                showLoaderOnConfirm: true, 
                preConfirm: () => {
                    // 1. Ambil Filter Tanggal
                    const fromDate = this.filter.from || '';
                    const toDate = this.filter.to || '';

                    // 2. Bangun URL ke endpoint PDF di server (Menggunakan GET Request)
                    let url = `/preview-map-pdf?from_date=${fromDate}&to_date=${toDate}`;

                    // 3. Buka URL ini di tab baru
                    window.open(url, "_blank");

                    // Langsung resolusi SweetAlert karena proses telah dipindahkan ke tab baru
                    return true; 
                }
            });
        },
    }
}

// Global Registration
window.kadisMapData = kadisMapData;