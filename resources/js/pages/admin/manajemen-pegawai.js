document.addEventListener("DOMContentLoaded", function () {
    /* ==========================
     *  MODAL TAMBAH PEGAWAI
     * ========================== */
    const addOpenBtn = document.getElementById("btn-open-add-pegawai");
    const addModal = document.getElementById("modal-add-pegawai");
    const addCloseBtn = document.getElementById("btn-close-add-pegawai");
    const addCancelBtn = document.getElementById("btn-cancel-add-pegawai");

    function openAddModal() {
        addModal.classList.remove("hidden");
        addModal.classList.add("flex");
    }

    function closeAddModal() {
        addModal.classList.add("hidden");
        addModal.classList.remove("flex");
    }

    if (addOpenBtn) addOpenBtn.addEventListener("click", openAddModal);
    if (addCloseBtn) addCloseBtn.addEventListener("click", closeAddModal);
    if (addCancelBtn)
        addCancelBtn.addEventListener("click", function (e) {
            e.preventDefault();
            closeAddModal();
        });

    addModal?.addEventListener("click", function (e) {
        if (e.target === addModal) closeAddModal();
    });

    /* ==========================
     *  MODAL UPLOAD EXCEL
     * ========================== */
    const excelOpenBtn = document.getElementById("btn-open-upload-excel");
    const excelModal = document.getElementById("modal-upload-excel");
    const excelCloseBtn = document.getElementById("btn-close-upload-excel");
    const excelCancelBtn = document.getElementById("btn-cancel-upload-excel");

    function openExcelModal() {
        excelModal.classList.remove("hidden");
        excelModal.classList.add("flex");
    }

    function closeExcelModal() {
        excelModal.classList.add("hidden");
        excelModal.classList.remove("flex");
    }

    if (excelOpenBtn) excelOpenBtn.addEventListener("click", openExcelModal);
    if (excelCloseBtn) excelCloseBtn.addEventListener("click", closeExcelModal);
    if (excelCancelBtn)
        excelCancelBtn.addEventListener("click", function (e) {
            e.preventDefault();
            closeExcelModal();
        });

    excelModal?.addEventListener("click", function (e) {
        if (e.target === excelModal) closeExcelModal();
    });
});
