import { showToast } from '../global/notification';

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const submitBtn = document.getElementById('btn-submit'); // Pastikan ID tombol submit sesuai
    const btnText   = document.getElementById('btn-text');   // Text di dalam tombol
    const btnLoader = document.getElementById('btn-loader'); // Icon loading (jika ada)

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // 1. Ambil Data Form
            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            // 2. Set Loading State
            if(submitBtn) submitBtn.disabled = true;
            if(btnText) btnText.classList.add('hidden');
            if(btnLoader) btnLoader.classList.remove('hidden');

            try {
                // 3. Kirim Request Login
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Login gagal');
                }

                // 4. LOGIN SUKSES - LOGIKA REDIRECT
                showToast('Login berhasil! Mengalihkan...', 'success');

                // Simpan token (opsional, untuk request API masa depan)
                if (result.access_token) {
                    localStorage.setItem('auth_token', result.access_token);
                }

                // Cek Role dan Redirect
                const user = result.data;
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
                        // Default Staf
                        window.location.href = '/staf/dashboard';
                    }
                }, 1000); // Delay sedikit agar user sempat baca toast

            } catch (error) {
                console.error(error);
                showToast(error.message, 'error');
                
                // Reset Loading State
                if(submitBtn) submitBtn.disabled = false;
                if(btnText) btnText.classList.remove('hidden');
                if(btnLoader) btnLoader.classList.add('hidden');
            }
        });
    }
});