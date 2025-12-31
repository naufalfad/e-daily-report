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

    // Pagination Elements
    const btnPrev = document.getElementById('prev-page');
    const btnNext = document.getElementById('next-page');
    const paginationInfo = document.getElementById('pagination-info');
    const paginationNumbers = document.getElementById('pagination-numbers');

    // Statistik Elements
    const statTotal = document.getElementById('stat-total');
    const statAvg = document.getElementById('stat-avg');
    const statSangatBaik = document.getElementById('stat-sb');
    const statPembinaan = document.getElementById('stat-pembinaan');

    const exportBtn = document.getElementById('export-pdf');

    // State Lokal
    let currentPage = 1;
    let searchTimeout = null;

    // === 1. EVENT HANDLERS ===

    if (btnFilter) {
        btnFilter.addEventListener('click', () => {
            fetchData(1); 
        });
    }

    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            const m = filterMonth ? filterMonth.value : '';
            const y = filterYear ? filterYear.value : '';
            
            const params = new URLSearchParams({
                month: m,
                year: y
            });
            
            window.location.href = `/penilai/skoring/export-pdf?${params.toString()}`;
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchData(1); 
            }, 500);
        });
    }

    // Pagination Event Delegation
    if (paginationNumbers) {
        paginationNumbers.addEventListener('click', (e) => {
            const target = e.target.closest('.js-page-link');
            if (target) {
                e.preventDefault();
                const page = parseInt(target.dataset.page);
                if (page && page !== currentPage) {
                    fetchData(page);
                    document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (currentPage > 1) {
                fetchData(currentPage - 1);
                document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }
    
    if (btnNext) {
        btnNext.addEventListener('click', () => {
            if (!btnNext.disabled) {
                fetchData(currentPage + 1);
                document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // === 2. FETCH DATA FUNCTION (FIXED) ===
    async function fetchData(page = 1) {
        try {
            if (loadingState) loadingState.classList.remove('hidden');
            if (tableBody) tableBody.innerHTML = '';
            if (emptyState) emptyState.classList.add('hidden');

            const month = filterMonth ? filterMonth.value : '';
            const year = filterYear ? filterYear.value : '';
            const search = searchInput ? searchInput.value : '';

            const params = new URLSearchParams({
                month: month,
                year: year,
                search: search,
                page: page,
                per_page: 10,
                t: new Date().getTime() 
            });

            const url = `/api/skoring-kinerja?${params.toString()}`;

            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }

            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

            const result = await response.json();

            // [FIX BUG]: Akses langsung ke properti root, bukan via .data
            // Structure: { message: "...", table_data: {...}, global_stats: {...} }
            
            const tableData = result.table_data; // SEBELUMNYA result.data.table_data (Salah)
            const globalStats = result.global_stats; // SEBELUMNYA result.data.global_stats (Salah)

            if (!tableData) {
                throw new Error("Format respons server tidak valid.");
            }

            const rows = tableData.data || [];
            currentPage = tableData.current_page || 1;

            renderTable(rows);
            updatePagination(tableData);
            updateDashboardStats(globalStats);

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
            const avatar = pegawai.foto || '/assets/man.png';
            const badgeColor = pegawai.badge_color || getBadgeColor(pegawai.predikat);

            const row = `
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition duration-150 group">
                    <td class="py-4 px-6 text-left whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="mr-3 shrink-0 h-10 w-10">
                                <img class="h-full w-full rounded-full border border-gray-200 object-cover bg-gray-100" 
                                     src="${avatar}" 
                                     onerror="this.src='/assets/man.png'"/>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-800 text-sm group-hover:text-indigo-600 transition-colors truncate" title="${pegawai.nama}">${pegawai.nama || 'Tanpa Nama'}</div>
                                <div class="text-xs text-gray-500 mt-0.5 truncate">${pegawai.nip || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-left">
                        <div class="max-w-xs">
                            <p class="text-sm font-medium text-gray-700 truncate" title="${pegawai.unit_kerja}">${pegawai.unit_kerja || '-'}</p>
                            <p class="text-[11px] text-gray-400 mt-1 truncate" title="${pegawai.jabatan}">${pegawai.jabatan || '-'}</p>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                         <div class="flex flex-col items-center">
                             <span class="font-bold text-gray-700 text-base">${pegawai.realisasi}</span>
                             <span class="text-[10px] text-gray-400">dari ${pegawai.target} Laporan</span>
                         </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden">
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

    // === 4. UPDATE STATS ===
    function updateDashboardStats(stats) {
        if (stats) {
            if (statTotal) statTotal.innerText = stats.total_bawahan || 0;
            if (statAvg) statAvg.innerText = (stats.avg_skor || 0) + '%';
            if (statSangatBaik) statSangatBaik.innerText = stats.sangat_baik || 0;
            if (statPembinaan) statPembinaan.innerText = stats.pembinaan || 0;
        }
    }

    // === 5. PAGINATION ===
    function updatePagination(paginationData) {
        if (!paginationInfo || !paginationNumbers) return;

        const { current_page, last_page, from, to, total, prev_page_url, next_page_url } = paginationData;

        paginationInfo.textContent = `Menampilkan ${from || 0}-${to || 0} dari ${total || 0} data`;

        if (btnPrev) {
            btnPrev.disabled = !prev_page_url;
            btnPrev.classList.toggle('opacity-50', !prev_page_url);
            btnPrev.classList.toggle('cursor-not-allowed', !prev_page_url);
        }
        if (btnNext) {
            btnNext.disabled = !next_page_url;
            btnNext.classList.toggle('opacity-50', !next_page_url);
            btnNext.classList.toggle('cursor-not-allowed', !next_page_url);
        }

        renderPaginationLinks(current_page, last_page);
    }

    function renderPaginationLinks(current, lastPage) {
        paginationNumbers.innerHTML = '';

        const createBtn = (page, isActive) => {
            const btn = document.createElement('button');
            btn.className = isActive 
                ? `w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-600 text-white text-sm font-medium shadow-sm transition-all`
                : `w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition-all js-page-link`;
            btn.textContent = page;
            if(!isActive) btn.dataset.page = page;
            return btn;
        };

        const createDots = () => {
            const span = document.createElement('span');
            span.className = "px-1 text-gray-400 text-sm";
            span.textContent = "...";
            return span;
        };

        if (lastPage <= 7) {
            for (let i = 1; i <= lastPage; i++) paginationNumbers.appendChild(createBtn(i, i === current));
        } else {
            paginationNumbers.appendChild(createBtn(1, 1 === current));
            if (current > 4) paginationNumbers.appendChild(createDots());

            let start = Math.max(2, current - 1);
            let end = Math.min(lastPage - 1, current + 1);

            if (current <= 4) end = 5;
            if (current >= lastPage - 3) start = lastPage - 4;

            for (let i = start; i <= end; i++) paginationNumbers.appendChild(createBtn(i, i === current));

            if (current < lastPage - 3) paginationNumbers.appendChild(createDots());
            paginationNumbers.appendChild(createBtn(lastPage, lastPage === current));
        }
    }

    function getBadgeColor(predikat) {
        switch (predikat) {
            case 'Sangat Baik': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            case 'Baik': return 'bg-blue-50 text-blue-700 border-blue-200';
            case 'Cukup': return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            default: return 'bg-red-50 text-red-700 border-red-200';
        }
    }

    // Initialize
    const now = new Date();
    if(filterMonth && !filterMonth.value) filterMonth.value = now.getMonth() + 1;
    if(filterYear && !filterYear.value) filterYear.value = now.getFullYear();

    fetchData(1);
});