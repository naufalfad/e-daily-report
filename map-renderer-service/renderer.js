const express = require('express');
const puppeteer = require('puppeteer-core');
const app = express();

app.use(express.json({ limit: '50mb' })); // Limit besar untuk handle banyak data koordinat

app.post('/render-map', async (req, res) => {
    try {
        // Ambil data dari Request Laravel
        const { activities, center, zoom } = req.body;

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
        
        // Set ukuran viewport (sesuai kebutuhan PDF A4 biasanya)
        await page.setViewport({ width: 800, height: 600 });

        // Template HTML Peta (Menggunakan Leaflet CDN)
        const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <style>
                body { margin: 0; padding: 0; }
                #map { width: 800px; height: 600px; }
            </style>
        </head>
        <body>
            <div id="map"></div>
            <script>
                // Inisialisasi Peta
                var map = L.map('map', {
                    zoomControl: false, // Hilangkan tombol zoom agar bersih
                    attributionControl: false
                }).setView([${mapCenter.lat}, ${mapCenter.lng}], ${mapZoom});

                // Tambahkan Tile Layer (OpenStreetMap)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(map);

                // Data Activities dari Laravel
                var activities = ${JSON.stringify(dataPoints)};

                // Icon Marker Custom (Optional: Pakai default Leaflet dulu biar aman)
                
                // Loop data dan tambahkan marker
                activities.forEach(function(item) {
                    if(item.lat && item.lng) {
                        L.marker([item.lat, item.lng]).addTo(map);
                    }
                });

                // Fit Bounds jika data banyak (Optional, override center)
                if (activities.length > 1) {
                    var group = new L.featureGroup(activities.map(a => L.marker([a.lat, a.lng])));
                    map.fitBounds(group.getBounds(), { padding: [50, 50] });
                }
            </script>
        </body>
        </html>`;

        // Set Content ke Page
        await page.setContent(htmlContent);

        // Tunggu sampai network idle (tile map selesai load)
        // Kita beri toleransi waktu agar gambar peta muncul sempurna
        await page.waitForNetworkIdle({ idleTime: 500, timeout: 10000 });

        // Ambil Screenshot (Base64)
        const screenshotBuffer = await page.screenshot({ encoding: 'base64', type: 'jpeg', quality: 80 });

        await browser.close();

        // Response ke Laravel
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
