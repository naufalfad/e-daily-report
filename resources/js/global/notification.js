/**
 * Menampilkan Notifikasi Toast
 * @param {string} message - Pesan yang ingin ditampilkan
 * @param {string} type - Tipe notifikasi ('success', 'error', 'warning', 'info')
 */
export function showToast(message, type = 'info') {
    // Cek apakah container toast sudah ada
    let toastContainer = document.getElementById('toast-container');

    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-5 right-5 z-50 flex flex-col gap-2';
        document.body.appendChild(toastContainer);
    }

    // Buat elemen toast
    const toast = document.createElement('div');

    // Warna background berdasarkan tipe
    const bgColors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };

    const colorClass = bgColors[type] || bgColors.info;

    toast.className = `${colorClass} text-white px-4 py-3 rounded shadow-lg flex items-center gap-3 min-w-[300px] transform transition-all duration-300 translate-x-full opacity-0`;

    toast.innerHTML = `
        <span class="flex-1 text-sm font-medium">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;

    toastContainer.appendChild(toast);

    // Animasi Masuk
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 10);

    // Auto Remove setelah 3 detik
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300); // Tunggu animasi selesai baru remove DOM
    }, 3000);
}

// --- LOGIKA NOTIFIKASI DROPDOWN (TETAP JALAN) ---
document.addEventListener("DOMContentLoaded", async () => {
    const APP_URL = window.APP_URL;
    const notifBadge = document.getElementById("notif-badge");
    const notifList = document.getElementById("notif-list");

    if (!notifBadge || !notifList) return;

    // Coba ambil token dari meta tag (Cara Laravel standar) atau LocalStorage
    // Jika Paduka pakai Sanctum cookie-based auth, token mungkin tidak perlu di header Authorization manual.
    // Tapi mari kita asumsikan logic auth Paduka sudah benar.
    const token = localStorage.getItem("auth_token");

    // Jika tidak ada token, mungkin pakai cookie session, jadi fetch tetap jalan.
    // Hapus 'if (!token) return;' jika pakai session based.

    try {
        const headers = {
            "Accept": "application/json"
        };

        if (token) {
            headers["Authorization"] = `Bearer ${token}`;
        }

        const res = await fetch(`${APP_URL}/api/notifikasi`, { headers });

        if (!res.ok) return; // Silent fail kalau unauthorized

        const { unread_count, data } = await res.json();

        if (notifBadge) {
            notifBadge.textContent = unread_count > 9 ? "9+" : unread_count;
            // Sembunyikan badge jika 0
            if (unread_count == 0) notifBadge.classList.add('hidden');
            else notifBadge.classList.remove('hidden');
        }

        if (data.length === 0) {
            notifList.innerHTML = `
                <div class="p-4 text-sm text-slate-500 text-center">
                    Tidak ada notifikasi.
                </div>
            `;
            return;
        }

        notifList.innerHTML = "";

        data.forEach(n => {
            // Format tanggal agar aman
            let dateStr = '-';
            try {
                dateStr = new Date(n.created_at).toLocaleString('id-ID', {
                    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
                });
            } catch (e) { }

            const html = `
                <a href="${n.redirect_url ?? '#'}"
                    class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition border-b border-slate-100 last:border-0">
                    
                    <div class="mt-1">
                        <div class="w-2 h-2 rounded-full ${n.read_at ? 'bg-slate-300' : 'bg-blue-500'}"></div>
                    </div>

                    <div class="flex-1">
                        <div class="text-sm font-semibold text-slate-800 line-clamp-1">
                            ${n.title || 'Pemberitahuan'}
                        </div>

                        <p class="text-xs text-slate-600 mt-0.5 line-clamp-2">
                            ${n.pesan || n.data?.message || ''}
                        </p>

                        <span class="text-[10px] text-slate-400 mt-1 block">
                            ${dateStr}
                        </span>
                    </div>
                </a>
            `;

            notifList.insertAdjacentHTML("beforeend", html);
        });

    } catch (error) {
        console.error("Gagal mengambil notifikasi:", error);
    }
});
