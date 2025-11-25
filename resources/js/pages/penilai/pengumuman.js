import { authFetch } from "../../utils/auth-fetch";

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

    // ======================================================
    // 1. LOAD LIST PENGUMUMAN (API TOKEN)
    // ======================================================
    async function fetchPengumuman() {
        try {
            const response = await authFetch("/api/pengumuman", {
                method: "GET"
            });

            if (!response.ok) throw new Error("Gagal memuat data");

            const result = await response.json();
            const data = result.data ?? result; // paginate vs non paginate

            renderList(data);

        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan saat memuat pengumuman.");
        }
    }

    // ======================================================
    // 2. RENDER LIST
    // ======================================================
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

                <button class="btn-delete hidden group-hover:flex items-center justify-center 
                        w-8 h-8 rounded-full bg-white text-red-500 shadow-sm hover:bg-red-50"
                        data-id="${item.id}">
                    🗑
                </button>
            </div>
        `;

        article.querySelector(".btn-delete").addEventListener("click", () => {
            deletePengumuman(item.id);
        });

        return article;
    }

    // ======================================================
    // 3. CREATE / STORE
    // ======================================================
    async function storePengumuman() {
        if (!inputJudul.value.trim() || !inputIsi.value.trim()) {
            alert("Judul dan isi wajib diisi.");
            return;
        }

        // [FIX] Kunci tombol secara visual dan logic
        btnSubmit.disabled = true;
        btnSubmit.dataset.processing = "true"; 
        btnSubmit.textContent = "Menyimpan...";

        try {
            const res = await authFetch("/api/pengumuman", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
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
            // [FIX] Buka kunci tombol
            btnSubmit.disabled = false;
            btnSubmit.dataset.processing = "false";
            btnSubmit.textContent = "🚀 Terbitkan";
        }
    }

    // ======================================================
    // 4. DELETE
    // ======================================================
    async function deletePengumuman(id) {
        if (!confirm("Hapus pengumuman ini?")) return;

        try {
            const res = await authFetch(`/api/pengumuman/${id}`, {
                method: "DELETE"
            });

            if (!res.ok) throw new Error("Gagal menghapus");

            fetchPengumuman();

        } catch (err) {
            alert(err.message);
        }
    }

    // ======================================================
    // 5. MODAL + PREVIEW
    // ======================================================
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

    btnOpen.addEventListener("click", openModal);
    btnClose.addEventListener("click", closeModal);
    btnCancel.addEventListener("click", closeModal);

    // [FIX CRITICAL] Ganti addEventListener dengan onclick untuk mencegah multiple binding
    // Tambahkan Guard Clause: Jika sedang processing, tolak klik berikutnya.
    btnSubmit.onclick = (e) => {
        e.preventDefault();
        e.stopImmediatePropagation(); // Hentikan event bubbling liar

        // Cek apakah tombol sedang dikunci?
        if (btnSubmit.disabled || btnSubmit.dataset.processing === "true") {
            return; // Abaikan klik
        }

        storePengumuman();
    };

    inputJudul.addEventListener("input", updatePreview);
    inputIsi.addEventListener("input", updatePreview);

    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    fetchPengumuman();
    updatePreview();
});