// =============================
// PENGATURAN SISTEM - JS TERPISAH
// =============================

document.addEventListener('DOMContentLoaded', function () {

    const buttons = document.querySelectorAll('.settings-menu-btn');
    const panels = document.querySelectorAll('[data-settings-panel]');

    // ---------------------------
    // SET ACTIVE MENU
    // ---------------------------
    function setActive(btn) {
        buttons.forEach(b => {
            b.classList.remove('text-[15px]', 'font-medium', 'text-[#0E1726]');
            b.classList.add('text-[14px]', 'font-normal', 'text-[#9CA3AF]');
        });

        btn.classList.remove('text-[14px]', 'font-normal', 'text-[#9CA3AF]');
        btn.classList.add('text-[15px]', 'font-medium', 'text-[#0E1726]');
    }

    // ---------------------------
    // TAMPILKAN PANEL
    // ---------------------------
    function showPanel(key) {
        panels.forEach(p => {
            if (p.dataset.settingsPanel === key) {
                p.classList.remove('hidden');
            } else {
                p.classList.add('hidden');
            }
        });
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const key = btn.getAttribute('data-settings-menu');
            setActive(btn);
            showPanel(key);
        });
    });

    // Default panel
    showPanel('sistem');

    // ==================================================================
    // RESET PASSWORD ADMIN — MODAL
    // ==================================================================

    const resetAdminCard = document.getElementById('reset-admin-card');
    const resetAdminModal = document.getElementById('reset-admin-modal');
    const btnCancelReset = document.getElementById('btn-reset-admin-cancel');
    const btnSaveReset = document.getElementById('btn-reset-admin-save');

    function openResetModal() {
        resetAdminModal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeResetModal() {
        resetAdminModal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    if (resetAdminCard) resetAdminCard.addEventListener('click', openResetModal);
    if (btnCancelReset) btnCancelReset.addEventListener('click', closeResetModal);
    if (btnSaveReset) btnSaveReset.addEventListener('click', closeResetModal);

    if (resetAdminModal) {
        resetAdminModal.addEventListener('click', function (e) {
            if (e.target === resetAdminModal) {
                closeResetModal();
            }
        });
    }

    // ==================================================================
    // TOGGLE PASSWORD EYE
    // ==================================================================

    const eyeButtons = document.querySelectorAll('[data-eye-target]');

    eyeButtons.forEach(btn => {
        const targetId = btn.getAttribute('data-eye-target');
        const input = document.getElementById(targetId);
        const eyeShow = btn.querySelector('.eye-show');
        const eyeHide = btn.querySelector('.eye-hide');

        if (!input) return;

        btn.addEventListener('click', function () {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            if (eyeShow && eyeHide) {
                if (isPassword) {
                    eyeShow.classList.add('hidden');
                    eyeHide.classList.remove('hidden');
                } else {
                    eyeShow.classList.remove('hidden');
                    eyeHide.classList.add('hidden');
                }
            }
        });
    });
});
