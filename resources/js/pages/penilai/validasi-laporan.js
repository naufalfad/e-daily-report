// resources/js/pages/penilai/validasi-laporan.js

document.addEventListener('DOMContentLoaded', () => {
    const detailModal  = document.getElementById('modal-detail');
    const approveModal = document.getElementById('modal-approve');
    const rejectModal  = document.getElementById('modal-reject');

    if (!detailModal) {
        // halaman lain, tidak perlu jalan
        return;
    }

    // ======== Helper show/hide ========
    const show = (el) => {
        el.classList.remove('hidden');
        el.classList.add('flex');
    };
    const hide = (el) => {
        el.classList.add('hidden');
        el.classList.remove('flex');
    };

    // ======== OPEN DETAIL ========
    const detailFields = {
        tanggal:  document.getElementById('detail-tanggal'),
        nama:     document.getElementById('detail-nama'),
        uraian:   document.getElementById('detail-uraian'),
        output:   document.getElementById('detail-output'),
        volume:   document.getElementById('detail-volume'),
        satuan:   document.getElementById('detail-satuan'),
        kategori: document.getElementById('detail-kategori'),
        jamMulai: document.getElementById('detail-jam-mulai'),
        jamSelesai: document.getElementById('detail-jam-selesai'),
        lokasi:   document.getElementById('detail-lokasi'),
        pegawai:  document.getElementById('detail-pegawai'),
        buktiBtn: document.getElementById('detail-bukti-btn'),
    };

    let currentBukti = null;

    document.querySelectorAll('.js-open-detail').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            detailFields.tanggal.textContent   = d.tanggal || '-';
            detailFields.nama.textContent      = d.nama || '-';
            detailFields.uraian.textContent    = d.uraian || '-';
            detailFields.output.textContent    = d.output || '-';
            detailFields.volume.textContent    = d.volume || '-';
            detailFields.satuan.textContent    = d.satuan || '-';
            detailFields.kategori.textContent  = d.kategori || '-';
            detailFields.jamMulai.textContent  = d.jamMulai || '-';
            detailFields.jamSelesai.textContent= d.jamSelesai || '-';
            detailFields.lokasi.textContent    = d.lokasi || '-';
            detailFields.pegawai.textContent   = d.pegawai || '-';

            currentBukti = d.bukti || null;

            show(detailModal);
        });
    });

    // tombol close detail
    detailModal.querySelectorAll('.js-close-detail').forEach(btn => {
        btn.addEventListener('click', () => hide(detailModal));
    });

    // klik di backdrop detail -> close
    detailModal.addEventListener('click', (e) => {
        if (e.target === detailModal) hide(detailModal);
    });

    // tombol "Lihat Bukti" (sementara cuma alert nama file)
    if (detailFields.buktiBtn) {
        detailFields.buktiBtn.addEventListener('click', () => {
            if (currentBukti) {
                alert(`Bukti: ${currentBukti}`);
            } else {
                alert('Bukti belum tersedia.');
            }
        });
    }

    // ======== APPROVE MODAL ========
    const openApproveButtons = document.querySelectorAll('.js-open-approve');
    const closeApproveButtons = approveModal.querySelectorAll('.js-close-approve');

    openApproveButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            hide(detailModal);
            show(approveModal);
        });
    });

    closeApproveButtons.forEach(btn => {
        btn.addEventListener('click', () => hide(approveModal));
    });

    approveModal.addEventListener('click', (e) => {
        if (e.target === approveModal) hide(approveModal);
    });

    // ======== REJECT MODAL ========
    const openRejectButtons = document.querySelectorAll('.js-open-reject');
    const closeRejectButtons = rejectModal.querySelectorAll('.js-close-reject');

    const rejectNote  = document.getElementById('reject-note');
    const rejectError = document.getElementById('reject-error');
    const btnSubmitReject = document.getElementById('btn-submit-reject');

    openRejectButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            hide(detailModal);
            rejectNote.value = '';
            rejectError.classList.add('hidden');
            show(rejectModal);
        });
    });

    closeRejectButtons.forEach(btn => {
        btn.addEventListener('click', () => hide(rejectModal));
    });

    rejectModal.addEventListener('click', (e) => {
        if (e.target === rejectModal) hide(rejectModal);
    });

    // validasi simple: catatan wajib diisi
    if (btnSubmitReject) {
        btnSubmitReject.addEventListener('click', () => {
            if (!rejectNote.value.trim()) {
                rejectError.classList.remove('hidden');
                rejectNote.focus();
                return;
            }

            // Di sini nanti bisa diganti submit form / fetch API
            alert('Laporan ditolak dengan catatan:\n' + rejectNote.value.trim());
            hide(rejectModal);
        });
    }

    // ESC untuk nutup modal apapun yang terbuka
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            [detailModal, approveModal, rejectModal].forEach(m => {
                if (m && !m.classList.contains('hidden')) hide(m);
            });
        }
    });
});
