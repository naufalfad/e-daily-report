/**
 * @file resources/js/components/map-input.js
 * @description Modul kontroler untuk Unified Map Interface (Fullscreen Mode).
 * Updates: UI/UX Seamless, Smooth Animations, & Optimized Control Stack.
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

        // Configuration Specific for Timika
        this.DEFAULT_KABUPATEN_ID = config.kabupatenId || '9412'; 

        // 2. Cache DOM Elements
        this.dom = {
            // Modal & Map
            modal: document.getElementById(config.modalId),
            mapContainer: document.getElementById(config.mapContainerId),
            
            // Triggers & Buttons (External Form)
            btnOpen: document.getElementById('btnOpenMap'),
            btnClose: document.getElementById('btnCloseMap'),
            btnConfirm: document.getElementById('btnConfirmLocation'),
            
            // Floating Controls (Optional/Legacy references)
            searchInput: document.getElementById('mapSearchInput'),
            btnUndo: document.getElementById('btnUndoMap'),
            
            // Inputs Form Utama
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

        // Bind methods
        this.init = this.init.bind(this);
        this.openMap = this.openMap.bind(this);
        this.closeMap = this.closeMap.bind(this);
        this.handleSearchInput = this.handleSearchInput.bind(this);
        this.handleMapMove = this.handleMapMove.bind(this);
        this.confirmLocation = this.confirmLocation.bind(this);
        this.toggleLayer = this.toggleLayer.bind(this);
        this.handleUndo = this.handleUndo.bind(this);
        this.handleLocateMe = this.handleLocateMe.bind(this);

        this.init();
    }

    /**
     * Inisialisasi Event Listener
     */
    init() {
        if (this.dom.btnOpen) this.dom.btnOpen.addEventListener('click', this.openMap);
        if (this.dom.btnClose) this.dom.btnClose.addEventListener('click', this.closeMap);
        if (this.dom.btnConfirm) this.dom.btnConfirm.addEventListener('click', this.confirmLocation);
        
        // Search Legacy (jika ada)
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.handleSearchInput(e.target.value), 500);
            });
        }
        if (this.dom.btnUndo) this.dom.btnUndo.addEventListener('click', this.handleUndo);
    }

    /**
     * Membuka Modal
     */
    openMap() {
        this.dom.modal.classList.remove('hidden');
        this.dom.modal.classList.add('flex');
        
        if (!this.isInitialized) {
            this.initLeaflet();
            this.isInitialized = true;
        } else {
            setTimeout(() => { this.map.invalidateSize(); }, 200);
        }

        // Reset
        if(this.dom.searchInput) this.dom.searchInput.value = '';
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
        const defaultLat = -4.546759;
        const defaultLng = 136.883713;

        this.map = L.map(this.config.mapContainerId, {
            center: [defaultLat, defaultLng],
            zoom: 13,
            zoomControl: false 
        });

        // Zoom Control di kanan bawah
        L.control.zoom({ position: 'bottomright' }).addTo(this.map);

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

        // Inject Unified Controls
        this.addUnifiedControlStack();

        // Event Listeners
        this.map.on('moveend', () => {
            if (this.isUndoAction) {
                this.isUndoAction = false;
                return;
            }
            this.handleMapMove();
        });
    }

    /**
     * [UPDATED] Unified Floating Stack Control
     * UI yang lebih modern, transisi halus, dan feedback interaktif.
     */
    addUnifiedControlStack() {
        const self = this;

        const UnifiedControl = L.Control.extend({
            onAdd: function(map) {
                const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                
                // Style Container: Glassmorphism effect, Rounded, Shadow
                container.className += " isolate bg-white/95 backdrop-blur-sm rounded-xl shadow-2xl overflow-hidden border border-slate-200/60 font-sans transition-all duration-300 hover:shadow-blue-900/10";
                container.style.minWidth = "280px"; 
                container.style.marginTop = "12px";
                container.style.marginRight = "12px";

                container.innerHTML = `
                    <div class="flex flex-col text-slate-700 divide-y divide-slate-100">
                        
                        <div class="p-3 bg-white/50">
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg id="icon-search-static" class="w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <svg id="icon-search-loading" class="hidden animate-spin w-4 h-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                                <input type="text" id="stack-search-input" 
                                    class="w-full pl-9 pr-8 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 focus:bg-white transition-all placeholder:text-slate-400"
                                    placeholder="Cari lokasi..." autocomplete="off">
                                
                                <div id="btn-clear-search" class="absolute inset-y-0 right-0 flex items-center pr-2 cursor-pointer hidden hover:text-red-500 text-slate-400 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                            </div>
                            
                            <div id="stack-search-results" class="hidden mt-2 max-h-48 overflow-y-auto border-t border-slate-100 text-xs scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent"></div>
                        </div>

                        <div>
                            <div id="stack-toggle-wilayah" class="group cursor-pointer p-3 hover:bg-blue-50/50 transition-colors flex items-center justify-between select-none">
                                <div class="flex items-center gap-3">
                                    <div class="bg-blue-100 text-blue-600 p-1.5 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-blue-700 transition-colors">Wilayah Kerja</span>
                                        <span class="text-[10px] text-slate-500">Distrik & Kampung (Timika)</span>
                                    </div>
                                </div>
                                <svg id="icon-chevron" class="w-4 h-4 text-slate-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <div id="stack-panel-wilayah-wrapper" class="grid grid-rows-[0fr] transition-all duration-300 ease-in-out">
                                <div class="overflow-hidden">
                                    <div class="bg-slate-50/80 px-3 pb-3 pt-1 space-y-2 border-t border-slate-100">
                                        <div class="relative">
                                            <select id="select-kecamatan" class="w-full text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-blue-500 p-2 outline-none shadow-sm appearance-none cursor-pointer hover:border-blue-300 transition-colors">
                                                <option value="">-- Pilih Distrik --</option>
                                                <option value="loading" disabled>Memuat data...</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-500">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <select id="select-kelurahan" class="w-full text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-blue-500 p-2 outline-none shadow-sm appearance-none cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 transition-colors" disabled>
                                                <option value="">-- Pilih Kampung --</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-500">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="stack-btn-gps" class="group cursor-pointer p-3 hover:bg-blue-50/50 transition-colors flex items-center gap-3 select-none">
                            <div class="bg-red-100 text-red-600 p-1.5 rounded-lg group-hover:bg-red-500 group-hover:text-white transition-colors duration-300" id="icon-container-gps">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700 group-hover:text-red-600 transition-colors">Lokasi Saya (GPS)</span>
                        </div>

                        <div id="stack-btn-layer" class="group cursor-pointer p-3 hover:bg-blue-50/50 transition-colors flex items-center gap-3 select-none">
                            <div class="bg-green-100 text-green-600 p-1.5 rounded-lg group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 7m0 13V7"></path></svg>
                            </div>
                            <span id="label-layer-mode" class="text-sm font-medium text-slate-700 group-hover:text-green-700 transition-colors">Mode Satelit</span>
                        </div>

                    </div>
                `;

                L.DomEvent.disableClickPropagation(container);
                L.DomEvent.disableScrollPropagation(container);
                return container;
            },
            onRemove: function(map) {}
        });

        this.map.addControl(new UnifiedControl({ position: 'topright' }));

        // === WIRING LOGIC ===

        // A. SEARCH LOGIC
        const searchInput = document.getElementById('stack-search-input');
        const searchResults = document.getElementById('stack-search-results');
        const searchLoading = document.getElementById('icon-search-loading');
        const searchStatic = document.getElementById('icon-search-static');
        const btnClear = document.getElementById('btn-clear-search');
        
        // Handle Clear Button
        btnClear.addEventListener('click', () => {
            searchInput.value = '';
            searchResults.classList.add('hidden');
            btnClear.classList.add('hidden');
            searchInput.focus();
        });

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value;
            
            // Show/Hide Clear Button
            if (query.length > 0) btnClear.classList.remove('hidden');
            else btnClear.classList.add('hidden');

            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                if (query.length < 3) {
                    searchResults.classList.add('hidden');
                    return;
                }
                // Show Loading
                searchStatic.classList.add('hidden');
                searchLoading.classList.remove('hidden');
                
                this.performStackSearch(query, searchResults).finally(() => {
                    // Hide Loading
                    searchStatic.classList.remove('hidden');
                    searchLoading.classList.add('hidden');
                });
            }, 500);
        });

        // B. WILAYAH ACCORDION TOGGLE
        const toggleBtn = document.getElementById('stack-toggle-wilayah');
        const panelWrapper = document.getElementById('stack-panel-wilayah-wrapper'); // The Grid Wrapper
        const iconChevron = document.getElementById('icon-chevron');
        const selectKecamatan = document.getElementById('select-kecamatan');
        let isExpanded = false;
        
        toggleBtn.addEventListener('click', () => {
            isExpanded = !isExpanded;
            if (isExpanded) {
                // Open: Change grid-rows to 1fr
                panelWrapper.classList.remove('grid-rows-[0fr]');
                panelWrapper.classList.add('grid-rows-[1fr]');
                iconChevron.style.transform = 'rotate(180deg)';
                
                // Load data if empty
                if (selectKecamatan.options.length <= 2) this.loadKecamatan(selectKecamatan);
            } else {
                // Close: Change grid-rows to 0fr
                panelWrapper.classList.remove('grid-rows-[1fr]');
                panelWrapper.classList.add('grid-rows-[0fr]');
                iconChevron.style.transform = 'rotate(0deg)';
            }
        });

        // C. WILAYAH CHAIN DROPDOWN
        const selectKelurahan = document.getElementById('select-kelurahan');
        selectKecamatan.addEventListener('change', (e) => {
            if (e.target.value) this.loadKelurahan(e.target.value, selectKelurahan);
            else {
                selectKelurahan.innerHTML = '<option value="">-- Pilih Kampung --</option>';
                selectKelurahan.disabled = true;
            }
        });

        selectKelurahan.addEventListener('change', (e) => {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const lat = parseFloat(selectedOption.getAttribute('data-lat'));
            const lng = parseFloat(selectedOption.getAttribute('data-lng'));
            if (lat && lng) {
                self.map.flyTo([lat, lng], 16, { animate: true, duration: 1.5 });
                self.updateMarker(lat, lng);
                if(self.dom.inputProvider) self.dom.inputProvider.value = 'internal_db_search';
                
                // Auto collapse on mobile
                if (window.innerWidth < 640) {
                    isExpanded = false;
                    panelWrapper.classList.remove('grid-rows-[1fr]');
                    panelWrapper.classList.add('grid-rows-[0fr]');
                    iconChevron.style.transform = 'rotate(0deg)';
                }
            } else {
                alert("Koordinat wilayah belum tersedia.");
            }
        });

        // D. GPS & LAYER
        document.getElementById('stack-btn-gps').addEventListener('click', () => this.handleLocateMe());
        document.getElementById('stack-btn-layer').addEventListener('click', () => {
            this.toggleLayer();
            const label = document.getElementById('label-layer-mode');
            label.innerText = (this.currentLayer === 'satellite') ? "Mode Peta Jalan" : "Mode Satelit";
        });
    }

    /**
     * Handle Search Result di dalam Stack
     */
    async performStackSearch(query, resultsContainer) {
        // resultsContainer.innerHTML = '<div class="p-3 text-center text-xs text-slate-400 italic">Mencari...</div>';
        resultsContainer.classList.remove('hidden');

        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=5`, {
                headers: { 'User-Agent': 'E-Daily-Report-App' }
            });
            const results = await response.json();

            resultsContainer.innerHTML = '';
            
            if (results.length === 0) {
                resultsContainer.innerHTML = '<div class="p-3 text-center text-xs text-slate-400">Lokasi tidak ditemukan</div>';
                return;
            }

            results.forEach(place => {
                const item = document.createElement('div');
                item.className = 'p-2.5 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0 truncate transition-colors text-slate-600 hover:text-blue-700 flex items-center gap-2';
                
                // Simple Pin Icon
                item.innerHTML = `
                    <svg class="w-3.5 h-3.5 shrink-0 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="truncate">${place.display_name}</span>
                `;
                
                item.onclick = () => {
                    const lat = parseFloat(place.lat);
                    const lng = parseFloat(place.lon);
                    
                    this.map.flyTo([lat, lng], 17);
                    this.updateMarker(lat, lng); 
                    
                    resultsContainer.classList.add('hidden');
                    document.getElementById('stack-search-input').value = place.display_name.split(',')[0];
                    if(this.dom.inputProvider) this.dom.inputProvider.value = 'manual_search'; 
                };
                resultsContainer.appendChild(item);
            });
        } catch (e) {
            console.error(e);
            resultsContainer.innerHTML = '<div class="p-3 text-center text-xs text-red-400">Gagal memuat data</div>';
        }
    }

    /**
     * Fetch API Data Kecamatan
     */
    async loadKecamatan(selectElement) {
        try {
            const response = await fetch(`/wilayah/kecamatan?kabupaten_id=${this.DEFAULT_KABUPATEN_ID}`);
            const data = await response.json();

            selectElement.innerHTML = '<option value="">-- Pilih Distrik --</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.nama;
                selectElement.appendChild(option);
            });
        } catch (error) {
            console.error("Gagal memuat kecamatan:", error);
            selectElement.innerHTML = '<option value="">Gagal Memuat</option>';
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

            selectElement.innerHTML = '<option value="">-- Pilih Kampung --</option>';
            if (data.length === 0) selectElement.innerHTML = '<option value="">Data Kosong</option>';

            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.nama;
                if (item.latitude && item.longitude) {
                    option.setAttribute('data-lat', item.latitude);
                    option.setAttribute('data-lng', item.longitude);
                } else {
                    option.text += ' (No Coord)';
                }
                selectElement.appendChild(option);
            });
            selectElement.disabled = false;
        } catch (error) {
            console.error("Gagal memuat kelurahan:", error);
            selectElement.innerHTML = '<option value="">Gagal</option>';
        }
    }

    /**
     * Logika Penentuan Lokasi Awal
     */
    determineInitialLocation() {
        const editLat = this.dom.inputLat.value;
        const editLng = this.dom.inputLng.value;
        const cachedLat = localStorage.getItem(this.CACHE_KEY_LAT);
        const cachedLng = localStorage.getItem(this.CACHE_KEY_LNG);

        if (editLat && editLng) {
            this.updateMarker(parseFloat(editLat), parseFloat(editLng));
            this.map.setView([editLat, editLng], 18);
        } else if (cachedLat && cachedLng) {
            this.updateMarker(parseFloat(cachedLat), parseFloat(cachedLng));
            this.map.setView([cachedLat, cachedLng], 15);
        } else {
            this.handleLocateMe(); 
        }
    }

    /**
     * Handle Tombol Locate Me (GPS)
     */
    handleLocateMe() {
        if (!navigator.geolocation) {
            alert("Browser tidak mendukung Geolocation.");
            return;
        }

        const iconContainer = document.getElementById('icon-container-gps');
        const originalContent = iconContainer ? iconContainer.innerHTML : '';
        
        // Loading State
        if(iconContainer) {
            iconContainer.innerHTML = `<svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                this.updateMarker(lat, lng);
                this.map.flyTo([lat, lng], 18, { animate: true, duration: 1.5 });

                if(this.dom.inputProvider) this.dom.inputProvider.value = 'gps_device';
                if(iconContainer) iconContainer.innerHTML = originalContent;
            },
            (error) => {
                let msg = "Gagal mengambil lokasi.";
                if(error.code === 1) msg = "Izin lokasi ditolak.";
                alert(msg);
                if(iconContainer) iconContainer.innerHTML = originalContent;
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    /**
     * Update State saat peta digeser
     */
    async handleMapMove() {
        const center = this.map.getCenter();
        const lat = center.lat;
        const lng = center.lng;

        this.historyStack.push({ lat, lng });
        if (this.historyStack.length > 10) this.historyStack.shift(); 
        if(this.dom.btnUndo) this.dom.btnUndo.disabled = this.historyStack.length <= 1;

        if(this.dom.modalCoordsPreview) this.dom.modalCoordsPreview.innerText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        if(this.dom.modalAddressPreview) this.dom.modalAddressPreview.innerText = "Memuat alamat...";

        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {
                headers: { 'User-Agent': 'E-Daily-Report-App' }
            });
            const data = await response.json();
            
            const shortAddress = data.name || (data.address ? (data.address.road || data.address.village || data.address.suburb) : "Lokasi Terpilih");
            const fullAddress = data.display_name;

            this.currentLocationData = { lat, lng, shortAddress, fullAddress };
            if(this.dom.modalAddressPreview) this.dom.modalAddressPreview.innerText = shortAddress;

        } catch (e) {
            this.currentLocationData = { lat, lng, shortAddress: "Lokasi Terpilih", fullAddress: "" };
            if(this.dom.modalAddressPreview) this.dom.modalAddressPreview.innerText = "Gagal memuat nama jalan";
        }
    }

    /**
     * Helper: Geser Peta
     */
    updateMarker(lat, lng) {
        this.map.setView([lat, lng], this.map.getZoom());
        this.handleMapMove();
    }

    /**
     * Legacy Search (Jika masih dipakai di luar stack)
     */
    async handleSearchInput(query) {
        // Logic ini sama dengan performStackSearch tapi untuk input lama
        // Dibiarkan untuk backward compatibility jika modal lama dipakai
    }

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

    handleUndo() {
        if (this.historyStack.length > 1) {
            this.historyStack.pop(); 
            const prev = this.historyStack[this.historyStack.length - 1]; 
            this.isUndoAction = true; 
            this.map.flyTo([prev.lat, prev.lng], this.map.getZoom());
            this.updateMarker(prev.lat, prev.lng);
        }
    }

    confirmLocation() {
        if (!this.currentLocationData) {
            const c = this.map.getCenter();
            this.currentLocationData = {
                lat: c.lat,
                lng: c.lng,
                shortAddress: `${c.lat.toFixed(5)}, ${c.lng.toFixed(5)}`,
                fullAddress: ""
            };
        }

        const data = this.currentLocationData;

        this.dom.inputLat.value = data.lat;
        this.dom.inputLng.value = data.lng;
        this.dom.inputAddress.value = data.shortAddress;
        this.dom.inputAddressAuto.value = data.fullAddress;
        
        if (this.dom.inputProvider && this.dom.inputProvider.value === '') {
             this.dom.inputProvider.value = 'manual_pin';
        }

        this.dom.previewInput.value = data.fullAddress || data.shortAddress;
        
        localStorage.setItem(this.CACHE_KEY_LAT, data.lat);
        localStorage.setItem(this.CACHE_KEY_LNG, data.lng);
        
        this.closeMap();
    }
}

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