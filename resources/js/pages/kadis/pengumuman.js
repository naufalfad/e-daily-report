import { authFetch } from "../../utils/auth-fetch";

document.addEventListener("DOMContentLoaded", () => {
    // Pastikan kita berada di halaman pengumuman (cek elemen root)
    const root = document.getElementById("pengumuman-root");
    if (!root) return;

    // --- DOM ELEMENTS ---
    const listEl = document.getElementById("announcement-list");
    const emptyEl = document.getElementById("announcement-empty");
    const loadingEl = document.getElementById("loading-indicator"); // Tambahan untuk UX Loading

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
    // 1. LOAD LIST PENGUMUMAN (Endpoint Khusus Kadis)
    // ======================================================
    async function fetchPengumuman() {
        // [PERBEDAAN 1] Menggunakan endpoint /kadis/pengumuman/list
        const endpoint = "/api/kadis/pengumuman/list"; 

        try {
            // Tampilkan loading jika ada elemennya
            if(loadingEl) {
                loadingEl.classList.remove("hidden");
                listEl.classList.add("hidden");
                emptyEl.classList.add("hidden");
            }

            const response = await authFetch(endpoint, {
                method: "GET"
            });

            if (!response.ok) throw new Error("Gagal memuat data");

            const result = await response.json();
            // Handle struktur data paginate (Laravel Default) vs raw array
            const data = result.data ?? result; 

            renderList(data);

        } catch (err) {
            console.error(err);
            // Fallback UI error sederhana
            if (listEl) listEl.innerHTML = `<p class="text-red-500 text-center py-4">Gagal memuat data.</p>`;
        } finally {
             if(loadingEl) loadingEl.classList.add("hidden");
        }
    }

    // ======================================================
    // 2. RENDER LIST
    // ======================================================
    function renderList(data) {
        listEl.innerHTML = "";

        // Cek data kosong
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
        // Style Card disamakan
        article.className =
            "rounded-[18px] border border-[#BFD4FF] bg-[#F4F8FF] px-5 py-4 shadow-sm relative group hover:shadow-md transition-all h-full flex flex-col justify-between";

        const dateStr = new Date(item.created_at).toLocaleDateString("id-ID", {
            day: 'numeric', month: 'long', year: 'numeric'
        });

        // [LOGIKA TAMBAHAN] Badge Unit vs Global
        const badge = item.unit_kerja_id 
            ? `<span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full mb-2 inline-block">Unit Kerja</span>`
            : `<span class="text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full mb-2 inline-block">Global</span>`;

        article.innerHTML = `
            <div>
                <div class="flex justify-between items-start">
                    ${badge}
                    <button class="btn-delete opacity-0 group-hover:opacity-100 transition-opacity text-slate-300 hover:text-red-500" 
                            title="Hapus" data-id="${item.id}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                
                <h3 class="text-[15px] font-bold text-slate-800 mb-2 leading-snug">${item.judul}</h3>
                <p class="text-[13px] text-slate-600 leading-relaxed whitespace-pre-line line-clamp-4">${item.isi_pengumuman}</p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200/60 flex justify-between items-center">
                <p class="text-[11px] text-slate-400">
                    ${dateStr}
                </p>
                <p class="text-[11px] font-medium text-slate-500">
                    Oleh: ${item.creator?.name || "Admin"}
                </p>
            </div>
        `;

        // Event Listener Delete
        const delBtn = article.querySelector(".btn-delete");
        if(delBtn) {
            delBtn.addEventListener("click", (e) => {
                e.stopPropagation(); // Mencegah klik tembus
                deletePengumuman(item.id);
            });
        }

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

        // Lock UI
        btnSubmit.disabled = true;
        btnSubmit.dataset.processing = "true";
        const originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = `<span class="inline-block animate-spin mr-2">⏳</span> Menyimpan...`;

        // [PERBEDAAN 2] Endpoint Store Khusus Kadis
        const endpoint = "/api/kadis/pengumuman/store";

        try {
            const res = await authFetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    judul: inputJudul.value,
                    isi_pengumuman: inputIsi.value,
                    // [CATATAN KADIS]
                    // Jika null, controller akan menganggap Global. 
                    // Controller 'store' bisa dimodifikasi backend-nya agar jika role Kadis dan null,
                    // otomatis set ke unit_kerja_id user.
                    // Untuk sekarang kita kirim null (Global) sesuai kode referensi.
                    unit_kerja_id: null 
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || "Gagal menyimpan");

            closeModal();
            inputJudul.value = "";
            inputIsi.value = "";
            
            // Reset Preview
            updatePreview();

            // Refresh List
            fetchPengumuman();

        } catch (err) {
            alert(err.message);
        } finally {
            // Unlock UI
            btnSubmit.disabled = false;
            btnSubmit.dataset.processing = "false";
            btnSubmit.innerHTML = originalText;
        }
    }

    // ======================================================
    // 4. DELETE
    // ======================================================
    async function deletePengumuman(id) {
        if (!confirm("Hapus pengumuman ini?")) return;

        // [PERBEDAAN 3] Endpoint Delete Khusus Kadis
        const endpoint = `/api/kadis/pengumuman/${id}`;

        try {
            const res = await authFetch(endpoint, {
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
        // Gunakan flex agar centering jalan (sesuai class CSS modal)
        modal.classList.add("flex"); 
        inputJudul.focus();
    }

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

    function updatePreview() {
        previewTitle.textContent = inputJudul.value || "Judul Pengumuman...";
        previewBody.textContent = inputIsi.value || "Isi pengumuman akan muncul di sini...";
    }

    // Event Listeners
    if(btnOpen) btnOpen.addEventListener("click", openModal);
    if(btnClose) btnClose.addEventListener("click", closeModal);
    if(btnCancel) btnCancel.addEventListener("click", closeModal);

    if(btnSubmit) {
        btnSubmit.onclick = (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (btnSubmit.disabled || btnSubmit.dataset.processing === "true") {
                return;
            }

            storePengumuman();
        };
    }

    if(inputJudul) inputJudul.addEventListener("input", updatePreview);
    if(inputIsi) inputIsi.addEventListener("input", updatePreview);

    // Close on backdrop click
    modal.addEventListener("click", (e) => {
        // Asumsi struktur modal: Backdrop ada di parent atau elemen modal itu sendiri
        // Kita cek apakah yang diklik adalah backdrop (id modal-pengumuman atau modal-backdrop)
        if (e.target === modal || e.target.id === 'modal-backdrop') {
            closeModal();
        }
    });

    // Initial Fetch
    fetchPengumuman();
    updatePreview();
});