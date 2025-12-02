document.addEventListener('DOMContentLoaded', () => {

    const detailModal = document.getElementById('modal-detail');
    const approveModal = document.getElementById('modal-approve');
    const rejectModal = document.getElementById('modal-reject');
    const listContainer = document.getElementById('lkh-validation-list');

    if (!listContainer) return;

    const btnSubmitApprove = document.getElementById('btn-submit-approve');
    const btnSubmitReject = document.getElementById('btn-submit-reject');
    const rejectError = document.getElementById('reject-error');

    const show = (el) => {
        el.classList.remove('hidden');
        el.classList.add('flex');
    };
    const hide = (el) => {
        el.classList.add('hidden');
        el.classList.remove('flex');
    };

    const getToken = () => localStorage.getItem('auth_token');

    const formatDate = (iso) => {
        try {
            return new Date(iso).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        } catch (_) {
            return iso;
        }
    };

    const createStatusBadge = (status) => {
        if (status === 'waiting_review')
            return `<span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700">Menunggu</span>`;
        if (status === 'approved')
            return `<span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700">Diterima</span>`;
        if (status === 'rejected')
            return `<span class="px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-700">Ditolak</span>`;
        return `<span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600">Draft</span>`;
    };

    async function fetchLkhList() {
        const APP_URL = window.APP_URL;
        listContainer.innerHTML =
            '<tr><td colspan="7" class="p-4 text-center text-slate-500">Memuat data...</td></tr>';

        const token = getToken();
        if (!token) return;

        try {
            const res = await fetch(`${APP_URL}/api/validator/lkh`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message);

            renderTable(json.data);

        } catch (err) {
            listContainer.innerHTML =
                `<tr><td colspan="7" class="p-4 text-center text-rose-600">${err.message}</td></tr>`;
        }
    }

    function renderTable(lkhs) {
        listContainer.innerHTML = '';

        if (!lkhs.length) {
            listContainer.innerHTML =
                '<tr><td colspan="7" class="p-4 text-center text-slate-500">Tidak ada laporan untuk divalidasi.</td></tr>';
            return;
        }

        lkhs.forEach((lkh) => {
            let waktu = `${lkh.waktu_mulai.substring(0, 5)} – ${lkh.waktu_selesai.substring(0, 5)}`;
            let lokasi = lkh.lokasi_manual_text || (lkh.is_luar_lokasi ? 'Luar Kantor' : 'GPS Dalam Kantor');

            listContainer.innerHTML += `
                <tr class="border-t border-slate-200">
                    <td class="px-4 py-2 whitespace-nowrap">${formatDate(lkh.created_at)}</td>
                    <td class="px-4 py-2">${lkh.deskripsi_aktivitas.substring(0, 40)}...</td>
                    <td class="px-4 py-2 whitespace-nowrap">${waktu}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${lkh.user?.name ?? '-'}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${lokasi}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${createStatusBadge(lkh.status)}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <button type="button"
                            class="js-open-detail inline-flex items-center justify-center rounded-[6px] bg-[#155FA6] text-white text-[11px] px-3 py-[4px]"
                            data-lkh-id="${lkh.id}"
                            data-lkh-data='${JSON.stringify(lkh)}'>
                            Lihat Detail
                        </button>
                    </td>
                </tr>
            `;
        });

        document.querySelectorAll('.js-open-detail').forEach((btn) =>
            btn.addEventListener('click', openDetailModal)
        );
    }


    // ========================
    // === OPEN DETAIL MODAL ==
    // ========================
    function openDetailModal(event) {

        const lkhData = JSON.parse(event.currentTarget.dataset.lkhData);

        detailModal.dataset.lkhId = lkhData.id;

        document.getElementById('detail-tanggal').textContent = formatDate(lkhData.tanggal_laporan);
        document.getElementById('detail-pegawai').textContent = lkhData.user?.name ?? '-';
        document.getElementById('detail-nama').textContent = lkhData.jenis_kegiatan ?? '-';
        document.getElementById('detail-uraian').textContent = lkhData.deskripsi_aktivitas ?? '-';

        document.getElementById('detail-output').textContent = lkhData.output_hasil_kerja ?? '-';
        document.getElementById('detail-volume').textContent = lkhData.volume ?? '-';
        document.getElementById('detail-satuan').textContent = lkhData.satuan ?? '-';
        document.getElementById('detail-kategori').textContent = lkhData.skp_id ? 'SKP' : 'Non-SKP';

        document.getElementById('detail-jam-mulai').textContent = lkhData.waktu_mulai.substring(0, 5);
        document.getElementById('detail-jam-selesai').textContent = lkhData.waktu_selesai.substring(0, 5);

        let lokasi = lkhData.lokasi_manual_text || (lkhData.is_luar_lokasi ? 'Luar Kantor' : 'Dalam Kantor (GPS)');
        document.getElementById('detail-lokasi').textContent = lokasi;

        document.getElementById('detail-status').innerHTML = createStatusBadge(lkhData.status);

        const buktiBtn = document.getElementById('detail-bukti-btn');
        if (lkhData.bukti?.length) {
            buktiBtn.disabled = false;
            buktiBtn.textContent = `Lihat Bukti (${lkhData.bukti.length})`;
            buktiBtn.onclick = () => window.open(lkhData.bukti[0].file_url, '_blank');
        } else {
            buktiBtn.disabled = true;
            buktiBtn.textContent = 'Tidak Ada Bukti';
        }

        const catWrap = document.getElementById('detail-catatan-wrapper');
        const catNote = document.getElementById('detail-catatan');

        if (lkhData.komentar_validasi) {
            catWrap.classList.remove('hidden');
            catNote.textContent =
                `${lkhData.validator?.name ?? 'Validator'}: ${lkhData.komentar_validasi}`;
        } else {
            catWrap.classList.add('hidden');
        }

        const actions = document.getElementById('validation-actions');
        const info = document.getElementById('validation-info');

        if (lkhData.status === 'waiting_review') {
            actions.classList.remove('hidden');
            info.classList.add('hidden');
        } else {
            actions.classList.add('hidden');
            info.classList.remove('hidden');
        }

        show(detailModal);
    }


    // ======================================
    // =========== SUBMIT VALIDASI ==========
    // ======================================
    async function submitValidation(status, note) {
        const APP_URL = window.APP_URL;
        const lkhId = detailModal.dataset.lkhId;

        const token = getToken();

        try {
            const res = await fetch(`${APP_URL}/api/validator/lkh/${lkhId}/validate`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: status,
                    komentar_validasi: note || null
                })
            });

            const json = await res.json();

            if (!res.ok) throw new Error(json.message);

            Swal.fire({
                icon: "success",
                title: status === "approved" ? "Laporan Diterima" : "Laporan Ditolak",
                text: "Validasi berhasil!",
                confirmButtonColor: "#1C7C54"
            });

            hide(detailModal);
            hide(approveModal);
            hide(rejectModal);

            fetchLkhList();

        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Gagal Memproses",
                text: err.message,
                confirmButtonColor: "#B6241C"
            });
        }
    }


    // ======================
    // ==== EVENT HANDLER ===
    // ======================

    document.querySelector('.js-close-detail').addEventListener('click', () => hide(detailModal));

    // open approve
    document.querySelector('.js-open-approve').addEventListener('click', () => {
        hide(detailModal);
        show(approveModal);
    });

    // close approve
    document.querySelector('.js-close-approve').addEventListener('click', () => {
        hide(approveModal);
        document.getElementById('approve-note').value = '';
    });

    btnSubmitApprove.addEventListener('click', () => {
        const note = document.getElementById('approve-note').value;

        Swal.fire({
            title: "Terima Laporan?",
            text: "Apakah laporan ini sudah sesuai?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#0E7A4A",
            cancelButtonColor: "#777",
            confirmButtonText: "Terima"
        }).then((result) => {
            if (result.isConfirmed) {
                submitValidation('approved', note);
            }
        });
    });

    // open reject
    document.querySelector('.js-open-reject').addEventListener('click', () => {
        hide(detailModal);
        show(rejectModal);
        rejectError.classList.add('hidden');
        document.getElementById('reject-note').value = '';
    });

    // close reject
    document.querySelector('.js-close-reject').addEventListener('click', () => {
        hide(rejectModal);
        rejectError.classList.add('hidden');
        document.getElementById('reject-note').value = '';
    });

    // submit reject
    btnSubmitReject.addEventListener('click', () => {
        const note = document.getElementById('reject-note').value;

        if (!note.trim()) {
            rejectError.classList.remove('hidden');
            return;
        }

        Swal.fire({
            title: "Tolak Laporan?",
            text: "Catatan sudah diisi dan laporan akan dikembalikan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#B6241C",
            cancelButtonColor: "#777",
            confirmButtonText: "Tolak"
        }).then((result) => {
            if (result.isConfirmed) {
                submitValidation('rejected', note);
            }
        });
    });

    fetchLkhList();
});
