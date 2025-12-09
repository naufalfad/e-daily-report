document.addEventListener('DOMContentLoaded', () => {
    // ==== DOM ELEMENTS ====
    const listContainer = document.getElementById('lkh-validation-list');
    
    // Filters Elements (NEW)
    const filterStatus = document.getElementById('filter-status');
    const filterMonth = document.getElementById('filter-month');
    const filterYear = document.getElementById('filter-year');
    const filterSearch = document.getElementById('filter-search');

    // Pagination Elements (NEW)
    const btnPrev = document.getElementById('prev-page');
    const btnNext = document.getElementById('next-page');
    const paginationInfo = document.getElementById('pagination-info');

    // Modals
    const detailModal = document.getElementById('modal-detail');
    const approveModal = document.getElementById('modal-approve');
    const rejectModal = document.getElementById('modal-reject');
    
    // Action Buttons
    const btnSubmitApprove = document.getElementById('btn-submit-approve');
    const btnSubmitReject = document.getElementById('btn-submit-reject');
    const rejectError = document.getElementById('reject-error');

    // State Variables
    let currentPage = 1;
    let searchTimeout = null;

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

    const formatTime = (isoString) => {
        if (!isoString) return '';
        try {
            return new Date(isoString).toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit'
            }).replace('.', ':');
        } catch { return ''; }
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

        // Construct Query Params (LOGIC BARU)
        const params = new URLSearchParams({
            page: page,
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
            
            // Render Data & Update Pagination
            renderTable(data.data);
            updatePagination(data);
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

    // ==== PAGINATION UPDATE ====
    function updatePagination(response) {
        if (!paginationInfo) return;
        
        const from = response.from || 0;
        const to = response.to || 0;
        const total = response.total || 0;
        
        paginationInfo.textContent = `Menampilkan ${from}-${to} dari ${total} data`;
        
        // Laravel Pagination JSON structure usually has prev_page_url & next_page_url
        if (btnPrev) btnPrev.disabled = !response.prev_page_url;
        if (btnNext) btnNext.disabled = !response.next_page_url;
    }

    // ==== FILTER EVENT LISTENERS (LOGIC BARU) ====
    if (filterStatus) filterStatus.addEventListener('change', () => fetchLkhList(1));
    if (filterMonth) filterMonth.addEventListener('change', () => fetchLkhList(1));
    if (filterYear) filterYear.addEventListener('change', () => fetchLkhList(1));
    
    // Search with Debounce (Agar tidak spam API)
    if (filterSearch) {
        filterSearch.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchLkhList(1);
            }, 500); 
        });
    }

    // Pagination Listeners
    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (currentPage > 1) fetchLkhList(currentPage - 1);
        });
    }
    
    if (btnNext) {
        btnNext.addEventListener('click', () => {
            fetchLkhList(currentPage + 1);
        });
    }

    // ==== MODAL LOGIC (Detail, Approve, Reject) ====
    function openDetailModal(e) {
        const lkhData = JSON.parse(e.currentTarget.dataset.lkhData);
        detailModal.dataset.lkhId = lkhData.id;

        const setMap = {
            'detail-tanggal': formatDate(lkhData.tanggal_laporan),
            'detail-pegawai': lkhData.user?.name || '-',
            'detail-nama': lkhData.jenis_kegiatan || '-',
            'detail-uraian': lkhData.deskripsi_aktivitas,
            'detail-output': lkhData.output_hasil_kerja || '-',
            'detail-volume': lkhData.volume || '-',
            'detail-satuan': lkhData.satuan || '',
            'detail-kategori': lkhData.skp_rencana_id ? 'SKP' : 'Non-SKP',
            'detail-jam': `${lkhData.waktu_mulai?.substring(0, 5)} - ${lkhData.waktu_selesai?.substring(0, 5)}`,
            'detail-lokasi': lkhData.lokasi_manual_text || (lkhData.is_luar_lokasi ? 'Luar Kantor' : 'Dalam Kantor')
        };

        Object.entries(setMap).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        });

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
            if (lkhData.bukti && lkhData.bukti.length > 0) {
                buktiBtn.disabled = false;
                buktiBtn.onclick = () => window.open(lkhData.bukti[0].file_url, "_blank");
            } else {
                buktiBtn.disabled = true;
            }
        }

        detailModal.classList.remove('hidden');
    }

    // Modal Close Logic
    const closeModals = () => {
        detailModal.classList.add('hidden');
        approveModal.classList.add('hidden');
        approveModal.classList.remove('flex');
        rejectModal.classList.add('hidden');
        rejectModal.classList.remove('flex');
    };

    document.querySelectorAll('.js-close-detail, .js-close-approve, .js-close-reject').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });

    // Helper untuk buka modal confirm
    function openModalFlex(modalEl) {
        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
    }

    // Button Open Approve/Reject from Detail
    const btnOpenApprove = document.querySelector('.js-open-approve');
    if (btnOpenApprove) {
        btnOpenApprove.addEventListener('click', () => {
            detailModal.classList.add('hidden');
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
                body: JSON.stringify({ status, komentar_validasi: note })
            });

            if (res.ok) {
                closeModals();
                fetchLkhList(currentPage); // Refresh list maintain current page
                alert('Berhasil memvalidasi laporan.');
            } else {
                const data = await res.json();
                alert(data.message || 'Gagal memproses validasi');
            }
        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan jaringan');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    if (btnSubmitApprove) {
        btnSubmitApprove.addEventListener('click', () => {
            submitValidation('approved', document.getElementById('approve-note').value, btnSubmitApprove);
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