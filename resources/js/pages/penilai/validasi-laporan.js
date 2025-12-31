document.addEventListener('DOMContentLoaded', () => {
    // ==== DOM ELEMENTS ====
    const listContainer = document.getElementById('lkh-validation-list');
    
    // Filters Elements
    const filterStatus = document.getElementById('filter-status');
    const filterMonth = document.getElementById('filter-month');
    const filterYear = document.getElementById('filter-year');
    const filterSearch = document.getElementById('filter-search');

    // Pagination Elements
    const btnPrev = document.getElementById('prev-page');
    const btnNext = document.getElementById('next-page');
    const paginationInfo = document.getElementById('pagination-info');
    const paginationNumbers = document.getElementById('pagination-numbers'); // Container Angka

    // Modals
    const detailModal = document.getElementById('modal-detail');
    const approveModal = document.getElementById('modal-approve');
    const rejectModal = document.getElementById('modal-reject');
    
    // NEW Modal Elements
    const buktiListModal = document.getElementById('modal-bukti-list');
    const buktiListContainer = document.getElementById('bukti-list-container');
    const previewModal = document.getElementById('modal-preview');
    const previewContent = document.getElementById('preview-content');

    // Action Buttons
    const btnSubmitApprove = document.getElementById('btn-submit-approve');
    const btnSubmitReject = document.getElementById('btn-submit-reject');
    const rejectError = document.getElementById('reject-error');
    
    // State Variables
    let currentPage = 1;
    let searchTimeout = null;
    let daftarBukti = [];
    let selectedBukti = null;

    // ==== HELPERS ====
    const getToken = () => localStorage.getItem('auth_token');
    
    const formatDate = (isoString) => {
        if (!isoString) return '-';
        try {
            return new Date(isoString).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
        } catch { return isoString; }
    };

    const getInitial = (name) => name ? name.charAt(0).toUpperCase() : '?';

    const createStatusBadge = (status) => {
        const config = {
            'waiting_review': { css: 'bg-amber-50 text-amber-600 border-amber-100', label: 'Pending' },
            'approved': { css: 'bg-emerald-50 text-emerald-600 border-emerald-100', label: 'Diterima' },
            'rejected': { css: 'bg-rose-50 text-rose-600 border-rose-100', label: 'Ditolak' }
        };
        const style = config[status] || config['waiting_review'];
        return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ${style.css}">${style.label}</span>`;
    };
    
    const getFileType = (url) => {
        if (!url) return "other";
        const ext = url.split(".").pop().toLowerCase();
        if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) return "image";
        if (ext === "pdf") return "pdf";
        if (["mp4", "mov", "webm"].includes(ext)) return "video";
        return "other";
    };

    const emptyToNull = (str) => {
        return (typeof str === 'string' && str.trim() === '') ? null : str;
    };

    // ==== MAIN FUNCTION: FETCH DATA ====
    async function fetchLkhList(page = 1) {
        if (!listContainer) return;

        // Show Loading State
        listContainer.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <svg class="animate-spin h-6 w-6 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memuat data laporan...</span>
                    </div>
                </td>
            </tr>`;

        const token = getToken();
        if (!token) {
            listContainer.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-rose-500">Sesi berakhir. Login ulang.</td></tr>`;
            return;
        }

        // Construct Query Params
        const params = new URLSearchParams({
            page: page,
            per_page: 10, // Sesuai dengan controller
            status: filterStatus ? filterStatus.value : 'waiting_review',
            month: filterMonth ? filterMonth.value : '',
            year: filterYear ? filterYear.value : '',
            search: filterSearch ? filterSearch.value : ''
        });

        try {
            const response = await fetch(`/api/validator/lkh?${params.toString()}`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            if (!response.ok) throw await response.json();
            
            const data = await response.json();
            
            renderTable(data.data);
            updatePagination(data); // Panggil fungsi pagination baru
            currentPage = page;

        } catch (err) {
            console.error(err);
            listContainer.innerHTML = `
                <tr><td colspan="7" class="p-6 text-center text-rose-500">Gagal memuat data. Silakan coba lagi.</td></tr>`;
        }
    }

    // ==== RENDER TABLE ====
    function renderTable(lkhs) {
        listContainer.innerHTML = '';

        if (!lkhs || lkhs.length === 0) {
            listContainer.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="font-medium">Tidak ada data ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">Coba ubah filter atau kata kunci pencarian.</p>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        lkhs.forEach(lkh => {
            const row = document.createElement('tr');
            row.className = "hover:bg-slate-50/80 transition-colors group border-b border-slate-50 last:border-none";

            const dateStr = formatDate(lkh.tanggal_laporan); 
            const timeRange = `${lkh.waktu_mulai?.substring(0, 5)} – ${lkh.waktu_selesai?.substring(0, 5)}`;
            const userName = lkh.user ? lkh.user.name : 'Unknown';
            const userInitial = getInitial(userName);

            let locationText = lkh.lokasi_manual_text || 'Lokasi GPS';
            if (lkh.is_luar_lokasi) locationText = 'Luar Kantor';

            row.innerHTML = `
                <td class="px-6 py-4 align-top">
                    <div class="text-sm font-semibold text-slate-700">${dateStr}</div>
                </td>
                <td class="px-6 py-4 align-top">
                    <div class="text-sm font-medium text-slate-900 truncate max-w-[200px]" title="${lkh.deskripsi_aktivitas}">
                        ${lkh.jenis_kegiatan || '-'}
                    </div>
                </td>
                <td class="px-6 py-4 align-top">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                        ${timeRange}
                    </span>
                </td>
                <td class="px-6 py-4 align-top">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shadow-sm shrink-0">
                            ${userInitial}
                        </div>
                        <span class="text-sm text-slate-700 font-medium truncate max-w-[140px]">${userName}</span>
                    </div>
                </td>
                <td class="px-6 py-4 align-top">
                    <div class="text-sm text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate max-w-[120px]">${locationText}</span>
                    </div>
                </td>
                <td class="px-6 py-4 align-top text-center">
                    ${createStatusBadge(lkh.status)}
                </td>
                <td class="px-6 py-4 align-top text-right">
                    <button class="js-open-detail text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline decoration-blue-600/30 underline-offset-4 transition-all"
                        data-lkh-data='${JSON.stringify(lkh)}'>
                        Lihat Detail
                    </button>
                </td>
            `;
            listContainer.appendChild(row);
        });

        // Re-attach Event Listeners for Details
        document.querySelectorAll('.js-open-detail').forEach(btn => {
            btn.addEventListener('click', openDetailModal);
        });
    }

    // ==== PAGINATION LOGIC (PHASE 3: Sliding Window) ====
    function updatePagination(response) {
        if (!paginationInfo) return;
        
        const from = response.from || 0;
        const to = response.to || 0;
        const total = response.total || 0;
        const lastPage = response.last_page || 1;
        const current = response.current_page || 1;
        
        // Update Info Text
        paginationInfo.textContent = `Menampilkan ${from}-${to} dari ${total} data`;
        
        // Update Prev/Next Buttons
        if (btnPrev) {
            btnPrev.disabled = !response.prev_page_url;
            btnPrev.classList.toggle('opacity-50', !response.prev_page_url);
            btnPrev.classList.toggle('cursor-not-allowed', !response.prev_page_url);
        }
        if (btnNext) {
            btnNext.disabled = !response.next_page_url;
            btnNext.classList.toggle('opacity-50', !response.next_page_url);
            btnNext.classList.toggle('cursor-not-allowed', !response.next_page_url);
        }

        // Render Angka Halaman
        renderPaginationLinks(current, lastPage);
    }

    function renderPaginationLinks(current, lastPage) {
        if (!paginationNumbers) return;
        paginationNumbers.innerHTML = ''; // Clear previous

        // Helper untuk membuat tombol angka
        const createPageBtn = (page, isActive = false) => {
            const btn = document.createElement('button');
            btn.className = isActive 
                ? "w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-600 text-white text-sm font-medium shadow-sm transition-all"
                : "w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-medium transition-all js-page-link";
            btn.textContent = page;
            if (!isActive) btn.dataset.page = page;
            return btn;
        };

        // Helper untuk ellipsis (...)
        const createEllipsis = () => {
            const span = document.createElement('span');
            span.className = "px-1 text-slate-400 text-sm";
            span.textContent = "...";
            return span;
        };

        // === ALGORITMA SLIDING WINDOW ===
        // Kasus 1: Halaman sedikit (<= 7), tampilkan semua
        if (lastPage <= 7) {
            for (let i = 1; i <= lastPage; i++) {
                paginationNumbers.appendChild(createPageBtn(i, i === current));
            }
        } 
        // Kasus 2: Halaman banyak (> 7)
        else {
            // Selalu tampilkan halaman 1
            paginationNumbers.appendChild(createPageBtn(1, 1 === current));

            if (current > 4) {
                paginationNumbers.appendChild(createEllipsis());
            }

            // Logic Window: Tampilkan 2 angka sebelum dan sesudah current
            // Tapi dibatasi jangan sampai < 2 atau > lastPage - 1
            let start = Math.max(2, current - 1);
            let end = Math.min(lastPage - 1, current + 1);

            // Adjustment jika di awal-awal (biar tetap ada minimal 3 angka di tengah)
            if (current <= 4) {
                end = 5; 
            }
            // Adjustment jika di akhir-akhir
            if (current >= lastPage - 3) {
                start = lastPage - 4;
            }

            for (let i = start; i <= end; i++) {
                paginationNumbers.appendChild(createPageBtn(i, i === current));
            }

            if (current < lastPage - 3) {
                paginationNumbers.appendChild(createEllipsis());
            }

            // Selalu tampilkan halaman terakhir
            paginationNumbers.appendChild(createPageBtn(lastPage, lastPage === current));
        }
    }

    // ==== EVENT HANDLING (PHASE 4: Event Delegation) ====
    
    // 1. Delegasi Klik pada Container Angka
    if (paginationNumbers) {
        paginationNumbers.addEventListener('click', (e) => {
            const target = e.target.closest('.js-page-link');
            if (target) {
                e.preventDefault(); // Mencegah scroll default browser jika ada href
                const page = parseInt(target.dataset.page);
                if (page && page !== currentPage) {
                    fetchLkhList(page);
                    // UX: Scroll ke atas tabel
                    document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    }

    // 2. Filter Event Listeners
    if (filterStatus) filterStatus.addEventListener('change', () => fetchLkhList(1));
    if (filterMonth) filterMonth.addEventListener('change', () => fetchLkhList(1));
    if (filterYear) filterYear.addEventListener('change', () => fetchLkhList(1));
    
    // Search with Debounce
    if (filterSearch) {
        filterSearch.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchLkhList(1);
            }, 500); 
        });
    }

    // 3. Prev/Next Listeners
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
            // Note: Kita butuh tahu lastPage untuk membatasi next, 
            // tapi tombol biasanya sudah disabled oleh updatePagination jika di halaman terakhir.
            if (!btnNext.disabled) {
                fetchLkhList(currentPage + 1);
                document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }


    // ==== MODAL LOGIC (Detail, Approve, Reject, Bukti, Preview) ====
    
    function openModalFlex(modalEl) {
        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
    }

    function closeModals() {
        detailModal.classList.add('hidden');
        approveModal.classList.add('hidden');
        approveModal.classList.remove('flex');
        rejectModal.classList.add('hidden');
        rejectModal.classList.remove('flex');
        buktiListModal.classList.add('hidden');
        buktiListModal.classList.remove('flex');
        previewModal.classList.add('hidden');
        previewModal.classList.remove('flex');
    };

    document.querySelectorAll('.js-close-detail, .js-close-approve, .js-close-reject, .js-close-bukti, .js-close-preview').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });

    // Bukti List Modal Logic
    document.querySelector('#modal-detail')?.addEventListener('click', (e) => {
        if (e.target.closest('.js-open-bukti')) {
            const btn = e.target.closest('.js-open-bukti');
            if (btn.disabled) return;
            
            if (daftarBukti && daftarBukti.length > 0) {
                renderBuktiList(daftarBukti);
                openModalFlex(buktiListModal);
            } else {
                if(typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: "info",
                        title: "Tidak Ada Bukti",
                        text: "Laporan ini tidak memiliki lampiran bukti.",
                        confirmButtonColor: "#155FA6",
                    });
                } else {
                    alert("Laporan ini tidak memiliki lampiran bukti.");
                }
            }
        }
    });

    // Render Bukti List
    function renderBuktiList(buktiArray) {
        buktiListContainer.innerHTML = '';
        
        if (!buktiArray || buktiArray.length === 0) return;

        buktiArray.forEach((bukti, index) => {
            const type = getFileType(bukti.file_url);
            let thumbnailHtml = '';

            switch(type) {
                case 'image':
                    thumbnailHtml = `<img src="${bukti.file_url}" class="w-full h-32 object-cover rounded-lg shadow-sm" />`;
                    break;
                case 'pdf':
                    thumbnailHtml = `
                        <div class="w-full h-32 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"></path><path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path></svg>
                        </div>`;
                    break;
                case 'video':
                    thumbnailHtml = `
                        <div class="w-full h-32 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500">
                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path></svg>
                        </div>`;
                    break;
                default:
                    thumbnailHtml = `
                        <div class="w-full h-32 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600">
                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>
                        </div>`;
                    break;
            }

            const item = document.createElement('div');
            item.className = "bg-slate-50 border border-slate-200 rounded-xl p-3 hover:border-blue-300 hover:bg-blue-50 transition cursor-pointer js-bukti-item";
            item.innerHTML = `
                ${thumbnailHtml}
                <p class="mt-2 text-sm font-medium text-slate-700 truncate">Lampiran ${index + 1}</p>
                <p class="text-xs text-slate-500 truncate" title="${bukti.file_url.split('/').pop()}">${bukti.file_url.split('/').pop()}</p>
            `;
            item.addEventListener('click', () => previewBukti(bukti));
            buktiListContainer.appendChild(item);
        });
    }

    // Preview Bukti
    function previewBukti(bukti) {
        selectedBukti = bukti;
        const type = getFileType(bukti.file_url);
        previewContent.innerHTML = '';

        let content = '';

        switch(type) {
            case 'image':
                content = `<img src="${bukti.file_url}" class="w-full rounded-lg shadow" />`;
                break;
            case 'pdf':
                content = `<iframe src="${bukti.file_url}" class="w-full h-[500px] rounded-lg"></iframe>`;
                break;
            case 'video':
                content = `
                    <video controls class="w-full rounded-lg">
                        <source src="${bukti.file_url}" type="video/mp4">
                        Maaf, browser Anda tidak mendukung tag video.
                    </video>`;
                break;
            default:
                content = `
                    <p class="text-center text-slate-600">
                        File tidak dapat dipreview. Silakan download:
                    </p>
                    <div class="text-center">
                        <a href="${bukti.file_url}" target="_blank"
                            class="mt-3 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg shadow">
                            Download File
                        </a>
                    </div>`;
                break;
        }
        
        previewContent.innerHTML = content;
        openModalFlex(previewModal);
    }
    
    // Helper untuk mengubah struktur data bukti dari API
    function normalizeBukti(buktiArray) {
        if (!buktiArray) return [];
        let arr = Array.isArray(buktiArray) ? buktiArray : 
                    (typeof buktiArray === 'string' ? JSON.parse(buktiArray) : []);
        
        if (!Array.isArray(arr)) return [];

        return arr.map((bukti) => {
            if (typeof bukti === "string") {
                return { file_url: `/storage/uploads/bukti/${bukti}` };
            }
            if (bukti.file_path) {
                return { file_url: `/storage/${bukti.file_path}` };
            }
            if (bukti.file_url) {
                return bukti;
            }
            return null;
        }).filter(Boolean);
    };

    // Logic Modal Detail
    function openDetailModal(e) {
        const lkhData = JSON.parse(e.currentTarget.dataset.lkhData);
        detailModal.dataset.lkhId = lkhData.id;
        
        // Reset bukti state dan simpan data baru
        daftarBukti = normalizeBukti(lkhData.bukti || []);
        selectedBukti = null;

        const setMap = {
            'detail-tanggal': formatDate(lkhData.tanggal_laporan),
            'detail-pegawai': lkhData.user?.name || '-',
            'detail-nama': lkhData.jenis_kegiatan || '-',
            'detail-uraian': lkhData.deskripsi_aktivitas,
            'detail-output': lkhData.output_hasil_kerja || '-',
            'detail-volume': `${lkhData.volume || '-'} ${lkhData.satuan || ''}`,
            'detail-kategori': lkhData.skp_rencana_id ? 'SKP' : 'Non-SKP',
            'detail-jam': `${lkhData.waktu_mulai?.substring(0, 5)} - ${lkhData.waktu_selesai?.substring(0, 5)}`,
            'detail-lokasi': lkhData.lokasi_manual_text || (lkhData.is_luar_lokasi ? 'Luar Kantor' : 'Dalam Kantor')
        };

        Object.entries(setMap).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        });
        
        const [volumeValue, ...satuanParts] = setMap['detail-volume'].split(' ');
        const volEl = document.getElementById('detail-volume');
        const satEl = document.getElementById('detail-satuan');
        if(volEl) volEl.textContent = volumeValue;
        if(satEl) satEl.textContent = satuanParts.join(' ');


        document.getElementById('detail-status').innerHTML = createStatusBadge(lkhData.status);

        // Handle Bukti & Catatan
        const catWrap = document.getElementById('detail-catatan-wrapper');
        const catText = document.getElementById('detail-catatan');
        if (lkhData.komentar_validasi) {
            catWrap.classList.remove('hidden');
            catWrap.classList.remove('bg-rose-50', 'border-rose-100');
            catWrap.classList.add('bg-amber-50', 'border-amber-100');
            catText.textContent = `"${lkhData.komentar_validasi}"`;
        } else {
            catWrap.classList.add('hidden');
        }

        // Handle Tombol Aksi di Modal Detail
        const actions = document.getElementById('validation-actions');
        const info = document.getElementById('validation-info');

        if (lkhData.status === "waiting_review") {
            actions?.classList.remove('hidden');
            actions?.classList.add('flex');
            info?.classList.add('hidden');
        } else {
            actions?.classList.add('hidden');
            actions?.classList.remove('flex');
            info?.classList.remove('hidden');
            info?.classList.add('flex');
        }

        // Handle Button Bukti
        const buktiBtn = document.getElementById('detail-bukti-btn');
        if (buktiBtn) {
            if (daftarBukti.length > 0) {
                buktiBtn.disabled = false;
                buktiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                buktiBtn.disabled = true;
                buktiBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        detailModal.classList.remove('hidden');
    }

    // Button Open Approve/Reject from Detail
    const btnOpenApprove = document.querySelector('.js-open-approve');
    if (btnOpenApprove) {
        btnOpenApprove.addEventListener('click', () => {
            detailModal.classList.add('hidden');
            document.getElementById('approve-note').value = ''; // Reset Catatan
            openModalFlex(approveModal);
        });
    }

    const btnOpenReject = document.querySelector('.js-open-reject');
    if (btnOpenReject) {
        btnOpenReject.addEventListener('click', () => {
            detailModal.classList.add('hidden');
            document.getElementById('reject-note').value = '';
            rejectError.classList.add('hidden');
            openModalFlex(rejectModal);
        });
    }

    // SUBMIT ACTIONS
    async function submitValidation(status, note, btn) {
        const lkhId = detailModal.dataset.lkhId;
        const originalText = btn.innerHTML;
        
        const finalNote = (status === 'approved') ? emptyToNull(note) : note; 

        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-white inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Proses...`;

        try {
            const res = await fetch(`/api/validator/lkh/${lkhId}/validate`, {
                method: "POST",
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status, komentar_validasi: finalNote })
            });

            if (res.ok) {
                closeModals();
                fetchLkhList(currentPage); 
                if(typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Laporan berhasil divalidasi.', confirmButtonColor: '#0E7A4A' });
                } else {
                    alert('Berhasil memvalidasi laporan.');
                }
            } else {
                const data = await res.json();
                if(typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Gagal memproses validasi', confirmButtonColor: '#d33' });
                } else {
                    alert(data.message || 'Gagal memproses validasi');
                }
            }
        } catch (e) {
            console.error(e);
            if(typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error Jaringan', text: 'Terjadi kesalahan jaringan atau server.', confirmButtonColor: '#d33' });
            } else {
                alert('Terjadi kesalahan jaringan');
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    if (btnSubmitApprove) {
        btnSubmitApprove.addEventListener('click', () => {
            const note = document.getElementById('approve-note').value;
            submitValidation('approved', note, btnSubmitApprove);
        });
    }

    if (btnSubmitReject) {
        btnSubmitReject.addEventListener('click', () => {
            const note = document.getElementById('reject-note').value;
            if (!note.trim()) {
                rejectError.classList.remove('hidden');
                return;
            }
            submitValidation('rejected', note, btnSubmitReject);
        });
    }

    // Initialize
    fetchLkhList();
});