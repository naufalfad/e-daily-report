import Swal from "sweetalert2";

export function manajemenPegawaiData() {
    // Config URL dengan Prefix Subpath
    const BASE_URL = "/e-daily-report/api/admin/pegawai";
    const getToken = () => localStorage.getItem("auth_token");

    return {
        items: [],
        isLoading: false,
        search: '',
        
        // Pagination
        pagination: {
            current_page: 1,
            last_page: 1,
            next_page_url: null,
            prev_page_url: null
        },

        // Modal States
        openAdd: false,
        openEdit: false,
        openUpload: false,
        
        // Data Holders
        editId: null,
        formData: {
            name: '',
            username: '', // [GANTI] Email jadi Username
            nip: '',
            password: '',
            unit_kerja_id: '',
            bidang_id: '',
            jabatan_id: '',
            role_id: '',
            atasan_id: ''
        },

        // --- INIT ---
        async initPage() {
            console.log("🚀 Manajemen Pegawai Started");
            await this.fetchData();
        },

        // --- FETCH DATA ---
        async fetchData(url = BASE_URL) {
            this.isLoading = true;
            
            let targetUrl = url;
            if (this.search) {
                const separator = targetUrl.includes('?') ? '&' : '?';
                targetUrl += `${separator}search=${encodeURIComponent(this.search)}`;
            }

            try {
                const response = await fetch(targetUrl, {
                    headers: { 
                        "Authorization": `Bearer ${getToken()}`, 
                        "Accept": "application/json" 
                    }
                });

                if (!response.ok) throw new Error("Gagal mengambil data");
                const json = await response.json();
                
                this.items = json.data || []; 
                this.pagination = {
                    current_page: json.current_page,
                    last_page: json.last_page,
                    next_page_url: json.next_page_url,
                    prev_page_url: json.prev_page_url
                };

            } catch (error) {
                console.error(error);
            } finally {
                this.isLoading = false;
            }
        },

        // --- SUBMIT FORM ---
        async submitForm(type) {
            const isEdit = type === 'edit';
            // URL Endpoint
            const url = isEdit ? `${BASE_URL}/${this.editId}` : BASE_URL;
            const method = isEdit ? 'PUT' : 'POST';

            // Copy Data untuk Payload
            const payload = { ...this.formData };

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        const errors = Object.values(result.errors).flat().join('\n');
                        throw new Error(errors || "Validasi gagal");
                    }
                    throw new Error(result.message || "Terjadi kesalahan");
                }

                Swal.fire("Berhasil", result.message, "success");
                
                if (isEdit) this.toggleEdit(false);
                else this.toggleAdd(false);
                
                this.fetchData(); 

            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        },

        // --- DELETE ---
        async deleteItem(id) {
            const confirm = await Swal.fire({
                title: 'Hapus Pegawai?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (confirm.isConfirmed) {
                try {
                    const response = await fetch(`${BASE_URL}/${id}`, {
                        method: "DELETE",
                        headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" }
                    });

                    if (!response.ok) throw new Error("Gagal menghapus data");
                    Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                    this.fetchData(); 

                } catch (error) {
                    Swal.fire("Error", error.message, "error");
                }
            }
        },

        // --- MODAL HANDLERS ---
        toggleAdd(val) {
            this.openAdd = val;
            if (val) {
                this.resetForm();
            }
        },

        toggleEdit(val) {
            this.openEdit = val;
            this.editId = null;
        },
        
        toggleUpload(val) {
            this.openUpload = val;
        },

        openModalEdit(item) {
            this.editId = item.id;
            this.formData = {
                name: item.name,
                username: item.username, // Load Username
                nip: item.nip,
                password: '',
                unit_kerja_id: item.unit_kerja_id,
                bidang_id: item.bidang_id,
                jabatan_id: item.jabatan_id,
                role_id: item.roles && item.roles.length > 0 ? item.roles[0].id : '',
                atasan_id: item.atasan_id
            };
            this.openEdit = true;
        },

        resetForm() {
            this.formData = {
                name: '', username: '', nip: '', password: '',
                unit_kerja_id: '', bidang_id: '', jabatan_id: '', role_id: '', atasan_id: ''
            };
        },

        changePage(url) { if (url) this.fetchData(url); }
    };
}