const express = require('express');
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const app = express();

app.use(express.json({ limit: '100mb' }));

// -----------------------------------------------------------------------------
// [LOGIC V5] HARDCODED PATH RESOLUTION
// Kita tembak langsung ke file dist/src nya. Ini cara paling brutal tapi paling aman.
// -----------------------------------------------------------------------------
let ASSETS = { css: {}, js: {} };

function loadLibraryAssets() {
    try {
        console.log('[Init] Membaca file library dari node_modules...');
        
        // 1. LEAFLET CORE
        // Tembak langsung file fisiknya
        const leafletPath = require.resolve('leaflet/dist/leaflet.js');
        const leafletCssPath = require.resolve('leaflet/dist/leaflet.css');
        
        ASSETS.js.leaflet = fs.readFileSync(leafletPath, 'utf8');
        ASSETS.css.leaflet = fs.readFileSync(leafletCssPath, 'utf8');

        // 2. LEAFLET MARKER CLUSTER (Yang bikin error sebelumnya)
        // Kita gunakan require.resolve ke path spesifik
        const clusterJsPath = require.resolve('leaflet.markercluster/dist/leaflet.markercluster.js');
        const clusterCssPath = require.resolve('leaflet.markercluster/dist/MarkerCluster.css');
        const clusterDefCssPath = require.resolve('leaflet.markercluster/dist/MarkerCluster.Default.css');

        ASSETS.js.markerCluster = fs.readFileSync(clusterJsPath, 'utf8');
        ASSETS.css.cluster = fs.readFileSync(clusterCssPath, 'utf8');
        ASSETS.css.clusterDefault = fs.readFileSync(clusterDefCssPath, 'utf8');

        // 3. LEAFLET HEAT
        try {
            const heatPath = require.resolve('leaflet.heat/dist/leaflet-heat.js');
            ASSETS.js.heat = fs.readFileSync(heatPath, 'utf8');
        } catch (e) {
            console.warn('[Warn] Heatmap lib not found via require.resolve, trying fallback path...');
            // Fallback manual check
            if (fs.existsSync('./node_modules/leaflet.heat/dist/leaflet-heat.js')) {
                 ASSETS.js.heat = fs.readFileSync('./node_modules/leaflet.heat/dist/leaflet-heat.js', 'utf8');
            } else {
                 ASSETS.js.heat = 'console.warn("Heatmap lib missing");';
            }
        }

        console.log('[Init] SUCCESS: Semua library JS & CSS berhasil diload.');
        return true;

    } catch (e) {
        console.error('====================================================');
        console.error('[Init] FATAL ERROR: Gagal membaca file library!');
        console.error('Penyebab:', e.message);
        console.error('Hint: Library belum terinstall di Docker. JALANKAN REBUILD NO-CACHE!');
        console.error('====================================================');
        return false;
    }
}

// Load assets. Kalau gagal, process exit biar container restart (dan kita tau errornya)
if (!loadLibraryAssets()) {
    process.exit(1); 
}

