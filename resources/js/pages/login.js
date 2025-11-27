import { showToast } from '../global/notification';

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const submitBtn = document.getElementById('btn-submit');
    const btnText   = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // 1. Validasi Token CSRF (Wajib ada di Blade)
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenMeta) {
                showToast('Error Sistem: Token keamanan (CSRF) tidak ditemukan di halaman ini.', 'error');
                return;
            }
            const csrfToken = csrfTokenMeta.getAttribute('content');

            // 2. Ambil Data Form
            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            // 3. Set Loading State
            if(submitBtn) submitBtn.disabled = true;
            if(btnText) btnText.classList.add('hidden');
            if(btnLoader) btnLoader.classList.remove('hidden');

            try {
                // 4. Kirim Request Login dengan Header Lengkap
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken // <-- INI KUNCI PERBAIKANNYA
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                // Handle Error Validation (422) atau Auth Failed (401/419)
                if (!response.ok) {
                    throw new Error(result.message || 'Login gagal, periksa kembali data Anda.');
                }

                // 5. LOGIN SUKSES
                showToast('Login berhasil! Mengalihkan...', 'success');

                // Simpan token (jika pakai Sanctum/API token di masa depan)
                if (result.access_token) {
                    localStorage.setItem('auth_token', result.access_token);
                }

                // Redirect Berdasarkan Role
                const user = result.data || {}; // Handle jika result.data undefined
                const roles = user.roles ? user.roles.map(r => r.nama_role.toLowerCase()) : [];

                setTimeout(() => {
                    if (roles.includes('admin')) {
                        window.location.href = '/admin/dashboard';
                    } 
                    else if (roles.includes('kepala dinas') || roles.includes('kadis')) {
                        window.location.href = '/kadis/dashboard';
                    } 
                    else if (roles.includes('penilai')) {
                        window.location.href = '/penilai/dashboard';
                    } 
                    else {
                        window.location.href = '/staf/dashboard';
                    }
                }, 1000);

            } catch (error) {
                console.error(error);
                
                // Pesan khusus jika error 419 (CSRF) masih muncul
                let errorMessage = error.message;
                if (errorMessage.includes('CSRF') || errorMessage.includes('mismatch')) {
                    errorMessage = 'Sesi Anda telah berakhir. Silakan refresh halaman dan coba lagi.';
                }

                showToast(errorMessage, 'error');
                
                // Reset Loading State
                if(submitBtn) submitBtn.disabled = false;
                if(btnText) btnText.classList.remove('hidden');
                if(btnLoader) btnLoader.classList.add('hidden');
            }
        });
    }
});