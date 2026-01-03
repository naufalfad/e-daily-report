/**
 * @file resources/js/components/map-input.js
 * @description Modul kontroler untuk Unified Map Interface (Fullscreen Mode).
 * Termasuk: Caching Logic, Red Pin, Inline SVG Controls, dan Current Location.
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
        this.marker = null;
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

        // 2. Cache DOM Elements
        this.dom = {
            // Modal & Map
            modal: document.getElementById(config.modalId),
            mapContainer: document.getElementById(config.mapContainerId),
            
            // Triggers
            btnOpen: document.getElementById('btnOpenMap'),
            btnClose: document.getElementById('btnCloseMap'),
            btnConfirm: document.getElementById('btnConfirmLocation'),
            btnLocateMe: document.getElementById('btnLocateMe'), // [BARU] Tombol Locate Me
            
            // Floating Controls
            searchInput: document.getElementById('mapSearchInput'),
            searchResults: document.getElementById('mapSearchResults'),
            btnLayerSatellite: document.getElementById('btnLayerSatellite'),
            btnUndo: document.getElementById('btnUndoMap'),
            
            // Inputs Form Utama (Target Pengisian)
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

        // Bind methods agar 'this' tetap merujuk ke instance class
        this.init = this.init.bind(this);
        this.openMap = this.openMap.bind(this);
        this.closeMap = this.closeMap.bind(this);
        this.handleSearchInput = this.handleSearchInput.bind(this);
        this.handleMapMove = this.handleMapMove.bind(this);
        this.confirmLocation = this.confirmLocation.bind(this);
        this.toggleLayer = this.toggleLayer.bind(this);
        this.handleUndo = this.handleUndo.bind(this);
        this.handleLocateMe = this.handleLocateMe.bind(this); // [BARU]

        // Jalankan inisialisasi listener
        this.init();
    }

    /**
     * Inisialisasi Event Listener
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
        
        // Search Input dengan Debounce
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.handleSearchInput(e.target.value), 500);
            });
        }

        // Layer Control
        if (this.dom.btnLayerSatellite) {
            this.dom.btnLayerSatellite.addEventListener('click', this.toggleLayer);
        }

        // Undo Control
        if (this.dom.btnUndo) {
            this.dom.btnUndo.addEventListener('click', this.handleUndo);
        }

        // [BARU] Locate Me Control
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
        
        if (!this.isInitialized) {
            this.initLeaflet();
            this.isInitialized = true;
        } else {
            // Refresh ukuran peta agar tidak abu-abu
            setTimeout(() => {
                this.map.invalidateSize();
            }, 200);
        }

        // Reset pencarian
        if(this.dom.searchInput) this.dom.searchInput.value = '';
        if(this.dom.searchResults) {
            this.dom.searchResults.innerHTML = '';
            this.dom.searchResults.classList.add('hidden');
        }

        // Tentukan Lokasi Awal
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
     * Setup Leaflet JS
     */
    initLeaflet() {
        // Default ke Mimika jika tidak ada koordinat
        const defaultLat = -4.546759;
        const defaultLng = 136.883713;

        // Inisialisasi Map
        this.map = L.map(this.config.mapContainerId, {
            center: [defaultLat, defaultLng],
            zoom: 13,
            zoomControl: false // Kita pakai tombol custom kalau perlu, atau default di pojok
        });

        // Pindahkan Zoom Control ke pojok kanan bawah agar rapi
        L.control.zoom({ position: 'bottomright' }).addTo(this.map);

        // Setup Tile Layers
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

        this.layers.roadmap.addTo(this.map);

        // Event Listener saat peta digeser selesai
        this.map.on('moveend', () => {
            if (this.isUndoAction) {
                this.isUndoAction = false;
                return;
            }
            this.handleMapMove();
        });
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
            // Prioritas 1: Data Edit
            this.updateMarker(parseFloat(editLat), parseFloat(editLng));
            this.map.setView([editLat, editLng], 18);
        } else if (cachedLat && cachedLng) {
            // Prioritas 2: Cache Terakhir
            this.updateMarker(parseFloat(cachedLat), parseFloat(cachedLng));
            this.map.setView([cachedLat, cachedLng], 15);
        } else {
            // Prioritas 3: GPS Browser
            this.handleLocateMe(); // Gunakan fungsi locate me untuk inisialisasi juga
        }
    }

    /**
     * [BARU] Handle Tombol Locate Me (GPS)
     */
    handleLocateMe() {
        if (!navigator.geolocation) {
            alert("Browser Anda tidak mendukung Geolocation.");
            return;
        }

        // Visual Feedback: Ubah icon jadi spinner
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
                const accuracy = position.coords.accuracy; // Akurasi dalam meter

                // Update Peta
                this.updateMarker(lat, lng);
                this.map.flyTo([lat, lng], 18, {
                    animate: true,
                    duration: 1.5
                });

                // Update Provider Metadata menjadi GPS Device
                if(this.dom.inputProvider) this.dom.inputProvider.value = 'gps_device';

                // Kembalikan Icon
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

                // Kembalikan Icon
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
     * Update State saat peta digeser (Center Pivot)
     */
    async handleMapMove() {
        const center = this.map.getCenter();
        const lat = center.lat;
        const lng = center.lng;

        // Push ke history stack untuk fitur Undo
        this.historyStack.push({ lat, lng });
        if (this.historyStack.length > 10) this.historyStack.shift(); // Limit history
        
        // Aktifkan tombol Undo jika ada history
        if(this.dom.btnUndo) this.dom.btnUndo.disabled = this.historyStack.length <= 1;

        // Update UI Preview Coordinates
        if(this.dom.modalCoordsPreview) this.dom.modalCoordsPreview.innerText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        if(this.dom.modalAddressPreview) this.dom.modalAddressPreview.innerText = "Memuat alamat...";

        // Reverse Geocoding (Nominatim)
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {
                headers: { 'User-Agent': 'E-Daily-Report-App' }
            });
            const data = await response.json();
            
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
     * Memindahkan Marker Visual (Tanpa trigger event move map berlebih)
     */
    updateMarker(lat, lng) {
        // Di mode Fullscreen dengan Center Pivot, marker sebenarnya adalah
        // ikon diam di tengah layar (CSS). Jadi kita cukup setView map-nya.
        this.map.setView([lat, lng], this.map.getZoom());
        
        // Trigger manual untuk load alamat
        this.handleMapMove();
    }

    /**
     * Logic Pencarian Lokasi
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
                    this.updateMarker(lat, lng); // Ini akan trigger moveend -> handleMapMove
                    
                    // Sembunyikan search result
                    this.dom.searchResults.classList.add('hidden');
                    this.dom.inputProvider.value = 'manual_search'; // Update provider metadata
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
        this.dom.inputAddress.value = data.shortAddress; // Nama jalan/tempat
        this.dom.inputAddressAuto.value = data.fullAddress; // Alamat lengkap
        
        // Provider hanya diupdate ke manual_pin jika belum diset oleh GPS/Search
        // atau jika user menggeser-geser peta setelah GPS lock
        if (this.dom.inputProvider.value === '') {
             this.dom.inputProvider.value = 'manual_pin';
        }

        // 2. Update Visual Preview Input di Form Utama
        this.dom.previewInput.value = data.fullAddress || data.shortAddress;
        
        // 3. SAVE TO CACHE (Fitur Baru)
        localStorage.setItem(this.CACHE_KEY_LAT, data.lat);
        localStorage.setItem(this.CACHE_KEY_LNG, data.lng);
        
        // 4. Tutup Modal
        this.closeMap();
    }
}

// Inisialisasi Otomatis (Helper untuk dipanggil di Blade)
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