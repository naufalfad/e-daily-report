// resources/js/pages/penilai/pengumuman.js

document.addEventListener("DOMContentLoaded", () => {
    const root = document.getElementById("pengumuman-root");
    if (!root) return;

    const listEl = document.getElementById("announcement-list");
    const emptyEl = document.getElementById("announcement-empty");

    const modal = document.getElementById("modal-pengumuman");
    const btnOpen = document.getElementById("btn-open-pengumuman");
    const btnClose = document.getElementById("btn-close-pengumuman");
    const btnCancel = document.getElementById("btn-cancel-pengumuman");
    const btnSubmit = document.getElementById("btn-submit-pengumuman");

    const inputJudul = document.getElementById("input-judul");
    const inputIsi = document.getElementById("input-isi");

    const previewTitle = document.getElementById("preview-title");
    const previewBody = document.getElementById("preview-body");
    const previewDate = document.getElementById("preview-date");

    const initialData = (window.PENILAI_PENGUMUMAN_DATA || []).slice();

    function syncVisibility() {
        if (!listEl || !emptyEl) return;

        if (listEl.children.length === 0) {
            listEl.classList.add("hidden");
            emptyEl.classList.remove("hidden");
        } else {
            listEl.classList.remove("hidden");
            emptyEl.classList.add("hidden");
        }
    }

    function openModal() {
        if (!modal) return;
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        inputJudul && inputJudul.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

    function formatToday() {
        const now = new Date();
        const formatter = new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
        });
        return "Diumumkan pada tanggal " + formatter.format(now);
    }

    function updatePreview() {
        if (previewTitle) {
            previewTitle.textContent =
                (inputJudul && inputJudul.value.trim()) || "Judul akan tampil di sini";
        }
        if (previewBody) {
            previewBody.textContent =
                (inputIsi && inputIsi.value.trim()) || "Isi pengumuman";
        }
        if (previewDate && !previewDate.dataset.fixed) {
            previewDate.textContent = formatToday();
        }
    }

    function createCard(item) {
        const article = document.createElement("article");
        article.className =
            "rounded-[18px] border border-[#BFD4FF] bg-[#F4F8FF] px-5 py-4 shadow-sm";

        article.innerHTML = `
            <h3 class="text-[14px] font-semibold text-slate-800 mb-1">
                ${item.judul}
            </h3>
            <p class="text-[12px] text-slate-700 leading-snug mb-4">
                ${item.isi}
            </p>
            <p class="text-[11px] text-slate-400">
                ${item.tanggal}
            </p>
        `;
        return article;
    }

    // ==== Event binding ====
    btnOpen && btnOpen.addEventListener("click", openModal);

    [btnClose, btnCancel].forEach((btn) => {
        btn &&
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                closeModal();
            });
    });

    // klik di luar panel -> tutup
    modal &&
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeModal();
        });

    inputJudul && inputJudul.addEventListener("input", updatePreview);
    inputIsi && inputIsi.addEventListener("input", updatePreview);

    btnSubmit &&
        btnSubmit.addEventListener("click", (e) => {
            e.preventDefault();
            if (!inputJudul || !inputIsi || !listEl) return;

            const judul = inputJudul.value.trim();
            const isi = inputIsi.value.trim();

            if (!judul || !isi) {
                alert("Judul dan isi pengumuman wajib diisi.");
                return;
            }

            const item = {
                judul,
                isi,
                tanggal: formatToday(),
            };

            const card = createCard(item);
            listEl.prepend(card);

            inputJudul.value = "";
            inputIsi.value = "";
            updatePreview();
            closeModal();
            syncVisibility();
        });

    // Inisialisasi awal
    if (initialData.length === 0 && listEl && listEl.children.length === 0) {
        syncVisibility();
    } else {
        syncVisibility();
    }
    updatePreview();
});
