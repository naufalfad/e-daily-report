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

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // ========================================
    // 1. LOAD LIST PENGUMUMAN
    // ========================================
    async function fetchPengumuman() {
        try {
            const response = await fetch('/penilai/pengumuman/list', {
                headers: {
                    "Accept": "application/json"
                }
            });

            if (!response.ok) throw new Error("Gagal memuat data");

            const result = await response.json();
            renderList(result.data);

        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan saat memuat pengumuman.");
        }
    }

    // ========================================
    // 2. RENDER LIST
    // ========================================
    function renderList(data) {
        listEl.innerHTML = "";

        if (!data || data.length === 0) {
            listEl.classList.add("hidden");
            emptyEl.classList.remove("hidden");
            return;
        }

        listEl.classList.remove("hidden");
        emptyEl.classList.add("hidden");

        data.forEach(item => {
            listEl.appendChild(createCard(item));
        });
    }

    // CARD UI
    function createCard(item) {
        const article = document.createElement("article");
        article.className =
            "rounded-[18px] border border-[#BFD4FF] bg-[#F4F8FF] px-5 py-4 shadow-sm relative group hover:shadow-md transition-all";

        const dateStr = new Date(item.created_at).toLocaleDateString("id-ID");

        article.innerHTML = `
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h3 class="text-[14px] font-semibold text-slate-800 mb-1">${item.judul}</h3>
                    <p class="text-[12px] text-slate-700 leading-snug mb-4 whitespace-pre-line">${item.isi_pengumuman}</p>
                    <p class="text-[11px] text-slate-400">
                        Diumumkan ${dateStr}
                        <span class="ml-1 text-slate-300">• Oleh ${item.creator?.name || "Admin"}</span>
                    </p>
                </div>

                <button class="btn-delete hidden group-hover:flex items-center justify-center w-8 h-8 rounded-full bg-white text-red-500 shadow-sm hover:bg-red-50"
                        data-id="${item.id}">
                    🗑
                </button>
            </div>
        `;

        article.querySelector(".btn-delete").addEventListener("click", (e) => {
            e.stopPropagation();
            deletePengumuman(item.id);
        });

        return article;
    }

    // ========================================
    // 3. STORE / CREATE
    // ========================================
    async function storePengumuman() {
        if (!inputJudul.value.trim() || !inputIsi.value.trim()) {
            alert("Judul dan isi wajib diisi.");
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.textContent = "Menyimpan...";

        try {
            const res = await fetch('/penilai/pengumuman/store', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    judul: inputJudul.value,
                    isi_pengumuman: inputIsi.value,
                    unit_kerja_id: null
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || "Gagal menyimpan");

            closeModal();
            inputJudul.value = "";
            inputIsi.value = "";

            alert("Pengumuman berhasil dibuat!");

            fetchPengumuman();

        } catch (err) {
            alert(err.message);
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = "🚀 Terbitkan";
        }
    }

    // ========================================
    // 4. DELETE
    // ========================================
    async function deletePengumuman(id) {
        if (!confirm("Hapus pengumuman ini?")) return;

        try {
            const res = await fetch(`/penilai/pengumuman/${id}`, {
                method: "DELETE",
                headers: {
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                }
            });

            if (!res.ok) throw new Error("Gagal menghapus");

            fetchPengumuman();

        } catch (err) {
            alert(err.message);
        }
    }

    // ========================================
    // 5. MODAL + PREVIEW
    // ========================================
    function openModal() {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        inputJudul.focus();
    }

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

    function updatePreview() {
        previewTitle.textContent = inputJudul.value || "Judul...";
        previewBody.textContent = inputIsi.value || "Isi pengumuman...";
    }

    // BIND EVENTS
    btnOpen.addEventListener("click", openModal);
    btnClose.addEventListener("click", closeModal);
    btnCancel.addEventListener("click", closeModal);

    btnSubmit.addEventListener("click", (e) => {
        e.preventDefault();
        storePengumuman();
    });

    inputJudul.addEventListener("input", updatePreview);
    inputIsi.addEventListener("input", updatePreview);

    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    // INITIAL
    fetchPengumuman();
    updatePreview();
});
