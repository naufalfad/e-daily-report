import { authFetch } from "../../utils/auth-fetch";
import { showToast } from "../../global/notification";

document.addEventListener('DOMContentLoaded', () => {

    // === DOM ELEMENTS ===
    const listContainer = document.getElementById('lkh-validation-list');
    const filterForm = document.getElementById('filter-form');
    
    // Filter Inputs
    const filterSearch = document.getElementById('filter-search');
    const filterMonth = document.getElementById('filter-month');
    const filterYear = document.getElementById('filter-year');
    const filterStatus = document.getElementById('filter-status');

    // Pagination Elements (NEW)
    const btnPrev = document.getElementById('prev-page');
    const btnNext = document.getElementById('next-page');
    const paginationInfo = document.getElementById('pagination-info');
    const paginationNumbers = document.getElementById('pagination-numbers');

    // Modals
    const detailModal = document.getElementById('modal-detail');
    const approveModal = document.getElementById('modal-approve');
    const rejectModal = document.getElementById('modal-reject');
    
    // NEW Modal Elements
    const buktiListModal = document.getElementById('modal-bukti-list');
    const buktiListContainer = document.getElementById('bukti-list-container');
    const previewModal = document.getElementById('modal-preview');
    const previewContent = document.getElementById('preview-content');
    const btnOpenBukti = document.getElementById('detail-bukti-btn'); 

    // Buttons
    const btnSubmitApprove = document.getElementById('btn-submit-approve');
    const btnSubmitReject = document.getElementById('btn-submit-reject');
    const rejectError = document.getElementById('reject-error');

    // STATE MANAGEMENT
    let currentLkhData = []; 
    let daftarBukti = []; 
    let selectedBukti = null; 
    let currentPage = 1; // NEW: Track Current Page
    let searchTimeout = null; // NEW: Debounce Search

    if (!listContainer) return;

    // === UTILS ===
    const show = (el) => {
        if(el) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        }
    };
    
    const hide = (el) => {
        if(el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    };
    
    const closeAllModals = () => {
        hide(detailModal);
        hide(approveModal);
        hide(rejectModal);
        hide(buktiListModal);
        hide(previewModal);
    };

    const formatDate = (iso) => {
        try {
            return new Date(iso).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
        } catch (_) { return iso; }
    };

    const createStatusBadge = (status) => {
        const styles = {
            'waiting_review': 'bg-amber-50 text-amber-600 border border-amber-200',
            'approved': 'bg-emerald-50 text-emerald-600 border border-emerald-200',
            'rejected': 'bg-rose-50 text-rose-600 border border-rose-200',
            'draft': 'bg-slate-50 text-slate-500 border border-slate-200'
        };
        const labels = {
            'waiting_review': 'Menunggu',
            'approved': 'Disetujui',
            'rejected': 'Ditolak',
            'draft': 'Draft'
        };
        const cls = styles[status] || styles['draft'];
        const lbl = labels[status] || status;
        return `<span class="px-2.5 py-1 text-[10px] uppercase font-bold rounded-full ${cls}">${lbl}</span>`;
    };
    
    const getFileType = (url) => {
        if (!url) return "other";
        const ext = url.split(".").pop().toLowerCase();
        if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) return "image";
        if (ext === "pdf") return "pdf";
        if (["mp4", "mov", "webm"].includes(ext)) return "video";
        return "other";
    };

    const normalizeBukti = (buktiArray) => {
        if (!buktiArray) return [];
        let arr = Array.isArray(buktiArray) ? buktiArray : 
                    (typeof buktiArray === 'string' ? JSON.parse(buktiArray) : []);
        if (!Array.isArray(arr)) return [];
        return arr.map((bukti) => {
            if (typeof bukti === "string") return { file_url: `/storage/uploads/bukti/${bukti}` };
            if (bukti.path) return { file_url: `/storage/${bukti.path}` };
            if (bukti.file_url) return bukti;
            return null;
        }).filter(Boolean);
    };

    // === MAIN FUNCTION: FETCH DATA ===
    async function fetchLkhList(page = 1) {
        // 1. Set Loading State
        listContainer.innerHTML = `
            <tr>
                <td colspan="6" class="p-8 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="animate-spin h-8 w-8 text-[#1C7C54] mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-slate-500 text-sm">Memuat daftar laporan...</span>
                    </div>
                </td>
            </tr>`;

        // 2. Build Query Params
        const params = new URLSearchParams({
            month: filterMonth ? filterMonth.value : new Date().getMonth() + 1,
            year: filterYear ? filterYear.value : new Date().getFullYear(),
            status: filterStatus ? filterStatus.value : 'all',
            search: filterSearch ? filterSearch.value : '',
            page: page, // Dynamic Page
            per_page: 10 // Consistent with controller
        });

        try {
            // 3. Fetch Data
            const res = await authFetch(`/api/validator/kadis/lkh?${params.toString()}`);
            const json = await res.json(); // Biasanya JSON langsung berisi object Paginator (data, current_page, etc) atau dibungkus 'data'

            // Handle Laravel Pagination Response Structure
            // Kadang response: { data: [...], current_page: 1 ... } 
            // Kadang response: { data: { data: [...], current_page: 1 ... } } tergantung Resource Class
            const paginationData = json.data?.data ? json.data : json; 
            const rows = paginationData.data || [];

            if (!res.ok) throw new Error(json.message || "Gagal memuat data");

            // Simpan data ke variabel global
            currentLkhData = rows;
            currentPage = page;

            renderTable(currentLkhData);
            updatePagination(paginationData); // Update UI Paginasi

        } catch (err) {
            console.error(err);
            listContainer.innerHTML = 
                `<tr><td colspan="6" class="p-6 text-center text-rose-500 text-sm font-medium">Gagal memuat data: ${err.message}</td></tr>`;
        }
    }

    // === RENDER TABLE ===
    function renderTable(lkhs) {
        listContainer.innerHTML = '';

        if (!lkhs || lkhs.length === 0) {
            listContainer.innerHTML = `
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-400">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                            <span class="text-sm">Tidak ada laporan yang sesuai filter.</span>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        lkhs.forEach((lkh) => {
            let waktu = `${lkh.waktu_mulai.substring(0, 5)} - ${lkh.waktu_selesai.substring(0, 5)}`;
            let pegawai = lkh.user ? lkh.user.name : 'Unknown';
            
            const row = `
                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700">${formatDate(lkh.tanggal_laporan)}</td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800 text-sm">${pegawai}</div>
                        <div class="text-xs text-slate-500">${lkh.user?.nip || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-800 truncate max-w-[200px]" title="${lkh.jenis_kegiatan}">
                            ${lkh.jenis_kegiatan}
                        </div>
                        <div class="text-xs text-slate-500 truncate max-w-[200px]">${lkh.deskripsi_aktivitas}</div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap font-mono text-xs text-slate-600">
                        ${waktu}
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        ${createStatusBadge(lkh.status)}
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <button type="button"
                            class="js-open-detail group inline-flex items-center justify-center gap-1.5 rounded-lg bg-white border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-blue-500 hover:text-blue-600 transition-all shadow-sm"
                            data-id="${lkh.id}">
                            <i class="fas fa-eye text-slate-400 group-hover:text-blue-500"></i> Detail
                        </button>
                    </td>
                </tr>
            `;
            listContainer.insertAdjacentHTML('beforeend', row);
        });
    }

    // === PAGINATION LOGIC (PHASE 3 & 4 Implementation) ===
    function updatePagination(data) {
        if (!paginationInfo) return;

        const { current_page, last_page, from, to, total, prev_page_url, next_page_url } = data;

        // 1. Update Info Text
        paginationInfo.textContent = `Menampilkan ${from || 0}-${to || 0} dari ${total || 0} data`;

        // 2. Update Prev/Next Buttons
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

        // 3. Render Numeric Links (Sliding Window)
        renderPaginationLinks(current_page, last_page);
    }

    function renderPaginationLinks(current, lastPage) {
        if (!paginationNumbers) return;
        paginationNumbers.innerHTML = '';

        // Helper: Create Button
        const createBtn = (page, isActive) => {
            const btn = document.createElement('button');
            btn.className = isActive 
                ? `w-8 h-8 flex items-center justify-center rounded-lg bg-[#1C7C54] text-white text-sm font-medium shadow-sm transition-all`
                : `w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-medium transition-all js-page-link`;
            btn.textContent = page;
            if(!isActive) btn.dataset.page = page;
            return btn;
        };

        // Helper: Create Ellipsis
        const createDots = () => {
            const span = document.createElement('span');
            span.className = "px-1 text-slate-400 text-sm";
            span.textContent = "...";
            return span;
        };

        // Algorithm
        if (lastPage <= 7) {
            for (let i = 1; i <= lastPage; i++) paginationNumbers.appendChild(createBtn(i, i === current));
        } else {
            // Always show first
            paginationNumbers.appendChild(createBtn(1, 1 === current));

            if (current > 4) paginationNumbers.appendChild(createDots());

            let start = Math.max(2, current - 1);
            let end = Math.min(lastPage - 1, current + 1);

            if (current <= 4) end = 5;
            if (current >= lastPage - 3) start = lastPage - 4;

            for (let i = start; i <= end; i++) paginationNumbers.appendChild(createBtn(i, i === current));

            if (current < lastPage - 3) paginationNumbers.appendChild(createDots());

            // Always show last
            paginationNumbers.appendChild(createBtn(lastPage, lastPage === current));
        }
    }

    // === EVENT HANDLERS (PHASE 4) ===

    // 1. Pagination Number Click (Event Delegation)
    if (paginationNumbers) {
        paginationNumbers.addEventListener('click', (e) => {
            const target = e.target.closest('.js-page-link');
            if (target) {
                e.preventDefault();
                const page = parseInt(target.dataset.page);
                if (page && page !== currentPage) {
                    fetchLkhList(page);
                    // UX: Scroll to Top of Table
                    document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    }

    // 2. Prev/Next Click
    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (currentPage > 1) {
                fetchLkhList(currentPage - 1);
                document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }
    if (btnNext) {
        btnNext.addEventListener('click', () => {
            // Kita bisa cek btnNext.disabled, tapi fetchLkhList akan handle page logic
            fetchLkhList(currentPage + 1);
            document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 3. Filters
    if (filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchLkhList(1); // Reset to page 1 on filter
        });
    }
    
    // Live search with debounce
    if (filterSearch) {
        filterSearch.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchLkhList(1);
            }, 500);
        });
    }

    // 4. Detail Button (Event Delegation)
    listContainer.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-open-detail');
        if (btn) {
            const id = btn.dataset.id;
            const lkhData = currentLkhData.find(item => item.id == id);
            if (lkhData) openDetailModal(lkhData);
        }
    });

    // === MODAL LOGIC ===
    function openDetailModal(lkhData) {
        detailModal.dataset.lkhId = lkhData.id;
        daftarBukti = normalizeBukti(lkhData.bukti || []);
        selectedBukti = null;

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        };

        setText('detail-tanggal', formatDate(lkhData.tanggal_laporan));
        setText('detail-pegawai', lkhData.user?.name ?? '-');
        setText('detail-nama', lkhData.jenis_kegiatan ?? '-');
        setText('detail-uraian', lkhData.deskripsi_aktivitas ?? '-');
        setText('detail-output', lkhData.output_hasil_kerja ?? '-');
        setText('detail-volume', `${lkhData.volume ?? '-'} ${lkhData.satuan ?? ''}`);
        
        const kategori = lkhData.skp_rencana_id ? 'SKP' : 'Non-SKP';
        setText('detail-kategori', kategori);
        setText('detail-jam-mulai', lkhData.waktu_mulai.substring(0, 5));
        setText('detail-jam-selesai', lkhData.waktu_selesai.substring(0, 5));

        let lokasi = lkhData.lokasi_manual_text || (lkhData.is_luar_lokasi ? 'Luar Kantor (GPS)' : 'Dalam Kantor (GPS)');
        setText('detail-lokasi', lokasi);

        const statusEl = document.getElementById('detail-status');
        if(statusEl) statusEl.innerHTML = createStatusBadge(lkhData.status);

        if (btnOpenBukti) {
            if (daftarBukti.length > 0) {
                btnOpenBukti.disabled = false;
                btnOpenBukti.innerHTML = `<i class="fas fa-file-alt"></i> Lihat Bukti (${daftarBukti.length})`;
                btnOpenBukti.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                btnOpenBukti.disabled = true;
                btnOpenBukti.innerHTML = `<i class="fas fa-eye-slash"></i> Tidak Ada Bukti`;
                btnOpenBukti.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        const catWrap = document.getElementById('detail-catatan-wrapper');
        if (catWrap) {
            if (lkhData.komentar_validasi) {
                catWrap.classList.remove('hidden');
                setText('detail-catatan', lkhData.komentar_validasi);
            } else {
                catWrap.classList.add('hidden');
            }
        }

        const actions = document.getElementById('validation-actions');
        const info = document.getElementById('validation-info');

        if (actions && info) {
            if (lkhData.status === 'waiting_review') {
                actions.classList.remove('hidden');
                info.classList.add('hidden');
            } else {
                actions.classList.add('hidden');
                info.classList.remove('hidden');
            }
        }

        show(detailModal);
    }
    
    function openBuktiListModal() {
        if (daftarBukti.length > 0) {
            renderBuktiList(daftarBukti);
            hide(detailModal);
            show(buktiListModal);
        } else {
            Swal.fire({
                icon: "info",
                title: "Tidak Ada Bukti",
                text: "Laporan ini tidak memiliki dokumen lampiran.",
                confirmButtonColor: "#1C7C54",
            });
        }
    }

    function renderBuktiList(buktiArray) {
        buktiListContainer.innerHTML = '';
        if (!buktiArray || buktiArray.length === 0) return;

        buktiArray.forEach((bukti, index) => {
            const type = getFileType(bukti.file_url);
            let thumbnailHtml = '';

            switch(type) {
                case 'image': thumbnailHtml = `<img src="${bukti.file_url}" class="w-full h-24 object-cover rounded-lg shadow-sm" />`; break;
                case 'pdf': thumbnailHtml = `<div class="w-full h-24 rounded-lg bg-red-100 flex items-center justify-center text-red-600"><i class="fas fa-file-pdf text-3xl"></i></div>`; break;
                case 'video': thumbnailHtml = `<div class="w-full h-24 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-video text-3xl"></i></div>`; break;
                default: thumbnailHtml = `<div class="w-full h-24 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600"><i class="fas fa-file text-3xl"></i></div>`; break;
            }

            const item = document.createElement('div');
            item.className = "bg-slate-50 border border-slate-200 rounded-xl p-3 hover:border-blue-300 hover:bg-blue-50 transition cursor-pointer js-preview-bukti";
            item.innerHTML = `
                ${thumbnailHtml}
                <p class="mt-2 text-xs font-medium text-slate-700 truncate">Lampiran ${index + 1}</p>
                <p class="text-[10px] text-slate-500 truncate" title="${bukti.file_url.split('/').pop()}">${bukti.file_url.split('/').pop()}</p>
            `;
            item.dataset.index = index;
            buktiListContainer.appendChild(item);
        });
        
        document.querySelectorAll('.js-preview-bukti').forEach(item => {
            item.addEventListener('click', (e) => {
                const index = e.currentTarget.dataset.index;
                previewBukti(daftarBukti[index]);
            });
        });
    }

    function previewBukti(bukti) {
        selectedBukti = bukti;
        const type = getFileType(bukti.file_url);
        previewContent.innerHTML = '';
        hide(buktiListModal);

        let content = '';
        const filename = bukti.file_url.split('/').pop();

        switch(type) {
            case 'image': content = `<img src="${bukti.file_url}" class="w-full rounded-lg shadow" />`; break;
            case 'pdf': content = `<iframe src="${bukti.file_url}" class="w-full h-[500px] rounded-lg"></iframe>`; break;
            case 'video': content = `<video controls class="w-full rounded-lg"><source src="${bukti.file_url}" type="video/mp4"></video>`; break;
            default: content = `
                <div class="text-center py-8">
                    <p class="text-center text-slate-600 mb-4">File **${filename}** tidak dapat dipreview.</p>
                    <a href="${bukti.file_url}" target="_blank" class="mt-3 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg shadow"><i class="fas fa-download"></i> Download File</a>
                </div>`; break;
        }
        
        previewContent.innerHTML = `<h4 class="text-sm font-bold text-slate-700 mb-2 truncate">Preview: ${filename}</h4>` + content;
        show(previewModal);
    }

    // === SUBMIT VALIDATION ===
    async function submitValidation(status, note) {
        const lkhId = detailModal.dataset.lkhId;
        
        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await authFetch(`/api/validator/kadis/lkh/${lkhId}/validate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    status: status, 
                    komentar_validasi: note || null
                })
            });

            const json = await res.json();
            
            if (res.status === 422) {
                let errorMessage = "Data validasi tidak lengkap.";
                if (json.errors) errorMessage = Object.values(json.errors).flat().join('; ');
                throw new Error(errorMessage);
            }

            if (!res.ok) throw new Error(json.message || `Error ${res.status}: Terjadi kesalahan server.`);

            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: json.message || "Status laporan diperbarui.",
                confirmButtonColor: "#1C7C54",
                timer: 2000,
                showConfirmButton: false
            });

            closeAllModals();
            fetchLkhList(currentPage); // Refresh current page

        } catch (err) {
            Swal.fire({ icon: "error", title: "Gagal", text: err.message, confirmButtonColor: "#B6241C" });
        }
    }

    // === MODAL LISTENERS ===
    document.querySelectorAll('.js-close-detail, .js-close-approve, .js-close-reject').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    
    document.querySelector('.js-close-bukti')?.addEventListener('click', () => {
        hide(buktiListModal);
        show(detailModal);
    });
    
    document.querySelector('.js-close-preview')?.addEventListener('click', () => {
        hide(previewModal);
        show(buktiListModal);
    });

    btnOpenBukti?.addEventListener('click', (e) => {
        if (!e.currentTarget.disabled) openBuktiListModal();
    });

    document.querySelector('.js-open-approve')?.addEventListener('click', () => {
        hide(detailModal);
        show(approveModal);
        const note = document.getElementById('approve-note');
        if(note) note.value = '';
    });

    btnSubmitApprove?.addEventListener('click', () => {
        const note = document.getElementById('approve-note').value;
        submitValidation('approved', note);
    });

    document.querySelector('.js-open-reject')?.addEventListener('click', () => {
        hide(detailModal);
        show(rejectModal);
        if(rejectError) rejectError.classList.add('hidden');
        const note = document.getElementById('reject-note');
        if(note) note.value = '';
    });

    btnSubmitReject?.addEventListener('click', () => {
        const note = document.getElementById('reject-note').value;
        if (!note.trim()) {
            if(rejectError) rejectError.classList.remove('hidden');
            return;
        }
        submitValidation('rejected', note);
    });

    // Initial Load
    fetchLkhList(1);
});