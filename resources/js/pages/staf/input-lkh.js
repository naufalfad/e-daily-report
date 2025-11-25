document.addEventListener("DOMContentLoaded", () => {

    // =============== FLATPICKR ===============
    flatpickr("#tanggal_lkh", {
        dateFormat: "d-m-Y",
        allowInput: true,
        disableMobile: false,
    });

    document.getElementById("tanggal_lkh_btn")?.addEventListener("click", () => {
        document.getElementById("tanggal_lkh")._flatpickr.open();
    });

    flatpickr("#jam_mulai", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        allowInput: true,
    });

    document.getElementById("jam_mulai_btn")?.addEventListener("click", () => {
        document.getElementById("jam_mulai")._flatpickr.open();
    });

    flatpickr("#jam_selesai", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        allowInput: true,
    });

    document.getElementById("jam_selesai_btn")?.addEventListener("click", () => {
        document.getElementById("jam_selesai")._flatpickr.open();
    });

    // ===== FILE PREVIEW =====
    const fileInput = document.getElementById("bukti_input");
    const fileLabel = document.getElementById("bukti_filename");

    if (fileInput) {
        fileInput.addEventListener("change", () => {
            if (!fileInput.files.length) {
                fileLabel.textContent = "Pilih File";
                return;
            }
            fileLabel.textContent =
                fileInput.files.length === 1
                    ? fileInput.files[0].name
                    : `${fileInput.files.length} file dipilih`;
        });
    }

    // ===== SUBMIT FORM =====
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
                    fileLabel.textContent = "Pilih File";
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
