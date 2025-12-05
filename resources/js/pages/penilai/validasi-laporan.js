document.addEventListener('DOMContentLoaded', () => {
    // ==== DOM ELEMENTS ====
    const detailModal = document.getElementById('modal-detail');
    const approveModal = document.getElementById('modal-approve');
    const rejectModal = document.getElementById('modal-reject');
    const listContainer = document.getElementById('lkh-validation-list');

    if (!listContainer) return;

    const btnSubmitApprove = document.getElementById('btn-submit-approve');
    const btnSubmitReject = document.getElementById('btn-submit-reject');
    const rejectError = document.getElementById('reject-error');

    // ==== HELPERS ====
    const getToken = () => localStorage.getItem('auth_token');
    const show = (el) => { el.classList.remove('hidden'); setTimeout(() => el.classList.remove('opacity-0'), 10); };
    const hide = (el) => { el.classList.add('opacity-0'); setTimeout(() => el.classList.add('hidden'), 300); };

    const formatDate = (isoString) => {
        if (!isoString) return '-';
        try {
            return new Date(isoString).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
        } catch {
            return isoString;
        }
    };

    const formatTime = (isoString) => {
        if (!isoString) return '';
        try {
            return new Date(isoString).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            }).replace('.', ':');
        } catch {
            return '';
        }
    };

    const getInitial = (name) => name ? name.charAt(0).toUpperCase() : '?';

    const createStatusBadge = (status) => {
        const config = {
            'waiting_review': {
                css: 'bg-amber-50 text-amber-600 border-amber-100',
                label: 'Pending'
            },
            'approved': {
                css: 'bg-emerald-50 text-emerald-600 border-emerald-100',
                label: 'Diterima'
            },
            'rejected': {
                css: 'bg-rose-50 text-rose-600 border-rose-100',
                label: 'Ditolak'
            }
        };

        const style = config[status] || config['waiting_review'];

        return `
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ${style.css}">
                ${style.label}
            </span>
        `;
    };

    // ==== FETCH DATA ====
    async function fetchLkhList() {
        listContainer.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-slate-400 italic">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span>Memuat data laporan...</span>
                    </div>
                </td>
            </tr>`;

        const token = getToken();

        if (!token) {
            listContainer.innerHTML = `
                <tr>
                    <td colspan="7" class="p-6 text-center text-rose-500 font-medium">
                        Sesi berakhir. Silakan login ulang.
                    </td>
                </tr>`;
            return;
        }

        try {
            const response = await fetch('/api/validator/lkh', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            if (!response.ok) throw await response.json();
            const data = await response.json();
            renderTable(data.data);

        } catch (err) {
            console.error(err);
            listContainer.innerHTML = `
                <tr>
                    <td colspan="7" class="p-6 text-center text-rose-500">
                        Gagal memuat data. Silakan refresh halaman.
                    </td>
                </tr>`;
        }
    }

    // ==== RENDER TABLE ====
    function renderTable(lkhs) {
        listContainer.innerHTML = '';

        if (lkhs.length === 0) {
            listContainer.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <p class="font-medium">Tidak ada laporan baru</p>
                        <p class="text-xs text-slate-400 mt-1">Saat ini belum ada laporan yang perlu divalidasi.</p>
                    </td>
                </tr>`;
            return;
        }

        lkhs.forEach(lkh => {
            const row = document.createElement('tr');
            row.className = "hover:bg-slate-50/80 transition-colors group border-b border-slate-50 last:border-none";

            const dateStr = formatDate(lkh.created_at);
            const timeStr = formatTime(lkh.created_at);
            const timeRange = `${lkh.waktu_mulai?.substring(0, 5)} – ${lkh.waktu_selesai?.substring(0, 5)}`;
            const userName = lkh.user ? lkh.user.name : 'Unknown';
            const userInitial = getInitial(userName);

            let locationText = lkh.lokasi_manual_text || 'Lokasi GPS';
            if (lkh.is_luar_lokasi) locationText = 'Luar Kantor';

            row.innerHTML = `
                <td class="px-6 py-4 align-top">
                    <div class="text-sm font-semibold text-slate-700">${dateStr}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">${timeStr}</div>
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="text-sm font-medium text-slate-900 truncate" title="${lkh.deskripsi_aktivitas}">
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
                        <div class="h-8 w-8 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold">
                            ${userInitial}
                        </div>
                        <span class="text-sm text-slate-700 font-medium truncate max-w-[140px]">${userName}</span>
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="text-sm text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate max-w-[120px]">${locationText}</span>
                    </div>
                </td>

                <td class="px-6 py-4 align-top text-center">
                    ${createStatusBadge(lkh.status)}
                </td>

                <td class="px-6 py-4 align-top text-right">
                    <button 
                        class="js-open-detail text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline decoration-blue-600/30 underline-offset-4 transition-all"
                        data-lkh-data='${JSON.stringify(lkh)}'>
                        Lihat Detail
                    </button>
                </td>
            `;

            listContainer.appendChild(row);
        });

        document.querySelectorAll('.js-open-detail').forEach(btn => {
            btn.addEventListener('click', openDetailModal);
        });
    }

    // ==== OPEN DETAIL MODAL ====
    function openDetailModal(e) {
        const lkhData = JSON.parse(e.currentTarget.dataset.lkhData);
        detailModal.dataset.lkhId = lkhData.id;

        const setMap = {
            'detail-tanggal': formatDate(lkhData.tanggal_laporan),
            'detail-pegawai': lkhData.user?.name || '-',
            'detail-nama': lkhData.jenis_kegiatan || '-',
            'detail-uraian': lkhData.deskripsi_aktivitas,
            'detail-output': lkhData.output_hasil_kerja,
            'detail-volume': lkhData.volume,
            'detail-satuan': lkhData.satuan,
            'detail-kategori': lkhData.skp_id ? 'SKP' : 'Non-SKP',
            'detail-jam': `${lkhData.waktu_mulai?.substring(0, 5)} - ${lkhData.waktu_selesai?.substring(0, 5)}`,
            'detail-lokasi': lkhData.lokasi_manual_text || (lkhData.is_luar_lokasi ? 'Luar Kantor' : 'Dalam Kantor')
        };

        Object.entries(setMap).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        });

        document.getElementById('detail-status').innerHTML = createStatusBadge(lkhData.status);

        const catWrap = document.getElementById('detail-catatan-wrapper');
        const catText = document.getElementById('detail-catatan');

        if (lkhData.komentar_validasi) {
            catWrap.classList.remove('hidden');
            catText.textContent = `"${lkhData.komentar_validasi}"`;
        } else {
            catWrap.classList.add('hidden');
        }

        const buktiBtn = document.getElementById('detail-bukti-btn');

        if (buktiBtn) {
            if (lkhData.bukti && lkhData.bukti.length > 0) {
                buktiBtn.disabled = false;
                buktiBtn.innerHTML = `
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    Lihat Bukti (${lkhData.bukti.length})
                `;
                buktiBtn.onclick = () => window.open(lkhData.bukti[0].file_url, "_blank");
            } else {
                buktiBtn.disabled = true;
                buktiBtn.innerHTML = `
                    <svg class="w-4 h-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Tidak Ada Bukti
                `;
            }
        }

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

        detailModal.classList.remove('hidden');
    }

    // ==== CLOSE HANDLER ====
    document.querySelectorAll('.js-close-detail').forEach(btn => {
        btn.addEventListener('click', () => detailModal.classList.add('hidden'));
    });

    document.querySelectorAll('.js-close-approve').forEach(btn => {
        btn.addEventListener('click', () => approveModal.classList.add('hidden'));
    });

    document.querySelectorAll('.js-close-reject').forEach(btn => {
        btn.addEventListener('click', () => rejectModal.classList.add('hidden'));
    });

    window.addEventListener('click', (e) => {
        if (e.target.dataset.backdrop) e.target.classList.add('hidden');
        if (e.target === detailModal || e.target === approveModal || e.target === rejectModal) {
            e.target.classList.add('hidden');
        }
    });

    // ==== ACTION BUTTONS & MODAL UI FIX ====
    const btnOpenApprove = document.querySelector('.js-open-approve');
    const btnOpenReject = document.querySelector('.js-open-reject');

    // Helper untuk buka modal dengan Display Flex (Agar Posisi Tengah)
    function openModalFlex(modalEl) {
        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex'); // PENTING: Tambah flex agar items-center bekerja
    }

    // Helper untuk tutup modal
    function closeModalFlex(modalEl) {
        modalEl.classList.add('hidden');
        modalEl.classList.remove('flex');
    }

    if (btnOpenApprove) {
        btnOpenApprove.onclick = () => {
            detailModal.classList.add('hidden'); // Tutup detail
            openModalFlex(approveModal); // Buka approve
        };
    }

    if (btnOpenReject) {
        btnOpenReject.onclick = () => {
            detailModal.classList.add('hidden'); // Tutup detail
            document.getElementById('reject-note').value = '';
            rejectError.classList.add('hidden');
            openModalFlex(rejectModal); // Buka reject
        };
    }

    // Update event listener untuk tombol close bawaan
    document.querySelectorAll('.js-close-approve').forEach(btn => {
        btn.addEventListener('click', () => closeModalFlex(approveModal));
    });

    document.querySelectorAll('.js-close-reject').forEach(btn => {
        btn.addEventListener('click', () => closeModalFlex(rejectModal));
    });

    // Update window click listener agar handle flex modal juga
    window.addEventListener('click', (e) => {
        if (e.target === detailModal) detailModal.classList.add('hidden');
        if (e.target === approveModal) closeModalFlex(approveModal);
        if (e.target === rejectModal) closeModalFlex(rejectModal);
    });


    // ==== SUBMIT VALIDATION (REVISI ERROR BTN) ====
    // Kita tambah parameter 'buttonElement' agar fungsi tahu tombol mana yang dipencet
    async function submitValidation(status, note, buttonElement) {
        const lkhId = detailModal.dataset.lkhId;
        const token = getToken();
        
        // Simpan teks asli tombol
        const originalText = buttonElement.innerHTML;

        // Set Loading State
        buttonElement.disabled = true;
        buttonElement.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg> Memproses...`;

        try {
            const res = await fetch(`/api/validator/lkh/${lkhId}/validate`, {
                method: "POST",
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status, komentar_validasi: note || null })
            });

            const result = await res.json();

            if (res.ok) {
                // Tutup modal yang aktif berdasarkan status
                if (status === 'approved') closeModalFlex(approveModal);
                else closeModalFlex(rejectModal);

                // Refresh data tabel
                fetchLkhList();
                
                // Opsional: Tampilkan notifikasi sukses sederhana
                alert("Berhasil memvalidasi laporan."); 
            } else {
                alert(result.message || "Gagal memproses validasi.");
            }
        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan jaringan.");
        } finally {
            // Restore Button State
            if (buttonElement) {
                buttonElement.disabled = false;
                buttonElement.innerHTML = originalText;
            }
        }
    }

    // Panggil submitValidation dengan mengirimkan 'this' (tombol itu sendiri) atau referensi variabel
    btnSubmitApprove?.addEventListener('click', () => {
        const note = document.getElementById('approve-note').value;
        submitValidation('approved', note, btnSubmitApprove); // <-- FIX: Kirim elemen tombol
    });

    btnSubmitReject?.addEventListener('click', () => {
        const note = document.getElementById('reject-note').value;
        if (!note.trim()) {
            rejectError.classList.remove('hidden');
            return;
        }
        submitValidation('rejected', note, btnSubmitReject); // <-- FIX: Kirim elemen tombol
    });

    // ==== INIT ====
    fetchLkhList();
});
