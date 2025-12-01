import Swal from "sweetalert2"; // Pastikan SweetAlert sudah diinstall/import

export function manajemenPegawaiData() {
    // [CONFIG]
    const BASE_URL = "/api/admin/pegawai"; 
    
    // Helper untuk ambil token (jika pakai Sanctum via Header)
    const getToken = () => localStorage.getItem("auth_token");

    return {
        // State
        items: [],
        isLoading: false,
        
        // Pagination State
        pagination: {
            current_page: 1,
            last_page: 1,
            next_page_url: null,
            prev_page_url: null
        },

        // State Modal
        openDetail: false,
        openEdit: false,
        openAdd: false,
        
        // Data Holder
        detailData: {},
        formData: {
            name: '',
            email: '',
            nip: '',
            password: '', // Optional saat edit
            unit_kerja_id: '',
            bidang_id: '',
            jabatan_id: '',
            role_id: '',
            atasan_id: ''
        },
        editId: null, // ID user yang sedang diedit

        // --- INIT ---
        async initPage() {
            console.log("🚀 Manajemen Pegawai: Initializing...");
            await this.fetchData();
        },

        // --- 1. READ (FETCH DATA) ---
        async fetchData(url = BASE_URL) {
            this.isLoading = true;
            try {
                const response = await fetch(url, {
                    headers: { 
                        "Authorization": `Bearer ${getToken()}`, 
                        "Accept": "application/json" 
                    }
                });

                if (!response.ok) throw new Error("Gagal mengambil data");

                const json = await response.json();
                
                // Laravel Pagination membungkus data dalam 'data'
                this.items = json.data; 
                
                // Update Pagination State
                this.pagination = {
                    current_page: json.current_page,
                    last_page: json.last_page,
                    next_page_url: json.next_page_url,
                    prev_page_url: json.prev_page_url
                };

            } catch (error) {
                console.error(error);
                Swal.fire("Error", "Gagal memuat data pegawai", "error");
            } finally {
                this.isLoading = false;
            }
        },

        // --- 2. CREATE & UPDATE (SUBMIT FORM) ---
        async submitForm(type) {
            // type: 'add' atau 'edit'
            const isEdit = type === 'edit';
            const url = isEdit ? `${BASE_URL}/${this.editId}` : BASE_URL;
            const method = isEdit ? 'PUT' : 'POST';

            // Ambil data dari Form HTML (pastikan input punya name="...")
            // Atau gunakan x-model binding this.formData
            const formElement = document.getElementById(isEdit ? 'form-edit' : 'form-add');
            const payload = new FormData(formElement);

            // Jika PUT (Edit), Laravel butuh trik _method atau x-www-form-urlencoded
            // Tapi karena kita pakai JSON body di fetch, kita convert FormData ke Object
            const dataObj = Object.fromEntries(payload.entries());

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        // CSRF Token jika perlu (untuk web routes), tapi API usually stateless
                        // "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(dataObj)
                });

                const result = await response.json();

                if (!response.ok) {
                    // Handle Validation Error
                    if (response.status === 422) {
                        let errorMsg = Object.values(result.errors).flat().join('\n');
                        throw new Error(errorMsg || "Validasi gagal");
                    }
                    throw new Error(result.message || "Terjadi kesalahan");
                }

                Swal.fire("Berhasil", result.message, "success");
                
                // Reset & Refresh
                if (isEdit) this.closeModalEdit();
                else this.toggleAdd(false);
                
                this.fetchData(); // Refresh tabel

            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        },

        // --- 3. DELETE ---
        async deleteItem(id) {
            const confirm = await Swal.fire({
                title: 'Hapus Pegawai?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (confirm.isConfirmed) {
                try {
                    const response = await fetch(`${BASE_URL}/${id}`, {
                        method: "DELETE",
                        headers: {
                            "Authorization": `Bearer ${getToken()}`,
                            "Accept": "application/json"
                        }
                    });

                    if (!response.ok) throw new Error("Gagal menghapus data");

                    Swal.fire('Terhapus!', 'Data pegawai berhasil dihapus.', 'success');
                    this.fetchData(); // Refresh

                } catch (error) {
                    Swal.fire("Error", error.message, "error");
                }
            }
        },

        // --- HELPER MODALS ---
        openModalDetail(item) {
            this.detailData = item;
            this.openDetail = true;
        },
        closeModalDetail() {
            this.openDetail = false;
            this.detailData = {};
        },
        openModalEdit(item) {
            this.editId = item.id;
            this.editData = { ...item }; // Copy object
            
            // Populate form logic (bisa via x-model atau manual set value di DOM)
            // Contoh manual untuk memastikan data masuk ke form edit
            setTimeout(() => {
               // Logic mengisi value input form edit jika tidak pakai x-model penuh
            }, 100);

            this.openEdit = true;
        },
        closeModalEdit() {
            this.openEdit = false;
            this.editId = null;
            this.editData = null;
        },
        toggleAdd(val) {
            this.openAdd = val;
            if(!val) {
                // Reset form add jika ditutup
                document.getElementById('form-add')?.reset();
            }
        },
        
        // Pagination Helpers
        changePage(url) {
            if (url) this.fetchData(url);
        }
    };
}