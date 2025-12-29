/**
 * @file resources/js/components/map-input.js
 * @description Modul kontroler untuk Unified Map Interface.
 * Mengelola logika inisialisasi peta (Leaflet), geolokasi (GPS), pencarian alamat,
 * dan sinkronisasi state ke Hidden Inputs form.
 *
 * @author Senior Software Architect
 */

export default class MapInput {
    /**
     * Constructor
     * Menerapkan Defensive Programming dengan memvalidasi konfigurasi awal.
     *
     * @param {Object} config - Objek konfigurasi mapping DOM ID.
     */
    constructor(config) {
        // 1. Validation & Setup
        if (!config || !config.mapId || !config.modalId) {
            throw new Error("MapInput: Configuration object with mapId and modalId is required.");
        }

        this.config = config;
        this.map = null;
        this.marker = null;
        this.accuracyCircle = null;
        this.isInitialized = false;

        // 2. DOM Elements Cache (Performance Optimization)
        this.dom = {
            modal: document.getElementById(config.modalId),
            mapContainer: document.getElementById(config.mapId),
            searchInput: document.getElementById('map_search_input'),
            searchBtn: document.getElementById('btn_map_search'),
            statusText: document.getElementById('gps_status'),
            accuracyInfo: document.getElementById('accuracy_display'),
            accuracyVal: document.getElementById('val_accuracy'),
            badge: document.getElementById('provider_badge'),
            inputs: {
                lat: document.getElementById(config.inputs.lat),
                lng: document.getElementById(config.inputs.lng),
                provider: document.getElementById(config.inputs.provider),
                accuracy: document.getElementById(config.inputs.accuracy),
                address: document.getElementById(config.inputs.address),
                display: document.getElementById(config.inputs.display),
            }
        };

        // 3. Constants (Single Source of Truth for Enums)
        this.PROVIDERS = {
            GPS: 'gps_device',      // High Trust
            MANUAL: 'manual_pin',   // Medium Trust
            SEARCH: 'search_result' // Medium Trust
        };

        // 4. Bind Events
        this.initEventListeners();
    }

