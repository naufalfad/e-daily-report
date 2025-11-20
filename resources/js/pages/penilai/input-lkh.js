document.addEventListener('DOMContentLoaded', function () {
    
    // --- LOGIKA SUBMIT FORM LKH BARU (DITAMBAHKAN) ---
    const formLkh = document.getElementById('form-lkh');

    if (formLkh) {
        formLkh.addEventListener('submit', async function (event) {
            event.preventDefault(); // Mencegah submit HTML default!

            // Tampilkan loading / disable tombol
            const submitButton = formLkh.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = 'Mengirim...'; // Feedback ke user

            // Ambil data formulir, termasuk file, menggunakan FormData
            // Ini PENTING untuk mengirim file (bukti[])
            const formData = new FormData(formLkh);
            
            // Hapus skp_id jika kategori adalah non-skp
            // Logika Alpine.js di blade file mungkin belum memperbarui hidden input
            // secara bersih, jadi kita pastikan skp_id kosong jika kategori non-skp.
            // Walaupun lebih baik dihandle di FE state, ini adalah fail-safe.
            if (formData.get('kategori') === 'non-skp') {
                formData.set('skp_id', '');
            }
            
            // Karena Alpine.js tidak menginisialisasi input Satuan/skpId/jenis_kegiatan
            // jika tidak diklik, kita harus pastikan semua field memiliki nilai. 
            // Namun, untuk kasus ini, kita fokus pada POST request-nya.

            try {
                const token = localStorage.getItem('auth_token');
                const response = await fetch('/api/lkh', { // Asumsi API endpoint adalah /api/lkh
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        // Content-Type TIDAK BOLEH diset manual saat menggunakan FormData
                        // karena browser akan mengaturnya secara otomatis (multipart/form-data)
                        'Accept': 'application/json' 
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    // Sukses: Tampilkan notifikasi dan reset form
                    alert('Laporan Harian berhasil dikirim!');
                    formLkh.reset();
                    // Opsional: Reload data tupoksi dan SKP jika perlu
                    // Di sini kita hanya menavigasi atau mereset state.
                } else {
                    // Gagal: Tampilkan pesan error dari backend
                    const errorMessage = result.message || 'Terjadi kesalahan saat menyimpan laporan.';
                    let errorDetails = '';
                    if (result.errors) {
                        // Tampilkan error validasi
                        errorDetails = Object.values(result.errors).map(e => `- ${e.join(', ')}`).join('\n');
                        alert(`Gagal menyimpan laporan:\n${errorMessage}\n\nDetail:\n${errorDetails}`);
                    } else {
                        alert(`Gagal menyimpan laporan: ${errorMessage}`);
                    }
                }
            } catch (error) {
                console.error('Network Error:', error);
                alert('Gagal terhubung ke server. Mohon periksa koneksi Anda.');
            } finally {
                // Kembalikan status tombol
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    }
    
    // ------------ TANGGAL ------------
    const tanggalInput = document.getElementById('tanggal_lkh');
    const tanggalBtn   = document.getElementById('tanggal_lkh_btn');

    if (tanggalInput && tanggalBtn) {
        tanggalBtn.addEventListener('click', function () {
            if (typeof tanggalInput.showPicker === 'function') {
                tanggalInput.showPicker(); // browser modern
            } else {
                tanggalInput.focus();      // fallback
            }
        });
    }

    // ------------ JAM MULAI & JAM SELESAI ------------
    const timeConfigs = [
        { inputId: 'jam_mulai',   btnId: 'jam_mulai_btn'   },
        { inputId: 'jam_selesai', btnId: 'jam_selesai_btn' },
    ];

    timeConfigs.forEach(cfg => {
        const input = document.getElementById(cfg.inputId);
        const btn   = document.getElementById(cfg.btnId);

        if (!input) return;

        // Atur warna teks: abu saat kosong, hitam saat ada nilai
        const refreshColor = () => {
            if (input.value) {
                input.classList.remove('time-placeholder');
                input.classList.add('time-filled');
            } else {
                input.classList.add('time-placeholder');
                input.classList.remove('time-filled');
            }
        };

        refreshColor();
        input.addEventListener('input',  refreshColor);
        input.addEventListener('change', refreshColor);

        // Klik icon -> buka time picker
        if (btn) {
            btn.addEventListener('click', function () {
                if (typeof input.showPicker === 'function') {
                    input.showPicker();
                } else {
                    input.focus();
                }
            });
        }
    });
});