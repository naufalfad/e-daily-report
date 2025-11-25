document.addEventListener("DOMContentLoaded", async () => {
    const notifBadge = document.getElementById("notif-badge");
    const notifList  = document.getElementById("notif-list");

    if (!notifBadge || !notifList) return;

    const token = localStorage.getItem("auth_token");
    if (!token) return;

    try {
        const res = await fetch('/api/notifikasi', {
            headers: {
                "Authorization": `Bearer ${token}`,
                "Accept": "application/json"
            }
        });

        const { unread_count, data } = await res.json();

        notifBadge.textContent = unread_count > 9 ? "9+" : unread_count;

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
            const html = `
                <a href="${n.redirect_url ?? '#'}"
                    class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition border-b">

                    <div class="flex-1">
                        <div class="text-[13px] font-semibold text-slate-900">
                            ${n.title}
                        </div>

                        <p class="text-[12px] text-slate-600 mt-0.5">
                            ${n.pesan}
                        </p>

                        <span class="text-[11px] text-slate-400">
                            ${new Date(n.created_at).toLocaleString('id-ID')}
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