    /**
     * Menginisialisasi Event Listeners.
     * Menggunakan pola Observer untuk mendeteksi interaksi user.
     */
    initEventListeners() {
        // Event: Saat Modal Peta Terbuka
        // Menggunakan 'shown.bs.modal' agar rendering peta Leaflet sempurna (ukuran container sudah final).
        this.dom.modal.addEventListener('shown.bs.modal', () => {
            if (!this.isInitialized) {
                this.initMap();
                this.startAutoLocation(); // Trigger "Real-time Reporting" flow
            } else {
                // Resize trigger wajib jika container berubah visibility
                setTimeout(() => this.map.invalidateSize(), 100);
            }
        });

        // Event: Pencarian Alamat
        this.dom.searchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.performSearch();
        });

        // Event: Enter pada kolom search
        this.dom.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.performSearch();
            }
        });
    }

    /**
     * Inisialisasi Peta Leaflet & Base Layers.
     * Menerapkan prinsip High Cohesion dengan memusatkan logika visual peta di sini.
     */
    initMap() {
        // Defensive Check: Pastikan Leaflet terload
        if (typeof L === 'undefined') {
            this.updateStatusUI("Library Peta gagal dimuat.", "danger");
            return;
        }

        // Default Coordinate (Kantor Pusat / Fallback)
        const defaultCoords = [-6.2088, 106.8456]; // Jakarta as Fallback

        // 1. Define Base Maps (Sesuai Instruksi User)
        const googleRoadmap = L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", {
            maxZoom: 20,
            attribution: 'Google Maps'
        });
        const googleSatelite = L.tileLayer("https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", {
            maxZoom: 22,
            attribution: 'Google Satellite'
        });

        // 2. Create Map Instance
        this.map = L.map(this.config.mapId, {
            center: defaultCoords,
            zoom: 13,
            layers: [googleRoadmap] // Default layer
        });

        // 3. Layer Controls
        const baseMaps = {
            "Peta Jalan": googleRoadmap,
            "Satelit": googleSatelite
        };
        L.control.layers(baseMaps).addTo(this.map);

        // 4. Initialize Marker (Draggable)
        this.marker = L.marker(defaultCoords, { draggable: true }).addTo(this.map);

        // 5. Bind Drag Event (Core Logic: Manual Override)
        this.marker.on('dragend', (event) => {
            const position = event.target.getLatLng();
            this.handleManualDrag(position.lat, position.lng);
        });

        this.isInitialized = true;
    }

    /**
     * Memulai proses Auto-Locate via GPS Browser.
     * Flow utama untuk user "On-Site".
     */
    startAutoLocation() {
        this.updateStatusUI("Mencari sinyal GPS...", "info");

        if (!navigator.geolocation) {
            this.handleGpsError({ message: "Browser tidak mendukung Geolocation." });
            return;
        }

        const options = {
            enableHighAccuracy: true, // Paksa hardware GPS jika tersedia
            timeout: 10000,           // 10 detik timeout
            maximumAge: 0             // Jangan gunakan cache lama
        };

        navigator.geolocation.getCurrentPosition(
            (pos) => this.handleGpsSuccess(pos),
            (err) => this.handleGpsError(err),
            options
        );
    }

    /**
     * Callback Sukses GPS.
     * Mengunci 'gps_device' sebagai provider untuk integritas data tinggi.
     */
    handleGpsSuccess(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;

        // Visual Update
        const latLng = new L.LatLng(lat, lng);
        this.map.flyTo(latLng, 18);
        this.marker.setLatLng(latLng);

        // Render Accuracy Circle
        if (this.accuracyCircle) this.map.removeLayer(this.accuracyCircle);
        this.accuracyCircle = L.circle(latLng, {
            radius: accuracy,
            color: '#1C7C54',
            fillColor: '#1C7C54',
            fillOpacity: 0.15
        }).addTo(this.map);

        // Update State
        this.updateState(lat, lng, accuracy, this.PROVIDERS.GPS);
        this.updateStatusUI(`GPS Terkunci (Akurasi: ${Math.round(accuracy)}m)`, "success");
    }

    /**
     * Callback Error GPS.
     * Fallback ke 'manual_pin' agar user tetap bisa kerja (Graceful Degradation).
     */
    handleGpsError(error) {
        let msg = "Gagal mengambil lokasi.";
        if (error.code === 1) msg = "Izin lokasi ditolak.";
        if (error.code === 2) msg = "Sinyal GPS tidak tersedia.";
        if (error.code === 3) msg = "Waktu permintaan habis.";

        this.updateStatusUI(`${msg} Silakan cari manual.`, "warning");
        
        // Set state ke Manual, tapi jangan ubah koordinat (biarkan di default/terakhir)
        // Set accuracy ke 0 karena manual tidak punya akurasi sinyal
        if (this.dom.inputs.provider.value !== this.PROVIDERS.SEARCH) {
             this.dom.inputs.provider.value = this.PROVIDERS.MANUAL;
             this.dom.inputs.accuracy.value = 0;
        }
    }

    /**
     * Logic saat user menggeser marker secara manual.
     * Provider dipaksa berubah jadi 'manual_pin'.
     */
    handleManualDrag(lat, lng) {
        // Hapus lingkaran akurasi karena lokasi sudah dimanipulasi
        if (this.accuracyCircle) {
            this.map.removeLayer(this.accuracyCircle);
            this.accuracyCircle = null;
        }

        this.updateState(lat, lng, 0, this.PROVIDERS.MANUAL);
        this.updateStatusUI("Mode Manual: Pin digeser user.", "warning");
    }

    /**
     * Logic Geocoding (Pencarian Alamat).
     * Provider berubah jadi 'search_result'.
     * Menggunakan Nominatim API (Free) sebagai solusi default.
     */
    async performSearch() {
        const query = this.dom.searchInput.value;
        if (!query || query.length < 3) return;

        this.updateStatusUI("Mencari alamat...", "info");
        this.dom.searchBtn.disabled = true;

        try {
            // Menggunakan Nominatim OpenStreetMap (No API Key Required)
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);

                // Visual Update
                const latLng = new L.LatLng(lat, lng);
                this.map.flyTo(latLng, 16);
                this.marker.setLatLng(latLng);
                
                if (this.accuracyCircle) this.map.removeLayer(this.accuracyCircle);

                // State Update
                this.updateState(lat, lng, 0, this.PROVIDERS.SEARCH);
                this.updateStatusUI("Lokasi ditemukan via pencarian.", "primary");
                
                // Auto-fill field teks alamat untuk user
                if(this.dom.inputs.address) {
                    this.dom.inputs.address.value = result.display_name;
                }

            } else {
                this.updateStatusUI("Alamat tidak ditemukan.", "danger");
            }
        } catch (error) {
            console.error("Geocoding Error:", error);
            this.updateStatusUI("Gagal koneksi ke server peta.", "danger");
        } finally {
            this.dom.searchBtn.disabled = false;
        }
    }

    /**
     * Core State Management.
     * Pusat sinkronisasi data dari JS ke HTML Hidden Inputs.
     * Menerapkan prinsip 'Single Source of Truth'.
     */
    updateState(lat, lng, accuracy, provider) {
        // 1. Update Hidden Inputs
        if (this.dom.inputs.lat) this.dom.inputs.lat.value = lat;
        if (this.dom.inputs.lng) this.dom.inputs.lng.value = lng;
        if (this.dom.inputs.accuracy) this.dom.inputs.accuracy.value = accuracy || 0;
        if (this.dom.inputs.provider) this.dom.inputs.provider.value = provider;

        // 2. Update Display Text (User Feedback)
        if (this.dom.inputs.display) {
            this.dom.inputs.display.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }

        // 3. Update Badge UI
        this.updateBadgeUI(provider);

        // 4. Update Accuracy Text
        if (this.dom.accuracyInfo) {
            if (accuracy > 0) {
                this.dom.accuracyInfo.style.display = 'block';
                if(this.dom.accuracyVal) this.dom.accuracyVal.textContent = Math.round(accuracy);
            } else {
                this.dom.accuracyInfo.style.display = 'none';
            }
        }
    }

    updateBadgeUI(provider) {
        if (!this.dom.badge) return;

        const config = {
            [this.PROVIDERS.GPS]: { text: 'GPS (Akurasi Tinggi)', class: 'bg-success' },
            [this.PROVIDERS.MANUAL]: { text: 'Manual (Geser Pin)', class: 'bg-warning' },
            [this.PROVIDERS.SEARCH]: { text: 'Hasil Pencarian', class: 'bg-info' }
        };

        const current = config[provider] || { text: 'Unknown', class: 'bg-secondary' };
        this.dom.badge.className = `badge ${current.class} text-white`; // Bootstrap classes
        this.dom.badge.textContent = `Sumber: ${current.text}`;
    }

    updateStatusUI(message, type = "info") {
        if (!this.dom.statusText) return;

        const colors = {
            info: 'text-primary',
            success: 'text-success',
            warning: 'text-warning',
            danger: 'text-danger'
        };

        this.dom.statusText.className = `small fw-bold ${colors[type] || 'text-muted'}`;
        
        // Tambahkan spinner jika info/loading
        if (type === 'info') {
            this.dom.statusText.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> ${message}`;
        } else {
            this.dom.statusText.innerHTML = message;
        }
    }
}