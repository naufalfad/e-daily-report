import { showToast } from '../../../global/notification'; 
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. CONFIGURATION ---
    const API_URL = '/admin/master/tupoksi';
    let currentPage = 1;
    let searchQuery = '';
    let filterBidang = ''; // Variable baru untuk filter
    let searchTimeout = null;

    // --- 2. DOM ELEMENTS ---
    const els = {
        tableBody: document.getElementById('table-body'),
        loadingState: document.getElementById('loading-state'),
        emptyState: document.getElementById('empty-state'),
        paginationInfo: document.getElementById('pagination-info'),
        paginationLinks: document.getElementById('pagination-links'),
        
        // Filter & Search
        searchInput: document.getElementById('searchInput'),
        filterBidangSelect: document.getElementById('filterBidang'), // Element Select Filter
        
        // Modal & Form
        modal: document.getElementById('modal-tupoksi'),
        modalBackdrop: document.getElementById('modal-backdrop'),
        modalPanel: document.getElementById('modal-panel'),
        modalTitle: document.getElementById('modal-title'),
        form: document.getElementById('form-tupoksi'),
        idInput: document.getElementById('id'),
        bidangSelect: $('#bidang_id'), 
        uraianInput: document.getElementById('uraian_tugas'),
        
        // Buttons
        btnCreate: document.getElementById('btn-create'),
        btnSave: document.getElementById('btn-save'),
        btnSaveText: document.getElementById('btn-save-text'),
        btnSaveLoading: document.getElementById('btn-save-loading'),
        closeButtons: document.querySelectorAll('.close-modal')
    };

    // --- 3. INITIALIZATION ---
    // Init Select2 untuk Modal
    if (typeof $ !== 'undefined') {
        els.bidangSelect.select2({
            dropdownParent: $(els.modal),
            width: '100%',
            placeholder: '-- Pilih Bidang --'
        });
    }

    // Load Data Pertama Kali
    fetchData();

    // --- 4. EVENT LISTENERS ---
    
    // Search Listener
    if (els.searchInput) {
        els.searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchQuery = e.target.value;
            searchTimeout = setTimeout(() => {
                currentPage = 1; 
                fetchData();
            }, 500); 
        });
    }

    // Filter Bidang Listener [BARU]
    if (els.filterBidangSelect) {
        els.filterBidangSelect.addEventListener('change', (e) => {
            filterBidang = e.target.value; // Update state filter
            currentPage = 1; // Reset ke halaman 1
            fetchData(); // Reload table
        });
    }

    if (els.btnCreate) els.btnCreate.addEventListener('click', () => openModal('create'));

    els.closeButtons.forEach(btn => btn.addEventListener('click', closeModal));

    if (els.tableBody) {
        els.tableBody.addEventListener('click', (e) => {
            const target = e.target.closest('button');
            if (!target) return;

            if (target.classList.contains('btn-edit')) {
                const data = JSON.parse(target.dataset.item);
                openModal('edit', data);
            }

            if (target.classList.contains('btn-delete')) {
                const id = target.dataset.id;
                deleteData(id);
            }
        });
    }

    if (els.form) els.form.addEventListener('submit', submitForm);

    if (els.paginationLinks) {
        els.paginationLinks.addEventListener('click', (e) => {
            e.preventDefault();
            const link = e.target.closest('a');
            if (link && link.dataset.page) {
                currentPage = link.dataset.page;
                fetchData();
            }
        });
    }

    // --- 5. CORE FUNCTIONS ---

    async function fetchData() {
        setLoading(true);
        try {
            // Bangun URL dengan Search & Filter Params
            const params = new URLSearchParams({
                page: currentPage,
                search: searchQuery,
                bidang_id: filterBidang // Kirim ID bidang ke server
            });

            const response = await fetch(`${API_URL}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Gagal memuat data');

            const result = await response.json();
            
            renderTable(result.data, result.from);
            renderPagination(result);

        } catch (error) {
            console.error(error);
            showToast('Terjadi kesalahan saat memuat data', 'error');
        } finally {
            setLoading(false);
        }
    }

    function renderTable(data, fromIndex) {
        els.tableBody.innerHTML = '';

        if (data.length === 0) {
            els.emptyState.classList.remove('hidden');
            return;
        }
        els.emptyState.classList.add('hidden');

        data.forEach((item, index) => {
            const rowNumber = (fromIndex || 1) + index;
            
            const isSub = item.bidang && item.bidang.level === 'sub_bidang';
            const badgeClass = isSub 
                ? 'bg-sky-100 text-sky-700 ring-sky-600/20' 
                : 'bg-indigo-100 text-indigo-700 ring-indigo-600/20';
            const badgeLabel = isSub ? 'Sub' : 'Bidang';
            const namaBidang = item.bidang ? item.bidang.nama_bidang : '<span class="text-red-500 italic">Tanpa Bidang</span>';
            const itemJson = JSON.stringify(item).replace(/"/g, '&quot;');

            const row = `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-slate-500">
                        ${rowNumber}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${badgeClass}">
                                ${badgeLabel}
                            </span>
                            <span class="text-sm font-medium text-slate-900">${namaBidang}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600 text-justify line-clamp-3 hover:line-clamp-none transition-all cursor-pointer" title="${item.uraian_tugas}">
                            ${item.uraian_tugas}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button class="btn-edit p-1.5 rounded-md text-amber-500 hover:bg-amber-50 hover:text-amber-600 transition-colors"
                                data-item="${itemJson}" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn-delete p-1.5 rounded-md text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors"
                                data-id="${item.id}" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            els.tableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    function renderPagination(meta) {
        const infoText = `Menampilkan ${meta.from || 0} sampai ${meta.to || 0} dari ${meta.total} data`;
        els.paginationInfo.textContent = infoText;
        
        // Update juga text pagination mobile jika ada
        const mobileInfo = document.getElementById('pagination-info-mobile');
        if(mobileInfo) mobileInfo.textContent = infoText;

        els.paginationLinks.innerHTML = '';

        if (meta.last_page <= 1) return;

        const createBtn = (page, text, isActive = false, isDisabled = false) => {
            const baseClass = "relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset focus:z-20 focus:outline-offset-0";
            const activeClass = "z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600";
            const inactiveClass = "text-slate-900 ring-slate-300 hover:bg-slate-50 focus:z-20 focus:outline-offset-0";
            const disabledClass = "text-slate-300 ring-slate-200 cursor-not-allowed";

            let className = baseClass;
            if (isActive) className += ` ${activeClass}`;
            else if (isDisabled) className += ` ${disabledClass}`;
            else className += ` ${inactiveClass}`;

            if (text === 'Prev') className += ' rounded-l-md';
            if (text === 'Next') className += ' rounded-r-md';

            return `
                <a href="#" data-page="${page}" class="${className} ${isDisabled ? 'pointer-events-none' : ''}">
                    ${text === 'Prev' ? '<i class="fas fa-chevron-left"></i>' : (text === 'Next' ? '<i class="fas fa-chevron-right"></i>' : text)}
                </a>
            `;
        };

        let html = '';
        html += createBtn(meta.current_page - 1, 'Prev', false, meta.current_page === 1);
        let start = Math.max(1, meta.current_page - 2);
        let end = Math.min(meta.last_page, meta.current_page + 2);
        for (let i = start; i <= end; i++) {
            html += createBtn(i, i, i === meta.current_page);
        }
        html += createBtn(meta.current_page + 1, 'Next', false, meta.current_page === meta.last_page);
        els.paginationLinks.innerHTML = html;
    }

    // --- 6. MODAL & FORM LOGIC ---

    function openModal(mode, data = null) {
        clearValidation();
        els.form.reset();
        els.modal.classList.remove('hidden');
        void els.modal.offsetWidth; // trigger reflow
        els.modalBackdrop.classList.remove('opacity-0');
        els.modalPanel.classList.remove('opacity-0', 'translate-y-4', 'scale-95');

        if (mode === 'create') {
            els.modalTitle.textContent = 'Tambah Tupoksi Baru';
            els.idInput.value = '';
            if (typeof $ !== 'undefined') els.bidangSelect.val('').trigger('change');
        } else {
            els.modalTitle.textContent = 'Edit Data Tupoksi';
            els.idInput.value = data.id;
            els.uraianInput.value = data.uraian_tugas;
            if (typeof $ !== 'undefined') els.bidangSelect.val(data.bidang_id).trigger('change');
        }
    }

    function closeModal() {
        els.modalBackdrop.classList.add('opacity-0');
        els.modalPanel.classList.add('opacity-0', 'translate-y-4', 'scale-95');
        setTimeout(() => {
            els.modal.classList.add('hidden');
        }, 300);
    }

    async function submitForm(e) {
        e.preventDefault();
        const id = els.idInput.value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `${API_URL}/${id}` : API_URL;

        els.btnSave.disabled = true;
        els.btnSaveText.classList.add('hidden');
        els.btnSaveLoading.classList.remove('hidden');
        clearValidation();

        try {
            const formData = new FormData(els.form);
            const jsonData = Object.fromEntries(formData.entries());

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(jsonData)
            });

            const result = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    showValidationErrors(result.errors);
                    throw new Error('Cek kembali inputan Anda.');
                }
                throw new Error(result.message || 'Terjadi kesalahan sistem.');
            }

            showToast(result.message, 'success');
            closeModal();
            fetchData(); 

        } catch (error) {
            if (error.message !== 'Cek kembali inputan Anda.') {
                Swal.fire('Gagal', error.message, 'error');
            }
        } finally {
            els.btnSave.disabled = false;
            els.btnSaveText.classList.remove('hidden');
            els.btnSaveLoading.classList.add('hidden');
        }
    }

    function deleteData(id) {
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data tupoksi ini akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-4 py-2 rounded-lg text-sm font-semibold',
                cancelButton: 'px-4 py-2 rounded-lg text-sm font-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(async res => {
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message || 'Gagal menghapus data');
                    return json;
                })
                .then(json => {
                    showToast(json.message, 'success');
                    fetchData();
                })
                .catch(err => {
                    Swal.fire('Gagal!', err.message, 'error');
                });
            }
        });
    }

    function setLoading(isLoading) {
        if (isLoading) {
            els.loadingState.classList.remove('hidden');
            els.tableBody.classList.add('opacity-50');
        } else {
            els.loadingState.classList.add('hidden');
            els.tableBody.classList.remove('opacity-50');
        }
    }

    function clearValidation() {
        document.querySelectorAll('.text-red-600').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('input, textarea, select').forEach(el => el.classList.remove('ring-red-500', 'focus:ring-red-500'));
        if (typeof $ !== 'undefined') $('.select2-selection').css('border-color', '#cbd5e1'); 
    }

    function showValidationErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const input = document.getElementById(field);
            const errorText = document.getElementById(`error-${field}`);
            if (input) {
                input.classList.add('ring-1', 'ring-red-500', 'focus:ring-red-500');
                if (field === 'bidang_id' && typeof $ !== 'undefined') {
                    $('.select2-selection').css('border-color', '#ef4444');
                }
            }
            if (errorText) {
                errorText.textContent = messages[0];
                errorText.classList.remove('hidden');
            }
        }
    }
});