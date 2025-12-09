import { showToast } from '../../global/notification';

document.addEventListener('DOMContentLoaded', function () {
    // === 1. DEKLARASI ELEMEN DOM ===
    const filterForm = document.getElementById('filterForm');
    const skoringTableBody = document.getElementById('skoringTableBody');
    // Perhatikan nama variabel ini:
    const monthSelect = document.getElementById('month'); 
    const yearSelect = document.getElementById('year');
    const searchInput = document.getElementById('search-input');
    const exportBtn = document.getElementById('export-pdf');

    // DOM Statistik
    const statTotalBidang = document.getElementById('stat-total-bidang');
    const statAvg = document.getElementById('stat-avg');
    const statSB = document.getElementById('stat-sb');
    const statPembinaan = document.getElementById('stat-pembinaan');
    
    // === 2. DEKLARASI GLOBAL / KONSTANTA ===
    const API_ENDPOINT = '/api/kadis/skoring-bidang';
    let currentSkoringData = []; // Data lokal untuk fitur Search

    // === 3. HELPER: BADGE PREDIKAT ===
    function getPredicateBadge(predikat) {
        const colors = {
            "Sangat Baik": "bg-green-100 text-green-700 border-green-300",
            "Baik": "bg-blue-100 text-blue-700 border-blue-300",
            "Cukup": "bg-yellow-100 text-yellow-700 border-yellow-300",
            "Kurang": "bg-red-100 text-red-700 border-red-300",
        };

        const safePredikat = predikat ? predikat.trim() : 'N/A';
        const colorClass = colors[safePredikat] ?? 'bg-gray-100 text-gray-700 border-gray-300';

        return `
            <span class="px-3 py-1 rounded-full text-xs font-semibold border ${colorClass}">
                ${safePredikat}
            </span>
        `;
    }

    // === 4. EVENT LISTENER: EXPORT PDF (FIXED) ===
    if (exportBtn) {
        exportBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            // [FIX] Menggunakan variabel yang benar (monthSelect & yearSelect)
            const m = monthSelect ? monthSelect.value : '';
            const y = yearSelect ? yearSelect.value : '';

            // Bangun URL dengan Query Params
            const url = `/kadis/skoring-bidang/export-pdf?month=${m}&year=${y}`;
            
            // Redirect window untuk download file
            window.location.href = url;
        });
    }

    // === 5. FUNGSI UTAMA: FETCH DATA ===
    async function fetchSkoringData(month, year) {
        skoringTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-gray-500">Memuat data skoring...</td></tr>`;
        currentSkoringData = []; 
        updateStatistics(null); 

        // Buat Query String
        const params = new URLSearchParams({
            month: month,
            year: year
        });
        const url = `${API_ENDPOINT}?${params.toString()}`;

        try {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                 // Redirect jika token habis
                 window.location.href = '/login';
                 return;
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
                throw new Error(result.message || 'Gagal mengambil data skoring.');
            }

            // Simpan data & render
            currentSkoringData = result.data;
            renderTable(currentSkoringData);
            updateStatistics(currentSkoringData);

        } catch (error) {
            console.error('Fetch Error:', error);
            skoringTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-red-500 py-4">Gagal memuat data: ${error.message}</td></tr>`;
            showToast(error.message, 'error');
        }
    }

    // === 6. FUNGSI RENDER TABEL ===
    function renderTable(data) {
        skoringTableBody.innerHTML = ''; 
        if (!data || data.length === 0) {
            skoringTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-gray-500 italic">Tidak ada data skoring untuk periode ini.</td></tr>`;
            return;
        }

        const fragment = document.createDocumentFragment();

        data.forEach((item, index) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 border-b border-gray-100 transition duration-150';

            const persentase = item.persentase ? parseFloat(item.persentase).toFixed(2) : '0.00';

            row.innerHTML = `
                <td class="py-4 px-6 text-center text-gray-500">${index + 1}</td>
                <td class="py-4 px-6 font-medium text-gray-800">${item.nama_bidang || '-'}</td>
                <td class="py-4 px-6 text-gray-600">${item.nama_kabid || '-'}</td>
                <td class="py-4 px-6 text-center text-blue-600 font-medium">${item.total_approved || 0} / ${item.total_submitted || 0}</td>
                <td class="py-4 px-6 text-center">
                    <span class="font-bold text-gray-800">${persentase}%</span>
                </td>
                <td class="py-4 px-6 text-center">${getPredicateBadge(item.predikat)}</td>
            `;
            fragment.appendChild(row);
        });
        skoringTableBody.appendChild(fragment);
    }
    
    // === 7. FUNGSI UPDATE STATISTIK ===
    function updateStatistics(data) {
        if (!data || data.length === 0) {
            if(statTotalBidang) statTotalBidang.innerText = '0';
            if(statAvg) statAvg.innerText = '0%';
            if(statSB) statSB.innerText = '0';
            if(statPembinaan) statPembinaan.innerText = '0';
            return;
        }

        const totalBidang = data.length;
        const totalPersentase = data.reduce((sum, item) => sum + (parseFloat(item.persentase) || 0), 0);
        const avgPersentase = totalBidang > 0 ? (totalPersentase / totalBidang).toFixed(0) : 0;

        const bidangSangatBaik = data.filter(item => item.predikat === 'Sangat Baik').length;
        // Asumsi pembinaan adalah Cukup & Kurang (sesuai logika controller)
        const bidangPerluPembinaan = data.filter(item => ['Cukup', 'Kurang'].includes(item.predikat)).length;

        if(statTotalBidang) statTotalBidang.innerText = totalBidang;
        if(statAvg) statAvg.innerText = `${avgPersentase}%`;
        if(statSB) statSB.innerText = bidangSangatBaik;
        if(statPembinaan) statPembinaan.innerText = bidangPerluPembinaan;
    }

    // === 8. FUNGSI PENCARIAN (SEARCH) ===
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            if (searchTerm === "") {
                renderTable(currentSkoringData);
                return;
            }
            
            const filteredData = currentSkoringData.filter(item => 
                (item.nama_bidang && item.nama_bidang.toLowerCase().includes(searchTerm)) ||
                (item.nama_kabid && item.nama_kabid.toLowerCase().includes(searchTerm))
            );
            
            renderTable(filteredData);
        });
    }
    
    // === 9. EVENT LISTENER FILTER FORM ===
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const month = monthSelect.value;
            const year = yearSelect.value;
            
            if(searchInput) searchInput.value = ''; // Reset search
            fetchSkoringData(month, year);
        });
    }

    // === 10. INITIAL LOAD ===
    // Load data bulan/tahun saat ini (default value dari Blade)
    if (monthSelect && yearSelect) {
        fetchSkoringData(monthSelect.value, yearSelect.value);
    }
});