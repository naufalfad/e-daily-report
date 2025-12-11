// map-renderer-service/renderer.js
const express = require('express');
const puppeteer = require('puppeteer');
const app = express();
const port = 3000; // Port lokal untuk service ini

app.use(express.json({ limit: '10mb' })); // Batasi ukuran payload

app.post('/render-map', async (req, res) => {
    const { activities, center, zoom } = req.body;

    if (!activities) {
        return res.status(400).send({ success: false, error: 'Missing activities data' });
    }

    let browser;
    try {
        // NOTE: Di Windows, path executable mungkin berbeda atau Puppeteer akan mendownload versi sendiri.
        // Jika error, coba hapus executablePath atau sesuaikan dengan path Chrome lokal Anda.
        browser = await puppeteer.launch({
            args: ['--no-sandbox', '--disable-setuid-sandbox'],
            // executablePath: '/usr/bin/chromium-browser', // Hapus ini di Windows/Mac lokal
            headless: 'new' 
        });
        const page = await browser.newPage();
        
        const viewportWidth = 800; 
        const viewportHeight = 450;
        await page.setViewport({ width: viewportWidth, height: viewportHeight });

        // Template HTML/Leaflet yang akan dirender oleh headless Chrome
        const htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
                <style>
                    #map { height: ${viewportHeight}px; width: ${viewportWidth}px; }
                </style>
            </head>
            <body>
                <div id="map"></div>
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                <script>
                    const activities = ${JSON.stringify(activities)};
                    
                    // Gunakan koordinat pertama sebagai pusat jika tidak ada center yang dihitung
                    const defaultCenter = [${center.lat}, ${center.lng}];
                    const defaultZoom = ${zoom};
                    
                    const map = L.map('map', { zoomControl: false }).setView(defaultCenter, defaultZoom);

                    //base map peta
                    L.tileLayer('http://www.google.cn/maps/vt?lyrs=m&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                    }).addTo(map);

                    const latlngs = [];
                    activities.forEach(act => {
                        if (!act.lat || !act.lng) return;

                        // Tentukan warna marker (sesuai logika Blade)
                        let color = act.status === 'approved' ? '#22c55e' : (act.status === 'rejected' ? '#ef4444' : '#f59e0b');

                        L.circleMarker([act.lat, act.lng], {
                            radius: 6,
                            fillColor: color,
                            color: "#FFF",
                            weight: 1,
                            fillOpacity: 0.9,
                        }).addTo(map);
                        
                        latlngs.push([act.lat, act.lng]);
                    });

                    // Auto-fit bounds (penting untuk akurasi)
                    if (latlngs.length > 0) {
                        map.fitBounds(latlngs, { padding: [20, 20] });
                    }
                </script>
            </body>
            </html>
        `;

        await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

        // Tunggu sebentar untuk memastikan tile selesai dimuat
        await new Promise(resolve => setTimeout(resolve, 1500)); 

        const mapElement = await page.$('#map');
        const buffer = await mapElement.screenshot({ encoding: 'base64' });

        res.json({ success: true, image: `data:image/png;base64,${buffer}` });

    } catch (error) {
        console.error('Renderer Error:', error);
        res.status(500).json({ success: false, error: error.message });
    } finally {
        if (browser) {
            await browser.close(); // Tutup browser untuk melepaskan RAM
        }
    }
});

app.listen(port, () => {
    console.log(`Map Renderer Service running on port ${port}`);
});