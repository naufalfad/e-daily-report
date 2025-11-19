// resources/js/profile-modal.js

document.addEventListener('DOMContentLoaded', () => {
    // Sidebar toggle (mobile)
    const sidebarToggle = document.getElementById('sb-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            // Di sini ikuti cara kamu sekarang untuk buka/tutup sidebar.
            // Misal: document.documentElement.classList.toggle('sidebar-open');
            document.documentElement.classList.toggle('sidebar-open');
        });
    }

    // Modal profil (masih ada di layout, walau icon profile di topbar dihapus)
    const modal = document.getElementById('profile-modal');
    const openBtn = document.getElementById('btn-open-profile-modal');
    const closeBtn = document.getElementById('btn-close-profile-modal');

    if (!modal) return;

    const openModal = () => modal.classList.remove('hidden');
    const closeModal = () => modal.classList.add('hidden');

    if (openBtn) {
        openBtn.addEventListener('click', openModal);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
});
