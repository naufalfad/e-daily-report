document.addEventListener("DOMContentLoaded", () => {
  // --- Toggle Password ---
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

  // --- Login Form ---
  const loginForm = document.getElementById('loginForm');
  const errorAlert = document.getElementById('error-alert');
  const errorMessage = document.getElementById('error-message');
  const btnSubmit = document.getElementById('btn-submit');
  const btnText = document.getElementById('btn-text');
  const btnLoader = document.getElementById('btn-loader');

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      // Reset state UI
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
          }
          throw new Error(errorText);
        }

        // Simpan token & data user
        localStorage.setItem('auth_token', result.access_token);
        localStorage.setItem('user_data', JSON.stringify(result.data));

        // --- Redirect berdasarkan role ---
        const roles = result.data.roles || [];
        const roleName = roles.length > 0 ? (roles[0].nama_role || 'staf').toLowerCase() : 'staf';

        if (roleName.includes('penilai')) {
          window.location.href = '/penilai/dashboard';
        } else if (roleName.includes('admin')) {
          window.location.href = '/admin/dashboard';
        } else {
          window.location.href = '/staf/dashboard';
        }

      } catch (error) {
        if (errorMessage) errorMessage.textContent = error.message;
        if (errorAlert) errorAlert.classList.remove('hidden');
      } finally {
        btnSubmit.disabled = false;
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
      }
    });
  }

  // --- Global helper: fetch API dengan token ---
  window.authFetch = async (url, options = {}) => {
    const token = localStorage.getItem('auth_token');
    if (!options.headers) options.headers = {};
    options.headers['Authorization'] = token ? `Bearer ${token}` : '';
    options.headers['Accept'] = 'application/json';
    return fetch(url, options);
  };
});
