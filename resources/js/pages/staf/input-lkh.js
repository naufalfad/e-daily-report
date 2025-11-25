document.addEventListener("DOMContentLoaded", () => {

    // ========================================================================
    // =============== TOMBOL IKON UNTUK BUKA DATE PICKER =====================
    // ========================================================================
    const tanggalInput = document.getElementById("tanggal_lkh");
    const tanggalBtn = document.getElementById("tanggal_lkh_btn");

    if (tanggalInput && tanggalBtn) {
        tanggalBtn.addEventListener("click", () => {
            try {
                tanggalInput.showPicker(); // browser modern
            } catch {
                tanggalInput.focus(); // fallback
            }
        });
    }

    // ========================================================================
    // =============== TOMBOL IKON UNTUK BUKA TIME PICKER =====================
    // ========================================================================
    const timeConfigs = [
        { inputId: "jam_mulai", btnId: "jam_mulai_btn" },
        { inputId: "jam_selesai", btnId: "jam_selesai_btn" },
    ];

    timeConfigs.forEach(cfg => {
        const input = document.getElementById(cfg.inputId);
        const btn = document.getElementById(cfg.btnId);

        if (!input) return;

        // Buka time picker lewat icon
        if (btn) {
            btn.addEventListener("click", () => {
                try {
                    input.showPicker();
                } catch {
                    input.focus();
                }
            });
        }

        // Placeholder color logic
        const updateColor = () => {
            if (input.value) {
                input.classList.remove("time-placeholder");
                input.classList.add("time-filled");
            } else {
                input.classList.add("time-placeholder");
                input.classList.remove("time-filled");
            }
        };

        updateColor();
        input.addEventListener("input", updateColor);
        input.addEventListener("change", updateColor);
    });

    // ========================================================================
    // =========================== FILE PREVIEW ===============================
    // ========================================================================
    const fileInput = document.getElementById("bukti_input");
    const fileLabel = document.getElementById("bukti_filename");

    if (fileInput && fileLabel) {
        fileInput.addEventListener("change", () => {
            if (!fileInput.files.length) {
                fileLabel.textContent = "Pilih File";
            } else if (fileInput.files.length === 1) {
                fileLabel.textContent = fileInput.files[0].name;
            } else {
                fileLabel.textContent = `${fileInput.files.length} file dipilih`;
            }
        });
    }

    // ========================================================================
    // ================== BLOKIR ANGKA NEGATIF UNTUK VOLUME ===================
    // ========================================================================
    const volumeInput = document.querySelector('input[name="volume"]');

    if (volumeInput) {
        // Blokir tombol '-' dan '+'
        volumeInput.addEventListener("keydown", (e) => {
            if (e.key === "-" || e.key === "+") {
                e.preventDefault();
            }
        });

        // Auto set ke 0 kalau kosong
        volumeInput.addEventListener("input", () => {
            volumeInput.value = volumeInput.value.replace(/[^0-9]/g, "");
            if (volumeInput.value === "") volumeInput.value = 0;
        });
    }

    // ========================================================================
    // ============================ SUBMIT FORM ===============================
    // ========================================================================
    const form = document.getElementById("form-lkh");

    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const submitBtn = form.querySelector("button[type=submit]");
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = "Mengirim...";

            const formData = new FormData(form);

            try {
                const token = localStorage.getItem("auth_token");

                const response = await fetch("/api/lkh", {
                    method: "POST",
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Accept": "application/json"
                    },
                    body: formData
                });

                const json = await response.json();

                if (response.ok) {
                    alert("LKH berhasil dikirim!");
                    form.reset();
                    if (fileLabel) fileLabel.textContent = "Pilih File";
                } else {
                    alert("Gagal mengirim: " + (json.message || "Error"));
                }
            } catch (error) {
                alert("Gagal koneksi server");
            }

            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

});