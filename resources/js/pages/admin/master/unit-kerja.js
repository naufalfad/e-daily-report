import { showToast } from '../../../global/notification';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    // === CONFIGURATION ===
    const API_URL = '/api/admin/master/unit-kerja';
    const TABLE_BODY = document.getElementById('table-body');
    const SEARCH_INPUT = document.getElementById('searchInput');
    const LOADING_STATE = document.getElementById('loading-state');
    const EMPTY_STATE = document.getElementById('empty-state');
    const PAGINATION_INFO = document.getElementById('pagination-info');
    const PAGINATION_NUMBERS = document.getElementById('pagination-numbers');
    const BTN_PREV = document.getElementById('prev-page');
    const BTN_NEXT = document.getElementById('next-page');

    let currentPage = 1;
    let searchTimeout = null;

    // === 1. INITIALIZATION & LISTENERS ===
    fetchData(1);

    if (SEARCH_INPUT) {
        SEARCH_INPUT.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchData(1), 500); // Debounce
        });
    }

    // Pagination Click Listener (Delegation)
    if (PAGINATION_NUMBERS) {
        PAGINATION_NUMBERS.addEventListener('click', (e) => {
            const target = e.target.closest('.js-page-link');
            if (target) {
                e.preventDefault();
                const page = parseInt(target.dataset.page);
                if (page && page !== currentPage) fetchData(page);
            }
        });
    }

    if (BTN_PREV) BTN_PREV.addEventListener('click', () => { if (currentPage > 1) fetchData(currentPage - 1); });
    if (BTN_NEXT) BTN_NEXT.addEventListener('click', () => { if (!BTN_NEXT.disabled) fetchData(currentPage + 1); });

    // === 2. CORE FUNCTIONS ===

    async function fetchData(page = 1) {
        try {
            showLoading(true);
            const search = SEARCH_INPUT ? SEARCH_INPUT.value : '';
            
            const params = new URLSearchParams({
                page: page,
                search: search,
                per_page: 10,
                t: new Date().getTime()
            });

            const response = await fetch(`${API_URL}?${params.toString()}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.status === 401) return window.location.href = '/login';
            if (!response.ok) throw new Error('Gagal mengambil data');

            const result = await response.json();
            
            // Laravel Pagination structure
            const rows = result.data || [];
            currentPage = result.current_page;

            renderTable(rows, result.from);
            renderPagination(result);

        } catch (error) {
            console.error(error);
            showToast('Gagal memuat data.', 'error');
        } finally {
            showLoading(false);
        }
    }

    function renderTable(data, fromIndex) {
        if (!TABLE_BODY) return;
        TABLE_BODY.innerHTML = '';

        if (data.length === 0) {
            EMPTY_STATE.classList.remove('hidden');
            return;
        }
        EMPTY_STATE.classList.add('hidden');

        data.forEach((item, index) => {
            const rowNum = (fromIndex || 1) + index;
            
            // Render Kolom Khusus Unit Kerja (Struktur & Personil)
            const html = `
                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100">
                    <td class="px-6 py-4 text-center font-medium text-slate-500">${rowNum}</td>
                    <td class="px-6 py-4 font-semibold text-slate-700">${item.nama_unit}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                ${item.bidang_count || 0} Bidang
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                ${item.jabatan_count || 0} Jabatan
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-100">
                            <i class="fas fa-users text-[10px]"></i> ${item.users_count || 0} Pegawai
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='window.openModal("edit", ${JSON.stringify(item)})' 
                                class="p-2 rounded-lg border border-transparent hover:border-amber-200 hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-all" title="Edit">
                                <i class="fas fa-pen-to-square"></i>
                            </button>
                            <button onclick="window.deleteData(${item.id})" 
                                class="p-2 rounded-lg border border-transparent hover:border-red-200 hover:bg-red-50 text-slate-400 hover:text-red-600 transition-all" title="Hapus">
                                <i class="fas fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            TABLE_BODY.insertAdjacentHTML('beforeend', html);
        });
    }

    function renderPagination(meta) {
        if (!PAGINATION_INFO || !PAGINATION_NUMBERS) return;

        PAGINATION_INFO.textContent = `Menampilkan ${meta.from || 0}-${meta.to || 0} dari ${meta.total} data`;

        BTN_PREV.disabled = !meta.prev_page_url;
        BTN_PREV.classList.toggle('opacity-30', !meta.prev_page_url);
        
        BTN_NEXT.disabled = !meta.next_page_url;
        BTN_NEXT.classList.toggle('opacity-30', !meta.next_page_url);

        // Sliding Window Logic
        PAGINATION_NUMBERS.innerHTML = '';
        const current = meta.current_page;
        const last = meta.last_page;
        
        const createBtn = (p, active) => `
            <button data-page="${p}" class="js-page-link w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-all ${active ? 'bg-[#1C7C54] text-white shadow-sm' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}">
                ${p}
            </button>`;
        const dots = `<span class="px-1 text-slate-400 text-sm">...</span>`;

        if (last <= 7) {
            for (let i = 1; i <= last; i++) PAGINATION_NUMBERS.innerHTML += createBtn(i, i === current);
        } else {
            PAGINATION_NUMBERS.innerHTML += createBtn(1, 1 === current);
            if (current > 4) PAGINATION_NUMBERS.innerHTML += dots;
            
            let start = Math.max(2, current - 1);
            let end = Math.min(last - 1, current + 1);
            if (current <= 4) end = 5;
            if (current >= last - 3) start = last - 4;

            for (let i = start; i <= end; i++) PAGINATION_NUMBERS.innerHTML += createBtn(i, i === current);
            
            if (current < last - 3) PAGINATION_NUMBERS.innerHTML += dots;
            PAGINATION_NUMBERS.innerHTML += createBtn(last, last === current);
        }
    }

    function showLoading(show) {
        if (show) {
            LOADING_STATE.classList.remove('hidden');
            TABLE_BODY.innerHTML = '';
            EMPTY_STATE.classList.add('hidden');
        } else {
            LOADING_STATE.classList.add('hidden');
        }
    }

    // === 3. GLOBAL EXPORTS (Untuk akses dari onclick HTML) ===
    
    // Modal Logic
    window.openModal = function(type, data = null) {
        const form = document.getElementById('formUnit');
        form.reset();
        
        if (type === 'add') {
            document.getElementById('modalTitle').textContent = 'Tambah Unit Kerja Baru';
            document.getElementById('unit_id').value = '';
            document.getElementById('method').value = 'POST';
        } else {
            document.getElementById('modalTitle').textContent = 'Perbarui Unit Kerja';
            document.getElementById('unit_id').value = data.id;
            document.getElementById('nama_unit').value = data.nama_unit;
            document.getElementById('method').value = 'PUT';
        }

        document.getElementById('modalUnit').classList.remove('hidden');
        requestAnimationFrame(() => {
            document.getElementById('modalBackdrop').classList.remove('opacity-0');
            document.getElementById('modalPanel').classList.remove('opacity-0', 'scale-95');
        });
    };

    window.closeModal = function() {
        document.getElementById('modalBackdrop').classList.add('opacity-0');
        document.getElementById('modalPanel').classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            document.getElementById('modalUnit').classList.add('hidden');
        }, 200);
    };

    // Delete Logic
    window.deleteData = function(id) {
        Swal.fire({
            title: 'Hapus Unit ini?',
            text: "Data tidak bisa dikembalikan. Pastikan unit kosong.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(async res => {
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message);
                    Swal.fire('Terhapus!', json.message, 'success');
                    fetchData(currentPage);
                })
                .catch(err => {
                    Swal.fire('Gagal!', err.message, 'error');
                });
            }
        });
    };
});