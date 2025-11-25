document.addEventListener("DOMContentLoaded", () => {
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
        // Blokir karakter negatif
        volumeInput.addEventListener("keydown", (e) => {
            if (e.key === "-" || e.key === "+") {
                e.preventDefault();
            }
        });

        // Tetap nol saat dihapus
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

            const btn = form.querySelector("button[type=submit]");
            const old = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = "Mengirim...";

            const formData = new FormData(form);

            try {
                const token = localStorage.getItem("auth_token");

                const res = await fetch("/api/lkh", {
                    method: "POST",
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Accept": "application/json",
                    },
                    body: formData
                });

                const json = await res.json();

                if (res.ok) {
                    alert("LKH berhasil dikirim!");
                    form.reset();

                    if (fileLabel) fileLabel.textContent = "Pilih File";
                } else {
                    alert("Gagal mengirim: " + (json.message || "Error"));
                }
            } catch (err) {
                alert("Gagal koneksi server");
            }

            btn.disabled = false;
            btn.innerHTML = old;
        });
    }

});