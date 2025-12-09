// resources/js/global/loader.js

// Fungsi helper yang bisa di-import jika butuh trigger manual
export const showLoader = () => {
    const loader = document.getElementById("global-loader");
    if (loader) loader.classList.remove("hidden");
};

export const hideLoader = () => {
    const loader = document.getElementById("global-loader");
    if (loader) loader.classList.add("hidden");
};

document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById("global-loader");
    if (!loader) return;

    // ------------------------------------------------------------------
    // 1. GLOBAL LINK HANDLER (DELEGATION)
    // ------------------------------------------------------------------
    // Kita pasang telinga di 'document', bukan di masing-masing link.
    // Ini menjamin script AJAX lain jalan duluan sebelum script ini.
    document.addEventListener("click", (e) => {
        // Cari apakah yang diklik adalah link <a> (atau anak dari <a>)
        const link = e.target.closest("a[href]");

        // Jika bukan link, atau link itu tidak punya href, abaikan
        if (!link || !link.getAttribute("href")) return;

        // [KUNCI SAKTI] 
        // Cek apakah event ini sudah dimatikan (preventDefault) oleh script lain?
        // Jika script Filter/AJAX Baginda sudah jalan dan mencegah refresh,
        // maka e.defaultPrevented akan bernilai TRUE.
        if (e.defaultPrevented) return; // Loader mundur teratur, jangan muncul.

        const url = link.getAttribute("href");
        const target = link.getAttribute("target");

        // Filter standar (Hash, JS, Tab Baru)
        if (url.startsWith("#") || url.startsWith("javascript")) return;
        if (target === "_blank") return;
        
        // Filter Modifier Keys (Ctrl+Click, dll)
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

        // Filter Download & Ekstensi File
        if (link.hasAttribute("download")) return;
        if (/\.(pdf|xlsx|xls|doc|docx|zip|rar|csv|jpg|png)$/i.test(url)) return;

        // Jika lolos semua filter, baru munculkan loader
        showLoader();

        // Safety Net: Matikan loader setelah 10 detik jika halaman macet
        setTimeout(hideLoader, 10000);
    });

    // ------------------------------------------------------------------
    // 2. GLOBAL FORM SUBMIT HANDLER (DELEGATION)
    // ------------------------------------------------------------------
    document.addEventListener("submit", (e) => {
        const form = e.target;

        // [KUNCI SAKTI]
        // Jika form ini submit via AJAX (sudah di-prevent defaultnya),
        // maka kita JANGAN nyalakan loader global.
        if (e.defaultPrevented) return;

        // Cek validitas HTML5
        if (!form.checkValidity()) return;

        // Cek target blank
        if (form.target === "_blank") return;

        showLoader();
    });

    // ------------------------------------------------------------------
    // 3. MEMBERSIHKAN LOADER SAAT USER "BACK"
    // ------------------------------------------------------------------
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) hideLoader();
    });
    
    // Double check saat load selesai
    window.addEventListener('load', hideLoader);
});