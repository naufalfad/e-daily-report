import Swal from "sweetalert2";

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("login-form");
    const submitBtn = document.getElementById("btn-submit");
    const btnText = document.getElementById("btn-text");
    const btnLoader = document.getElementById("btn-loader");

    if (loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const csrfTokenMeta = document.querySelector(
                'meta[name="csrf-token"]'
            );
            if (!csrfTokenMeta) {
                Swal.fire({
                    icon: "error",
                    title: "Kesalahan Sistem",
                    text: "Token keamanan (CSRF) tidak ditemukan.",
                });
                return;
            }

            const csrfToken = csrfTokenMeta.getAttribute("content");

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            submitBtn.disabled = true;
            btnText.classList.add("hidden");
            btnLoader.classList.remove("hidden");

            try {
                const response = await fetch("/login", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                // ==========================================================
                // [PERBAIKAN UTAMA] Cek Status 403 & Redirect URL
                // ==========================================================
                if (!response.ok) {

                    // 1. Cek apakah ada redirect_url (Maintenance Mode atau Security Gate)
                    if (response.status === 403 && result.redirect_url) {

                        // Hapus semua data token lokal jika ada (Clean up)
                        localStorage.removeItem("auth_token");

                        Swal.fire({
                            icon: "warning",
                            title: "Akses Dibatasi",
                            text: result.message,
                            showConfirmButton: false,
                            timer: 1500,
                        });

                        // Lakukan pengalihan ke halaman Maintenance
                        setTimeout(() => {
                            window.location.href = result.redirect_url;
                        }, 500);

                        return; // Hentikan alur kode di sini
                    }

                    // 2. Tangani Error Non-Redirect (Gagal login, Suspend, dll.)
                    throw new Error(
                        result.message || "Username atau password salah."
                    );
                }

                // Jika sukses (response.ok)
                Swal.fire({
                    icon: "success",
                    title: "Login Berhasil",
                    text: "Mengalihkan halaman...",
                    timer: 1500,
                    showConfirmButton: false,
                });

                if (result.access_token) {
                    localStorage.setItem("auth_token", result.access_token);
                }

                const user = result.data || {};
                const roles = user.roles
                    ? user.roles.map((r) => r.nama_role.toLowerCase())
                    : [];

                setTimeout(() => {
                    if (roles.some(role => role.includes("admin"))) {
                        window.location.href = "/admin/dashboard";

                    } else if (
                        roles.includes("kepala dinas") ||
                        roles.includes("kadis")
                    ) {
                        window.location.href = "/kadis/dashboard";
                    } else if (roles.includes("penilai")) {
                        window.location.href = "/penilai/dashboard";
                    } else {
                        window.location.href = "/staf/dashboard";
                    }
                }, 1200);
            } catch (error) {
                console.error(error);

                Swal.fire({
                    icon: "error",
                    title: "Login Gagal",
                    // Tampilkan pesan error dari throw Error di atas
                    text: error.message || "Maaf, akun tidak ditemukan. Silakan periksa kembali username atau password Anda.",
                    confirmButtonColor: "#1C7C54",
                });

                // Reset Loading State
                if (submitBtn) submitBtn.disabled = false;
                if (btnText) btnText.classList.remove("hidden");
                if (btnLoader) btnLoader.classList.add("hidden");
            }
        });
    }
});