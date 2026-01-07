const express = require('express');
const puppeteer = require('puppeteer-core');
const app = express();

// Konfigurasi limit payload JSON diperbesar untuk mengakomodasi array koordinat yang banyak
app.use(express.json({ limit: '50mb' }));

/**
 * Endpoint: /render-map
 * Method: POST
 * Description: Menerima data geoJSON dan parameter visualisasi, merender peta via Headless Browser, mengembalikan Base64 Image.
 */
app.post('/render-map', async (req, res) => {
    let browser = null;
    try {
        // 1. Destructuring & Validation
        const { activities, center, zoom, mode } = req.body;
        
        // Fallback default jika mode tidak terdefinisi
        const renderMode = mode || 'heatmap'; 
        
        // Default Center (Kantor Pusat Pemerintahan / Timika) jika null
        const mapCenter = center || { lat: -4.5467, lng: 136.8833 }; 
        const mapZoom = zoom || 13;
        const dataPoints = activities || [];

        // 2. Browser Initialization
        // Menggunakan executablePath ke Chromium di dalam container Docker
        browser = await puppeteer.launch({
            executablePath: '/usr/bin/chromium-browser', // Sesuaikan dengan path di Alpine Linux (seringkali /usr/bin/chromium-browser)
            args: [
                '--no-sandbox', 
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage', // Penting untuk performa di Docker agar tidak crash memory
                '--disable-gpu',
                '--disable-software-rasterizer' // Tambahan optimasi
            ],
            headless: 'new'
        });

        const page = await browser.newPage();

        // Ukuran Viewport disesuaikan untuk proporsi PDF A4
        await page.setViewport({ width: 1200, height: 800, deviceScaleFactor: 2 });

        // 3. HTML Template Construction
        const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />

            <style>
                body { margin: 0; padding: 0; background: #fff; }
                #map { width: 100vw; height: 100vh; }
                
                /* Kustomisasi Tampilan Marker Cluster */
                .marker-cluster-small { background-color: rgba(181, 226, 140, 0.6); }
                .marker-cluster-small div { background-color: rgba(110, 204, 57, 0.6); }
                .marker-cluster-medium { background-color: rgba(241, 211, 87, 0.6); }
                .marker-cluster-medium div { background-color: rgba(240, 194, 12, 0.6); }
                .marker-cluster-large { background-color: rgba(253, 156, 115, 0.6); }
                .marker-cluster-large div { background-color: rgba(241, 128, 23, 0.6); }
            </style>
        </head>
        <body>
            <div id="map"></div>

            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
            <script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>

            <script>
                // --- 1. Init Map ---
                var map = L.map('map', {
                    zoomControl: false,       // Hilangkan kontrol zoom untuk screenshot bersih
                    attributionControl: false, // Hilangkan atribusi
                    fadeAnimation: false,      // Matikan animasi agar render lebih cepat stabil
                    zoomAnimation: false
                }).setView([${mapCenter.lat}, ${mapCenter.lng}], ${mapZoom});

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(map);

                // --- 2. Data Injection ---
                var rawData = ${JSON.stringify(dataPoints)};
                var mode = "${renderMode}";

                // --- 3. Visualization Logic ---
                if (mode === 'cluster') {
                    // === LOGIKA CLUSTERING ===
                    var markers = L.markerClusterGroup({
                        chunkedLoading: true // Optimasi untuk data dalam jumlah besar
                    });
                    
                    rawData.forEach(function(item) {
                        if(item.lat && item.lng) {
                            var m = L.marker([item.lat, item.lng]);
                            markers.addLayer(m);
                        }
                    });
                    
                    map.addLayer(markers);
                    
                    // Auto fit bounds agar semua marker terlihat
                    if (rawData.length > 0) {
                        map.fitBounds(markers.getBounds(), { padding: [50, 50] });
                    }

                } else if (mode === 'heatmap') {
                    // === LOGIKA HEATMAP ===
                    // Format data leaflet-heat: [lat, lng, intensity]
                    var heatPoints = rawData
                        .filter(d => d.lat && d.lng)
                        .map(d => [d.lat, d.lng, 0.8]); // Intensitas 0.8

                    var heat = L.heatLayer(heatPoints, {
                        radius: 30,
                        blur: 20,
                        maxZoom: 12,
                        minOpacity: 0.4,
                        gradient: {
                            0.2: 'blue',
                            0.4: 'cyan',
                            0.6: 'lime',
                            0.8: 'yellow',
                            1.0: 'red'
                        }
                    }).addTo(map);

                    // Fit bounds manual untuk heatmap
                    if (heatPoints.length > 0) {
                        var bounds = L.latLngBounds(heatPoints.map(p => [p[0], p[1]]));
                        map.fitBounds(bounds, { padding: [50, 50] });
                    }

                } else {
                    // === DEFAULT MARKER (Fallback) ===
                    var group = new L.featureGroup();
                    rawData.forEach(function(item) {
                        if(item.lat && item.lng) {
                            var marker = L.marker([item.lat, item.lng]).addTo(map);
                            group.addLayer(marker);
                        }
                    });
                    if (rawData.length > 0) map.fitBounds(group.getBounds(), { padding: [50, 50] });
                }

                // Tanda untuk Puppeteer bahwa map sudah siap
                document.body.setAttribute('data-ready', 'true');
            </script>
        </body>
        </html>`;

        // 4. Render & Screenshot
        // PERBAIKAN: Menggunakan networkidle2 dan timeout yang lebih panjang (60 detik)
        await page.setContent(htmlContent, { 
            waitUntil: 'networkidle2', 
            timeout: 60000 
        });

        // Tunggu manual sedikit lebih lama (3 detik) untuk memastikan tile ter-render visualnya
        await new Promise(r => setTimeout(r, 3000)); 

        const screenshotBuffer = await page.screenshot({ 
            encoding: 'base64', 
            type: 'jpeg', 
            quality: 85,
            fullPage: false 
        });

        await browser.close();

        // 5. Send Response
        res.json({
            success: true,
            mode: renderMode,
            image: "data:image/jpeg;base64," + screenshotBuffer
        });

    } catch (error) {
        if (browser) await browser.close();
        console.error("Map Renderer Error:", error);
        res.status(500).json({ success: false, error: error.message });
    }
});

// Health Check
app.get('/', (req, res) => {
    res.send('Map Renderer Service is Running (Ready for Heatmap/Cluster)');
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Map Renderer Service running on port ${PORT}`);
});
