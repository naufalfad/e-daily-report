/**
 * @file resources/js/components/map-input.js
 * @description Modul kontroler untuk Unified Map Interface (Fullscreen Mode).
 * Updates: Penambahan Fitur "Local Search Fallback" (Pencarian Wilayah Internal).
 *
 * @author Senior Software Architect
 */

export default class MapInput {
    constructor(config) {
        // 1. Validasi Config
        if (!config || !config.modalId || !config.mapContainerId) {
            throw new Error("MapInput: Config object must contain modalId and mapContainerId.");
        }

        this.config = config;
        this.map = null;
        this.marker = null; // Virtual marker (center screen)
        this.isInitialized = false;
        
        // State Management
        this.historyStack = []; 
        this.isUndoAction = false;
        this.debounceTimer = null;
        this.currentLocationData = null;
        this.currentLayer = 'roadmap'; // Default layer
        
        // Cache Key untuk LocalStorage
        this.CACHE_KEY_LAT = 'lkh_last_lat';
        this.CACHE_KEY_LNG = 'lkh_last_lng';

        // Configuration Specific for Timika (Bisa dipindah ke env/meta tag nanti)
        // Kode 9412 adalah Kode Wilayah Kab. Mimika (Papua Tengah)
        this.DEFAULT_KABUPATEN_ID = config.kabupatenId || '9412'; 

        // 2. Cache DOM Elements
        this.dom = {
            // Modal & Map
            modal: document.getElementById(config.modalId),
            mapContainer: document.getElementById(config.mapContainerId),
            
            // Triggers & Buttons
            btnOpen: document.getElementById('btnOpenMap'),
            btnClose: document.getElementById('btnCloseMap'),
            btnConfirm: document.getElementById('btnConfirmLocation'),
            btnLocateMe: document.getElementById('btnLocateMe'), 
            
            // Floating Controls
            searchInput: document.getElementById('mapSearchInput'),
            searchResults: document.getElementById('mapSearchResults'),
            btnLayerSatellite: document.getElementById('btnLayerSatellite'),
            btnUndo: document.getElementById('btnUndoMap'),
            
            // Inputs Form Utama (Target Pengisian Data)
            inputLat: document.getElementById('input_lat'),
            inputLng: document.getElementById('input_lng'),
            inputAddress: document.getElementById('input_lokasi_teks'),
            inputAddressAuto: document.getElementById('input_address_auto'),
            inputProvider: document.getElementById('input_provider'),
            previewInput: document.getElementById('preview_lokasi'),
            
            // UI Preview di dalam Modal
            modalAddressPreview: document.getElementById('mapAddressPreview'),
            modalCoordsPreview: document.getElementById('mapCoordsPreview'),
        };

        // Bind methods agar 'this' scope aman
        this.init = this.init.bind(this);
        this.openMap = this.openMap.bind(this);
        this.closeMap = this.closeMap.bind(this);
        this.handleSearchInput = this.handleSearchInput.bind(this);
        this.handleMapMove = this.handleMapMove.bind(this);
        this.confirmLocation = this.confirmLocation.bind(this);
        this.toggleLayer = this.toggleLayer.bind(this);
        this.handleUndo = this.handleUndo.bind(this);
        this.handleLocateMe = this.handleLocateMe.bind(this);

        // Jalankan inisialisasi listener
        this.init();
    }

