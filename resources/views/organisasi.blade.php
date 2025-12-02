<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Struktur Organisasi Bapenda</title>

    <style>
        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            /* Memastikan scrollbar muncul di body jika konten sangat lebar */
            overflow-x: auto; 
        }

        h1.header-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }

        /* --- Container Tree --- */
        .tree-container {
            display: block; /* Ubah ke block agar scroll berfungsi baik */
            width: 100%;
            overflow-x: auto; /* Scroll horizontal pada container */
            padding-bottom: 20px; /* Ruang untuk scrollbar */
            text-align: center; /* Pusatkan konten jika muat */
            white-space: nowrap; /* Cegah wrapping yang merusak garis */
        }

        .tree {
            display: inline-block; /* Agar lebar menyesuaikan konten */
            min-width: 100%;
        }

        .tree ul {
            position: relative;
            padding-top: 20px; /* Sedikit dikurangi */
            display: flex;
            justify-content: center;
            gap: 20px; /* [PENTING] Dikurangi drastis dari 60px ke 20px agar rapat */
        }

        .tree li {
            list-style-type: none;
            text-align: center;
            position: relative;
            padding: 20px 5px 0 5px; /* Padding horizontal dikurangi */
        }

        /* --- Connectors (Garis Penghubung) --- */
        .tree li::before,
        .tree li::after {
            content: "";
            position: absolute;
            top: 0;
            width: 50%;
            border-top: 2px solid #c4ccd6;
            height: 20px;
        }

        .tree li::before {
            right: 50%;
        }

        .tree li::after {
            left: 50%;
            border-left: 2px solid #c4ccd6;
        }

        /* Hapus garis untuk anak tunggal */
        .tree li:only-child::before,
        .tree li:only-child::after {
            display: none;
        }

        /* Hapus border atas untuk anak pertama & terakhir */
        .tree li:first-child::before,
        .tree li:last-child::after {
            border: none;
        }

        /* Garis lengkung untuk anak pertama */
        .tree li:first-child::after {
            border-radius: 5px 0 0 0;
        }

        /* Garis lengkung untuk anak terakhir */
        .tree li:last-child::before {
            border-radius: 0 5px 0 0;
            border-right: 2px solid #c4ccd6;
        }

        /* Garis vertikal turun dari parent ke anak-anak */
        .tree ul ul::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            width: 0;
            height: 20px; /* Samakan dengan padding-top ul */
            border-left: 2px solid #c4ccd6;
        }

        /* --- Node Box (Kotak Jabatan) --- */
        .node {
            background: #fff;
            padding: 10px 8px; /* [PENTING] Padding diperkecil */
            border-radius: 6px;
            min-width: 140px; /* [PENTING] Lebar minimal diperkecil dari 220px */
            max-width: 180px; /* Batasi lebar maksimal agar tidak terlalu melar */
            border: 1px solid #bbb; /* Border lebih tipis */
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            font-size: 12px; /* [PENTING] Font diperkecil */
            white-space: normal; /* Biarkan teks wrap ke bawah */
            line-height: 1.3;
            display: inline-block;
            vertical-align: top;
        }

        .node:hover {
            background: #eef3f8;
            border-color: #0d6efd;
            transform: translateY(-2px);
            transition: all 0.2s;
        }

        .node-nama {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 3px;
            color: #2c3e50;
        }

        .node-jabatan {
            font-size: 11px;
            color: #5f6f81;
            margin-bottom: 2px;
        }
        
        .node-bidang {
            font-size: 10px;
            color: #0d6efd;
            font-weight: 600;
        }

        /* Loading Indicator */
        #loading {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1 class="header-title">Struktur Organisasi Badan Pendapatan Daerah</h1>

    <div class="tree-container">
        <div id="chart-container" class="tree">
            <div id="loading">Sedang memuat hierarki...</div>
        </div>
    </div>

    <script>
        // Token API Paduka
        const API_TOKEN = "8Y83PJjgH6NGmYXLHYBIhYisoYwcO95qi9rPL7Bm2454ccb9";

        function buildNode(pegawai) {
            const li = document.createElement("li");

            const node = document.createElement("div");
            node.className = "node";
            // Menggunakan optional chaining (?.) untuk keamanan data null
            node.innerHTML = `
                <div class="node-nama">${pegawai.name}</div>
                <div class="node-jabatan">${pegawai.jabatan?.nama_jabatan || "-"}</div>
                <div class="node-bidang">${pegawai.bidang?.nama_bidang || ""}</div>
            `;

            li.appendChild(node);

            // Cek apakah punya bawahan (recursive)
            if (pegawai.bawahan_recursif && pegawai.bawahan_recursif.length > 0) {
                const ul = document.createElement("ul");
                pegawai.bawahan_recursif.forEach((child) => {
                    ul.appendChild(buildNode(child));
                });
                li.appendChild(ul);
            }

            return li;
        }

        document.addEventListener("DOMContentLoaded", () => {
            const chartContainer = document.getElementById("chart-container");
            const loadingEl = document.getElementById("loading");
            const APP_URL = window.APP_URL;

            fetch(`${APP_URL}/api/organisasi/tree`, {
                headers: {
                    Authorization: `Bearer ${API_TOKEN}`,
                    Accept: "application/json",
                },
            })
            .then((response) => {
                if (!response.ok) throw new Error(response.statusText);
                return response.json();
            })
            .then((data) => {
                loadingEl.remove();
                
                // Membuat root UL
                const ul = document.createElement("ul");
                ul.appendChild(buildNode(data));
                
                chartContainer.appendChild(ul);
            })
            .catch((err) => {
                loadingEl.innerHTML = "Gagal memuat data: " + err.message;
                loadingEl.style.color = "red";
            });
        });
    </script>
</body>
</html>