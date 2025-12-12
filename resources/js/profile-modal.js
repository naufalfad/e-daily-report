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
                fileNameDisplay.querySelector('span').textContent = "File terpilih: " + name;
                fileNameDisplay.classList.remove('hidden');
            }
        });
    }

    // --- 3. VALIDASI & LOGIKA MODAL (AKUN) ---
    const modal = document.getElementById('tailwind-modal');
    const btnTrigger = document.getElementById('btn-trigger-modal');
    const btnCancel = document.getElementById('btn-cancel-modal');
    const btnConfirm = document.getElementById('btn-confirm-final');
    const formAccount = document.getElementById('form-account');
    const usernameInput = document.querySelector('input[name="username"]');

    /**
     * Memastikan Username hanya berisi huruf, angka, titik (.), dan underscore (_).
     */
    const isValidUsername = (username) => {
        // Asumsi: Swal.fire sudah dimuat di layouts.app (karena Anda sudah menggunakannya sebelumnya)
        if (typeof Swal === 'undefined') return true; 

        if (!username) return false;
        // Regex: Hanya mengizinkan a-z, A-Z, 0-9, titik, dan underscore. TIDAK ADA SPASI.
        const regex = /^[a-zA-Z0-9._]+$/;
        return regex.test(username) && username.length >= 3; 
    };

    // Fungsi untuk mengurus logout setelah sukses
    const handleAccountUpdateSuccess = (newUsername) => {
        Swal.fire({
            title: 'Berhasil Diperbarui!',
            html: `
                <p>Username atau Password Anda telah berhasil diganti.</p>
                <p class="mt-2 text-red-500 font-bold">Anda harus login ulang!</p>
                <p class="text-sm mt-1">Gunakan username baru: <code>${newUsername}</code></p>
            `,
            icon: 'success',
            showCancelButton: false,
            confirmButtonText: 'OK, Logout Sekarang',
            allowOutsideClick: false,
            customClass: {
                confirmButton: 'bg-red-600 hover:bg-red-700'
            }
        }).then(() => {
            // Ini akan memicu logout di backend
            window.location.href = '{{ route("logout") }}'; // Harap pastikan route ini ada
        });
    };
    
    // Fungsi untuk SweetAlert sukses update Biodata
    const handleBiodataUpdateSuccess = (message) => {
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: message || 'Biodata berhasil diperbarui.',
            timer: 2000,
            showConfirmButton: false
        });
    };


    // Buka Modal (Sekaligus Validasi Frontend)
    if (btnTrigger) {
        btnTrigger.addEventListener('click', (e) => {
            e.preventDefault(); 

            // 1. Validasi Username Format
            if (!isValidUsername(usernameInput.value)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Username hanya boleh mengandung huruf (a-z), angka (0-9), titik (.), atau underscore (_). Spasi tidak diizinkan.',
                    confirmButtonColor: '#B6241C'
                });
                usernameInput.focus();
                usernameInput.classList.add('border-red-500', 'ring-red-500');
                return;
            }

            // 2. Jika valid, buka modal konfirmasi
            usernameInput.classList.remove('border-red-500', 'ring-red-500');
            modal.classList.remove('hidden');
        });
    }

    // Tutup Modal
    if (btnCancel) {
        btnCancel.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }

    // Eksekusi Simpan Final
    if (btnConfirm) {
        btnConfirm.addEventListener('click', () => {
            // Loading State
            btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            btnConfirm.disabled = true;
            btnCancel.disabled = true;

            formAccount.submit(); // Submit form
        });
    }

    // --- 4. LOGIKA TOGGLE PASSWORD (EYE ICON) ---
    window.togglePassword = function(fieldId) {
        const input = document.getElementById(fieldId);
        const toggleButton = input.nextElementSibling;
        const icon = toggleButton.querySelector('i');
        
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

    // --- 5. DETEKSI SUKSES DARI SESSION (Setelah page reload) ---
    const successAccountElement = document.getElementById('account-update-success');
    const successBiodataElement = document.getElementById('biodata-update-success');

    if (successAccountElement) {
        const newUsername = successAccountElement.dataset.username;
        handleAccountUpdateSuccess(newUsername);
    } else if (successBiodataElement) {
        const message = successBiodataElement.dataset.message;
        handleBiodataUpdateSuccess(message);
    }
});