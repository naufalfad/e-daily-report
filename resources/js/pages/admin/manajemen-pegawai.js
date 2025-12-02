import Swal from "sweetalert2";

export function manajemenPegawaiData() {
    // [KONFIGURASI]
    // Pastikan URL ini mengarah ke API yang benar (sesuai prefix sub-folder jika ada)
    const BASE_URL = "/e-daily-report/api/admin/pegawai";
    const MASTER_URL = "/e-daily-report/api/admin/master-dropdown"; // Endpoint Helper

    // Helper ambil token
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

        // [WATCHER] Flag untuk mencegah looping
        isFetchingAtasan: false,

        // --- INIT ---
        async initPage() {
            console.log("🚀 Manajemen Pegawai: Initializing...");

            // Load Data Master (Dropdown)
            await this.fetchMasterData();

            // Load Data Pegawai
            await this.fetchData();

            // Setup Watcher Manual (Alpine x-effect bisa juga, tapi ini lebih terkontrol)
            this.$watch('formData.unit_kerja_id', () => this.onUnitKerjaChange());
            this.$watch('formData.bidang_id', () => this.fetchCalonAtasan());
            this.$watch('formData.jabatan_id', () => this.fetchCalonAtasan());
        },

        // --- MASTER DATA FETCHING ---
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

        // --- LOGIKA PINTAR: CHANGE HANDLERS ---

        // 1. Saat Unit Kerja Berubah -> Ambil Bidang yang Sesuai
        async onUnitKerjaChange() {
            const unitId = this.formData.unit_kerja_id;
            this.bidangList = []; // Reset

            if (!unitId) return;

            try {
                const res = await fetch(`${MASTER_URL}/bidang-by-unit-kerja/${unitId}`, {
                    headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" }
                });
                this.bidangList = await res.json();
            } catch (e) {
                console.error("Gagal load bidang", e);
            }
        },

        // 2. Saat Bidang/Jabatan Berubah -> Cari Calon Atasan
        async fetchCalonAtasan() {
            const { unit_kerja_id, bidang_id, jabatan_id } = this.formData;

            // Reset list atasan dulu
            this.atasanList = [];

            // Jangan fetch kalau data belum lengkap
            if (!unit_kerja_id || !jabatan_id) return;

            this.isFetchingAtasan = true;

            try {
                // Buat Query String
                const params = new URLSearchParams({
                    unit_kerja_id,
                    bidang_id: bidang_id || '', // Bidang boleh kosong utk Kepala Dinas
                    jabatan_id
                });

                const res = await fetch(`${MASTER_URL}/calon-atasan?${params}`, {
                    headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" }
                });

                const candidates = await res.json();
                this.atasanList = candidates;

                // [AUTO SELECT] Jika cuma ada 1 calon, langsung pilih dia!
                if (candidates.length === 1) {
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

        // --- 2. CREATE & UPDATE (SUBMIT FORM) ---
        async submitForm(type) {
            const isEdit = type === 'edit';
            const url = isEdit ? `${BASE_URL}/${this.editId}` : BASE_URL;
            const method = isEdit ? 'PUT' : 'POST';

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

        // --- 3. DELETE ---
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

        // --- HELPER MODALS ---
        toggleAdd(val) {
            this.openAdd = val;
            if (val) {
                // Reset form saat buka
                this.formData = {
                    name: '', email: '', username: '', nip: '', password: '',
                    unit_kerja_id: '', bidang_id: '', jabatan_id: '',
                    role_id: '', atasan_id: ''
                };
                // Reset list dinamis
                this.bidangList = [];
                this.atasanList = [];
            }
        },

        toggleEdit(val) {
            this.openEdit = val;
            this.editId = null;
        },

        async openModalEdit(item) {
            this.editId = item.id;

            // Populate Data Dasar
            this.formData = {
                name: item.name,
                username: item.username,
                nip: item.nip,
                password: '',
                unit_kerja_id: item.unit_kerja_id,
                bidang_id: item.bidang_id,
                jabatan_id: item.jabatan_id,
                role_id: item.roles && item.roles.length > 0 ? item.roles[0].id : '',
                atasan_id: item.atasan_id // Sementara simpan ID lama
            };

            // [PENTING] Trigger manual load Bidang & Atasan agar dropdown terisi
            // Kita harus 'await' agar list terisi sebelum ID terpilih
            if (item.unit_kerja_id) {
                await this.onUnitKerjaChange(); // Load list bidang
                this.formData.bidang_id = item.bidang_id; // Set ulang ID bidang
            }

            if (item.jabatan_id) {
                await this.fetchCalonAtasan(); // Load list atasan
                this.formData.atasan_id = item.atasan_id; // Set ulang ID atasan
            }

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