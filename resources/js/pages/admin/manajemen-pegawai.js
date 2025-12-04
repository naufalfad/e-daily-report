import Swal from "sweetalert2";

export function manajemenPegawaiData() {
<<<<<<< Updated upstream

    // Helper ambil token
    const BASE_URL = "/api/admin/pegawai";
    const MASTER_URL = "/api/admin/master-dropdown";

=======
    const BASE_URL = "/api/admin/pegawai";
    const MASTER_URL = "/api/admin/master-dropdown";
>>>>>>> Stashed changes
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
        // roleList: [], // DIHAPUS - Dipindah ke Akun Pengguna
        atasanList: [],

        // Form Data
        formData: {
            name: '',
            // username: '', // DIHAPUS
            nip: '',
            // password: '', // DIHAPUS
            unit_kerja_id: '',
            bidang_id: '',
            jabatan_id: '',
            // role_id: '', // DIHAPUS
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

            // Setup Watcher Manual
            this.$watch('formData.unit_kerja_id', () => this.onUnitKerjaChange());
            this.$watch('formData.bidang_id', () => this.fetchCalonAtasan());
            this.$watch('formData.jabatan_id', () => this.fetchCalonAtasan());
        },

        // --- MASTER DATA FETCHING ---
        async fetchMasterData() {
            try {
                const headers = { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" };

                // [PERBAIKAN] Hanya fetch data HR: Unit Kerja dan Jabatan
                const [resUnit, resJab] = await Promise.all([
                    fetch(`${MASTER_URL}/unit-kerja`, { headers }),
                    fetch(`${MASTER_URL}/jabatan`, { headers }),
                    // Endpoint roles dihapus dari sini
                ]);

                this.unitKerjaList = await resUnit.json();
                this.jabatanList = await resJab.json();
                // this.roleList = []; // Dikosongkan
            } catch (e) {
                console.error("Gagal load master data", e);
            }
        },

        // --- LOGIKA PINTAR: CHANGE HANDLERS (Tidak ada perubahan signifikan) ---

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

            this.atasanList = [];

            // Jangan fetch kalau data belum lengkap
            if (!unit_kerja_id || !jabatan_id) return;

            this.isFetchingAtasan = true;

            try {
                // Buat Query String
                const params = new URLSearchParams({
                    unit_kerja_id,
                    bidang_id: bidang_id || '',
                    jabatan_id
                });

                const res = await fetch(`${MASTER_URL}/calon-atasan?${params}`, {
                    headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" }
                });

                const candidates = await res.json();
                this.atasanList = candidates;

                // [AUTO SELECT]
                if (candidates.length === 1) {
                    this.formData.atasan_id = candidates[0].id;
                }
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
            // [PERBAIKAN] Hapus pencarian Username di client-side
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
                // Menampilkan error 404 dari console (asumsi BASE_URL sudah benar di server)
                Swal.fire("Error API", "Gagal memuat data. Cek BASE_URL di JS dan Route API.", "error");
            } finally {
                this.isLoading = false;
            }
        },

        // --- 2. CREATE & UPDATE (SUBMIT FORM) ---
        async submitForm(type) {
            const isEdit = type === 'edit';
            const url = isEdit ? `${BASE_URL}/${this.editId}` : BASE_URL;
            // Gunakan method POST untuk Laravel PUT/PATCH jika form-data tidak mendukung PUT
            const method = isEdit ? 'PUT' : 'POST'; 

            // [PERBAIKAN UTAMA] Payload hanya berisi data HR (tanpa kredensial)
            const payload = {
                name: this.formData.name,
                nip: this.formData.nip,
                unit_kerja_id: this.formData.unit_kerja_id,
                bidang_id: this.formData.bidang_id,
                jabatan_id: this.formData.jabatan_id,
                atasan_id: this.formData.atasan_id,
                // Hilangkan role_id, username, password
            };

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        // Tambahkan header X-HTTP-METHOD-OVERRIDE jika menggunakan POST untuk PUT/PATCH
                        ...(isEdit && { 'X-HTTP-Method-Override': 'PUT' })
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
                // [PERBAIKAN] Reset hanya field HR
                this.formData = {
                    name: '', nip: '',
                    unit_kerja_id: '', bidang_id: '', jabatan_id: '',
                    atasan_id: ''
                };
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

            // Populate Data Dasar (Hanya field HR)
            this.formData = {
                name: item.name,
                nip: item.nip,
                unit_kerja_id: item.unit_kerja_id,
                bidang_id: item.bidang_id,
                jabatan_id: item.jabatan_id,
                atasan_id: item.atasan_id,
                // Kredensial lain tidak dimasukkan
            };

            // Trigger manual load Bidang & Atasan
            if (item.unit_kerja_id) {
                await this.onUnitKerjaChange();
                this.formData.bidang_id = item.bidang_id;
            }

            if (item.jabatan_id) {
                await this.fetchCalonAtasan();
                this.formData.atasan_id = item.atasan_id;
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
