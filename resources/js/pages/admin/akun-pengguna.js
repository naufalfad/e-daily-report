import Swal from "sweetalert2";

export function akunPenggunaData() {
    // [KONFIGURASI]
    // BASE_URL mengarah ke Controller UserAccountController
    const BASE_URL = "/api/admin/akun"; 
    const MASTER_URL = "/api/admin/master-dropdown"; 

    const getToken = () => localStorage.getItem("auth_token");

    return {
        // --- STATE ---
        items: [],
        isLoading: false,
        search: '',

        // Data Master
        roleList: [],

        // Modal States
        openCred: false, // Modal Credential (Username & Password)
        openRole: false, // Modal Role
        
        // Data Holders
        targetId: null,
        targetName: '',
        
        // Form Data
        formData: {
            username: '',
            password: '',
            password_confirmation: '',
            role_id: ''
        },

        // --- INIT ---
        async initPage() {
            console.log("🚀 Akun Pengguna: Initializing...");
            await this.fetchMasterData();
            await this.fetchData();
        },

        // --- MASTER DATA FETCHING (Hanya Role) ---
        async fetchMasterData() {
            try {
                const headers = { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" };
                const resRole = await fetch(`${MASTER_URL}/roles`, { headers });
                this.roleList = await resRole.json();
            } catch (e) {
                console.error("Gagal load master role", e);
            }
        },

        // --- 1. READ (FETCH DATA UTAMA) ---
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

                if (!response.ok) throw new Error("Gagal mengambil data akun");

                const json = await response.json();

                this.items = json.data || [];

                // Asumsi pagination juga ada, jika tidak, field ini akan diabaikan
                // this.pagination = json.meta; 

            } catch (error) {
                console.error(error);
                Swal.fire("Error API", "Gagal memuat data akun. Cek API endpoint.", "error");
            } finally {
                this.isLoading = false;
            }
        },

        // --- 2. SECURITY ACTIONS ---
        
        // A. Update Username & Password
        async submitCredentialUpdate() {
            const url = `${BASE_URL}/${this.targetId}/credentials`;
            
            // Payload yang diizinkan oleh UserAccountController
            const payload = {
                username: this.formData.username,
                password: this.formData.password,
                password_confirmation: this.formData.password_confirmation
            };

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
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
                this.toggleCred(false);
                this.fetchData();

            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        },

        // B. Update Role
        async submitRoleUpdate() {
            const url = `${BASE_URL}/${this.targetId}/role`;
            
            const payload = { role_id: this.formData.role_id };

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok) throw new Error(result.message || "Gagal mengubah role");

                Swal.fire("Berhasil", "Hak akses (Role) berhasil diperbarui.", "success");
                this.toggleRole(false);
                this.fetchData();

            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        },

        // C. Toggle Status (Suspend/Activate)
        async toggleStatus(item) {
            const newStatus = !item.is_active;
            const actionText = newStatus ? 'Mengaktifkan' : 'Menonaktifkan (Suspend)';
            
            const confirm = await Swal.fire({
                title: `${actionText} Akun?`,
                text: `Anda yakin ingin ${actionText.toLowerCase()} akun ${item.name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: newStatus ? '#128C60' : '#d33',
                confirmButtonText: `Ya, ${actionText}!`
            });

            if (confirm.isConfirmed) {
                try {
                    const response = await fetch(`${BASE_URL}/${item.id}/status`, {
                        method: "PATCH",
                        headers: {
                            "Authorization": `Bearer ${getToken()}`,
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({ is_active: newStatus })
                    });

                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || "Gagal mengubah status");

                    Swal.fire('Berhasil!', `Akun berhasil ${newStatus ? 'diaktifkan' : 'dinonaktifkan'}.`, 'success');
                    this.fetchData();

                } catch (error) {
                    Swal.fire("Error", error.message, "error");
                }
            }
        },

        // --- HELPER MODALS ---
        
        openModalCred(item) {
            this.targetId = item.id;
            this.targetName = item.name;
            this.formData.username = item.username;
            this.formData.password = '';
            this.formData.password_confirmation = '';
            this.toggleCred(true);
        },

        openModalRole(item) {
            this.targetId = item.id;
            this.targetName = item.name;
            // Ambil role_id pertama (jika ada)
            this.formData.role_id = item.roles && item.roles.length > 0 ? item.roles[0].id : '';
            this.toggleRole(true);
        },

        toggleCred(val) {
            this.openCred = val;
            if (!val) {
                this.targetId = null;
                this.targetName = '';
            }
        },

        toggleRole(val) {
            this.openRole = val;
            if (!val) {
                this.targetId = null;
                this.targetName = '';
            }
        },
    };
}