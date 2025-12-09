import { showToast } from '../../global/notification';

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('table-body');
    const searchInput = document.getElementById('search-input');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    // Statistik Elements
    const statTotal = document.getElementById('stat-total');
    const statAvg = document.getElementById('stat-avg');
    const statSangatBaik = document.getElementById('stat-sb');
    const statPembinaan = document.getElementById('stat-pembinaan');

    const exportBtn = document.getElementById('export-pdf');

    let subordinateData = [];

    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // Kita gunakan window.location karena ini request file (download)
            // Route ini sudah didefinisikan di web.php dan menggunakan Session Auth
            window.location.href = '/penilai/skoring/export-pdf';
        });
    }

    // --- 1. FETCH DATA ---
    async function fetchData() {
        try {
            loadingState.classList.remove('hidden');
            tableBody.innerHTML = '';
            emptyState.classList.add('hidden');

            // [PERBAIKAN UTAMA] Gunakan URL API (/api/...), bukan URL Web (/penilai/...)
            const url = `/api/skoring-kinerja?t=${new Date().getTime()}`;
            
            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`, // Jangan lupa Token!
                    'Accept': 'application/json'
                }
            });

            // Cek jika session habis / unauthenticated
            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }

            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

            const result = await response.json();

            // Mapping data dari response controller
            // Controller mengembalikan: { data: [...] }
            if (result.data && Array.isArray(result.data)) {
                subordinateData = result.data;
            } else {
                subordinateData = [];
            }

            calculateStats(subordinateData);
            renderTable(subordinateData);

        } catch (error) {
            console.error("Gagal memuat data skoring:", error);
            showToast('Gagal memuat data kinerja.', 'error');
            emptyState.classList.remove('hidden');
        } finally {
            loadingState.classList.add('hidden');
        }
    }

    // --- 2. RENDER TABEL ---
    function renderTable(data) {
        tableBody.innerHTML = '';

        if (!data || data.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        }
        emptyState.classList.add('hidden');

        data.forEach(pegawai => {
            // Tentukan Predikat & Warna Badge
            // Logika Sederhana: >90 Sangat Baik, >75 Baik, >60 Cukup, Sisanya Kurang
            let predikat = 'Kurang';
            if (pegawai.capaian >= 90) predikat = 'Sangat Baik';
            else if (pegawai.capaian >= 75) predikat = 'Baik';
            else if (pegawai.capaian >= 60) predikat = 'Cukup';
            
            const badgeColor = getBadgeColor(predikat);
            const avatar = pegawai.foto || '/assets/icon/avatar.png';

            const row = `
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition duration-150">
                    <td class="py-4 px-6 text-left whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="mr-3">
                                <img class="w-10 h-10 rounded-full border border-slate-200 object-cover bg-slate-100" 
                                     src="${avatar}" 
                                     onerror="this.src='/assets/icon/avatar.png'"/>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-sm">${pegawai.nama || 'Tanpa Nama'}</div>
                                <div class="text-xs text-slate-500 mt-0.5">${pegawai.nip || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-left">
                        <div class="max-w-xs">
                            <p class="text-sm font-medium text-slate-700 truncate" title="${pegawai.rhk}">${pegawai.rhk || '-'}</p>
                            <p class="text-[11px] text-slate-400 mt-1">Total LKH yang dikirim: ${pegawai.target} ${pegawai.satuan}</p>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                         <span class="font-bold text-[#155FA6] text-base">${pegawai.realisasi}</span>
                         <span class="text-xs text-slate-400 block">${pegawai.satuan}</span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full bg-slate-100 rounded-full h-2 w-24 overflow-hidden">
                                <div class="bg-[#1C7C54] h-2 rounded-full transition-all duration-500" 
                                     style="width: ${Math.min(pegawai.capaian, 100)}%"></div>
                            </div>
                            <span class="text-xs font-bold text-[#1C7C54]">${pegawai.capaian}%</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="${badgeColor} py-1 px-3 rounded-full text-[11px] font-bold tracking-wide border inline-block">
                            ${predikat}
                        </span>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    // --- 3. SEARCH ---
    searchInput.addEventListener('input', (e) => {
        const keyword = e.target.value.toLowerCase();
        const filtered = subordinateData.filter(p => {
            return (p.nama && p.nama.toLowerCase().includes(keyword)) ||
                   (p.nip && p.nip.toLowerCase().includes(keyword));
        });
        renderTable(filtered);
    });

    // --- 4. STATS ---
    function calculateStats(data) {
        if (statTotal) statTotal.innerText = data.length;

        if (statAvg) {
            if (data.length > 0) {
                const sum = data.reduce((acc, curr) => acc + parseFloat(curr.capaian || 0), 0);
                statAvg.innerText = (sum / data.length).toFixed(1) + '%';
            } else {
                statAvg.innerText = "0%";
            }
        }

        // Hitung manual berdasarkan logika predikat di renderTable
        let sb = 0;
        let pembinaan = 0;

        data.forEach(p => {
            const score = parseFloat(p.capaian || 0);
            if (score >= 90) sb++;
            if (score < 60) pembinaan++;
        });

        if (statSangatBaik) statSangatBaik.innerText = sb;
        if (statPembinaan) statPembinaan.innerText = pembinaan;
    }

    function getBadgeColor(predikat) {
        switch (predikat) {
            case 'Sangat Baik': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            case 'Baik': return 'bg-blue-50 text-blue-700 border-blue-200';
            case 'Cukup': return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            default: return 'bg-red-50 text-red-700 border-red-200';
        }
    }

    // Jalankan
    fetchData();
});