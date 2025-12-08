document.addEventListener('DOMContentLoaded', function () {
    // === 1. DEKLARASI ELEMEN DOM ===
    const filterForm = document.getElementById('filterForm');
    const skoringTableBody = document.getElementById('skoringTableBody');
    const monthSelect = document.getElementById('month');
    const yearSelect = document.getElementById('year');
    const searchInput = document.getElementById('search-input'); // Tambah: Input Pencarian
    const exportPdfButton = document.getElementById('export-pdf'); // Tambah: Tombol Export

    // DOM Statistik
    const statTotalBidang = document.getElementById('stat-total-bidang');
    const statAvg = document.getElementById('stat-avg');
    const statSB = document.getElementById('stat-sb');
    const statPembinaan = document.getElementById('stat-pembinaan');
    
    // === 2. DEKLARASI GLOBAL / KONSTANTA ===
    const API_ENDPOINT = '/api/kadis/skoring-bidang';
    let currentSkoringData = []; // Data yang diambil dari API (digunakan untuk Search)

    /**
     * @param {string} predikat
     * @returns {string} HTML Badge
     */
    function getPredicateBadge(predikat) {
        const colors = {
            "Sangat Baik": "bg-green-100 text-green-700 border-green-300",
            "Baik": "bg-blue-100 text-blue-700 border-blue-300",
            "Cukup": "bg-yellow-100 text-yellow-700 border-yellow-300",
            "Kurang": "bg-red-100 text-red-700 border-red-300",
        };

        const safePredikat = predikat ? predikat.trim() : 'N/A';

        return `
            <span class="px-3 py-1 rounded-full text-xs font-semibold border ${colors[safePredikat] ?? 'bg-gray-100 text-gray-700 border-gray-300'}">
                ${safePredikat}
            </span>
        `;
    }

    // === 3. FUNGSI UTAMA: FETCH DATA ===
    async function fetchSkoringData(month, year) {
        skoringTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-gray-500">Memuat data skoring...</td></tr>`;
        currentSkoringData = []; // Reset data
        updateStatistics(null); // Reset statistik

        // Buat Query String
        const params = new URLSearchParams({
            month: month,
            year: year
        });
        const url = `${API_ENDPOINT}?${params.toString()}`;

        try {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                 throw new Error('Token otentikasi tidak ditemukan. Harap login kembali.');
            }
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            const result = await response.json();

            if (!response.ok || result.status === 'error') {
                const errorMessage = result.message || 'Gagal mengambil data skoring dari server.';
                throw new Error(errorMessage);
            }

            // Simpan data ke variabel global, lalu render
            currentSkoringData = result.data;
            renderTable(currentSkoringData);
            updateStatistics(currentSkoringData);

        } catch (error) {
            console.error('Fetch Error:', error);
            skoringTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-red-500 py-4">Error: ${error.message}</td></tr>`;
            updateStatistics(null);
        }
    }

    // === 4. FUNGSI MERENDER DATA KE TABEL ===
    /**
     * @param {Array<Object>} data - Data skoring yang akan dirender
     */
    function renderTable(data) {
        skoringTableBody.innerHTML = ''; // Clear existing rows
        if (data.length === 0) {
            skoringTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-gray-500">Tidak ada data skoring untuk periode ini.</td></tr>`;
            return;
        }

        const fragment = document.createDocumentFragment();

        data.forEach((item, index) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 border-b border-gray-100'; // Tambah hover dan pemisah baris

            const persentase = item.persentase ? parseFloat(item.persentase).toFixed(2) : '0.00';

            row.innerHTML = `
                <td class="py-3 px-6 text-center">${index + 1}</td>
                <td class="py-3 px-6 font-medium">${item.nama_bidang || 'N/A'}</td>
                <td class="py-3 px-6">${item.nama_kabid || 'N/A'}</td>
                <td class="py-3 px-6 text-center">${item.total_submitted || 0}</td>
                <td class="py-3 px-6 text-center">${item.total_approved || 0}</td>
                <td class="py-3 px-6 text-center">
                    <span class="font-semibold">${persentase}%</span>
                </td>
                <td class="py-3 px-6 text-center">${getPredicateBadge(item.predikat)}</td>
            `;
            fragment.appendChild(row);
        });
        skoringTableBody.appendChild(fragment);
    }
    
    // === 5. FUNGSI UPDATE STATISTIK CARD ===
    /**
     * @param {Array<Object>} data - Data skoring
     */
    function updateStatistics(data) {
        if (!data || data.length === 0) {
            statTotalBidang.textContent = '0';
            statAvg.textContent = '0%';
            statSB.textContent = '0';
            statPembinaan.textContent = '0';
            return;
        }

        const totalBidang = data.length;
        const totalPersentase = data.reduce((sum, item) => sum + (parseFloat(item.persentase) || 0), 0);
        const avgPersentase = (totalPersentase / totalBidang).toFixed(2);

        const bidangSangatBaik = data.filter(item => item.predikat === 'Sangat Baik').length;
        const bidangPerluPembinaan = data.filter(item => item.predikat === 'Cukup' || item.predikat === 'Kurang').length;

        statTotalBidang.textContent = totalBidang;
        statAvg.textContent = `${avgPersentase}%`;
        statSB.textContent = bidangSangatBaik;
        statPembinaan.textContent = bidangPerluPembinaan;
    }

    // === 6. FUNGSI PENCARIAN (SEARCH) ===
    searchInput.addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        
        if (searchTerm === "") {
            renderTable(currentSkoringData); // Tampilkan semua data jika kosong
            return;
        }
        
        const filteredData = currentSkoringData.filter(item => 
            (item.nama_bidang && item.nama_bidang.toLowerCase().includes(searchTerm)) ||
            (item.nama_kabid && item.nama_kabid.toLowerCase().includes(searchTerm))
        );
        
        renderTable(filteredData);
    });
    
    // === 7. EVENT LISTENER FILTER FORM ===
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const month = monthSelect.value;
        const year = yearSelect.value;
        // Reset search saat filter diubah
        searchInput.value = '';
        fetchSkoringData(month, year);
    });
    
    // === 8. EVENT LISTENER EXPORT PDF (Implementasi Dummy) ===
    exportPdfButton.addEventListener('click', function() {
        // Ambil filter saat ini
        const month = monthSelect.value;
        const year = yearSelect.value;
        
        // Buat URL export (misalnya)
        const exportUrl = `${API_ENDPOINT}/export-pdf?month=${month}&year=${year}`;
        
        // Asumsi: Server akan merespons dengan file PDF
        // Implementasi ini hanya untuk memastikan tombol berfungsi
        alert(`Fungsi Export PDF dipanggil untuk Bulan: ${month} dan Tahun: ${year}. \n\nEndpoint yang dipanggil: ${exportUrl}`);
        // window.open(exportUrl, '_blank'); // Uncomment ini jika sudah ada endpoint
    });

    // === 9. INITIAL LOAD ===
    const initialMonth = monthSelect.value;
    const initialYear = yearSelect.value;
    fetchSkoringData(initialMonth, initialYear);
});