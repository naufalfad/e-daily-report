// resources/js/pages/admin/manajemen-pegawai.js

export function manajemenPegawaiData() {
    const TOKEN = localStorage.getItem("auth_token");
    const BASE_URL = "/api/admin/pegawai"; // Sesuaikan dengan route API backend Anda

    return {
        // State Data
        items: [],
        isLoading: false,

        // State Modal Detail
        openDetail: false,
        detailData: null,

        // State Modal Edit
        openEdit: false,
        editData: null,

        // State Modal Tambah (Migrasi dari Vanilla JS)
        openAdd: false,

        // State Modal Upload (Migrasi dari Vanilla JS)
        openUpload: false,

        // Init
        async initPage() {
            console.log("Manajemen Pegawai Component Initialized");
            await this.fetchData();
        },

        // Fetch Data Pegawai
        async fetchData() {
            this.isLoading = true;
            try {
                // Contoh call API, sesuaikan endpoint backend
                // const response = await fetch(BASE_URL, {
                //     headers: { Authorization: `Bearer ${TOKEN}`, Accept: "application/json" }
                // });
                // const data = await response.json();
                // this.items = data.data;

                // DUMMY DATA (Hapus ini jika backend sudah siap)
                this.items = [
                    { 
                        id: 1, 
                        nama: 'Fahrizal Mudzaqi', 
                        nip: '1988030109', 
                        jabatan: 'Staf Teknis', 
                        unit_kerja: 'BAPENDA', 
                        atasan: 'Joko Anwar', 
                        status: 'Aktif' 
                    },
                    { 
                        id: 2, 
                        nama: 'Muhammad Naufal', 
                        nip: '1990010101', 
                        jabatan: 'Kepala Bidang', 
                        unit_kerja: 'Bidang I', 
                        atasan: 'Darius Sabon', 
                        status: 'Cuti' 
                    },
                ];

            } catch (error) {
                console.error("Gagal memuat data pegawai:", error);
            } finally {
                this.isLoading = false;
            }
        },

        // --- Logic Detail ---
        openModalDetail(item) {
            this.detailData = item; // Set data untuk ditampilkan
            this.openDetail = true;
        },
        closeModalDetail() {
            this.openDetail = false;
            setTimeout(() => { this.detailData = null; }, 300); // Clear data setelah animasi
        },

        // --- Logic Edit ---
        openModalEdit(item) {
            // Clone object agar perubahan tidak langsung reaktif ke tabel sebelum disave
            this.editData = JSON.parse(JSON.stringify(item));
            this.openEdit = true;
        },
        closeModalEdit() {
            this.openEdit = false;
            setTimeout(() => { this.editData = null; }, 300);
        },

        // --- Logic Tambah & Upload (Pengganti Vanilla JS) ---
        toggleAdd(state) {
            this.openAdd = state;
        },
        toggleUpload(state) {
            this.openUpload = state;
        }
    };
}