app.post('/render-map', async (req, res) => {
    let browser = null;
    try {
        const { activities, center, zoom, mode } = req.body;
        
        let renderMode = mode || 'heatmap';
        if (renderMode === 'clustering') renderMode = 'cluster';

        console.log(`[Request] Mode: ${renderMode}, Data: ${activities?.length || 0}`);

        browser = await puppeteer.launch({
            executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/chromium', 
            args: [
                '--no-sandbox', 
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--font-render-hinting=none',
                '--disable-software-rasterizer'
            ],
            headless: 'new',
            protocolTimeout: 120000 
        });

        const page = await browser.newPage();
        page.setDefaultNavigationTimeout(120000); 
        page.setDefaultTimeout(120000);
        await page.setViewport({ width: 1200, height: 800, deviceScaleFactor: 2 });

        // Forward log browser ke terminal Docker
        page.on('console', msg => console.log('   [Browser]', msg.text()));
        page.on('pageerror', err => console.error('   [Browser ERR]', err.toString()));

        const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                /* RESET & LEAFLET CSS */
                html, body { margin: 0; padding: 0; width: 100%; height: 100%; background: #fff; }
                #map { width: 100vw; height: 100vh; }
                ${ASSETS.css.leaflet}
                
                /* CLUSTER CSS */
                ${ASSETS.css.cluster}
                ${ASSETS.css.clusterDefault}

                /* CUSTOM CLUSTER STYLE (OVERRIDE) */
                .marker-cluster-small { background-color: rgba(181, 226, 140, 0.6); }
                .marker-cluster-small div { background-color: rgba(110, 204, 57, 0.6); }
                .marker-cluster-medium { background-color: rgba(241, 211, 87, 0.6); }
                .marker-cluster-medium div { background-color: rgba(240, 194, 12, 0.6); }
                .marker-cluster-large { background-color: rgba(253, 156, 115, 0.6); }
                .marker-cluster-large div { background-color: rgba(241, 128, 23, 0.6); }
                .marker-cluster { background-clip: padding-box; border-radius: 20px; }
                .marker-cluster div { width: 30px; height: 30px; margin-left: 5px; margin-top: 5px; text-align: center; border-radius: 15px; font: 12px "Helvetica Neue", Arial, Helvetica, sans-serif; }
                .marker-cluster span { line-height: 30px; }
            </style>
        </head>
        <body>
            <div id="map"></div>

            <script>${ASSETS.js.leaflet}</script>
            <script>${ASSETS.js.markerCluster}</script>
            <script>${ASSETS.js.heat}</script>

            <script>
                try {
                    if (typeof L === 'undefined') throw new Error('Leaflet L undefined');

                    var map = L.map('map', {
                        zoomControl: false,
                        attributionControl: false,
                        fadeAnimation: false,
                        zoomAnimation: false,
                        markerZoomAnimation: false,
                        preferCanvas: true
                    }).setView([${center?.lat || -4.5467}, ${center?.lng || 136.8833}], ${zoom || 13});

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                    var rawData = ${JSON.stringify(activities || [])};
                    var mode = "${renderMode}";

                    console.log('Rendering Mode: ' + mode);

                    if (mode === 'cluster') {
                        if (typeof L.markerClusterGroup === 'undefined') throw new Error('MarkerCluster undefined');
                        
                        var markers = L.markerClusterGroup({
                            chunkedLoading: true,
                            animate: false,
                            spiderfyOnMaxZoom: false,
                            showCoverageOnHover: false
                        });
                        
                        rawData.forEach(p => {
                            if(p.lat && p.lng) markers.addLayer(L.marker([parseFloat(p.lat), parseFloat(p.lng)]));
                        });
                        
                        map.addLayer(markers);
                        if (rawData.length > 0) map.fitBounds(markers.getBounds(), { padding: [50,50], animate: false });

                    } else if (mode === 'heatmap') {
                        if (typeof L.heatLayer === 'undefined') throw new Error('HeatLayer undefined');
                        
                        var heatData = rawData.filter(p => p.lat && p.lng).map(p => [parseFloat(p.lat), parseFloat(p.lng), 0.7]);
                        L.heatLayer(heatData, { radius: 25, blur: 15 }).addTo(map);
                        
                        if (heatData.length > 0) {
                             var bounds = L.latLngBounds(heatData.map(p => [p[0], p[1]]));
                             map.fitBounds(bounds, { padding: [50,50], animate: false });
                        }

                    } else {
                        var group = new L.featureGroup();
                        rawData.forEach(p => {
                            if(p.lat && p.lng) L.marker([parseFloat(p.lat), parseFloat(p.lng)]).addTo(group);
                        });
                        map.addLayer(group);
                        if (rawData.length > 0) map.fitBounds(group.getBounds(), { padding: [50,50], animate: false });
                    }

                    document.body.setAttribute('data-ready', 'true');

                } catch (e) {
                    console.error(e);
                    document.body.setAttribute('data-error', e.message);
                }
            </script>
        </body>
        </html>`;

        await page.setContent(htmlContent, { waitUntil: 'networkidle0', timeout: 120000 });

        try {
            await page.waitForSelector('body[data-ready="true"]', { timeout: 120000 });
        } catch (e) {
            const err = await page.evaluate(() => document.body.getAttribute('data-error'));
            if (err) throw new Error('Page JS Error: ' + err);
            console.warn('[Renderer] Timeout waiting ready signal.');
        }

        await new Promise(r => setTimeout(r, 1000));

        const screenshotBuffer = await page.screenshot({ encoding: 'base64', type: 'jpeg', quality: 80, fullPage: false });
        await browser.close();
        
        res.json({ success: true, mode: renderMode, image: "data:image/jpeg;base64," + screenshotBuffer });

    } catch (error) {
        if (browser) await browser.close();
        console.error('[Renderer] FATAL:', error.message);
        res.status(500).json({ success: false, error: error.message });
    }
});

app.get('/', (req, res) => res.send('Map Renderer v5 OK'));
const PORT = 3000;
app.listen(PORT, () => console.log(`Map Renderer listening on port ${PORT}`));
