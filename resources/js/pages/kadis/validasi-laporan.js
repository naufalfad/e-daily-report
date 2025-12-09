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

    // Modals
    const detailModal = document.getElementById('modal-detail');
    const approveModal = document.getElementById('modal-approve');
    const rejectModal = document.getElementById('modal-reject');

    // Buttons
    const btnSubmitApprove = document.getElementById('btn-submit-approve');
    const btnSubmitReject = document.getElementById('btn-submit-reject');
    const rejectError = document.getElementById('reject-error');

    // STATE MANAGEMENT (Simpan Data Lokal)
    let currentLkhData = []; 

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

    // === MAIN FUNCTION: FETCH DATA ===
    async function fetchLkhList() {
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

        // 2. Build Query Params (Default to Current Date if null)
        const params = new URLSearchParams({
            month: filterMonth ? filterMonth.value : new Date().getMonth() + 1,
            year: filterYear ? filterYear.value : new Date().getFullYear(),
            status: filterStatus ? filterStatus.value : 'all',
            search: filterSearch ? filterSearch.value : '',
            page: 1 
        });

        try {
            // 3. Fetch Data
            const res = await authFetch(`/api/validator/kadis/lkh?${params.toString()}`);
            const json = await res.json();

            if (!res.ok) throw new Error(json.message || "Gagal memuat data");

            // Simpan data ke variabel global agar bisa diakses Modal
            currentLkhData = json.data.data ? json.data.data : json.data;

            renderTable(currentLkhData);

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
            
            // Kita hanya simpan ID di tombol. Data diambil dari currentLkhData
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

    // === EVENT DELEGATION (KUNCI PERBAIKAN TOMBOL DETAIL) ===
    listContainer.addEventListener('click', function(e) {
        // Cek apakah elemen yang diklik adalah (atau ada di dalam) tombol .js-open-detail
        const btn = e.target.closest('.js-open-detail');
        if (btn) {
            const id = btn.dataset.id;
            // Cari data objek lengkap dari array global
            const lkhData = currentLkhData.find(item => item.id == id);
            
            if (lkhData) {
                openDetailModal(lkhData);
            } else {
                console.error("Data LKH tidak ditemukan untuk ID:", id);
            }
        }
    });

    // === MODAL LOGIC ===
    function openDetailModal(lkhData) {
        detailModal.dataset.lkhId = lkhData.id;

        // Helper untuk mengisi teks dengan aman
        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        };

        // Isi Data ke Modal
        setText('detail-tanggal', formatDate(lkhData.tanggal_laporan));
        setText('detail-pegawai', lkhData.user?.name ?? '-');
        setText('detail-nama', lkhData.jenis_kegiatan ?? '-');
        setText('detail-uraian', lkhData.deskripsi_aktivitas ?? '-');
        setText('detail-output', lkhData.output_hasil_kerja ?? '-');
        setText('detail-volume', lkhData.volume ?? '-');
        setText('detail-satuan', lkhData.satuan ?? '-');
        
        // Cek relasi rencana/skp (kadang namanya skp_rencana_id atau skp_id)
        const kategori = lkhData.skp_rencana_id ? 'SKP' : 'Non-SKP';
        setText('detail-kategori', kategori);
        
        setText('detail-jam-mulai', lkhData.waktu_mulai.substring(0, 5));
        setText('detail-jam-selesai', lkhData.waktu_selesai.substring(0, 5));

        let lokasi = lkhData.lokasi_teks || (lkhData.is_luar_lokasi ? 'Luar Kantor' : 'Dalam Kantor (GPS)');
        setText('detail-lokasi', lokasi);

        const statusEl = document.getElementById('detail-status');
        if(statusEl) statusEl.innerHTML = createStatusBadge(lkhData.status);

        // Tombol Bukti
        const buktiBtn = document.getElementById('detail-bukti-btn');
        if (buktiBtn) {
            if (lkhData.bukti && lkhData.bukti.length > 0) {
                buktiBtn.disabled = false;
                const fileUrl = `/storage/${lkhData.bukti[0].file_path}`; 
                buktiBtn.onclick = () => window.open(fileUrl, '_blank');
                buktiBtn.innerHTML = `<i class="fas fa-file-alt"></i> Lihat Bukti (${lkhData.bukti.length})`;
                buktiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                buktiBtn.disabled = true;
                buktiBtn.innerHTML = `<i class="fas fa-eye-slash"></i> Tidak Ada Bukti`;
                buktiBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Catatan Validasi
        const catWrap = document.getElementById('detail-catatan-wrapper');
        if (catWrap) {
            if (lkhData.komentar_validasi) {
                catWrap.classList.remove('hidden');
                setText('detail-catatan', lkhData.komentar_validasi);
            } else {
                catWrap.classList.add('hidden');
            }
        }

        // Tombol Aksi (Hanya muncul jika Waiting Review)
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
                body: JSON.stringify({
                    status: status,
                    komentar_validasi: note || null
                })
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message);

            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: json.message || "Status laporan diperbarui.",
                confirmButtonColor: "#1C7C54",
                timer: 2000,
                showConfirmButton: false
            });

            hide(detailModal);
            hide(approveModal);
            hide(rejectModal);
            
            fetchLkhList(); // Refresh data

        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: err.message,
                confirmButtonColor: "#B6241C"
            });
        }
    }

    // === EVENT LISTENERS ===

    if(filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchLkhList();
        });
    }

    document.querySelector('.js-close-detail')?.addEventListener('click', () => hide(detailModal));
    document.querySelector('.js-close-approve')?.addEventListener('click', () => hide(approveModal));
    document.querySelector('.js-close-reject')?.addEventListener('click', () => hide(rejectModal));

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
    fetchLkhList();
});