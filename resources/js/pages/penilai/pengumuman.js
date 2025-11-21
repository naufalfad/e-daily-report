// resources/js/pages/penilai/pengumuman.js

document.addEventListener("DOMContentLoaded", () => {
    const root = document.getElementById("pengumuman-root");
    if (!root) return;

    // --- ELEMENTS ---
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

    // Ambil CSRF Token untuk keamanan request
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // --- LOGIC UTAMA ---

    // 1. Load Data dari Server (READ)
    async function fetchPengumuman() {
        try {
            // Tampilkan skeleton/loading jika perlu (opsional)
            const response = await fetch('/penilai/pengumuman/list', {
                headers: { 'Accept': 'application/json' }
            });
            
            if (!response.ok) throw new Error("Gagal memuat data");
            
            const result = await response.json();
            renderList(result.data); // result.data karena pagination
        } catch (error) {
            console.error(error);
            alert("Terjadi kesalahan saat memuat pengumuman.");
        }
    }

    // 2. Render Data ke HTML
    function renderList(data) {
        listEl.innerHTML = ''; // Bersihkan list lama

        if (data.length === 0) {
            listEl.classList.add("hidden");
            emptyEl.classList.remove("hidden");
            return;
        }

        listEl.classList.remove("hidden");
        emptyEl.classList.add("hidden");

        data.forEach(item => {
            const card = createCard(item);
            listEl.appendChild(card);
        });
    }

    // 3. Buat Elemen Kartu HTML
    function createCard(item) {
        const article = document.createElement("article");
        article.className = "rounded-[18px] border border-[#BFD4FF] bg-[#F4F8FF] px-5 py-4 shadow-sm relative group transition-all hover:shadow-md";

        // Format Tanggal: "Diumumkan 21 November 2025"
        const dateObj = new Date(item.created_at);
        const dateStr = new Intl.DateTimeFormat("id-ID", {
            day: "numeric", month: "long", year: "numeric"
        }).format(dateObj);

        article.innerHTML = `
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h3 class="text-[14px] font-semibold text-slate-800 mb-1">
                        ${escapeHtml(item.judul)}
                    </h3>
                    <p class="text-[12px] text-slate-700 leading-snug mb-4 whitespace-pre-line">
                        ${escapeHtml(item.isi_pengumuman)}
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Diumumkan ${dateStr}
                        <span class="ml-1 text-slate-300">• Oleh ${item.creator?.name || 'Admin'}</span>
                    </p>
                </div>
                
                <button class="btn-delete hidden group-hover:flex items-center justify-center w-8 h-8 rounded-full bg-white text-red-500 shadow-sm hover:bg-red-50 transition-colors"
                    data-id="${item.id}" title="Hapus Pengumuman">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </div>
        `;

        // Bind Event Delete per Card
        const btnDel = article.querySelector('.btn-delete');
        if(btnDel) {
            btnDel.addEventListener('click', (e) => {
                e.stopPropagation();
                deletePengumuman(item.id);
            });
        }

        return article;
    }

    // 4. Simpan Data (CREATE)
    async function storePengumuman() {
        if (!inputJudul.value.trim() || !inputIsi.value.trim()) {
            alert("Judul dan isi wajib diisi!");
            return;
        }

        const originalBtnText = btnSubmit.innerText;
        btnSubmit.innerText = "Menyimpan...";
        btnSubmit.disabled = true;

        try {
            const payload = {
                judul: inputJudul.value,
                isi_pengumuman: inputIsi.value,
                unit_kerja_id: null // Default Global (bisa diubah jika ada dropdown input)
            };

            const res = await fetch('/penilai/pengumuman/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (!res.ok) {
                const errData = await res.json();
                throw new Error(errData.message || "Gagal menyimpan");
            }

            // Sukses
            inputJudul.value = "";
            inputIsi.value = "";
            closeModal();
            fetchPengumuman(); // Reload list
            alert("Pengumuman berhasil diterbitkan dan notifikasi dikirim!");

        } catch (error) {
            alert(error.message);
        } finally {
            btnSubmit.innerText = originalBtnText;
            btnSubmit.disabled = false;
        }
    }

    // 5. Hapus Data (DELETE)
    async function deletePengumuman(id) {
        if(!confirm("Apakah Anda yakin ingin menghapus pengumuman ini?")) return;

        try {
            const res = await fetch(`/penilai/pengumuman/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error("Gagal menghapus");

            fetchPengumuman(); // Reload list

        } catch (error) {
            alert("Gagal menghapus pengumuman. Mungkin bukan milik Anda.");
        }
    }


    // --- UTILS & EVENT BINDING ---

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
        previewTitle.textContent = inputJudul.value.trim() || "Judul akan tampil di sini";
        previewBody.textContent = inputIsi.value.trim() || "Isi pengumuman";
        
        const now = new Date();
        const formatter = new Intl.DateTimeFormat("id-ID", { day: "numeric", month: "long", year: "numeric" });
        previewDate.textContent = "Diumumkan tanggal " + formatter.format(now);
    }
    
    // XSS Prevention simple helper
    function escapeHtml(text) {
        if (!text) return "";
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Event Listeners
    btnOpen?.addEventListener("click", openModal);
    btnClose?.addEventListener("click", closeModal);
    btnCancel?.addEventListener("click", closeModal);
    
    modal?.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    inputJudul?.addEventListener("input", updatePreview);
    inputIsi?.addEventListener("input", updatePreview);

    btnSubmit?.addEventListener("click", (e) => {
        e.preventDefault();
        storePengumuman();
    });

    // Initial Load
    fetchPengumuman();
    updatePreview();
});