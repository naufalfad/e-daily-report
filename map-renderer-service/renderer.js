const express = require('express');
const puppeteer = require('puppeteer-core');
const app = express();

// Limit besar untuk handle banyak data koordinat
app.use(express.json({ limit: '50mb' })); 

app.post('/render-map', async (req, res) => {
    try {
        // 1. Ambil data & MODE dari Request Laravel
        // Default mode ke 'heatmap' jika tidak dikirim
        const { activities, center, zoom, mode } = req.body;
        const renderMode = mode || 'heatmap'; 

        // Validasi input minimal
        const mapCenter = center || { lat: -4.546, lng: 136.883 }; // Default Timika
        const mapZoom = zoom || 13;
        const dataPoints = activities || [];

        // Launch Browser (Headless)
        const browser = await puppeteer.launch({
            executablePath: '/usr/bin/chromium',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        const page = await browser.newPage();

        // Set ukuran viewport (sesuai kebutuhan PDF A4 landscape/portrait)
        await page.setViewport({ width: 800, height: 600 });

        // Template HTML Peta
        const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
            <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

            <script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>

            <style>
                body { margin: 0; padding: 0; }
                #map { width: 800px; height: 600px; }
            </style>
        </head>
        <body>
            <div id="map"></div>
            <script>
                // --- Inisialisasi Peta ---
                var map = L.map('map', {
                    zoomControl: false, 
                    attributionControl: false
                }).setView([${mapCenter.lat}, ${mapCenter.lng}], ${mapZoom});

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(map);

                // --- Data dari Laravel ---
                var rawData = ${JSON.stringify(dataPoints)};
                var mode = "${renderMode}";

                // --- LOGIKA VISUALISASI (SWITCH MODE) ---
                if (mode === 'cluster') {
                    // MODE A: MARKER CLUSTERING
                    var markers = L.markerClusterGroup();
                    
                    rawData.forEach(function(item) {
                        if(item.lat && item.lng) {
                            markers.addLayer(L.marker([item.lat, item.lng]));
                        }
                    });
                    
                    map.addLayer(markers);
                    
                    // Fit bounds ke marker
                    if (rawData.length > 0) {
                        map.fitBounds(markers.getBounds(), { padding: [50, 50] });
                    }

                } else {
                    // MODE B: HEATMAP (Default)
                    // Format data untuk heatmap: [lat, lng, intensity]
                    var heatPoints = rawData
                        .filter(d => d.lat && d.lng)
                        .map(d => [d.lat, d.lng, 1.0]); // Intensitas default 1.0

                    var heat = L.heatLayer(heatPoints, {
                        radius: 25,   // Radius sebaran
                        blur: 15,     // Kehalusan gradasi
                        maxZoom: 15,  // Zoom maksimal sebelum memudar
                        minOpacity: 0.4,
                        gradient: {0.4: 'blue', 0.65: 'lime', 1: 'red'} // Biru (Sepi) -> Merah (Padat)
                    }).addTo(map);

                    // Fit bounds manual untuk heatmap
                    if (heatPoints.length > 0) {
                        var bounds = L.latLngBounds(heatPoints.map(p => [p[0], p[1]]));
                        map.fitBounds(bounds, { padding: [50, 50] });
                    }
                }
            </script>
        </body>
        </html>`;

        // Set Content ke Page
        await page.setContent(htmlContent);

        // Tunggu render selesai
        await page.waitForNetworkIdle({ idleTime: 500, timeout: 10000 });

        // Ambil Screenshot
        const screenshotBuffer = await page.screenshot({ encoding: 'base64', type: 'jpeg', quality: 80 });

        await browser.close();

        // Response
        res.json({
            success: true,
            image: "data:image/jpeg;base64," + screenshotBuffer
        });

    } catch (error) {
        console.error("Renderer Error:", error);
        res.status(500).json({ success: false, error: error.message });
    }
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Map Renderer Service running on port ${PORT}`);
});
