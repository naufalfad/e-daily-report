document.addEventListener("DOMContentLoaded", async () => {
    const notifBadge = document.getElementById("notif-badge");
    const notifList  = document.getElementById("notif-list");

    if (!notifBadge || !notifList) return;

    const token = localStorage.getItem("auth_token");
    if (!token) return;

    try {
        // ======================================================
        // 1. FETCH DATA DARI BACKEND
        // ======================================================
        const res = await fetch('/api/notifikasi', {
            headers: {
                "Authorization": `Bearer ${token}`,
                "Accept": "application/json"
            }
        });

        const { unread_count, data } = await res.json();

        // ======================================================
        // 2. UPDATE BADGE
        // ======================================================
        notifBadge.textContent = unread_count > 9 ? "9+" : unread_count;

        if (data.length === 0) {
            notifList.innerHTML = `
                <div class="p-4 text-sm text-slate-500 text-center">
                    Tidak ada notifikasi.
                </div>
            `;
            return;
        }

        // ======================================================
        // 3. ICON MAPPING (berdasarkan tipe_notifikasi)
        // ======================================================
        const typeIconMap = {
            lkh_new_submission: {
                bg: "bg-blue-500/10",
                icon: "/assets/icon/notif-new.svg"
            },
            lkh_update_submission: {
                bg: "bg-blue-600/10",
                icon: "/assets/icon/notif-edit.svg"
            },
            lkh_approved: {
                bg: "bg-[#0E7A4A]/10",
                icon: "/assets/icon/notif-success.svg"
            },
            lkh_rejected: {
                bg: "bg-[#DC2626]/10",
                icon: "/assets/icon/notif-danger.svg"
            },
            skp_submitted: {
                bg: "bg-indigo-500/10",
                icon: "/assets/icon/notif-skp.svg"
            },
            skp_approved: {
                bg: "bg-green-600/10",
                icon: "/assets/icon/notif-success.svg"
            },
            skp_rejected: {
                bg: "bg-red-500/10",
                icon: "/assets/icon/notif-danger.svg"
            },
            pengumuman: {
                bg: "bg-yellow-500/10",
                icon: "/assets/icon/notif-warning.svg"
            },
            default: {
                bg: "bg-slate-300/10",
                icon: "/assets/icon/notif-default.svg"
            }
        };

        // ======================================================
        // 4. RENDER LOOP
        // ======================================================
        notifList.innerHTML = ""; // Reset

        data.forEach(n => {
            const icon = typeIconMap[n.tipe_notifikasi] ?? typeIconMap.default;

            const html = `
                <a href="${n.redirect_url ?? '#'}"
                    class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 transition border-b">
                    <div class="flex-1">
                        <div class="text-[13px] font-semibold text-slate-800">
                            ${n.tipe_notifikasi.replaceAll('_', ' ').toUpperCase()}
                        </div>

                        <p class="text-[12px] text-slate-600">
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
