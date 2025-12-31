import { showToast } from '../../global/notification';

document.addEventListener('DOMContentLoaded', () => {
    // === DOM ELEMENTS ===
    const tableBody = document.getElementById('table-body');
    const searchInput = document.getElementById('search-input');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    // Filter Elements
    const filterMonth = document.getElementById('filter-month');
    const filterYear = document.getElementById('filter-year');
    const btnFilter = document.getElementById('btn-filter');

    // Statistik Elements (Dashboard Cards)
    const statTotal = document.getElementById('stat-total');
    const statAvg = document.getElementById('stat-avg');
    const statSangatBaik = document.getElementById('stat-sb');
    const statPembinaan = document.getElementById('stat-pembinaan');

    const exportBtn = document.getElementById('export-pdf');

    // State Lokal untuk data tabel (agar search tidak perlu hit API lagi)
    let subordinateData = [];

    // === 1. EVENT HANDLERS (Filter & Export) ===

    // Tombol Filter (Reload Data)
    if (btnFilter) {
        btnFilter.addEventListener('click', () => {
            fetchData();
        });
    }

    // Tombol Export PDF (Dinamis)
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            const m = filterMonth ? filterMonth.value : '';
            const y = filterYear ? filterYear.value : '';
            
            // Redirect ke route export dengan query params
            // Backend Controller akan menangkap 'month' dan 'year'
            const params = new URLSearchParams({
                month: m,
                year: y
            });
            
            window.location.href = `/penilai/skoring/export-pdf?${params.toString()}`;
        });
    }

    // Search (Client-side filtering for speed)
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const keyword = e.target.value.toLowerCase();
            
            // Filter array lokal
            const filtered = subordinateData.filter(p => {
                const nama = p.nama ? p.nama.toLowerCase() : '';
                const nip = p.nip ? p.nip.toLowerCase() : '';
                return nama.includes(keyword) || nip.includes(keyword);
            });

            renderTable(filtered);
            // Opsional: Hitung ulang statistik berdasarkan hasil pencarian
            calculateStats(filtered); 
        });
    }

    // === 2. FETCH DATA FUNCTION ===
    async function fetchData() {
        try {
            // UI State: Loading
            if (loadingState) loadingState.classList.remove('hidden');
            if (tableBody) tableBody.innerHTML = '';
            if (emptyState) emptyState.classList.add('hidden');

            // Ambil Nilai Filter
            const month = filterMonth ? filterMonth.value : '';
            const year = filterYear ? filterYear.value : '';

            // Bangun URL API
            const params = new URLSearchParams({
                month: month,
                year: year,
                t: new Date().getTime() // Anti-cache
            });

            const url = `/api/skoring-kinerja?${params.toString()}`;

            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            // Handle Unauthorized
            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }

            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

            const result = await response.json();

            // Mapping Data
            // Backend (SkoringService) mengembalikan Collection, jadi di JSON dia ada di result.data
            if (result.data && Array.isArray(result.data)) {
                subordinateData = result.data;
            } else {
                subordinateData = [];
            }

            // Update UI
            renderTable(subordinateData);
            calculateStats(subordinateData);

        } catch (error) {
            console.error("Gagal memuat data skoring:", error);
            showToast('Gagal memuat data kinerja.', 'error');
            if (emptyState) emptyState.classList.remove('hidden');
        } finally {
            if (loadingState) loadingState.classList.add('hidden');
        }
    }

    // === 3. RENDER TABLE ===
    function renderTable(data) {
        if (!tableBody) return;
        tableBody.innerHTML = '';

        if (!data || data.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }
        if (emptyState) emptyState.classList.add('hidden');

        data.forEach(pegawai => {
            // Data sudah matang dari Backend (termasuk badge_color, predikat, capaian)
            // Kita tinggal tampilkan saja. Tidak perlu logic if-else yang rumit di sini.
            
            const avatar = pegawai.foto || '/assets/man.png';
            
            // Fallback jika backend belum kirim badge_color (untuk keamanan)
            const badgeColor = pegawai.badge_color || getBadgeColor(pegawai.predikat);

            const row = `
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition duration-150 group">
                    <td class="py-4 px-6 text-left whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="mr-3 shrink-0 h-10 w-10">
                                <img class="w-10 h-10 rounded-full border border-slate-200 object-cover bg-slate-100" 
                                     src="${avatar}" 
                                     onerror="this.src='/assets/man.png'"/>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition-colors">${pegawai.nama || 'Tanpa Nama'}</div>
                                <div class="text-xs text-slate-500 mt-0.5">${pegawai.nip || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-left">
                        <div class="max-w-xs">
                            <p class="text-sm font-medium text-slate-700 truncate" title="${pegawai.unit_kerja}">${pegawai.unit_kerja || '-'}</p>
                            <p class="text-[11px] text-slate-400 mt-1">${pegawai.jabatan || '-'}</p>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                         <div class="flex flex-col items-center">
                             <span class="font-bold text-slate-700 text-base">${pegawai.realisasi}</span>
                             <span class="text-[10px] text-slate-400">dari ${pegawai.target} Laporan</span>
                         </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-[#1C7C54] h-1.5 rounded-full transition-all duration-500" 
                                     style="width: ${Math.min(pegawai.capaian, 100)}%"></div>
                            </div>
                            <span class="text-xs font-bold text-[#1C7C54]">${pegawai.capaian}%</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="${badgeColor} py-1 px-3 rounded-full text-[10px] font-bold tracking-wide border inline-block uppercase shadow-sm">
                            ${pegawai.predikat}
                        </span>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    // === 4. HELPER: STATS CALCULATOR ===
    // Menghitung statistik sederhana di Client Side agar responsif
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

        let sb = 0;
        let pembinaan = 0;

        data.forEach(p => {
            // Gunakan data predikat yang dikirim backend jika ada
            if (p.predikat === 'Sangat Baik') sb++;
            // Atau logic manual untuk pembinaan
            const score = parseFloat(p.capaian || 0);
            if (score < 60) pembinaan++;
        });

        if (statSangatBaik) statSangatBaik.innerText = sb;
        if (statPembinaan) statPembinaan.innerText = pembinaan;
    }

    // Helper Fallback Warna (Jika backend belum kirim)
    function getBadgeColor(predikat) {
        switch (predikat) {
            case 'Sangat Baik': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            case 'Baik': return 'bg-blue-50 text-blue-700 border-blue-200';
            case 'Cukup': return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            default: return 'bg-red-50 text-red-700 border-red-200';
        }
    }

    // Initialize (Load Data Pertama Kali)
    // Set default value dropdown ke bulan/tahun sekarang agar sesuai dengan default controller
    const now = new Date();
    if(filterMonth && !filterMonth.value) filterMonth.value = now.getMonth() + 1;
    if(filterYear && !filterYear.value) filterYear.value = now.getFullYear();

    fetchData();
});