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

        // State Modal Detail
        showModal: false,
        selectedActivity: null,

        // State Modal Reject (Legacy state, actually handled by Swal now)
        showRejectModal: false,
        rejectReason: '',
        selectedIdForAction: null,

        loading: false,

        initMap() {
            this.$nextTick(() => {
                // -------------------------------------------------------------
                // 1. LOGIKA RESTORE ZOOM & POSISI (Agar tidak reset saat reload)
                // -------------------------------------------------------------
                const savedLat = sessionStorage.getItem('map_lat');
                const savedLng = sessionStorage.getItem('map_lng');
                const savedZoom = sessionStorage.getItem('map_zoom');

                const initialLat = savedLat ? parseFloat(savedLat) : -4.5467;
                const initialLng = savedLng ? parseFloat(savedLng) : 136.8833;
                const initialZoom = savedZoom ? parseInt(savedZoom) : 13;

                // Bersihkan storage
                sessionStorage.removeItem('map_lat');
                sessionStorage.removeItem('map_lng');
                sessionStorage.removeItem('map_zoom');

                // Init Map
                this.map = L.map('map', { zoomControl: true }).setView([initialLat, initialLng], initialZoom);

                // Tile Layers
                L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", { 
                    attribution: "Google Maps", 
                    maxZoom: 20 
                }).addTo(this.map);

                this.markersLayer = L.layerGroup().addTo(this.map);

                this.loadData();
                this.initDatePickers();

                new ResizeObserver(() => this.map.invalidateSize()).observe(document.getElementById('map'));

                // Register Global Functions
                window.openActivityDetail = (id) => this.openModal(id);
                window.approveActivity = (id) => this.confirmApprove(id);
                window.rejectActivity = (id) => this.handleReject(id);
            });
        },

        // ---------------- LOGIC DATA (SERVER SIDE) ----------------
        loadData() {
            this.loading = true;
            
            // Endpoint Khusus Penilai (Bawahan)
            let url = '/api/staf-aktivitas'; 
            
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

            const latlngs = [];

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

                // Button "Lihat Detail" di Popup (Aksi Validasi ada di dalam Modal)
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

            if (latlngs.length > 0 && !sessionStorage.getItem('map_lat')) {
                this.map.fitBounds(latlngs, { padding: [50, 50] });
            }
        },

        // ---------------- ACTION HANDLERS (SWEETALERT) ----------------
        
        // 1. APPROVE (Updated: Now with Input Field)
        confirmApprove(id) {
            this.closeModal(); // Tutup modal detail agar fokus ke alert

            Swal.fire({
                title: 'Setujui Laporan?',
                input: 'textarea', // [BARU] Tambah input textarea
                inputLabel: 'Catatan Validasi (Opsional)',
                inputPlaceholder: 'Berikan catatan untuk staf (boleh dikosongkan)...',
                inputAttributes: {
                    'aria-label': 'Catatan validasi'
                },
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // [PENTING] Kirim string kosong '' jika user tidak mengisi, jangan null
                    const reason = result.value || ''; 
                    this.sendValidation(id, 'approved', reason);
                } else {
                    // Opsional: Buka kembali modal detail jika dibatalkan
                    this.openModal(id);
                }
            });
        },

        // 2. REJECT (Wajib ada alasan)
        handleReject(id) {
            this.closeModal(); // Tutup modal detail

            Swal.fire({
                title: 'Tolak Laporan',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan (Wajib)',
                inputPlaceholder: 'Tuliskan alasan penolakan disini...',
                inputAttributes: {
                    'aria-label': 'Tuliskan alasan penolakan disini'
                },
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Kirim Penolakan',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan wajib diisi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.sendValidation(id, 'rejected', result.value);
                } else {
                    this.openModal(id);
                }
            });

            // Auto Zoom ke area marker jika ada data
            if (latlngs.length > 0) {
                this.map.fitBounds(latlngs, { padding: [50, 50] });
            }
        },

        // 3. CORE VALIDATION (Strict Mode: Async/Await)
        async sendValidation(id, status, reason) {
            // A. Simpan Posisi Peta
            const center = this.map.getCenter();
            sessionStorage.setItem('map_lat', center.lat);
            sessionStorage.setItem('map_lng', center.lng);
            sessionStorage.setItem('map_zoom', this.map.getZoom());

            // B. Loading UI
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });

            // [PENTING] Pastikan komentar_validasi adalah STRING (bukan null)
            const payload = { 
                status: status, 
                komentar_validasi: reason || "", // Force string empty jika undefined/null
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            try {
                // C. Fetch Request
                const response = await fetch(`/penilai/validasi-laporan/${id}`, {
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

                // Cek Status Code HTTP (Harus 200-299)
                if (!response.ok) {
                    // Jika validasi gagal (misal field null), lempar error
                    throw new Error(data.message || JSON.stringify(data.errors) || 'Gagal memproses validasi.');
                }

                // D. Jika Sukses -> Tampilkan Pesan & Reload
                await Swal.fire({
                    icon: 'success',
                    title: status === 'approved' ? 'Disetujui!' : 'Ditolak!',
                    text: 'Data berhasil diperbarui. Halaman akan dimuat ulang.',
                    timer: 1500,
                    showConfirmButton: false
                });

                // E. Reload Halaman
                window.location.reload();

            } catch (error) {
                console.error("Validation Error:", error);
                
                // Tampilkan Error Asli dari Server di SweetAlert
                let errorMsg = error.message;
                // Jika error berupa object JSON string (dari Laravel Validation), parse sedikit biar rapi
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
        
        // ---------------- MODAL DETAIL & UTILS ----------------
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

window.penilaiMapData = penilaiMapData;