import { showToast } from '../../../global/notification';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    // ================================================================
    // 1. CONFIGURATION & SELECTORS
    // ================================================================
    const API_URL = '/api/admin/master/jabatan';

    // Elements Table & UI
    const TABLE_BODY = document.getElementById('table-body');
    const SEARCH_INPUT = document.getElementById('searchInput');
    const LOADING_STATE = document.getElementById('loading-state');
    const EMPTY_STATE = document.getElementById('empty-state');

    // Elements Pagination
    const PAGINATION_INFO = document.getElementById('pagination-info');
    const PAGINATION_NUMBERS = document.getElementById('pagination-numbers');
    const BTN_PREV = document.getElementById('prev-page');
    const BTN_NEXT = document.getElementById('next-page');

    // Elements Form & Modal
    const FORM_MODAL = document.getElementById('formJabatan');

    // State Manager
    let currentPage = 1;
    let searchTimeout = null;
    let currentLimit = 10;
    let currentSort = 'created_at';
    let currentDir = 'desc';
    let currentDataLength = 0; // Menyimpan panjang data aktual di halaman aktif

    // ================================================================
    // 2. INITIALIZATION & EVENT LISTENERS
    // ================================================================

    fetchData(1);

    // Search Listener with Debounce
    if (SEARCH_INPUT) {
        SEARCH_INPUT.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchData(1), 500);
        });
    }

    // Pagination Listeners
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

    // Form Submit Listener (Wajib ditambahkan untuk memproses Add/Edit)
    if (FORM_MODAL) {
        FORM_MODAL.addEventListener('submit', handleFormSubmit);
    }

    // ================================================================
    // 3. CORE FUNCTIONS (CRUD & LOGIC)
    // ================================================================

    async function fetchData(page = 1) {
        try {
            showLoading(true);

            // Standardisasi parameter sesuai kontrak Paginator Back-End
            const params = new URLSearchParams({
                page: page,
                limit: currentLimit,
                sort: currentSort,
                dir: currentDir,
                t: new Date().getTime() // Anti-cache browser
            });

            const search = SEARCH_INPUT ? SEARCH_INPUT.value : '';
            if (search) params.append('search', search);

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

            currentPage = result.current_page || 1;
            currentDataLength = result.data ? result.data.length : 0;

            renderTable(result.data || [], result.from);
            renderPagination(result);

        } catch (error) {
            console.error(error);
            showToast('Gagal memuat data.', 'error');
        } finally {
            showLoading(false);
        }
    }

    // [CUSTOM] Render Table Khusus Jabatan
    function renderTable(data, fromIndex) {
        if (!TABLE_BODY) return;
        TABLE_BODY.innerHTML = '';

        if (!data || data.length === 0) {
            EMPTY_STATE.classList.remove('hidden');
            return;
        }
        EMPTY_STATE.classList.add('hidden');

        data.forEach((item, index) => {
            const rowNum = (fromIndex || 1) + index;
            const unitName = item.unit_kerja ? item.unit_kerja.nama_unit : '<span class="text-red-400 italic">Tanpa Unit</span>';

            const html = `
                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100">
                    <td class="px-6 py-4 text-center font-medium text-slate-500">${rowNum}</td>
                    <td class="px-6 py-4 font-semibold text-slate-700">${item.nama_jabatan}</td>
                    <td class="px-6 py-4 text-slate-600">
                        <div class="flex items-center gap-2"><i class="fas fa-building text-slate-400"></i> ${unitName}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-100">
                            <i class="fas fa-users text-[10px]"></i> ${item.users_count || 0} Pegawai
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='window.openModal("edit", ${JSON.stringify(item).replace(/'/g, "&#39;")})' 
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

    async function handleFormSubmit(e) {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(e.target);
        const id = document.getElementById('jabatan_id').value;
        const method = document.getElementById('method').value;
        let url = API_URL;

        if (method === 'PUT') {
            url += `/${id}`;
            formData.append('_method', 'PUT'); // Spoofing method for Laravel
        }

        try {
            const response = await fetch(url, {
                method: 'POST', // Use POST with _method spoofing for FormData compatibility
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    let errorMsg = Object.values(result.errors).flat().join('\n');
                    throw new Error(errorMsg);
                }
                throw new Error(result.message || 'Terjadi kesalahan.');
            }

            showToast(result.message, 'success');
            window.closeModal();
            fetchData(currentPage);

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: error.message,
                confirmButtonColor: '#ef4444'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    }

    function renderPagination(meta) {
        if (!PAGINATION_INFO || !PAGINATION_NUMBERS) return;
        PAGINATION_INFO.textContent = `Menampilkan ${meta.from || 0}-${meta.to || 0} dari ${meta.total || 0} data`;

        BTN_PREV.disabled = !meta.prev_page_url;
        BTN_PREV.classList.toggle('opacity-30', !meta.prev_page_url);

        BTN_NEXT.disabled = !meta.next_page_url;
        BTN_NEXT.classList.toggle('opacity-30', !meta.next_page_url);

        PAGINATION_NUMBERS.innerHTML = '';
        const current = meta.current_page;
        const last = meta.last_page;

        const createBtn = (p, active) => `<button data-page="${p}" class="js-page-link w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-all ${active ? 'bg-[#1C7C54] text-white shadow-sm' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}">${p}</button>`;
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

    // ================================================================
    // 4. GLOBAL EXPORTS FOR HTML ONCLICK
    // ================================================================

    window.openModal = function (type, data = null) {
        const form = document.getElementById('formJabatan');
        if (form) form.reset();

        if (type === 'add') {
            document.getElementById('modalTitle').textContent = 'Tambah Jabatan Baru';
            document.getElementById('jabatan_id').value = '';
            document.getElementById('method').value = 'POST';

            // Defensif: Support baik native maupun jQuery/Select2
            const unitEl = document.getElementById('unit_kerja_id');
            if (unitEl) unitEl.value = '';
            if (typeof $ !== 'undefined') $('#unit_kerja_id').trigger('change');

        } else {
            document.getElementById('modalTitle').textContent = 'Perbarui Jabatan';
            document.getElementById('jabatan_id').value = data.id;
            document.getElementById('nama_jabatan').value = data.nama_jabatan;
            document.getElementById('method').value = 'PUT';

            const unitEl = document.getElementById('unit_kerja_id');
            if (unitEl) unitEl.value = data.unit_kerja_id;
            if (typeof $ !== 'undefined') $('#unit_kerja_id').trigger('change');
        }

        // Animasi Pembukaan Modal
        document.getElementById('modalJabatan').classList.remove('hidden');
        requestAnimationFrame(() => {
            document.getElementById('modalBackdrop').classList.remove('opacity-0');
            document.getElementById('modalPanel').classList.remove('opacity-0', 'scale-95');
        });
    };

    window.closeModal = function () {
        document.getElementById('modalBackdrop').classList.add('opacity-0');
        document.getElementById('modalPanel').classList.add('opacity-0', 'scale-95');
        setTimeout(() => { document.getElementById('modalJabatan').classList.add('hidden'); }, 200);
    };

    window.deleteData = function (id) {
        Swal.fire({
            title: 'Hapus Jabatan?',
            text: "Pastikan tidak ada pegawai di jabatan ini.",
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
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(async res => {
                        const json = await res.json();
                        if (!res.ok) throw new Error(json.message || 'Gagal menghapus data');

                        Swal.fire('Terhapus!', json.message, 'success');

                        // Logika Smart Pagination: Jika yang dihapus adalah item terakhir di halaman, mundur 1 halaman.
                        const newPage = (currentDataLength === 1 && currentPage > 1) ? currentPage - 1 : currentPage;
                        fetchData(newPage);
                    })
                    .catch(err => Swal.fire('Gagal!', err.message, 'error'));
            }
        });
    };
});