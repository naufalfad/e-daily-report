import { showToast } from '../../../global/notification';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    // ================================================================
    // 1. CONFIGURATION & SELECTORS
    // ================================================================
    const API_URL = '/admin/master/bidang'; // Base URL CRUD
    const API_PARENT_URL = '/admin/master/bidang/get-parents'; // Endpoint khusus AJAX (Fase 2)

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
    const FORM_MODAL = document.getElementById('formBidang');
    const INPUT_UNIT = document.getElementById('unit_kerja_id');
    const INPUT_LEVEL = document.getElementById('level');
    const INPUT_PARENT = document.getElementById('parent_id');
    const CONTAINER_PARENT = document.getElementById('parent_container');

    let currentPage = 1;
    let searchTimeout = null;

    // ================================================================
    // 2. INITIALIZATION & EVENT LISTENERS
    // ================================================================
    
    // Load initial data
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

    // Form Submit Listener
    if (FORM_MODAL) {
        FORM_MODAL.addEventListener('submit', handleFormSubmit);
    }

    // ----------------------------------------------------------------
    // [CORE LOGIC] HIERARCHY HANDLERS (Parent-Child Dependency)
    // ----------------------------------------------------------------

    // Logic 1: Saat Level Berubah
    if (INPUT_LEVEL) {
        INPUT_LEVEL.addEventListener('change', function() {
            const selectedLevel = this.value;
            const unitId = INPUT_UNIT.value;

            if (selectedLevel === 'sub_bidang') {
                // Tampilkan dropdown parent
                CONTAINER_PARENT.classList.remove('hidden');
                INPUT_PARENT.setAttribute('required', 'required'); // Add validation HTML5

                // Jika Unit Kerja sudah dipilih, otomatis fetch data induk
                if (unitId) {
                    fetchParents(unitId);
                } else {
                    // Jika unit kerja belum dipilih, ingatkan user (opsional) atau biarkan kosong
                    showToast('Silakan pilih Unit Kerja terlebih dahulu.', 'info');
                }
            } else {
                // Sembunyikan dan Reset
                resetParentDropdown();
            }
        });
    }

    // Logic 2: Saat Unit Kerja Berubah
    if (INPUT_UNIT) {
        INPUT_UNIT.addEventListener('change', function() {
            const unitId = this.value;
            const currentLevel = INPUT_LEVEL.value;

            // Selalu reset parent dulu agar data tidak mismatch (beda unit kerja)
            INPUT_PARENT.innerHTML = '<option value="" disabled selected>-- Pilih Bidang Induk --</option>';

            // Jika posisi sedang di 'Sub Bidang', refresh data induk sesuai unit baru
            if (currentLevel === 'sub_bidang' && unitId) {
                fetchParents(unitId);
            }
        });
    }

    // ================================================================
    // 3. CORE FUNCTIONS (CRUD & LOGIC)
    // ================================================================

    /**
     * Mengambil data Induk Bidang via AJAX (Fase 2 Requirement)
     * @param {number} unitId 
     * @param {number|null} selectedId (Optional - untuk mode edit)
     */
    async function fetchParents(unitId, selectedId = null) {
        try {
            // Disable dropdown saat loading
            INPUT_PARENT.disabled = true;
            INPUT_PARENT.innerHTML = '<option>Memuat data...</option>';

            const response = await fetch(`${API_PARENT_URL}?unit_kerja_id=${unitId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Gagal mengambil data induk.');

            const data = await response.json();
            
            // Build Options
            let options = '<option value="" disabled selected>-- Pilih Bidang Induk --</option>';
            data.forEach(item => {
                const isSelected = selectedId && item.id == selectedId ? 'selected' : '';
                options += `<option value="${item.id}" ${isSelected}>${item.nama_bidang}</option>`;
            });

            INPUT_PARENT.innerHTML = options;

        } catch (error) {
            console.error('Error fetching parents:', error);
            INPUT_PARENT.innerHTML = '<option value="" disabled>Gagal memuat data</option>';
            showToast('Gagal memuat data induk bidang', 'error');
        } finally {
            INPUT_PARENT.disabled = false;
        }
    }

    function resetParentDropdown() {
        CONTAINER_PARENT.classList.add('hidden');
        INPUT_PARENT.removeAttribute('required');
        INPUT_PARENT.value = '';
        INPUT_PARENT.innerHTML = '<option value="" disabled selected>-- Pilih Bidang Induk --</option>';
    }

    async function fetchData(page = 1) {
        try {
            showLoading(true);
            const search = SEARCH_INPUT ? SEARCH_INPUT.value : '';
            const params = new URLSearchParams({ page, search, per_page: 10 });

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
            currentPage = result.current_page;
            renderTable(result.data, result.from);
            renderPagination(result);

        } catch (error) {
            console.error(error);
            showToast('Gagal memuat data.', 'error');
        } finally {
            showLoading(false);
        }
    }

    // Render Table dengan Kolom Hirarki
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
            const unitName = item.unit_kerja ? item.unit_kerja.nama_unit : '-';
            
            // Logic Badge Level
            let levelBadge = '';
            if (item.level === 'bidang') {
                levelBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">BIDANG</span>`;
            } else {
                const parentName = item.parent ? `<br><span class="text-[10px] text-slate-400 font-normal">Induk: ${item.parent.nama_bidang}</span>` : '';
                levelBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200">SUB BIDANG</span>${parentName}`;
            }

            const html = `
                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100">
                    <td class="px-6 py-4 text-center font-medium text-slate-500">${rowNum}</td>
                    <td class="px-6 py-4 font-semibold text-slate-700">
                        ${item.nama_bidang}
                    </td>
                    <td class="px-6 py-4 text-slate-600 text-xs">
                        <div class="flex items-center gap-1.5"><i class="fas fa-building text-slate-400"></i> ${unitName}</div>
                    </td>
                    <td class="px-6 py-4 text-slate-600 leading-tight">
                        ${levelBadge}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-100">
                            <i class="fas fa-users text-[10px]"></i> ${item.users_count || 0}
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

    async function handleFormSubmit(e) {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(e.target);
        const id = document.getElementById('bidang_id').value;
        const method = document.getElementById('method').value;
        let url = API_URL;
        
        if (method === 'PUT') {
            url += `/${id}`;
            formData.append('_method', 'PUT'); // Spoofing method for Laravel
        }

        try {
            const response = await fetch(url, {
                method: 'POST', // Use POST with _method spoofing
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json',
                    // Jangan set Content-Type secara manual saat menggunakan FormData
                },
                body: formData
            });

            const result = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    // Validation Error
                    let errorMsg = Object.values(result.errors).flat().join('\n');
                    throw new Error(errorMsg);
                }
                throw new Error(result.message || 'Terjadi kesalahan.');
            }

            showToast(result.message, 'success');
            window.closeModal();
            fetchData(currentPage); // Refresh table

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
        PAGINATION_INFO.textContent = `Menampilkan ${meta.from || 0}-${meta.to || 0} dari ${meta.total} data`;
        BTN_PREV.disabled = !meta.prev_page_url;
        BTN_PREV.classList.toggle('opacity-30', !meta.prev_page_url);
        BTN_NEXT.disabled = !meta.next_page_url;
        BTN_NEXT.classList.toggle('opacity-30', !meta.next_page_url);
        PAGINATION_NUMBERS.innerHTML = '';
        const current = meta.current_page;
        const last = meta.last_page;
        const createBtn = (p, active) => `<button data-page="${p}" class="js-page-link w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-all ${active ? 'bg-[#1C7C54] text-white shadow-sm' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}">${p}</button>`;
        const dots = `<span class="px-1 text-slate-400 text-sm">...</span>`;
        if (last <= 7) { for (let i = 1; i <= last; i++) PAGINATION_NUMBERS.innerHTML += createBtn(i, i === current); } 
        else {
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
        if (show) { LOADING_STATE.classList.remove('hidden'); TABLE_BODY.innerHTML = ''; EMPTY_STATE.classList.add('hidden'); } 
        else { LOADING_STATE.classList.add('hidden'); }
    }

    // ================================================================
    // 4. GLOBAL EXPORTS FOR HTML ONCLICK
    // ================================================================

    /**
     * Membuka Modal (Add / Edit)
     * Menggunakan Async untuk handling fetchParents saat Edit
     */
    window.openModal = async function(type, data = null) {
        const form = document.getElementById('formBidang');
        form.reset();
        
        // Reset validasi visual (opsional)
        resetParentDropdown();

        if (type === 'add') {
            document.getElementById('modalTitle').textContent = 'Tambah Bidang Baru';
            document.getElementById('bidang_id').value = '';
            document.getElementById('method').value = 'POST';
            
            // Set default: unit kosong, level kosong
            INPUT_UNIT.value = '';
            INPUT_LEVEL.value = '';

        } else {
            document.getElementById('modalTitle').textContent = 'Perbarui Bidang';
            document.getElementById('bidang_id').value = data.id;
            document.getElementById('method').value = 'PUT';

            // 1. Isi data standar
            document.getElementById('nama_bidang').value = data.nama_bidang;
            INPUT_UNIT.value = data.unit_kerja_id;

            // 2. Handling Level & Parent
            // Pastikan data.level ada, atau deteksi dari parent_id jika level null (legacy data compatibility)
            const level = data.level || (data.parent_id ? 'sub_bidang' : 'bidang');
            INPUT_LEVEL.value = level;

            if (level === 'sub_bidang') {
                CONTAINER_PARENT.classList.remove('hidden');
                INPUT_PARENT.setAttribute('required', 'required');
                
                // [PENTING] Tunggu fetch selesai baru set value parent
                await fetchParents(data.unit_kerja_id, data.parent_id);
            } else {
                resetParentDropdown();
            }
        }

        // Show Modal Animation
        document.getElementById('modalBidang').classList.remove('hidden');
        requestAnimationFrame(() => {
            document.getElementById('modalBackdrop').classList.remove('opacity-0');
            document.getElementById('modalPanel').classList.remove('opacity-0', 'scale-95');
        });
    };

    window.closeModal = function() {
        document.getElementById('modalBackdrop').classList.add('opacity-0');
        document.getElementById('modalPanel').classList.add('opacity-0', 'scale-95');
        setTimeout(() => { document.getElementById('modalBidang').classList.add('hidden'); }, 200);
    };

    window.deleteData = function(id) {
        Swal.fire({
            title: 'Hapus Bidang?',
            text: "Data tidak bisa dikembalikan. Pastikan tidak ada pegawai di bidang ini.",
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
                .catch(err => Swal.fire('Gagal!', err.message, 'error'));
            }
        });
    };
});