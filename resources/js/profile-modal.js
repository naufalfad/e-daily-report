document.addEventListener('DOMContentLoaded', function () {
    
    // --- 1. LOGIKA TAB SWITCHING (Manual Tailwind) ---
    window.switchTab = function(tabName) {
        // Hide semua konten tab
        document.getElementById('tab-content-biodata').classList.add('hidden');
        document.getElementById('tab-content-account').classList.add('hidden');
        document.getElementById('tab-content-biodata').classList.remove('block');
        document.getElementById('tab-content-account').classList.remove('block');

        // Reset style tombol tab jadi default (abu-abu)
        const btnBio = document.getElementById('tab-btn-biodata');
        const btnAcc = document.getElementById('tab-btn-account');
        
        const activeClass = ['border-[#1C7C54]', 'text-[#1C7C54]', 'bg-green-50/50', 'border-b-2'];
        const inactiveClass = ['border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-50', 'border-b-2'];

        // Hapus kelas aktif dari keduanya dulu
        btnBio.classList.remove(...activeClass);
        btnAcc.classList.remove(...activeClass);
        btnBio.classList.add(...inactiveClass);
        btnAcc.classList.add(...inactiveClass);

        // Aktifkan tab yang dipilih
        const selectedContent = document.getElementById('tab-content-' + tabName);
        const selectedBtn = document.getElementById('tab-btn-' + tabName);

        if (selectedContent && selectedBtn) {
            selectedContent.classList.remove('hidden');
            selectedContent.classList.add('block'); // Pastikan muncul

            selectedBtn.classList.remove(...inactiveClass);
            selectedBtn.classList.add(...activeClass);
        }
    };

    // --- 2. LOGIKA UPLOAD FILE PREVIEW ---
    const fileInput = document.getElementById('foto_profil');
    const fileNameDisplay = document.getElementById('file-name-display');

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const name = e.target.files[0].name;
                fileNameDisplay.textContent = "File terpilih: " + name;
                fileNameDisplay.classList.remove('hidden');
            }
        });
    }

    // --- 3. LOGIKA MODAL TAILWIND ---
    const modal = document.getElementById('tailwind-modal');
    const btnTrigger = document.getElementById('btn-trigger-modal');
    const btnCancel = document.getElementById('btn-cancel-modal');
    const btnConfirm = document.getElementById('btn-confirm-final');
    const formAccount = document.getElementById('form-account');

    // Buka Modal
    if (btnTrigger) {
        btnTrigger.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });
    }

    // Tutup Modal
    if (btnCancel) {
        btnCancel.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }

    // Eksekusi Simpan
    if (btnConfirm) {
        btnConfirm.addEventListener('click', () => {
            // Loading State
            btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            btnConfirm.disabled = true;
            btnCancel.disabled = true;

            formAccount.submit();
        });
    }

    // --- 4. LOGIKA TOGGLE PASSWORD (EYE ICON) ---
    window.togglePassword = function(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };
});