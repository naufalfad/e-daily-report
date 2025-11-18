document.addEventListener('DOMContentLoaded', () => {
    const detailModal = document.getElementById('modal-detail');
    const approveModal = document.getElementById('modal-approve');
    const rejectModal = document.getElementById('modal-reject');
    const listContainer = document.getElementById('lkh-validation-list');

    if (!listContainer) return; // Exit if not on the correct page

    // Button hooks
    const btnSubmitApprove = document.getElementById('btn-submit-approve');
    const btnSubmitReject = document.getElementById('btn-submit-reject');
    const rejectError = document.getElementById('reject-error');

    // Utility function to get auth token
    const getToken = () => localStorage.getItem('auth_token');

    // Helper show/hide
    const show = (el) => {
        el.classList.remove('hidden');
        el.classList.add('flex');
    };
    const hide = (el) => {
        el.classList.add('hidden');
        el.classList.remove('flex');
    };
    
    // Helper to format date and time
    const formatDateTime = (isoString) => {
        const date = new Date(isoString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric'
        }) + ' | ' + date.toLocaleTimeString('id-ID', {
            hour: '2-digit', minute: '2-digit'
        });
    };
    
    const formatDate = (isoString) => {
        if (!isoString) return '-';
        try {
            return new Date(isoString).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'long', year: 'numeric'
            });
        } catch (e) {
            return isoString;
        }
    };

    /**
     * Helper untuk membuat badge status
     */
    function createStatusBadge(status) {
        let text = 'Draft';
        let className = 'bg-slate-100 text-slate-600';
        
        if (status === 'waiting_review') {
            text = 'Menunggu';
            className = 'bg-amber-100 text-amber-700';
        } else if (status === 'approved') {
            text = 'Diterima';
            className = 'bg-emerald-100 text-emerald-700';
        } else if (status === 'rejected') {
            text = 'Ditolak';
            className = 'bg-rose-100 text-rose-700';
        }

        return `<span class="px-2 py-0.5 text-xs font-medium rounded-full ${className}">${text}</span>`;
    }

    /**
     * Fetch LKH list from API and render the table
     */
    async function fetchLkhList() {
        listContainer.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-slate-500">Memuat data...</td></tr>';
        
        const token = getToken();
        if (!token) {
            console.error('ERROR: Auth token not found.');
            listContainer.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-rose-600">Sesi berakhir. Mohon Login ulang.</td></tr>';
            return;
        }

        try {
            // Memanggil endpoint API Validator
            const response = await fetch('/api/validator/lkh', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(`Gagal fetch LKH list. Status: ${response.status}. Pesan: ${errorData.message || 'Unknown Error'}`);
            }
            
            const data = await response.json();
            renderTable(data.data); // data.data is the array of LaporanHarian models

        } catch (error) {
            console.error('Error fetching LKH list:', error);
            listContainer.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-rose-600">Gagal memuat laporan. (${error.message})</td></tr>`;
        }
    }

    /**
     * Renders the fetched LKH data into the table body
     * @param {Array} lkhs
     */
    function renderTable(lkhs) {
        listContainer.innerHTML = ''; // Clear loading
        if (lkhs.length === 0) {
            listContainer.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-slate-500">Tidak ada laporan untuk divalidasi.</td></tr>';
            return;
        }

        lkhs.forEach(lkh => {
            const waktu = `${lkh.waktu_mulai.substring(0, 5)} – ${lkh.waktu_selesai.substring(0, 5)}`;
            const row = document.createElement('tr');
            row.className = 'border-t border-slate-200';
            
            // Determine Lokasi Text
            let lokasiText = lkh.lokasi_manual_text || 'Lokasi GPS';
            if (lkh.is_luar_lokasi) {
                lokasiText = 'Luar Kantor';
            }
            
            const statusBadge = createStatusBadge(lkh.status);

            row.innerHTML = `
                <td class="px-4 py-2 whitespace-nowrap">${formatDateTime(lkh.created_at)}</td>
                <td class="px-4 py-2">${lkh.deskripsi_aktivitas.substring(0, 30)}...</td>
                <td class="px-4 py-2 whitespace-nowrap">${waktu}</td>
                <td class="px-4 py-2 whitespace-nowrap">${lkh.user ? lkh.user.name : 'N/A'}</td>
                <td class="px-4 py-2 whitespace-nowrap">${lokasiText}</td>
                <td class="px-4 py-2 whitespace-nowrap text-center">${statusBadge}</td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <button type="button"
                        class="js-open-detail inline-flex items-center justify-center rounded-[6px] bg-[#155FA6] text-white text-[11px] px-3 py-[4px] leading-none hover:brightness-95"
                        data-lkh-id="${lkh.id}"
                        data-lkh-data='${JSON.stringify(lkh)}'>
                        Lihat Detail
                    </button>
                </td>
            `;
            listContainer.appendChild(row);
        });
        
        // Reattach event listeners to the new buttons
        document.querySelectorAll('.js-open-detail').forEach(button => {
            button.addEventListener('click', openDetailModal);
        });
    }

    /**
     * Opens and populates the detail modal
     */
    function openDetailModal(event) {
        const button = event.currentTarget;
        const lkhId = button.dataset.lkhId;
        const lkhData = JSON.parse(button.dataset.lkhData);
        
        // Set LKH ID for validation submissions
        detailModal.dataset.lkhId = lkhId; 
        
        // --- Populate modal fields ---
        document.getElementById('detail-tanggal').textContent = formatDate(lkhData.tanggal_laporan);
        document.getElementById('detail-pegawai').textContent = lkhData.user ? lkhData.user.name : 'N/A';
        document.getElementById('detail-nama').textContent = lkhData.jenis_kegiatan || '-';
        document.getElementById('detail-uraian').textContent = lkhData.deskripsi_aktivitas;
        document.getElementById('detail-output').textContent = lkhData.output_hasil_kerja;
        document.getElementById('detail-volume').textContent = lkhData.volume;
        document.getElementById('detail-satuan').textContent = lkhData.satuan;
        document.getElementById('detail-kategori').textContent = lkhData.skp_id ? 'SKP' : 'Non-SKP';
        document.getElementById('detail-jam').textContent = `${lkhData.waktu_mulai.substring(0, 5)} - ${lkhData.waktu_selesai.substring(0, 5)}`;
        
        // Lokasi Display
        document.getElementById('detail-lokasi').textContent = lkhData.lokasi_manual_text || (lkhData.is_luar_lokasi ? 'Luar Kantor (GPS)' : 'Dalam Kantor (GPS)');
        
        // Status Badge di Modal
        document.getElementById('detail-status').innerHTML = createStatusBadge(lkhData.status);

        // Bukti button
        const buktiBtn = document.getElementById('detail-bukti-btn');
        if (lkhData.bukti && lkhData.bukti.length > 0) {
            buktiBtn.disabled = false;
            const filePath = lkhData.bukti[0].file_url; 
            buktiBtn.onclick = () => window.open(filePath, '_blank');
            buktiBtn.textContent = `Lihat Bukti (${lkhData.bukti.length} file)`;
        } else {
            buktiBtn.disabled = true;
            buktiBtn.textContent = 'Tidak Ada Bukti';
        }
        
        // Catatan Verifikasi Sebelumnya
        const catatanWrapper = document.getElementById('detail-catatan-wrapper');
        const catatanEl = document.getElementById('detail-catatan');
        
        // Menggunakan lkhData.komentar_validasi dan lkhData.validator
        if (lkhData.komentar_validasi) {
            catatanEl.textContent = `Dari ${lkhData.validator ? lkhData.validator.name : 'Penilai'}: ${lkhData.komentar_validasi}`;
            catatanWrapper.classList.remove('hidden');
        } else {
            catatanWrapper.classList.add('hidden');
        }

        // Tampilkan/Sembunyikan Tombol Aksi Validasi berdasarkan Status
        const validationActions = document.getElementById('validation-actions');
        const validationInfo = document.getElementById('validation-info');

        if (lkhData.status === 'waiting_review') {
            show(validationActions);
            hide(validationInfo);
        } else {
            hide(validationActions);
            show(validationInfo);
        }

        // Show detail modal
        show(detailModal);
    }

    /**
     * Handles the final validation submission (Approve or Reject)
     */
    async function submitValidation(status, note) {
        const lkhId = detailModal.dataset.lkhId;
        if (!lkhId) return alert('Error: ID LKH tidak ditemukan.');

        // Disable buttons and show loading
        const targetModal = status === 'approved' ? approveModal : rejectModal;
        const submitBtn = status === 'approved' ? btnSubmitApprove : btnSubmitReject;
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses...';

        try {
            const response = await fetch(`/api/validator/lkh/${lkhId}/validate`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: status,
                    komentar_validasi: note || null 
                })
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message);
                // Close all modals
                hide(detailModal);
                hide(targetModal);
                // Clear note fields
                document.getElementById('approve-note').value = '';
                document.getElementById('reject-note').value = '';

                // Refresh list
                fetchLkhList();
            } else {
                let msg = result.message || 'Gagal memproses validasi.';
                if (result.errors) {
                    msg += '\n\nDetail: ' + Object.values(result.errors).flat().join(', ');
                }
                alert(msg);
            }
        } catch (error) {
            console.error('Validation Submission Error:', error);
            alert('Terjadi kesalahan koneksi saat memproses validasi.');
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }


    // --- Modal Control Events ---

    // Close Detail Modal (Tutup detail saat tombol X diklik)
    detailModal.querySelector('.js-close-detail').addEventListener('click', () => hide(detailModal));
    detailModal.addEventListener('click', (e) => {
        if (e.target === detailModal) hide(detailModal);
    });

    // Open Approve Modal
    document.querySelector('.js-open-approve').addEventListener('click', () => {
        hide(detailModal);
        show(approveModal);
    });

    // Close Approve Modal
    document.querySelector('.js-close-approve').addEventListener('click', () => {
        hide(approveModal);
        document.getElementById('approve-note').value = '';
    });
    
    // Open Reject Modal
    document.querySelector('.js-open-reject').addEventListener('click', () => {
        hide(detailModal);
        document.getElementById('reject-note').value = '';
        rejectError.classList.add('hidden');
        show(rejectModal);
    });

    // Close Reject Modal
    document.querySelector('.js-close-reject').addEventListener('click', () => {
        hide(rejectModal);
        document.getElementById('reject-note').value = '';
        rejectError.classList.add('hidden');
    });
    
    // Submit Approve Logic
    btnSubmitApprove.addEventListener('click', () => {
        const note = document.getElementById('approve-note').value;
        submitValidation('approved', note);
    });
    
    // Submit Reject Logic
    btnSubmitReject.addEventListener('click', () => {
        const note = document.getElementById('reject-note').value;
        if (!note.trim()) {
            rejectError.classList.remove('hidden');
            document.getElementById('reject-note').focus();
            return;
        }
        rejectError.classList.add('hidden');
        submitValidation('rejected', note);
    });


    // Initial Load
    fetchLkhList();
});