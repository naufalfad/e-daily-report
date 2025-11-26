document.addEventListener("DOMContentLoaded", () => {
  // --- 1. Toggle Password UI ---
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

  // --- 2. Login Form Logic (Murni Local Storage) ---
  const loginForm = document.getElementById('loginForm');
  const errorAlert = document.getElementById('error-alert');
  const errorMessage = document.getElementById('error-message');
  const btnSubmit = document.getElementById('btn-submit');
  const btnText = document.getElementById('btn-text');
  const btnLoader = document.getElementById('btn-loader');

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (errorAlert) errorAlert.classList.add('hidden');
      btnSubmit.disabled = true;
      btnText.classList.add('hidden');
      btnLoader.classList.remove('hidden');

      const formData = new FormData(loginForm);
      const payload = Object.fromEntries(formData.entries());

      try {
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
          let errorText = result.message || 'Terjadi kesalahan saat login.';
          
          if (response.status === 422 && result.errors) {
            const firstField = Object.keys(result.errors)[0];
            errorText = result.errors[firstField][0];
          } else if (response.status === 401) {
            errorText = result.message || 'Username atau password salah.';
          } else if (response.status === 419) {
            errorText = "Terjadi konflik sesi. Mohon refresh halaman atau clear cache browser.";
          }
          
          throw new Error(errorText);
        }

        localStorage.setItem('auth_token', result.access_token);
        localStorage.setItem('user_data', JSON.stringify(result.data));

        const roles = result.data.roles || [];
        const roleName = roles.length > 0 ? (roles[0].nama_role || 'staf').toLowerCase() : 'staf';

        if (roleName.includes('penilai')) {
          window.location.href = '/penilai/dashboard';
        } else if (roleName.includes('admin')) {
          window.location.href = '/admin/dashboard';
        } else if (roleName.includes('kadis')) {
          window.location.href = '/kadis/dashboard';
        } else {
          window.location.href = '/staf/dashboard';
        }

      } catch (error) {
        console.error("Login Error:", error);
        if (errorMessage) errorMessage.textContent = error.message;
        if (errorAlert) errorAlert.classList.remove('hidden');
      } finally {
        btnSubmit.disabled = false;
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
      }
    });
  }
});