    /**
     * Inisialisasi Event Listener pada DOM Elements
     */
    init() {
        if (this.dom.btnOpen) {
            this.dom.btnOpen.addEventListener('click', this.openMap);
        }
        if (this.dom.btnClose) {
            this.dom.btnClose.addEventListener('click', this.closeMap);
        }
        if (this.dom.btnConfirm) {
            this.dom.btnConfirm.addEventListener('click', this.confirmLocation);
        }
        
        // Search Input dengan Debounce (Mencegah spam request)
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.handleSearchInput(e.target.value), 500);
            });
        }

        // Layer Control (Satelit/Jalan)
        if (this.dom.btnLayerSatellite) {
            this.dom.btnLayerSatellite.addEventListener('click', this.toggleLayer);
        }

        // Undo Control
        if (this.dom.btnUndo) {
            this.dom.btnUndo.addEventListener('click', this.handleUndo);
        }

        // Locate Me (GPS) Control
        if (this.dom.btnLocateMe) {
            this.dom.btnLocateMe.addEventListener('click', this.handleLocateMe);
        }
    }

    /**
     * Membuka Modal dan Inisialisasi Peta (Lazy Load)
     */
    openMap() {
        this.dom.modal.classList.remove('hidden');
        this.dom.modal.classList.add('flex');
        
        // Inisialisasi Leaflet hanya sekali
        if (!this.isInitialized) {
            this.initLeaflet();
            this.isInitialized = true;
        } else {
            // Refresh ukuran peta agar tile tidak error/abu-abu
            setTimeout(() => {
                this.map.invalidateSize();
            }, 200);
        }

        // Reset kolom pencarian
        if(this.dom.searchInput) this.dom.searchInput.value = '';
        if(this.dom.searchResults) {
            this.dom.searchResults.innerHTML = '';
            this.dom.searchResults.classList.add('hidden');
        }

        // Tentukan Lokasi Awal Peta
        this.determineInitialLocation();
    }

    /**
     * Menutup Modal
     */
    closeMap() {
        this.dom.modal.classList.add('hidden');
        this.dom.modal.classList.remove('flex');
    }

    /**
     * Setup Leaflet JS & Tile Layers
     */
    initLeaflet() {
        // Default ke Mimika jika tidak ada koordinat lain
        const defaultLat = -4.546759;
        const defaultLng = 136.883713;

        // Inisialisasi Map Object
        this.map = L.map(this.config.mapContainerId, {
            center: [defaultLat, defaultLng],
            zoom: 13,
            zoomControl: false // Kita pakai tombol zoom posisi custom
        });

        // Pindahkan Zoom Control ke pojok kanan bawah agar rapi
        L.control.zoom({ position: 'bottomright' }).addTo(this.map);

        // Setup Google Maps Tile Layers
        this.layers = {
            roadmap: L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: 'Google Maps'
            }),
            satellite: L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: 'Google Satellite'
            })
        };

        // Add default layer
        this.layers.roadmap.addTo(this.map);

        // [NEW FEATURE] Inject Local Search Control (Distrik/Kampung)
        this.addLocalSearchControl();

        // Event Listener saat peta selesai digeser (Drag End)
        this.map.on('moveend', () => {
            if (this.isUndoAction) {
                this.isUndoAction = false;
                return;
            }
            this.handleMapMove();
        });
    }

    /**
     * [NEW METHOD] Membuat Kontrol Pencarian Wilayah (Distrik -> Kelurahan)
     * Menggunakan Data Internal Database
     */
    addLocalSearchControl() {
        const self = this;

        const WilayahControl = L.Control.extend({
            onAdd: function(map) {
                // Container Utama
                const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                container.style.backgroundColor = 'white';
                container.style.padding = '5px';
                container.style.borderRadius = '8px';
                container.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
                container.style.minWidth = '40px';
                container.style.minHeight = '40px';

                // UI Structure: Toggle Button & Dropdown Panel
                container.innerHTML = `
                    <div id="wilayah-toggle-btn" class="cursor-pointer flex items-center justify-center w-8 h-8 hover:bg-gray-100 rounded" title="Cari Wilayah (Distrik/Kampung)">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div id="wilayah-panel" class="hidden mt-2 p-2 w-64">
                        <div class="text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Wilayah Kerja (Timika)</div>
                        
                        <div class="mb-2">
                            <select id="select-kecamatan" class="w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 p-1">
                                <option value="">-- Pilih Distrik --</option>
                                <option value="loading" disabled>Memuat data...</option>
                            </select>
                        </div>

                        <div class="mb-1">
                            <select id="select-kelurahan" class="w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 p-1" disabled>
                                <option value="">-- Pilih Kampung/Kel --</option>
                            </select>
                        </div>
                    </div>
                `;

                // Stop propagation agar klik di dropdown tidak menggeser peta
                L.DomEvent.disableClickPropagation(container);

                return container;
            },

            onRemove: function(map) {
                // Clean up logic if needed
            }
        });

        // Add Control to Map (Top Right)
        this.map.addControl(new WilayahControl({ position: 'topright' }));

        // --- Logic Handler ---
        const btnToggle = document.getElementById('wilayah-toggle-btn');
        const panel = document.getElementById('wilayah-panel');
        const selectKecamatan = document.getElementById('select-kecamatan');
        const selectKelurahan = document.getElementById('select-kelurahan');

        // Toggle Visibility
        btnToggle.addEventListener('click', () => {
            panel.classList.toggle('hidden');
            // Load Data Kecamatan on first open if empty
            if (!panel.classList.contains('hidden') && selectKecamatan.options.length <= 2) {
                this.loadKecamatan(selectKecamatan);
            }
        });

        // Handle Change Kecamatan
        selectKecamatan.addEventListener('change', (e) => {
            const kecId = e.target.value;
            if (kecId) {
                this.loadKelurahan(kecId, selectKelurahan);
            } else {
                selectKelurahan.innerHTML = '<option value="">-- Pilih Kampung/Kel --</option>';
                selectKelurahan.disabled = true;
            }
        });

        // Handle Change Kelurahan (THE MAIN FEATURE)
        selectKelurahan.addEventListener('change', (e) => {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const lat = parseFloat(selectedOption.getAttribute('data-lat'));
            const lng = parseFloat(selectedOption.getAttribute('data-lng'));

            if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                // Action: Fly To Coordinates
                self.map.flyTo([lat, lng], 16, {
                    animate: true,
                    duration: 1.5
                });
                self.updateMarker(lat, lng);
                
                // Set provider meta
                if(self.dom.inputProvider) self.dom.inputProvider.value = 'internal_db_search';
                
                // Optional: Auto close panel on mobile/small screen
                if (window.innerWidth < 640) panel.classList.add('hidden');
                
            } else {
                alert("Maaf, Data Koordinat untuk Kampung/Kelurahan ini belum tersedia di database. Silakan geser pin secara manual.");
            }
        });
    }

    /**
     * Fetch API Data Kecamatan
     */
    async loadKecamatan(selectElement) {
        try {
            // Gunakan Default Kabupaten ID (Mimika)
            const response = await fetch(`/wilayah/kecamatan?kabupaten_id=${this.DEFAULT_KABUPATEN_ID}`);
            const data = await response.json();

            // Reset Options
            selectElement.innerHTML = '<option value="">-- Pilih Distrik --</option>';
            
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.nama;
                selectElement.appendChild(option);
            });
        } catch (error) {
            console.error("Gagal memuat kecamatan:", error);
            selectElement.innerHTML = '<option value="">Gagal memuat data</option>';
        }
    }

    /**
     * Fetch API Data Kelurahan + Coordinates
     */
    async loadKelurahan(kecamatanId, selectElement) {
        selectElement.disabled = true;
        selectElement.innerHTML = '<option>Memuat...</option>';

        try {
            const response = await fetch(`/wilayah/kelurahan?kecamatan_id=${kecamatanId}`);
            const data = await response.json();

            selectElement.innerHTML = '<option value="">-- Pilih Kampung/Kel --</option>';
            
            if (data.length === 0) {
                selectElement.innerHTML = '<option value="">Data Kosong</option>';
            }

            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.nama;
                
                // Inject Coordinates ke Attribute Option
                if (item.latitude && item.longitude) {
                    option.setAttribute('data-lat', item.latitude);
                    option.setAttribute('data-lng', item.longitude);
                } else {
                    option.text += ' (No Coord)'; // Visual cue
                }
                
                selectElement.appendChild(option);
            });
            selectElement.disabled = false;
        } catch (error) {
            console.error("Gagal memuat kelurahan:", error);
            selectElement.innerHTML = '<option value="">Gagal memuat data</option>';
        }
    }

    /**
     * Logika Penentuan Lokasi Awal (Priority System)
     */
    determineInitialLocation() {
        const editLat = this.dom.inputLat.value;
        const editLng = this.dom.inputLng.value;
        const cachedLat = localStorage.getItem(this.CACHE_KEY_LAT);
        const cachedLng = localStorage.getItem(this.CACHE_KEY_LNG);

        if (editLat && editLng) {
            // Prioritas 1: Data Edit (dari database)
            this.updateMarker(parseFloat(editLat), parseFloat(editLng));
            this.map.setView([editLat, editLng], 18);
        } else if (cachedLat && cachedLng) {
            // Prioritas 2: Cache Terakhir (LocalStorage)
            this.updateMarker(parseFloat(cachedLat), parseFloat(cachedLng));
            this.map.setView([cachedLat, cachedLng], 15);
        } else {
            // Prioritas 3: GPS Browser (Fresh)
            this.handleLocateMe(); 
        }
    }

    /**
     * Handle Tombol Locate Me (GPS)
     */
    handleLocateMe() {
        if (!navigator.geolocation) {
            alert("Browser Anda tidak mendukung Geolocation.");
            return;
        }

        // Visual Feedback: Ubah icon jadi spinner loading
        const originalIcon = this.dom.btnLocateMe.innerHTML;
        this.dom.btnLocateMe.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
        this.dom.btnLocateMe.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                // Update Peta: Terbang ke lokasi
                this.updateMarker(lat, lng);
                this.map.flyTo([lat, lng], 18, {
                    animate: true,
                    duration: 1.5
                });

                // Update Provider Metadata menjadi 'gps_device'
                if(this.dom.inputProvider) this.dom.inputProvider.value = 'gps_device';

                // Kembalikan Icon Normal
                this.dom.btnLocateMe.innerHTML = originalIcon;
                this.dom.btnLocateMe.disabled = false;
                
                console.log(`GPS Locked: ${lat}, ${lng} (Akurasi: ${accuracy}m)`);
            },
            (error) => {
                console.error("Error Geolocation:", error);
                
                let msg = "Gagal mengambil lokasi.";
                if(error.code === 1) msg = "Izin lokasi ditolak. Mohon aktifkan izin lokasi di browser.";
                else if(error.code === 2) msg = "Sinyal GPS tidak tersedia.";
                else if(error.code === 3) msg = "Waktu permintaan habis (timeout).";

                alert(msg);

                // Kembalikan Icon Normal
                this.dom.btnLocateMe.innerHTML = originalIcon;
                this.dom.btnLocateMe.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    /**
     * Update State saat peta digeser (Center Pivot Logic)
     */
    async handleMapMove() {
        const center = this.map.getCenter();
        const lat = center.lat;
        const lng = center.lng;

        // Push ke history stack untuk fitur Undo
        this.historyStack.push({ lat, lng });
        if (this.historyStack.length > 10) this.historyStack.shift(); 
        
        // Aktifkan tombol Undo jika ada history
        if(this.dom.btnUndo) this.dom.btnUndo.disabled = this.historyStack.length <= 1;

        // Update UI Preview Coordinates
        if(this.dom.modalCoordsPreview) this.dom.modalCoordsPreview.innerText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        if(this.dom.modalAddressPreview) this.dom.modalAddressPreview.innerText = "Memuat alamat...";

        // Reverse Geocoding (Nominatim OpenStreetMap)
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {
                headers: { 'User-Agent': 'E-Daily-Report-App' }
            });
            const data = await response.json();
            
            // Coba ambil nama jalan atau nama gedung
            const shortAddress = data.name || (data.address ? (data.address.road || data.address.village || data.address.suburb) : "Lokasi Terpilih");
            const fullAddress = data.display_name;

            // Simpan ke state sementara
            this.currentLocationData = {
                lat, lng, shortAddress, fullAddress
            };

            // Update UI Preview
            if(this.dom.modalAddressPreview) this.dom.modalAddressPreview.innerText = shortAddress;

        } catch (e) {
            console.error("Geocoding error", e);
            this.currentLocationData = {
                lat, lng, shortAddress: "Lokasi Terpilih", fullAddress: ""
            };
            if(this.dom.modalAddressPreview) this.dom.modalAddressPreview.innerText = "Gagal memuat nama jalan";
        }
    }

    /**
     * Memindahkan Marker Visual (Sebenarnya menggeser peta ke tengah)
     */
    updateMarker(lat, lng) {
        this.map.setView([lat, lng], this.map.getZoom());
        // Trigger manual untuk load alamat karena setView tidak selalu trigger moveend
        this.handleMapMove();
    }

    /**
     * Logic Pencarian Lokasi (Nominatim Search)
     */
    async handleSearchInput(query) {
        if (query.length < 3) return;

        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=5`, {
                headers: { 'User-Agent': 'E-Daily-Report-App' }
            });
            const results = await response.json();

            this.dom.searchResults.innerHTML = '';
            this.dom.searchResults.classList.remove('hidden');

            if (results.length === 0) {
                this.dom.searchResults.innerHTML = '<div class="p-3 text-sm text-slate-500 text-center">Tidak ditemukan</div>';
                return;
            }

            results.forEach(place => {
                const item = document.createElement('div');
                item.className = 'p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 text-sm text-slate-700';
                item.innerText = place.display_name;
                item.onclick = () => {
                    const lat = parseFloat(place.lat);
                    const lng = parseFloat(place.lon);
                    
                    this.map.flyTo([lat, lng], 17);
                    this.updateMarker(lat, lng); 
                    
                    // Sembunyikan search result
                    this.dom.searchResults.classList.add('hidden');
                    // Set provider jadi manual search
                    if(this.dom.inputProvider) this.dom.inputProvider.value = 'manual_search'; 
                };
                this.dom.searchResults.appendChild(item);
            });

        } catch (e) {
            console.error("Search error", e);
        }
    }

    /**
     * Mengganti Layer Peta (Roadmap <-> Satellite)
     */
    toggleLayer() {
        if (this.currentLayer === 'roadmap') {
            this.map.removeLayer(this.layers.roadmap);
            this.layers.satellite.addTo(this.map);
            this.currentLayer = 'satellite';
        } else {
            this.map.removeLayer(this.layers.satellite);
            this.layers.roadmap.addTo(this.map);
            this.currentLayer = 'roadmap';
        }
    }

    /**
     * Fitur Undo Pergerakan
     */
    handleUndo() {
        if (this.historyStack.length > 1) {
            this.historyStack.pop(); // Buang posisi sekarang
            const prev = this.historyStack[this.historyStack.length - 1]; // Ambil sebelumnya
            
            this.isUndoAction = true; // Flag agar tidak trigger moveend berlebih
            this.map.flyTo([prev.lat, prev.lng], this.map.getZoom());
            this.updateMarker(prev.lat, prev.lng);
        }
    }

    /**
     * Konfirmasi Pilihan Lokasi
     */
    confirmLocation() {
        if (!this.currentLocationData) {
            // Fallback jika user langsung confirm tanpa nunggu geocoding
            const c = this.map.getCenter();
            this.currentLocationData = {
                lat: c.lat,
                lng: c.lng,
                shortAddress: `${c.lat.toFixed(5)}, ${c.lng.toFixed(5)}`,
                fullAddress: ""
            };
        }

        const data = this.currentLocationData;

        // 1. Isi Hidden Inputs Form Utama
        this.dom.inputLat.value = data.lat;
        this.dom.inputLng.value = data.lng;
        this.dom.inputAddress.value = data.shortAddress; // Nama jalan/tempat singkat
        this.dom.inputAddressAuto.value = data.fullAddress; // Alamat lengkap
        
        // Logic Provider: Jika belum diset GPS/Search, anggap manual pin
        if (this.dom.inputProvider && this.dom.inputProvider.value === '') {
             this.dom.inputProvider.value = 'manual_pin';
        }

        // 2. Update Visual Preview Input di Form Utama
        this.dom.previewInput.value = data.fullAddress || data.shortAddress;
        
        // 3. SIMPAN KE CACHE (Untuk UX kunjungan berikutnya)
        localStorage.setItem(this.CACHE_KEY_LAT, data.lat);
        localStorage.setItem(this.CACHE_KEY_LNG, data.lng);
        
        // 4. Tutup Modal
        this.closeMap();
    }
}

// Inisialisasi Otomatis (Helper Global untuk dipanggil di Blade)
window.initMapComponent = function() {
    try {
        window.mapInputInstance = new MapInput({
            modalId: 'fullscreenMapModal',
            mapContainerId: 'map_fullscreen'
        });
        console.log("Map Component Initialized Successfully");
    } catch (e) {
        console.error("Map Init Failed:", e);
    }
};