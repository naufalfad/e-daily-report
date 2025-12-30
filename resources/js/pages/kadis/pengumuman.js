import { authFetch } from "../../utils/auth-fetch";

document.addEventListener("DOMContentLoaded", () => {
    // Pastikan kita berada di halaman pengumuman (cek elemen root)
    const root = document.getElementById("pengumuman-root");
    if (!root) return;

    // --- DOM ELEMENTS (MAIN LIST) ---
    const listEl = document.getElementById("announcement-list");
    const emptyEl = document.getElementById("announcement-empty");
    const loadingEl = document.getElementById("loading-indicator");
    const paginationContainer = document.getElementById("pagination-container");

    // --- DOM ELEMENTS (MODAL & FORM) ---
    const modal = document.getElementById("modal-pengumuman");
    const btnOpen = document.getElementById("btn-open-pengumuman");
    const btnClose = document.getElementById("btn-close-pengumuman");
    const btnCancel = document.getElementById("btn-cancel-pengumuman");
    const btnSubmit = document.getElementById("btn-submit-pengumuman");

    const inputJudul = document.getElementById("input-judul");
    const inputIsi = document.getElementById("input-isi");
    
    // [KHUSUS KADIS] Element Select Bidang
    const selectTargetBidang = document.getElementById("select-target-bidang");

    const previewTitle = document.getElementById("preview-title");
    const previewBody = document.getElementById("preview-body");
    const previewBadge = document.getElementById("preview-scope-badge");

    // --- DOM ELEMENTS (FILTER BAR - NEW) ---
    const filterSearch = document.getElementById("filter-search");
    const filterStartDate = document.getElementById("filter-start-date");
    const filterEndDate = document.getElementById("filter-end-date");
    const btnResetFilter = document.getElementById("btn-reset-filter");

    // --- VARIABLES ---
    let currentUserId = null;
    let searchTimeout = null; // Variabel untuk Debounce

    // ======================================================
    // 0. INIT & UTILS
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

    // Fungsi Debounce untuk search text (mencegah spam request)
    function debounce(func, wait) {
        return function (...args) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ======================================================
    // 1. LOAD LIST PENGUMUMAN (SMART FILTERING)
    // ======================================================
    /**
     * @param {string|null} url - Jika null, bangun URL dari filter. Jika ada (pagination), pakai itu.
     */
    async function fetchPengumuman(url = null) { 
        try {
            // Tampilkan loading state
            if (listEl.children.length === 0) {
                if (loadingEl) loadingEl.classList.remove("hidden");
                listEl.classList.add("hidden");
                emptyEl.classList.add("hidden");
            } else {
                listEl.classList.add("opacity-50", "pointer-events-none"); // Efek loading halus
            }

            let endpoint = url;

            // Jika URL tidak diberikan (bukan klik pagination), bangun dari Filter Input
            if (!endpoint) {
                const params = new URLSearchParams();
                
                // Ambil value dari input filter
                if (filterSearch && filterSearch.value.trim()) {
                    params.append("q", filterSearch.value.trim());
                }
                if (filterStartDate && filterStartDate.value) {
                    params.append("start_date", filterStartDate.value);
                }
                if (filterEndDate && filterEndDate.value) {
                    params.append("end_date", filterEndDate.value);
                }

                endpoint = `/api/pengumuman/list?${params.toString()}`;
            }

            const response = await authFetch(endpoint, { method: "GET" });
            if (!response.ok) throw new Error("Gagal memuat data");

            const result = await response.json();
            
            // Laravel Paginate Structure
            const data = result.data ?? result;

            renderList(data);

            // Render Pagination jika ada links
            if (result.links && result.links.length > 3) {
                renderPagination(result.links);
            } else {
                if (paginationContainer) paginationContainer.innerHTML = "";
            }

        } catch (err) {
            console.error(err);
            if (listEl) listEl.innerHTML = `<div class="col-span-full text-center py-10"><p class="text-rose-500 bg-rose-50 inline-block px-4 py-2 rounded-lg border border-rose-100">Gagal memuat data.</p></div>`;
        } finally {
            if (loadingEl) loadingEl.classList.add("hidden");
            listEl.classList.remove("opacity-50", "pointer-events-none");
        }
    }

    // ======================================================
    // 2. RENDER PAGINATION
    // ======================================================
    function renderPagination(links) {
        if (!paginationContainer) return;
        paginationContainer.innerHTML = "";

        const nav = document.createElement("nav");
        nav.className = "flex items-center justify-center gap-1";

        links.forEach(link => {
            if (link.url === null && link.label === '...') {
                const span = document.createElement("span");
                span.className = "px-3 py-1 text-slate-400 text-sm";
                span.innerHTML = link.label;
                nav.appendChild(span);
                return;
            }

            const btn = document.createElement("button");
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = link.label;
            const labelText = tempDiv.textContent || tempDiv.innerText || "";

            let btnClass = "px-3.5 py-2 rounded-lg text-sm font-medium transition-all duration-200 border ";
            if (link.active) {
                btnClass += "bg-[#1C7C54] text-white border-[#1C7C54] shadow-md";
            } else if (link.url === null) {
                btnClass += "bg-slate-50 text-slate-300 border-slate-100 cursor-not-allowed";
            } else {
                btnClass += "bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:text-[#1C7C54] hover:border-[#1C7C54]/30";
            }
            
            btn.className = btnClass;
            btn.innerHTML = labelText;

            if (link.url) {
                btn.onclick = (e) => {
                    e.preventDefault();
                    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    fetchPengumuman(link.url);
                };
            } else {
                btn.disabled = true;
            }

            nav.appendChild(btn);
        });

        paginationContainer.appendChild(nav);
    }

    // ======================================================
    // 3. RENDER LIST (Strict Ownership & Scope Badge)
    // ======================================================
    function renderList(data) {
        listEl.innerHTML = "";

        if (!data || data.length === 0) {
            listEl.classList.add("hidden");
            emptyEl.classList.remove("hidden");
            if (paginationContainer) paginationContainer.innerHTML = "";
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
        article.className = "rounded-[18px] border border-[#BFD4FF] bg-[#F4F8FF] px-5 py-4 shadow-sm relative group hover:shadow-md transition-all h-full flex flex-col justify-between";

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

        // Delete Button Logic (Strict Ownership)
        let deleteBtnHtml = '';
        if (currentUserId && item.user_id_creator === currentUserId) {
            deleteBtnHtml = `
                <button class="btn-delete opacity-0 group-hover:opacity-100 transition-opacity text-slate-400 hover:text-rose-600 p-1.5 rounded-full hover:bg-rose-50" 
                        title="Hapus Pengumuman" data-id="${item.id}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            `;
        }

        article.innerHTML = `
            <div>
                <div class="flex justify-between items-start mb-2">
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
    // 4. CREATE / STORE (Handling Dropdown Value)
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

        // [LOGIKA KHUSUS KADIS] Pemetaan nilai dropdown
        const dropdownValue = selectTargetBidang.value;
        const payload = {
            judul: inputJudul.value,
            isi_pengumuman: inputIsi.value,
            // Jika pilih 'umum' -> target 'umum', else -> target 'divisi'
            target: dropdownValue === 'umum' ? 'umum' : 'divisi',
            // Jika pilih ID -> kirim ID, else -> null
            target_bidang_id: dropdownValue === 'umum' ? null : dropdownValue
        };

        btnSubmit.disabled = true;
        const originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = `<span class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Menyimpan...`;

        try {
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
            selectTargetBidang.value = "umum"; // Reset dropdown ke default

            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: "Pengumuman strategis berhasil diterbitkan.",
                showConfirmButton: false,
                timer: 1600,
            });

            // Reload data ke halaman awal (reset filter)
            resetFilter();
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Gagal Menyimpan",
                text: err.message,
            });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
        }
    }

    // ======================================================
    // 5. DELETE (Handling 403 Forbidden)
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

            fetchPengumuman(); // Refresh current view
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Gagal Menghapus",
                text: err.message,
            });
        }
    }

    // ======================================================
    // 6. HELPER: MODAL & RESET
    // ======================================================
    function resetFilter() {
        if (filterSearch) filterSearch.value = "";
        if (filterStartDate) filterStartDate.value = "";
        if (filterEndDate) filterEndDate.value = "";
        fetchPengumuman(); // Load ulang data bersih
    }

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
        
        // [LOGIKA KHUSUS KADIS] Update Preview Badge berdasarkan Dropdown Text
        if (previewBadge && selectTargetBidang) {
            const selectedText = selectTargetBidang.options[selectTargetBidang.selectedIndex].text;
            const isUmum = selectTargetBidang.value === 'umum';
            
            previewBadge.textContent = isUmum ? 'UMUM' : selectedText.toUpperCase();
            previewBadge.className = `text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded ${
                isUmum ? 'bg-slate-200 text-slate-600' : 'bg-emerald-100 text-emerald-700'
            }`;
        }

        inputIsi.style.height = 'auto';
        inputIsi.style.height = inputIsi.scrollHeight + 'px';
    }

    // ======================================================
    // 7. EVENT LISTENERS
    // ======================================================
    
    // -- Filter Listeners --
    if (filterSearch) {
        filterSearch.addEventListener("input", debounce(() => {
            fetchPengumuman(); // Reset ke page 1 dengan filter baru
        }, 500));
    }

    if (filterStartDate) filterStartDate.addEventListener("change", () => fetchPengumuman());
    if (filterEndDate) filterEndDate.addEventListener("change", () => fetchPengumuman());
    
    if (btnResetFilter) {
        btnResetFilter.addEventListener("click", (e) => {
            e.preventDefault();
            resetFilter();
        });
    }

    // -- Modal Listeners --
    if (btnOpen) btnOpen.addEventListener("click", openModal);
    if (btnClose) btnClose.addEventListener("click", closeModal);
    if (btnCancel) btnCancel.addEventListener("click", closeModal);

    if (btnSubmit) {
        btnSubmit.onclick = (e) => {
            e.preventDefault();
            storePengumuman();
        };
    }

    inputJudul?.addEventListener("input", updatePreview);
    inputIsi?.addEventListener("input", updatePreview);
    
    // Listener untuk perubahan dropdown target
    if (selectTargetBidang) selectTargetBidang.addEventListener("change", updatePreview);

    modal?.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    // --- EXECUTE ---
    initUser(); 
    fetchPengumuman(); // Default load page 1
});