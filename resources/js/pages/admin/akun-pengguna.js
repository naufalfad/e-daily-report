document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modal-add-akun");
    const openBtn = document.getElementById("btn-open-add-akun");
    const closeBtn = document.getElementById("btn-close-add-akun");
    const cancelBtn = document.getElementById("btn-cancel-add-akun");

    if (!modal) return;

    const openModal = () => {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    };

    const closeModal = () => {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    };

    openBtn?.addEventListener("click", openModal);
    closeBtn?.addEventListener("click", closeModal);

    cancelBtn?.addEventListener("click", (e) => {
        e.preventDefault();
        closeModal();
    });

    // Klik area gelap di belakang untuk close
    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });
});
