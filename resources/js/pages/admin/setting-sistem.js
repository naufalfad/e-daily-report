// =============================
// PENGATURAN SISTEM - JS LOGIC
// =============================

document.addEventListener('DOMContentLoaded', function () {

    const buttons = document.querySelectorAll('.settings-menu-btn');
    const panels = document.querySelectorAll('[data-settings-panel]');

    // --- 1. TAB SWITCHING LOGIC ---
    function setActive(btn) {
        // Reset semua tombol ke style default (inactive)
        buttons.forEach(b => {
            b.classList.remove('bg-slate-50', 'text-[#0E1726]', 'font-medium');
            b.classList.add('text-[#5B687A]', 'font-normal', 'hover:bg-slate-50');
        });

        // Set tombol aktif
        btn.classList.remove('text-[#5B687A]', 'font-normal', 'hover:bg-slate-50');
        btn.classList.add('bg-slate-50', 'text-[#0E1726]', 'font-medium');
    }

    function showPanel(key) {
        panels.forEach(p => {
            if (p.dataset.settingsPanel === key) {
                p.classList.remove('hidden');
                // Efek fade-in sederhana
                p.style.opacity = 0;
                setTimeout(() => p.style.opacity = 1, 50);
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

    // Default: Buka tab pertama ('sistem')
    const defaultBtn = document.querySelector('[data-settings-menu="sistem"]');
    if(defaultBtn) defaultBtn.click();


    // --- 2. MODAL LOGIC ---
    const resetAdminCard = document.getElementById('reset-admin-card');
    const resetAdminModal = document.getElementById('reset-admin-modal');
    const btnCancelReset = document.getElementById('btn-reset-admin-cancel');
    const btnSaveReset = document.getElementById('btn-reset-admin-save');

    function toggleModal(show) {
        if(!resetAdminModal) return;
        if(show) {
            resetAdminModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden'); // Prevent scroll
        } else {
            resetAdminModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    if (resetAdminCard) resetAdminCard.addEventListener('click', () => toggleModal(true));
    if (btnCancelReset) btnCancelReset.addEventListener('click', () => toggleModal(false));
    
    // Close ketika klik di luar modal (overlay)
    if (resetAdminModal) {
        resetAdminModal.addEventListener('click', (e) => {
            if (e.target === resetAdminModal) toggleModal(false);
        });
    }


    // --- 3. PASSWORD VISIBILITY TOGGLE ---
    const eyeButtons = document.querySelectorAll('[data-eye-target]');

    eyeButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-eye-target');
            const input = document.getElementById(targetId);
            const eyeShow = this.querySelector('.eye-show');
            const eyeHide = this.querySelector('.eye-hide');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                eyeShow.classList.add('hidden');
                eyeHide.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeShow.classList.remove('hidden');
                eyeHide.classList.add('hidden');
            }
        });
    });
});