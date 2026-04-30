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

    // Helper Status
    const createStatusBadge = (status) => {
        const config = {
            'waiting_review': { css: 'bg-amber-50 text-amber-600 border-amber-200', label: 'Pending' },
            'approved': { css: 'bg-emerald-50 text-emerald-700 border-emerald-200', label: 'Diterima' },
            'rejected': { css: 'bg-rose-50 text-rose-700 border-rose-200', label: 'Ditolak' }
        };
        const style = config[status] || config['waiting_review'];
        return `<span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold border ${style.css}">${style.label}</span>`;
    };

    // --- NEW: Helper Kategori Lokasi ---
    const getKategoriText = (kat) => {
        switch (kat) {
            case "WFO": return "WFO";
            case "WFH": return "WFH";
            case "WFA": return "WFA";
            case "DL": return "Dinas Luar";
            default: return kat || "WFO";
        }
    };

    const createKategoriBadge = (kat) => {
        const safeKat = kat || 'WFO';
        let css = '';

        switch (safeKat) {
            case "WFO": css = "bg-emerald-50 border-emerald-200 text-emerald-700"; break;
            case "WFH": css = "bg-blue-50 border-blue-200 text-blue-700"; break;
            case "WFA": css = "bg-indigo-50 border-indigo-200 text-indigo-700"; break;
            case "DL": css = "bg-purple-50 border-purple-200 text-purple-700"; break;
            default: css = "bg-slate-50 border-slate-200 text-slate-700"; break;
        }

        return `<span class="rounded-md border ${css} text-[10px] font-extrabold px-2 py-0.5 tracking-wider">${getKategoriText(safeKat)}</span>`;
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
                        <svg class="animate-spin h-6 w-6 text-[#1C7C54]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Menarik antrean laporan...</span>
                    </div>
                </td>
            </tr>`;

        const token = getToken();
        if (!token) {
            listContainer.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-rose-500 font-bold">Sesi berakhir. Silakan login ulang.</td></tr>`;
            return;
        }

        // Construct Query Params
        const params = new URLSearchParams({
            page: page,
            per_page: 10,
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
            updatePagination(data);
            currentPage = page;

        } catch (err) {
            console.error(err);
            listContainer.innerHTML = `
                <tr><td colspan="7" class="p-6 text-center text-rose-500 font-bold">Gagal memuat data. Periksa koneksi internet Anda.</td></tr>`;
        }
    }

    // ==== RENDER TABLE ====
    function renderTable(lkhs) {
        listContainer.innerHTML = '';

        if (!lkhs || lkhs.length === 0) {
            listContainer.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center text-slate-500 bg-slate-50/50">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm border border-slate-100">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-slate-700 font-bold text-base mb-1">Antrean Kosong</h3>
                            <p class="text-xs text-slate-400">Tidak ada laporan yang perlu divalidasi pada filter ini.</p>
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

            row.innerHTML = `
                <td class="px-6 py-4 align-top">
                    <div class="text-sm font-semibold text-slate-700">${dateStr}</div>
                </td>
                <td class="px-6 py-4 align-top">
                    <div class="text-[13px] font-bold text-slate-900 truncate max-w-[200px]" title="${lkh.deskripsi_aktivitas}">
                        ${lkh.jenis_kegiatan || '-'}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1 truncate max-w-[200px]">${lkh.deskripsi_aktivitas || '-'}</div>
                </td>
                <td class="px-6 py-4 align-top">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        ${timeRange}
                    </span>
                </td>
                <td class="px-6 py-4 align-top">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shadow-sm shrink-0">
                            ${userInitial}
                        </div>
                        <span class="text-sm text-slate-700 font-bold truncate max-w-[140px]">${userName}</span>
                    </div>
                </td>
                <td class="px-6 py-4 align-top text-center">
                    ${createKategoriBadge(lkh.kategori_lokasi)}
                </td>
                <td class="px-6 py-4 align-top text-center">
                    ${createStatusBadge(lkh.status)}
                </td>
                <td class="px-6 py-4 align-top text-right">
                    <button class="js-open-detail text-xs font-bold text-[#1C7C54] hover:text-[#166443] bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors border border-transparent hover:border-emerald-200"
                        data-lkh-data='${JSON.stringify(lkh).replace(/'/g, "&apos;")}'>
                        Tinjau
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

    // ==== PAGINATION LOGIC ====
    function updatePagination(response) {
        if (!paginationInfo) return;

        const from = response.from || 0;
        const to = response.to || 0;
        const total = response.total || 0;
        const lastPage = response.last_page || 1;
        const current = response.current_page || 1;

        paginationInfo.innerHTML = `Menampilkan <span class="font-bold text-slate-700">${from}-${to}</span> dari <span class="font-bold text-[#1C7C54]">${total}</span> laporan`;

        if (btnPrev) {
            btnPrev.disabled = !response.prev_page_url;
            btnPrev.classList.toggle('opacity-30', !response.prev_page_url);
            btnPrev.classList.toggle('cursor-not-allowed', !response.prev_page_url);
        }
        if (btnNext) {
            btnNext.disabled = !response.next_page_url;
            btnNext.classList.toggle('opacity-30', !response.next_page_url);
            btnNext.classList.toggle('cursor-not-allowed', !response.next_page_url);
        }

        renderPaginationLinks(current, lastPage);
    }

    function renderPaginationLinks(current, lastPage) {
        if (!paginationNumbers) return;
        paginationNumbers.innerHTML = '';

        const createPageBtn = (page, isActive = false) => {
            const btn = document.createElement('button');
            btn.className = isActive
                ? "w-8 h-8 flex items-center justify-center rounded-lg bg-[#1C7C54] text-white text-sm font-bold shadow-md shadow-emerald-700/20 transition-all"
                : "w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-emerald-50 hover:border-emerald-200 hover:text-[#1C7C54] text-sm font-bold transition-all js-page-link";
            btn.textContent = page;
            if (!isActive) btn.dataset.page = page;
            return btn;
        };

        const createEllipsis = () => {
            const span = document.createElement('span');
            span.className = "px-1 text-slate-400 text-sm font-bold cursor-default";
            span.textContent = "...";
            return span;
        };

        if (lastPage <= 7) {
            for (let i = 1; i <= lastPage; i++) {
                paginationNumbers.appendChild(createPageBtn(i, i === current));
            }
        } else {
            paginationNumbers.appendChild(createPageBtn(1, 1 === current));
            if (current > 4) paginationNumbers.appendChild(createEllipsis());

            let start = Math.max(2, current - 1);
            let end = Math.min(lastPage - 1, current + 1);

            if (current <= 4) end = 5;
            if (current >= lastPage - 3) start = lastPage - 4;

            for (let i = start; i <= end; i++) {
                paginationNumbers.appendChild(createPageBtn(i, i === current));
            }

            if (current < lastPage - 3) paginationNumbers.appendChild(createEllipsis());
            paginationNumbers.appendChild(createPageBtn(lastPage, lastPage === current));
        }
    }

    // ==== EVENT HANDLING ====

    if (paginationNumbers) {
        paginationNumbers.addEventListener('click', (e) => {
            const target = e.target.closest('.js-page-link');
            if (target) {
                e.preventDefault();
                const page = parseInt(target.dataset.page);
                if (page && page !== currentPage) {
                    fetchLkhList(page);
                    document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    }

    if (filterStatus) filterStatus.addEventListener('change', () => fetchLkhList(1));
    if (filterMonth) filterMonth.addEventListener('change', () => fetchLkhList(1));
    if (filterYear) filterYear.addEventListener('change', () => fetchLkhList(1));

    if (filterSearch) {
        filterSearch.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => { fetchLkhList(1); }, 500);
        });
    }

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
            if (!btnNext.disabled) {
                fetchLkhList(currentPage + 1);
                document.querySelector('.overflow-x-auto')?.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }


    // ==== MODAL LOGIC ====

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
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: "info",
                        title: "Tidak Ada Bukti",
                        text: "Laporan ini tidak memiliki lampiran bukti.",
                        confirmButtonColor: "#1C7C54",
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

            switch (type) {
                case 'image':
                    thumbnailHtml = `<img src="${bukti.file_url}" class="w-full h-32 object-cover rounded-xl shadow-sm" />`;
                    break;
                case 'pdf':
                    thumbnailHtml = `
                        <div class="w-full h-32 rounded-xl bg-red-50 flex items-center justify-center text-red-500 border border-red-100">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"></path><path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path></svg>
                        </div>`;
                    break;
                case 'video':
                    thumbnailHtml = `
                        <div class="w-full h-32 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 border border-indigo-100">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path></svg>
                        </div>`;
                    break;
                default:
                    thumbnailHtml = `
                        <div class="w-full h-32 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 border border-slate-200">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>
                        </div>`;
                    break;
            }

            const item = document.createElement('div');
            item.className = "bg-white border border-slate-200 rounded-2xl p-2 hover:border-[#1C7C54] hover:shadow-lg transition-all cursor-pointer js-bukti-item group";
            item.innerHTML = `
                ${thumbnailHtml}
                <div class="mt-2 px-1 pb-1">
                    <p class="text-sm font-bold text-slate-700 truncate group-hover:text-[#1C7C54]">Lampiran ${index + 1}</p>
                    <p class="text-[10px] text-slate-400 truncate mt-0.5" title="${bukti.file_url.split('/').pop()}">${bukti.file_url.split('/').pop()}</p>
                </div>
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

        switch (type) {
            case 'image':
                content = `<img src="${bukti.file_url}" class="w-full max-h-[85vh] object-contain rounded-xl shadow-md" />`;
                break;
            case 'pdf':
                content = `<iframe src="${bukti.file_url}" class="w-full h-[80vh] rounded-xl shadow-md"></iframe>`;
                break;
            case 'video':
                content = `
                    <video controls class="w-full max-h-[80vh] rounded-xl shadow-md bg-black">
                        <source src="${bukti.file_url}" type="video/mp4">
                        Maaf, browser Anda tidak mendukung tag video.
                    </video>`;
                break;
            default:
                content = `
                    <div class="flex flex-col items-center justify-center p-12">
                        <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-center text-slate-600 font-bold mb-1">Preview Tidak Tersedia</p>
                        <p class="text-center text-slate-400 text-sm mb-6">Format file ini tidak dapat ditampilkan langsung di browser.</p>
                        <a href="${bukti.file_url}" target="_blank"
                            class="px-6 py-2.5 bg-[#1C7C54] hover:bg-[#166443] text-white font-bold rounded-xl shadow-md transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download File
                        </a>
                    </div>`;
                break;
        }

        previewContent.innerHTML = content;
        openModalFlex(previewModal);
    }

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

    // Logic Modal Detail (INJECTION DATA)
    function openDetailModal(e) {
        const lkhData = JSON.parse(e.currentTarget.dataset.lkhData);
        detailModal.dataset.lkhId = lkhData.id;

        daftarBukti = normalizeBukti(lkhData.bukti || []);
        selectedBukti = null;

        const setMap = {
            'detail-tanggal': formatDate(lkhData.tanggal_laporan),
            'detail-pegawai': lkhData.user?.name || '-',
            'detail-pegawai-jabatan': lkhData.user?.jabatan?.nama_jabatan || 'Pegawai',
            'detail-nama': lkhData.jenis_kegiatan || '-',
            'detail-uraian': lkhData.deskripsi_aktivitas,
            'detail-output': lkhData.output_hasil_kerja || '-',
            'detail-volume': `${lkhData.volume || '-'} ${lkhData.satuan || ''}`,
            'detail-kategori': lkhData.skp_rencana_id ? 'SKP' : 'Non-SKP',
            'detail-jam': `${lkhData.waktu_mulai?.substring(0, 5)} - ${lkhData.waktu_selesai?.substring(0, 5)}`,

            // Lokasi
            'detail-lokasi': lkhData.lokasi_manual_text || (lkhData.is_luar_lokasi ? 'Luar Kantor (GPS)' : 'Dalam Kantor (GPS)'),
        };

        Object.entries(setMap).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        });

        // Split volume dan satuan
        const [volumeValue, ...satuanParts] = setMap['detail-volume'].split(' ');
        const volEl = document.getElementById('detail-volume');
        const satEl = document.getElementById('detail-satuan');
        if (volEl) volEl.textContent = volumeValue;
        if (satEl) satEl.textContent = satuanParts.join(' ');

        // [NEW] Set Kategori Lokasi & Status Badges
        document.getElementById('detail-kategori-lokasi').innerHTML = createKategoriBadge(lkhData.kategori_lokasi);
        document.getElementById('detail-status').innerHTML = createStatusBadge(lkhData.status);

        // Handle Bukti & Catatan
        const catWrap = document.getElementById('detail-catatan-wrapper');
        const catText = document.getElementById('detail-catatan');
        if (lkhData.komentar_validasi) {
            catWrap.classList.remove('hidden');
            catText.textContent = `"${lkhData.komentar_validasi}"`;
        } else {
            catWrap.classList.add('hidden');
        }

        // Handle Tombol Aksi
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

    const btnOpenApprove = document.querySelector('.js-open-approve');
    if (btnOpenApprove) {
        btnOpenApprove.addEventListener('click', () => {
            detailModal.classList.add('hidden');
            document.getElementById('approve-note').value = '';
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
        btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...`;

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
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Laporan berhasil divalidasi.', confirmButtonColor: '#1C7C54' });
                } else {
                    alert('Berhasil memvalidasi laporan.');
                }
            } else {
                const data = await res.json();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Gagal memproses validasi', confirmButtonColor: '#d33' });
                } else {
                    alert(data.message || 'Gagal memproses validasi');
                }
            }
        } catch (e) {
            console.error(e);
            if (typeof Swal !== 'undefined') {
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