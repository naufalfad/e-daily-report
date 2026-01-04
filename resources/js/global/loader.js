// resources/js/global/loader.js

import Swal from 'sweetalert2';

/**
 * --------------------------------------------------------------------------
 * UTILITY: PROCESS LOADER (REUSABLE)
 * --------------------------------------------------------------------------
 * Gunakan ini untuk proses berat (Export PDF, Import Excel, Kalkulasi SKP)
 * tanpa perlu membuat HTML Modal di setiap file Blade.
 * * Cara Pakai di Module Lain:
 * import { ProcessLoader } from '../../global/loader';
 * * ProcessLoader.start('Export Data', 'Sedang menyusun laporan...');
 * ProcessLoader.update('Mengunduh aset gambar...');
 * ProcessLoader.success('Selesai', 'File siap diunduh');
 */
export const ProcessLoader = {
    
    // Memulai Modal Loading
    start: (title = 'Memproses...', message = 'Mohon tunggu sebentar...') => {
        Swal.fire({
            title: title,
            html: `<div class="mt-2 text-sm text-slate-600">${message}</div>`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            width: 400,
            padding: '2em',
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-slate-100',
                title: 'text-xl font-bold text-slate-800 font-poppins',
                htmlContainer: 'font-poppins'
            },
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },

    // Update pesan teks secara real-time (tanpa tutup modal)
    update: (message) => {
        const popup = Swal.getHtmlContainer();
        if(popup) {
            popup.innerHTML = `<div class="mt-2 text-sm text-slate-600 transition-all duration-300">${message}</div>`;
        }
    },

    // Menutup loader
    close: () => {
        Swal.close();
    },

    // Tampilkan pesan sukses
    success: (title = 'Berhasil!', message = 'Proses selesai.') => {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            confirmButtonColor: '#10b981', // Emerald-500
            confirmButtonText: 'Tutup',
            customClass: {
                popup: 'rounded-2xl font-poppins',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold shadow-lg shadow-emerald-200'
            }
        });
    },

    // Tampilkan pesan error
    error: (title = 'Gagal', message = 'Terjadi kesalahan sistem.') => {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonColor: '#f43f5e', // Rose-500
            confirmButtonText: 'Tutup',
            customClass: {
                popup: 'rounded-2xl font-poppins',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold shadow-lg shadow-rose-200'
            }
        });
    }
};


/**
 * --------------------------------------------------------------------------
 * GLOBAL: NAVIGATION LOADER (PAGE TRANSITION)
 * --------------------------------------------------------------------------
 * Logika ini menangani loading bar/spinner saat user pindah halaman (Link/Form)
 * agar UX terasa seperti SPA (Single Page Application).
 */

// Helpers untuk manipulasi DOM Loader Bawaan Layout (jika ada)
const showGlobalLoader = () => {
    const loader = document.getElementById("global-loader");
    if (loader) loader.classList.remove("hidden");
};

const hideGlobalLoader = () => {
    const loader = document.getElementById("global-loader");
    if (loader) loader.classList.add("hidden");
};

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. GLOBAL LINK HANDLER
    // Menangkap semua klik link <a> untuk memunculkan loader
    document.addEventListener("click", (e) => {
        const link = e.target.closest("a[href]");

        // Validasi: Harus link, punya href, dan bukan anchor (#)
        if (!link || !link.getAttribute("href") || link.getAttribute("href").startsWith("#")) return;

        // Validasi: Cek modifier keys (Ctrl/Shift/Cmd + Click) -> Biarkan browser handle (tab baru)
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

        // Validasi: Cek target blank
        if (link.target === "_blank") return;

        // Validasi: Cek apakah event sudah di-prevent oleh script lain (misal: tombol delete, export js)
        if (e.defaultPrevented) return;

        // Validasi: Filter Download & Ekstensi File
        const url = link.getAttribute("href");
        if (link.hasAttribute("download")) return;
        if (/\.(pdf|xlsx|xls|doc|docx|zip|rar|csv|jpg|png|mp4)$/i.test(url)) return;
        if (url.startsWith("mailto:") || url.startsWith("tel:")) return;

        // Lolos semua filter -> Munculkan Loader
        showGlobalLoader();

        // Safety Net: Matikan loader otomatis setelah 8 detik (jika jaringan lambat/timeout)
        setTimeout(hideGlobalLoader, 8000);
    });

    // 2. GLOBAL FORM SUBMIT HANDLER
    // Menangkap submit form untuk memunculkan loader
    document.addEventListener("submit", (e) => {
        const form = e.target;

        // Jika form submit via AJAX (e.preventDefault() sudah dipanggil script lain), abaikan loader navigasi
        if (e.defaultPrevented) return;

        // Cek validitas HTML5 (required, type email, dll)
        if (!form.checkValidity()) return;

        // Cek target blank (print/preview)
        if (form.target === "_blank") return;

        showGlobalLoader();
    });

    // 3. CLEANUP HANDLER
    // Sembunyikan loader saat user menekan tombol "Back" di browser (BFCache restore)
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) hideGlobalLoader();
    });

    // Sembunyikan loader saat halaman selesai dimuat sepenuhnya
    window.addEventListener('load', hideGlobalLoader);
});