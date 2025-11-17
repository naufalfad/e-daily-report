document.addEventListener('DOMContentLoaded', function () {
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