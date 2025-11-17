document.addEventListener("DOMContentLoaded", () => {
  // --- 1. Toggle Password Logic ---
  const pwd = document.getElementById("password");
  const btnToggle = document.getElementById("togglePassword");
  const eyeOpen = document.getElementById("eyeOpen");
  const eyeClosed = document.getElementById("eyeClosed");

  if (btnToggle && pwd) {
    btnToggle.addEventListener("click", () => {
      const showing = pwd.type === "password";
      pwd.type = showing ? "text" : "password";

      eyeOpen.classList.toggle("hidden", showing);
      eyeClosed.classList.toggle("hidden", !showing);
      btnToggle.setAttribute("aria-pressed", String(showing));
    });
  }

  // --- 2. Login API Logic ---
  const loginForm = document.getElementById('loginForm');
  const errorAlert = document.getElementById('error-alert');
  const errorMessage = document.getElementById('error-message');
  const btnSubmit = document.getElementById('btn-submit');
  const btnText = document.getElementById('btn-text');
  const btnLoader = document.getElementById('btn-loader');

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      // Reset State
      errorAlert.classList.add('hidden');
      btnSubmit.disabled = true;
      btnText.classList.add('hidden');
      btnLoader.classList.remove('hidden');

      // Ambil data
      const formData = new FormData(loginForm);
      const payload = Object.fromEntries(formData.entries());

      try {
        // Panggil API
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.message || 'Terjadi kesalahan saat login');
        }

        // Login Sukses
        // 1. Simpan Token & Data User
        localStorage.setItem('auth_token', result.access_token);
        localStorage.setItem('user_data', JSON.stringify(result.data));

        // 2. Redirect Sesuai Role
        // Asumsi struktur: result.data.roles = [{name: 'admin'}, ...]
        const roles = result.data.roles || [];
        const roleName = roles.length > 0 ? roles[0].name.toLowerCase() : 'staf';

        if (roleName.includes('admin')) {
          window.location.href = '/admin/dashboard'; // Sesuaikan route admin Paduka
        } else {
          window.location.href = '/staf/dashboard';  // Sesuaikan route staf Paduka
        }

      } catch (error) {
        // Tampilkan Error
        errorMessage.textContent = error.message;
        errorAlert.classList.remove('hidden');
        
        // Reset Button
        btnSubmit.disabled = false;
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
      }
    });
  }
});