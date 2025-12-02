import Swal from "sweetalert2";

export function manajemenPegawaiData() {
    const BASE_URL = "/e-daily-report/api/admin/pegawai";
    const MASTER_URL = "/e-daily-report/api/admin/master-dropdown";
    const getToken = () => localStorage.getItem("auth_token");

    return {
        // --- STATE ---
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
        openDetail: false,
        openUpload: false,

        // Data Holders
        editId: null,
        detailData: {},

        // [DATA MASTER] Untuk Dropdown
        unitKerjaList: [],
        bidangList: [],
        jabatanList: [],
        roleList: [],
        atasanList: [], // Dinamis dari API

        // Form Data
        formData: {
            name: '',
            username: '',
            nip: '',
            password: '',
            unit_kerja_id: '',
            bidang_id: '',
            jabatan_id: '',
            role_id: '',
            atasan_id: ''
        },
        isFetchingAtasan: false,

        async initPage() {
            console.log("🚀 Manajemen Pegawai: Initializing...");

            // Load Data Master (Dropdown)
            await this.fetchMasterData();

            // Load Data Pegawai
            await this.fetchData();

            // Watchers (Aktifkan setelah load awal agar tidak spam API)
            this.$watch('formData.unit_kerja_id', (val) => { if(this.openAdd || this.openEdit) this.onUnitKerjaChange(); });
            this.$watch('formData.bidang_id', (val) => { if(this.openAdd || this.openEdit) this.fetchCalonAtasan(); });
            this.$watch('formData.jabatan_id', (val) => { if(this.openAdd || this.openEdit) this.fetchCalonAtasan(); });
        },

        async fetchMasterData() {
            try {
                const headers = { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" };

                const [resUnit, resJab, resRole] = await Promise.all([
                    fetch(`${MASTER_URL}/unit-kerja`, { headers }),
                    fetch(`${MASTER_URL}/jabatan`, { headers }),
                    fetch(`${MASTER_URL}/roles`, { headers })
                ]);
                this.unitKerjaList = await resUnit.json();
                this.jabatanList = await resJab.json();
                this.roleList = await resRole.json();

            } catch (e) {
                console.error("Gagal load master data", e);
            }
        },

        async onUnitKerjaChange() {
            const unitId = this.formData.unit_kerja_id;
            this.bidangList = []; // Reset

            if (!unitId) return;
            try {
                const res = await fetch(`${MASTER_URL}/bidang-by-unit-kerja/${unitId}`, {
                    headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" }
                });
                this.bidangList = await res.json();
            } catch (e) { console.error(e); }
        },

        async fetchCalonAtasan() {
            const { unit_kerja_id, bidang_id, jabatan_id } = this.formData;
            this.atasanList = [];

            // Jangan fetch kalau data belum lengkap
            if (!unit_kerja_id || !jabatan_id) return;

            this.isFetchingAtasan = true;
            try {
                const params = new URLSearchParams({ unit_kerja_id, bidang_id: bidang_id || '', jabatan_id });
                const res = await fetch(`${MASTER_URL}/calon-atasan?${params}`, {
                    headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" }
                });
                const candidates = await res.json();
                this.atasanList = candidates;
                
                // Auto-select jika cuma 1 dan bukan mode edit (agar tidak menimpa data lama user)
                if (!this.editId && candidates.length === 1) {
                    this.formData.atasan_id = candidates[0].id;
                }
                // Jika kosong (misal Kepala Dinas), set null
                else if (candidates.length === 0) {
                    this.formData.atasan_id = '';
                }

            } catch (e) {
                console.error("Gagal cari atasan", e);
            } finally {
                this.isFetchingAtasan = false;
            }
        },

        async fetchData(url = BASE_URL) {
            this.isLoading = true;
            let targetUrl = url;
            if (this.search) targetUrl += (targetUrl.includes('?') ? '&' : '?') + `search=${encodeURIComponent(this.search)}`;
            
            try {
                const response = await fetch(targetUrl, {
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Accept": "application/json"
                    }
                });

                if (!response.ok) throw new Error("Gagal mengambil data");

                const json = await response.json();
                console.log("Data Loaded:", json);

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

        async submitForm(type) {
            const isEdit = type === 'edit';
            const url = isEdit ? `${BASE_URL}/${this.editId}` : BASE_URL;
            const method = isEdit ? 'PUT' : 'POST';
            
            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { "Authorization": `Bearer ${getToken()}`, "Content-Type": "application/json", "Accept": "application/json" },
                    body: JSON.stringify(this.formData)
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
                        headers: {
                            "Authorization": `Bearer ${getToken()}`,
                            "Accept": "application/json"
                        }
                    });

                    if (!response.ok) throw new Error("Gagal menghapus data");

                    Swal.fire('Terhapus!', 'Data pegawai berhasil dihapus.', 'success');
                    this.fetchData();

                } catch (error) {
                    Swal.fire("Error", error.message, "error");
                }
            }
        },

        toggleAdd(val) { 
            this.openAdd = val; 
            if(val) {
                this.editId = null;
                this.formData = { name: '', username: '', nip: '', password: '', unit_kerja_id: '', bidang_id: '', jabatan_id: '', role_id: '', atasan_id: '' };
                this.bidangList = []; this.atasanList = [];
            }
        },
        toggleEdit(val) { this.openEdit = val; },
        toggleUpload(val) { this.openUpload = val; },

        async openModalEdit(item) {
            this.editId = item.id;
            
            // Pre-fill data dasar
            this.formData = {
                name: item.name,
                username: item.username,
                nip: item.nip,
                password: '',
                unit_kerja_id: item.unit_kerja_id,
                bidang_id: item.bidang_id,
                jabatan_id: item.jabatan_id,
                role_id: item.roles?.[0]?.id || '',
                atasan_id: item.atasan_id
            };

            // Load Dependent Dropdowns (Sequential agar aman)
            if (item.unit_kerja_id) await this.onUnitKerjaChange();
            
            // Restore Bidang ID setelah list terload
            this.formData.bidang_id = item.bidang_id;

            // Load Atasan Candidates
            if (item.jabatan_id) await this.fetchCalonAtasan();
            
            // Restore Atasan ID setelah list terload
            this.formData.atasan_id = item.atasan_id;

            this.openEdit = true;
        },

        toggleUpload(val) {
            this.openUpload = val;
        },

        // Pagination Helpers
        changePage(url) {
            if (url) this.fetchData(url);
        }
    };
}
