document.addEventListener("DOMContentLoaded", () => {
  const pwd = document.getElementById("password");
  const btn = document.getElementById("togglePassword");
  const eyeOpen = document.getElementById("eyeOpen");
  const eyeClosed = document.getElementById("eyeClosed");

  if (!pwd || !btn || !eyeOpen || !eyeClosed) return;

  btn.addEventListener("click", () => {
    const showing = pwd.type === "password";
    pwd.type = showing ? "text" : "password";

    // toggle icon & aksesibilitas
    eyeOpen.classList.toggle("hidden", showing);
    eyeClosed.classList.toggle("hidden", !showing);
    btn.setAttribute("aria-pressed", String(showing));
    btn.setAttribute(
      "aria-label",
      showing ? "Sembunyikan password" : "Tampilkan password"
    );
    // Optional: cegah fokus pindah
    btn.blur();
  });
});
