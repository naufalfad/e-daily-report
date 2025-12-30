import { authFetch } from "../../utils/auth-fetch";

document.addEventListener("DOMContentLoaded", () => {
    // Pastikan kita berada di halaman pengumuman (cek elemen root)
    const root = document.getElementById("pengumuman-root");
    if (!root) return;

    // --- DOM ELEMENTS ---
    const listEl = document.getElementById("announcement-list");
    const emptyEl = document.getElementById("announcement-empty");
    const loadingEl = document.getElementById("loading-indicator");

    const modal = document.getElementById("modal-pengumuman");
    const btnOpen = document.getElementById("btn-open-pengumuman");
    const btnClose = document.getElementById("btn-close-pengumuman");
    const btnCancel = document.getElementById("btn-cancel-pengumuman");
    const btnSubmit = document.getElementById("btn-submit-pengumuman");

    const inputJudul = document.getElementById("input-judul");
    const inputIsi = document.getElementById("input-isi");
    
    // [LOGIKA BARU] Element Select Bidang Khusus Kadis
    const selectTargetBidang = document.getElementById("select-target-bidang");

    const previewTitle = document.getElementById("preview-title");
    const previewBody = document.getElementById("preview-body");
    const previewBadge = document.getElementById("preview-scope-badge");

    // --- VARIABLES ---
    let currentUserId = null;

    // ======================================================
    // 0. INIT: AMBIL ID USER YANG SEDANG LOGIN (Information Expert)
    // ======================================================
    function initUser() {
        const metaId = document.querySelector('meta[name="user-id"]');
        if (metaId) {
            currentUserId = parseInt(metaId.content);
            return;
        }

        try {
            const storedUser = localStorage.getItem('user'); 
            if (storedUser) {
                const userObj = JSON.parse(storedUser);
                currentUserId = userObj.id;
            }
        } catch (e) {
            console.error("Gagal mengambil user ID dari storage", e);
        }
    }

    // ======================================================
    // 1. LOAD LIST PENGUMUMAN
    // ======================================================
    async function fetchPengumuman() {
        // [FIX] Menggunakan rute universal sesuai web.php
        const endpoint = "/api/pengumuman/list"; 

        try {
            if (loadingEl) {
                loadingEl.classList.remove("hidden");
                listEl.classList.add("hidden");
                emptyEl.classList.add("hidden");
            }

            const response = await authFetch(endpoint, {
                method: "GET"
            });

            if (!response.ok) throw new Error("Gagal memuat data");

            const result = await response.json();
            const data = result.data ?? result;

            renderList(data);

        } catch (err) {
            console.error(err);
            if (listEl) listEl.innerHTML = `<p class="text-rose-500 text-center py-4 bg-rose-50 rounded-lg border border-rose-100">Gagal memuat data. Silakan refresh halaman.</p>`;
        } finally {
            if (loadingEl) loadingEl.classList.add("hidden");
        }
    }

    // ======================================================
    // 2. RENDER LIST (Strict Ownership & Dynamic Scope Badge)
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
            "rounded-[18px] border border-[#BFD4FF] bg-[#F4F8FF] px-5 py-4 shadow-sm relative group hover:shadow-md transition-all h-full flex flex-col justify-between";

        const dateStr = new Date(item.created_at).toLocaleDateString("id-ID", {
            day: 'numeric', month: 'long', year: 'numeric'
        });

        // [LOGIKA BARU] Badge Logic berdasarkan Bidang ID
        let scopeBadge = '';
        if (item.bidang_id) {
            scopeBadge = `<span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full mb-2 inline-block font-bold">DIVISI</span>`;
        } else {
            scopeBadge = `<span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full mb-2 inline-block font-bold">UMUM</span>`;
        }

        let deleteBtnHtml = '';
        if (currentUserId && item.user_id_creator === currentUserId) {
            deleteBtnHtml = `
                <button class="btn-delete opacity-0 group-hover:opacity-100 transition-opacity text-slate-300 hover:text-rose-600 p-1 rounded-full hover:bg-rose-50" 
                        title="Hapus Pengumuman" data-id="${item.id}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            `;
        }

        article.innerHTML = `
            <div>
                <div class="flex justify-between items-start">
                    ${scopeBadge}
                    ${deleteBtnHtml}
                </div>
                
                <h3 class="text-[15px] font-bold text-slate-800 mb-2 leading-snug break-words">${item.judul}</h3>
                <p class="text-[13px] text-slate-600 leading-relaxed whitespace-pre-line line-clamp-4">${item.isi_pengumuman}</p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200/60 flex justify-between items-center">
                <p class="text-[11px] text-slate-400 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    ${dateStr}
                </p>
                <p class="text-[11px] font-medium text-slate-500 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    ${item.creator?.name || "Kepala Badan"}
                </p>
            </div>
        `;

        const delBtn = article.querySelector(".btn-delete");
        if (delBtn) {
            delBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                deletePengumuman(item.id);
            });
        }

        return article;
    }

    // ======================================================
    // 3. CREATE / STORE (Logical Target Integration)
    // ======================================================
    async function storePengumuman() {
        if (!inputJudul.value.trim() || !inputIsi.value.trim()) {
            Swal.fire({
                icon: "warning",
                title: "Belum Lengkap",
                text: "Judul dan Isi pengumuman wajib diisi!",
                timer: 2000,
                showConfirmButton: false,
            });
            return;
        }

        // [LOGIKA BARU] Pemetaan nilai dropdown Kadis
        const dropdownValue = selectTargetBidang.value;
        const payload = {
            judul: inputJudul.value,
            isi_pengumuman: inputIsi.value,
            target: dropdownValue === 'umum' ? 'umum' : 'divisi',
            target_bidang_id: dropdownValue === 'umum' ? null : dropdownValue
        };

        btnSubmit.disabled = true;
        btnSubmit.dataset.processing = "true";
        const originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = `<svg class="animate-spin h-4 w-4 text-white inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...`;

        try {
            // [FIX] Menggunakan rute universal store sesuai web.php
            const res = await authFetch("/api/pengumuman/store", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok) throw new Error(data.message || "Gagal menyimpan");

            closeModal();
            inputJudul.value = "";
            inputIsi.value = "";
            selectTargetBidang.value = "umum"; // Reset dropdown

            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: "Pengumuman strategis berhasil diterbitkan.",
                showConfirmButton: false,
                timer: 1600,
            });

            fetchPengumuman();
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Gagal Menyimpan",
                text: err.message,
            });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.dataset.processing = "false";
            btnSubmit.innerHTML = originalText;
        }
    }

    // ======================================================
    // 4. DELETE (Handling 403 Forbidden)
    // ======================================================
    async function deletePengumuman(id) {
        const confirm = await Swal.fire({
            title: "Hapus pengumuman ini?",
            text: "Tindakan ini tidak dapat dibatalkan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await authFetch(`/api/pengumuman/${id}`, {
                method: "DELETE",
            });

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 403) {
                    throw new Error("Anda tidak memiliki izin menghapus pengumuman ini karena bukan milik Anda.");
                }
                throw new Error(data.message || "Gagal menghapus");
            }

            Swal.fire({
                icon: "success",
                title: "Terhapus",
                text: "Pengumuman berhasil dihapus.",
                timer: 1500,
                showConfirmButton: false,
            });

            fetchPengumuman();
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Gagal Menghapus",
                text: err.message,
            });
        }
    }

    // ======================================================
    // 5. MODAL + ENHANCED PREVIEW
    // ======================================================
    function openModal() {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        inputJudul.focus();
        updatePreview();
    }

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

    function updatePreview() {
        previewTitle.textContent = inputJudul.value || "Judul Pengumuman...";
        previewBody.textContent = inputIsi.value || "Isi arahan akan muncul di sini...";
        
        // [LOGIKA BARU] Update Preview Badge berdasarkan Dropdown
        if (previewBadge && selectTargetBidang) {
            const selectedText = selectTargetBidang.options[selectTargetBidang.selectedIndex].text;
            const isUmum = selectTargetBidang.value === 'umum';
            
            previewBadge.textContent = isUmum ? 'UMUM' : selectedText;
            previewBadge.className = `text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded ${
                isUmum ? 'bg-slate-200 text-slate-600' : 'bg-emerald-100 text-emerald-700'
            }`;
        }

        inputIsi.style.height = 'auto';
        inputIsi.style.height = inputIsi.scrollHeight + 'px';
    }

    // --- SETUP EVENT LISTENERS ---
    if (btnOpen) btnOpen.addEventListener("click", openModal);
    if (btnClose) btnClose.addEventListener("click", closeModal);
    if (btnCancel) btnCancel.addEventListener("click", closeModal);

    if (btnSubmit) {
        btnSubmit.onclick = (e) => {
            e.preventDefault();
            if (btnSubmit.disabled || btnSubmit.dataset.processing === "true") return;
            storePengumuman();
        };
    }

    if (inputJudul) inputJudul.addEventListener("input", updatePreview);
    if (inputIsi) inputIsi.addEventListener("input", updatePreview);
    
    // [LOGIKA BARU] Listener untuk perubahan dropdown target
    if (selectTargetBidang) selectTargetBidang.addEventListener("change", updatePreview);

    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    // --- EXECUTE ---
    initUser(); 
    fetchPengumuman(); 
    updatePreview();
});