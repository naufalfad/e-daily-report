/**
 * @file resources/js/components/map-input.js
 * @description Modul kontroler untuk Unified Map Interface (Fullscreen Mode).
 * Termasuk: Caching Logic, Red Pin, Inline SVG Controls.
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
            
            // Floating Controls
            searchInput: document.getElementById('mapSearchInput'),
            searchResults: document.getElementById('mapSearchResults'),
            btnUndo: document.getElementById('btnUndoMap'),
            btnLayerSat: document.getElementById('btnLayerSatellite'),
            
            // Info Display
            addressPreview: document.getElementById('mapAddressPreview'),
            coordsPreview: document.getElementById('mapCoordsPreview'),
            
            // Main Form Inputs (Hidden/Readonly)
            inputLat: document.getElementById('input_lat'),
            inputLng: document.getElementById('input_lng'),
            inputAddress: document.getElementById('input_lokasi_teks'), // Nama Tempat/Jalan
            inputAddressAuto: document.getElementById('input_address_auto'), // Alamat Lengkap
            inputProvider: document.getElementById('input_provider'),
            previewInput: document.getElementById('preview_lokasi')
        };

        // 3. Bind Events
        this.initEventListeners();
    }

    initEventListeners() {
        // Buka Modal -> Init Map
        if (this.dom.btnOpen) {
            this.dom.btnOpen.addEventListener('click', () => this.openMap());
        }

        // Tutup Modal
        if (this.dom.btnClose) {
            this.dom.btnClose.addEventListener('click', () => this.closeMap());
        }

        // Konfirmasi Lokasi
        if (this.dom.btnConfirm) {
            this.dom.btnConfirm.addEventListener('click', () => this.confirmLocation());
        }

        // Search Input (Debounce & Enter)
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('input', (e) => this.handleSearchInput(e.target.value));
            this.dom.searchInput.addEventListener('keydown', (e) => {
                if(e.key === 'Enter') this.searchLocation(e.target.value);
            });
        }

        // Undo Button
        if (this.dom.btnUndo) {
            this.dom.btnUndo.addEventListener('click', () => this.undoLastMove());
        }

        // Layer Switcher
        if (this.dom.btnLayerSat) {
            this.dom.btnLayerSat.addEventListener('click', () => this.toggleLayer());
        }
    }

    openMap() {
        this.dom.modal.classList.remove('hidden');
        this.dom.modal.classList.add('flex'); // Gunakan flex agar centering content jalan

        if (!this.isInitialized) {
            this.initMapInstance();
        } else {
            this.map.invalidateSize(); // Refresh layout leaflet saat modal visible
            
            // Panggil logika penentuan lokasi pintar
            this.determineInitialLocation();
        }
    }

    closeMap() {
        this.dom.modal.classList.add('hidden');
        this.dom.modal.classList.remove('flex');
    }

    initMapInstance() {
        // Default Jakarta
        const defaultCenter = [-6.200000, 106.816666];

        // 1. Layers Setup
        this.layers = {
            roadmap: L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'], attribution: 'Google Maps'
            }),
            satellite: L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'], attribution: 'Google Satellite'
            })
        };

        // 2. Create Map (Zoom Control di-offkan dulu agar bisa custom posisi nanti jika mau, atau pakai default)
        this.map = L.map(this.config.mapContainerId, {
            center: defaultCenter,
            zoom: 13,
            layers: [this.layers.roadmap], // Default layer
            zoomControl: false // Kita pakai tombol custom/default di posisi lain
        });

        L.control.zoom({ position: 'bottomright' }).addTo(this.map);

        // 3. Event Listeners Core (Fixed Center Pin Logic)
        this.map.on('moveend', () => this.handleMapMoveEnd());
        this.map.on('movestart', () => {
            this.dom.addressPreview.textContent = "Menggeser peta...";
            this.dom.addressPreview.classList.add('text-slate-400');
        });

        this.isInitialized = true;
        this.currentLayer = 'roadmap';

        // Panggil penentuan lokasi awal
        this.determineInitialLocation();
    }

    /**
     * SMART LOCATION LOGIC
     * 1. Cek apakah sedang Edit (ada value di hidden input).
     * 2. Jika tidak, cek LocalStorage (Cache).
     * 3. Jika tidak, minta GPS.
     */
    determineInitialLocation() {
        const editLat = parseFloat(this.dom.inputLat.value);
        const editLng = parseFloat(this.dom.inputLng.value);
        
        const cachedLat = parseFloat(localStorage.getItem(this.CACHE_KEY_LAT));
        const cachedLng = parseFloat(localStorage.getItem(this.CACHE_KEY_LNG));

        if (!isNaN(editLat) && !isNaN(editLng) && editLat !== 0) {
            // Priority 1: Edit Mode
            this.map.setView([editLat, editLng], 18);
            this.fetchAddress(editLat, editLng); // Refresh alamat
        } else if (!isNaN(cachedLat) && !isNaN(cachedLng)) {
            // Priority 2: Cache / History
            this.map.setView([cachedLat, cachedLng], 17);
            this.fetchAddress(cachedLat, cachedLng);
            console.log("Restored location from cache");
        } else {
            // Priority 3: Fresh GPS
            this.locateUser();
        }
    }

    // --- Core Logic: Map Movement & Reverse Geocoding ---

    handleMapMoveEnd() {
        const center = this.map.getCenter();
        const lat = center.lat;
        const lng = center.lng;

        // Update UI Koordinat
        this.dom.coordsPreview.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

        // Push to History (Only if not undo action)
        if (!this.isUndoAction) {
            this.addToHistory({ lat, lng, zoom: this.map.getZoom() });
        }
        this.isUndoAction = false; // Reset flag

        // Trigger Reverse Geocoding (Debounced 800ms)
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.fetchAddress(lat, lng);
        }, 800);
    }

    addToHistory(state) {
        // Batasi stack history maks 10 langkah
        if (this.historyStack.length >= 10) {
            this.historyStack.shift(); // Hapus yang terlama
        }
        
        // Jangan simpan duplikat berturut-turut
        const last = this.historyStack[this.historyStack.length - 1];
        // Cegah duplikat stack yg berdekatan (kurang dari 0.00001 derajat)
        if (last && Math.abs(last.lat - state.lat) < 0.00001 && Math.abs(last.lng - state.lng) < 0.00001) return;

        this.historyStack.push(state);
        
        // Enable/Disable Undo Button UI
        this.updateUndoButtonState();
    }

    undoLastMove() {
        if (this.historyStack.length < 2) return; // Butuh min 2 state (current & previous)

        this.historyStack.pop(); // Buang state "sekarang"
        const prevState = this.historyStack[this.historyStack.length - 1]; // Ambil state "sebelumnya"

        if (prevState) {
            this.isUndoAction = true; // Tandai agar tidak masuk history lagi
            this.map.flyTo([prevState.lat, prevState.lng], prevState.zoom);
        }
        
        this.updateUndoButtonState();
    }

    updateUndoButtonState() {
        if(this.dom.btnUndo) {
            if (this.historyStack.length > 1) {
                // Style Active
                this.dom.btnUndo.classList.remove('text-slate-400', 'cursor-not-allowed');
                this.dom.btnUndo.classList.add('text-slate-700', 'hover:text-[#155FA6]');
                this.dom.btnUndo.disabled = false;
            } else {
                // Style Disabled
                this.dom.btnUndo.classList.add('text-slate-400', 'cursor-not-allowed');
                this.dom.btnUndo.classList.remove('text-slate-700', 'hover:text-[#155FA6]');
                this.dom.btnUndo.disabled = true;
            }
        }
    }

    // --- API Interactions ---

    locateUser() {
        if (!navigator.geolocation) {
            alert("Browser tidak mendukung GPS");
            return;
        }

        this.dom.addressPreview.textContent = "Mencari sinyal GPS...";
        
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const { latitude, longitude } = pos.coords;
                this.map.flyTo([latitude, longitude], 18, {
                    animate: true, duration: 1.5
                });
                // Note: moveend will trigger handleMapMoveEnd automatically
            },
            (err) => {
                console.error(err);
                this.dom.addressPreview.textContent = "Gagal mengambil GPS. Silakan geser peta.";
            },
            { enableHighAccuracy: true, timeout: 5000 }
        );
    }

    async fetchAddress(lat, lng) {
        this.dom.addressPreview.classList.remove('text-slate-400');
        this.dom.addressPreview.innerHTML = '<span class="animate-pulse">Mengambil alamat...</span>';

        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
            const res = await fetch(url, { headers: { 'User-Agent': 'EDailyReport/1.0' } });
            
            if (!res.ok) throw new Error("Network error");
            
            const data = await res.json();
            
            // Format Alamat yang user-friendly
            const addr = data.address;
            const street = addr.road || addr.pedestrian || addr.suburb || "";
            const number = addr.house_number ? `No. ${addr.house_number}` : "";
            const city = addr.city || addr.town || addr.county || "";
            
            const shortAddress = [street, number].filter(Boolean).join(" ").trim() || city;
            const fullAddress = data.display_name;

            // Simpan di properti internal sementara
            this.currentLocationData = {
                lat: lat,
                lng: lng,
                shortAddress: shortAddress || "Lokasi Terpilih",
                fullAddress: fullAddress
            };

            // Update UI Preview di Modal
            this.dom.addressPreview.textContent = fullAddress;

        } catch (e) {
            console.error(e);
            this.dom.addressPreview.textContent = "Alamat tidak ditemukan (Koordinat tersimpan)";
            this.currentLocationData = {
                lat: lat,
                lng: lng,
                shortAddress: `${lat.toFixed(5)}, ${lng.toFixed(5)}`,
                fullAddress: ""
            };
        }
    }

    // --- Search Logic ---

    handleSearchInput(query) {
        // Minimal 3 karakter untuk search
        if (query.length < 3) {
            this.dom.searchResults.classList.add('hidden');
            return;
        }

        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => this.searchLocation(query), 600);
    }

    async searchLocation(query) {
        if (!query) return;

        try {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`;
            const res = await fetch(url);
            const data = await res.json();

            this.renderSearchResults(data);
        } catch (e) {
            console.error(e);
        }
    }

    renderSearchResults(results) {
        const container = this.dom.searchResults;
        container.innerHTML = '';
        
        if (results.length === 0) {
            container.classList.add('hidden');
            return;
        }

        results.forEach(item => {
            const div = document.createElement('div');
            div.className = "px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0 text-left";
            div.innerHTML = `
                <p class="text-sm font-medium text-slate-800 truncate">${item.display_name.split(',')[0]}</p>
                <p class="text-xs text-slate-500 truncate">${item.display_name}</p>
            `;
            
            div.addEventListener('click', () => {
                const lat = parseFloat(item.lat);
                const lng = parseFloat(item.lon);
                this.map.flyTo([lat, lng], 18);
                this.dom.searchInput.value = ""; // Clear input
                container.classList.add('hidden');
            });

            container.appendChild(div);
        });

        container.classList.remove('hidden');
    }

    // --- UI Controls ---

    toggleLayer() {
        if (this.currentLayer === 'roadmap') {
            this.map.removeLayer(this.layers.roadmap);
            this.map.addLayer(this.layers.satellite);
            this.currentLayer = 'satellite';
            
            // Visual Feedback: Active State (Updated for SVG logic)
            this.dom.btnLayerSat.classList.add('bg-blue-50', 'border-blue-300');
            const svg = this.dom.btnLayerSat.querySelector('svg');
            if(svg) svg.classList.add('text-blue-600');
        } else {
            this.map.removeLayer(this.layers.satellite);
            this.map.addLayer(this.layers.roadmap);
            this.currentLayer = 'roadmap';
            
            this.dom.btnLayerSat.classList.remove('bg-blue-50', 'border-blue-300');
            const svg = this.dom.btnLayerSat.querySelector('svg');
            if(svg) svg.classList.remove('text-blue-600');
        }
    }

    confirmLocation() {
        if (!this.currentLocationData) {
            // Fallback jika user langsung klik confirm tanpa nunggu geocoding
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
        this.dom.inputProvider.value = 'manual_pin'; // Default karena hasil geser peta

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
        console.log("Map Component Initialized Successfully with Caching");
    } catch (e) {
        console.error("Map Init Failed:", e);
    }
};