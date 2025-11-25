document.addEventListener("DOMContentLoaded", () => {

    const loader = document.getElementById("global-loader");

    if (!loader) return;

    // -----------------------------
    // TAMPILKAN LOADER SAAT KLIK LINK
    // -----------------------------
    document.querySelectorAll("a[href]").forEach(link => {

        link.addEventListener("click", function (e) {
            const url = this.getAttribute("href");

            if (!url) return;

            // Abaikan anchor dan javascript links
            if (url.startsWith("#") || url.startsWith("javascript")) return;

            loader.classList.remove("hidden");
        });
    });

    // -----------------------------
    // TAMPILKAN LOADER SAAT SUBMIT FORM
    // -----------------------------
    document.querySelectorAll("form").forEach(form => {
        form.addEventListener("submit", () => {
            loader.classList.remove("hidden");
        });
    });
});
