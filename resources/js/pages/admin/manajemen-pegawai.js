import Swal from "sweetalert2";

// Export Alpine component object
export function manajemenPegawaiData() {
    // === CONFIG ===
    const BASE_URL = "/api/admin/pegawai";
    const MASTER_URL = "/api/admin/master-dropdown";
    const IMPORT_URL = "/api/admin/pegawai/import";

    const getToken = () => localStorage.getItem("auth_token");

    return {
        // --- STATE ---
        items: [],
        isLoading: false,
        
        // Filter State
        search: "",
        filterUnitKerja: "",

        fileUpload: null,
        isImporting: false,

        // Pagination Meta
        pagination: {
            current_page: 1, last_page: 1, next_page_url: null, prev_page_url: null, from: 0, to: 0, total: 0
        },

        // Modal States
        openAdd: false,
        openEdit: false,
        openUpload: false,

        // Data Holders
        editId: null,

        // [DATA MASTER] Dropdown Options
        unitKerjaList: [],
        bidangList: [],
        jabatanList: [],
        roleList: [], 
        atasanList: [], 
        
        // [METADATA] Untuk menyimpan info detail bidang yang dipilih (level, occupied slots)
        selectedBidangMeta: null,

        // Form Data (Model)
        form: {
            name: "", nip: "", email: "", username: "", password: "",
            unit_kerja_id: "", bidang_id: "", jabatan_id: "", role: "", atasan_id: "",
            is_active: true
        },

        // Loading Flags
        isFetchingAtasan: false,

        // --- LIFECYCLE ---
        async initPage() {
            console.log("🚀 Manajemen Pegawai: Initializing...");

            // Reset modal states
            this.openAdd = false;
            this.openEdit = false;
            this.openUpload = false;

            // 1. Load Data Master Basic
            await this.fetchMasterData();

            // 2. Load Data Table Pegawai
            await this.fetchData(1);

            // 3. Setup Watchers (Reactive Logic)
            this.setupWatchers();
        },

        setupWatchers() {
            // [WATCHER] Unit Kerja
            this.$watch("form.unit_kerja_id", (val) => {
                if (val && (this.openAdd || this.openEdit)) {
                    this.onUnitKerjaChange(val);
                    // Reset bidang & atasan saat unit ganti
                    // Kecuali saat first load edit mode (handled in openModalEdit)
                    if (this.bidangList.length === 0) { // Indikasi bukan load awal edit
                        this.form.bidang_id = "";
                        this.atasanList = [];
                        this.selectedBidangMeta = null;
                    }
                }
            });

            // [WATCHER] Bidang (The Brain of Validation)
            this.$watch("form.bidang_id", (val) => {
                if (!val) {
                    this.selectedBidangMeta = null;
                    return;
                }

                // 1. Cari metadata bidang yang dipilih dari list
                // Pastikan bidangList sudah terisi objek lengkap dari backend
                const meta = this.bidangList.find(b => b.id == val);
                this.selectedBidangMeta = meta || null;

                if ((this.openAdd || this.openEdit) && this.selectedBidangMeta) {
                    // 2. Trigger Validasi Jabatan:
                    // Jika jabatan yang sedang dipilih ternyata sudah penuh di bidang ini, reset.
                    // Kecuali jika user yang diedit adalah pemegang jabatan itu sendiri (ID jabatan == current user's job)
                    // Tapi di frontend kita belum punya info detail itu, jadi validasi basic dulu.
                    
                    const currentJabatanId = parseInt(this.form.jabatan_id);
                    if (currentJabatanId && this.selectedBidangMeta.occupied_positions.includes(currentJabatanId)) {
                        // Cek apakah kita sedang mengedit user ini sendiri? 
                        // (Logic kompleks, simplifikasinya: warning user / reset)
                        // Untuk UX yang aman, kita biarkan dulu tapi dropdown option nanti didisable.
                        // User harus ganti manual.
                        // Atau kita bisa auto-reset:
                        // this.form.jabatan_id = ""; 
                    }

                    // 3. Trigger Fetch Atasan baru
                    this.fetchAtasanList();
                }
            });
            
            // [WATCHER] Jabatan (Auto-Role Logic)
            this.$watch("form.jabatan_id", (val) => {
                if (val && (this.openAdd || this.openEdit)) {
                    const id = parseInt(val);
                    
                    // Skenario 9 & 10: Auto-fill Role
                    // ID 2=Sekretaris, 3=Kabid, 4=Kasub -> Penilai (Role ID 3 biasanya, tapi kita pakai string 'Penilai' atau ID dari roleList)
                    // ID 5=Staf -> Staf
                    
                    // Kita cari nama role di listRole berdasarkan logika bisnis
                    // Asumsi di DB: Role 3 = "Penilai", Role 4 = "Staf"
                    if ([2, 3, 4].includes(id)) {
                        // Cari role Penilai
                        const penilaiRole = this.roleList.find(r => r.nama_role === 'Penilai');
                        if (penilaiRole) this.form.role = penilaiRole.nama_role; 
                    } else if (id === 5) {
                        // Cari role Staf
                        const stafRole = this.roleList.find(r => r.nama_role === 'Staf');
                        if (stafRole) this.form.role = stafRole.nama_role;
                    }

                    // Trigger ulang fetch atasan karena jabatan berubah (hierarki berubah)
                    this.fetchAtasanList();
                }
            });
        },

        // --- FETCH DATA (READ TABLE) ---
        async fetchData(page = 1) {
            this.isLoading = true;
            try {
                const params = new URLSearchParams({
                    page: page,
                    search: this.search,
                    unit_kerja_id: this.filterUnitKerja, 
                    per_page: 10
                });

                const response = await fetch(`${BASE_URL}?${params.toString()}`, {
                    headers: {
                        Authorization: `Bearer ${getToken()}`,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                });

                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                if (!response.ok) throw new Error("Gagal mengambil data pegawai");

                const json = await response.json();
                
                const data = json.data?.data ? json.data.data : (json.data || []);
                const meta = json.data?.data ? json.data : json;

                this.items = data;
                
                this.pagination = {
                    current_page: meta.current_page,
                    last_page: meta.last_page,
                    next_page_url: meta.next_page_url,
                    prev_page_url: meta.prev_page_url,
                    from: meta.from,
                    to: meta.to,
                    total: meta.total
                };
            } catch (error) {
                console.error(error);
                Swal.fire("Error", "Gagal memuat data pegawai.", "error");
            } finally {
                this.isLoading = false;
            }
        },

        // --- MASTER DATA LOGIC ---
        async fetchMasterData() {
            try {
                const headers = { Authorization: `Bearer ${getToken()}`, Accept: "application/json" };
                
                // Fetch paralel
                const [resUnit, resJab, resRole] = await Promise.all([
                    fetch(`${MASTER_URL}/unit-kerja`, { headers }),
                    fetch(`${MASTER_URL}/jabatan`, { headers }),
                    fetch(`${MASTER_URL}/roles`, { headers }) 
                ]);

                const unitData = await resUnit.json();
                const jabData = await resJab.json();
                const roleData = await resRole.json();

                this.unitKerjaList = unitData.data || unitData;
                
                // Jabatan: Pastikan bersih, logic is_taken nanti dibaca dari selectedBidangMeta
                this.jabatanList = (jabData.data || jabData).map(j => ({
                    id: j.id,
                    nama_jabatan: j.nama_jabatan
                }));

                this.roleList = roleData.data || roleData;

            } catch (e) {
                console.error("Gagal load master data:", e);
            }
        },

        async onUnitKerjaChange(unitId) {
            // Reset bidang list
            this.bidangList = []; 
            if (!unitId) return;

            try {
                // Endpoint ini sekarang mengembalikan data yang DIPERKAYA (level, parent, occupied)
                const res = await fetch(`${MASTER_URL}/bidang?unit_kerja_id=${unitId}`, {
                    headers: { Authorization: `Bearer ${getToken()}`, Accept: "application/json" }
                });
                const json = await res.json();
                // Simpan data lengkap ke state
                this.bidangList = json.data || json; 
            } catch (e) { console.error("Gagal load bidang", e); }
        },

        async fetchAtasanList() {
            const { unit_kerja_id, bidang_id, jabatan_id } = this.form;
            
            // Atasan butuh unit_kerja minimal, dan jabatan user (untuk tahu hierarki)
            if (!unit_kerja_id || !jabatan_id) {
                this.atasanList = [];
                return;
            }

            this.isFetchingAtasan = true;
            try {
                const params = new URLSearchParams({ 
                    unit_kerja_id, 
                    bidang_id: bidang_id || '', // Bisa null untuk Kaban/Sekban
                    jabatan_id: jabatan_id
                });
                
                const res = await fetch(`${MASTER_URL}/pegawai/calon-atasan?${params}`, {
                    headers: { Authorization: `Bearer ${getToken()}`, Accept: "application/json" }
                });
                
                const json = await res.json();
                const candidates = json.data || json;
                this.atasanList = candidates;

                // UX: Auto select jika cuma ada 1 kandidat atasan
                if (candidates.length === 1) {
                    this.form.atasan_id = candidates[0].id;
                } else if (candidates.length === 0) {
                    this.form.atasan_id = ""; // Reset jika tidak ada kandidat
                }
            } catch (e) {
                console.error("Gagal cari atasan:", e);
                this.atasanList = [];
            } finally {
                this.isFetchingAtasan = false;
            }
        },

        // --- CRUD ACTIONS ---
        toggleAdd(val) {
            this.openAdd = val;
            if (val) {
                this.form = {
                    name: "", nip: "", email: "", username: "", password: "",
                    unit_kerja_id: "", bidang_id: "", jabatan_id: "", role: "", atasan_id: "",
                    is_active: true
                };
                this.atasanList = [];
                this.bidangList = [];
                this.selectedBidangMeta = null;
                this.editId = null;
            }
        },

        toggleEdit(val) {
            this.openEdit = val;
            this.editId = null;
            this.selectedBidangMeta = null;
        },

        async openModalEdit(item) {
            this.editId = item.id;
            
            // Populate basic form
            this.form = {
                name: item.name,
                nip: item.nip,
                email: item.email,
                username: item.username,
                password: "", 
                unit_kerja_id: item.unit_kerja_id,
                bidang_id: item.bidang_id,
                jabatan_id: item.jabatan_id,
                role: item.roles && item.roles.length > 0 ? item.roles[0].nama_role : (item.role || ""), // Pakai nama_role untuk string match
                atasan_id: item.atasan_id,
                is_active: item.is_active
            };

            // Load data relasi untuk edit state
            if (this.form.unit_kerja_id) {
                // Tunggu load bidang selesai dulu
                await this.onUnitKerjaChange(this.form.unit_kerja_id);
                
                // Set metadata bidang terpilih (untuk validasi jabatan)
                if (this.form.bidang_id) {
                    const meta = this.bidangList.find(b => b.id == this.form.bidang_id);
                    this.selectedBidangMeta = meta || null;
                }
                
                // Fetch atasan list
                await this.fetchAtasanList();
            }

            // Restore atasan_id (karena fetchAtasanList mungkin meresetnya)
            if(item.atasan_id) {
                 this.form.atasan_id = item.atasan_id;
            }

            this.openEdit = true;
        },

        async submitForm() {
            const isEdit = this.openEdit;
            const url = isEdit ? `${BASE_URL}/${this.editId}` : BASE_URL;
            const method = isEdit ? "PUT" : "POST";

            // Validasi nama role sebelum kirim (karena backend butuh 'nama_role')
            if (this.form.role && !this.roleList.some(r => r.nama_role === this.form.role)) {
                // Fallback: jika role yang diinput tidak ada di list (misal salah ketik manual), warning
                 // Swal.fire("Warning", "Role tidak valid", "warning"); 
                 // Tapi karena dropdown, harusnya aman.
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        Authorization: `Bearer ${getToken()}`,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify(this.form),
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        const errors = result.errors ? Object.values(result.errors).flat().join("\n") : result.message;
                        throw new Error(errors);
                    }
                    throw new Error(result.message || "Terjadi kesalahan saat menyimpan");
                }

                Swal.fire("Berhasil", result.message, "success");
                
                if (isEdit) this.openEdit = false;
                else this.openAdd = false;

                this.fetchData(this.pagination.current_page);

            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        },

        async deleteItem(id) {
            const confirm = await Swal.fire({
                title: "Hapus Pegawai?",
                text: "Data ini akan dihapus permanen.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            });

            if (confirm.isConfirmed) {
                try {
                    const response = await fetch(`${BASE_URL}/${id}`, {
                        method: "DELETE",
                        headers: {
                            Authorization: `Bearer ${getToken()}`,
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        },
                    });

                    if (!response.ok) throw new Error("Gagal menghapus data");

                    Swal.fire("Terhapus!", "Data pegawai berhasil dihapus.", "success");
                    this.fetchData(this.pagination.current_page);
                } catch (error) {
                    Swal.fire("Error", error.message, "error");
                }
            }
        },

        // --- IMPORT DATA (REFACTORED) ---
        toggleUpload(val) {
            this.openUpload = val;
            if (!val) {
                this.fileUpload = null;
                // Reset value input file html jika ada
                const input = document.getElementById('file_import');
                if (input) input.value = '';
            }
        },

        handleFileUpload(e) {
            this.fileUpload = e.target.files[0];
        },

        async submitImport() {
            if (!this.fileUpload) {
                Swal.fire("Peringatan", "Pilih file CSV/Excel terlebih dahulu.", "warning");
                return;
            }

            this.isImporting = true;
            const formData = new FormData();
            
            // [CRITICAL] Key harus 'csv_file' sesuai validasi controller
            formData.append('csv_file', this.fileUpload);

            try {
                const response = await fetch(IMPORT_URL, {
                    method: "POST",
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "X-Requested-With": "XMLHttpRequest"
                        // Jangan set Content-Type header secara manual untuk FormData!
                    },
                    body: formData
                });

                const result = await response.json();

                // -----------------------------------------------------------
                // SKENARIO 1: SUKSES SEMPURNA (HTTP 200)
                // -----------------------------------------------------------
                if (response.ok) {
                    Swal.fire({
                        title: "Import Berhasil",
                        text: result.message,
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    this.toggleUpload(false);
                    this.fetchData(1);
                    return;
                }

                // -----------------------------------------------------------
                // SKENARIO 2: PARTIAL SUCCESS / WARNING (HTTP 422)
                // Controller mengirim status 422 + array 'errors' di dalam 'data'
                // -----------------------------------------------------------
                if (response.status === 422 && result.data && result.data.errors) {
                    const errorList = result.data.errors;
                    
                    // Format error array menjadi list HTML yang scrollable
                    let errorHtml = '<div class="text-left text-sm mt-3 p-3 bg-red-50 border border-red-200 rounded-lg max-h-60 overflow-y-auto">';
                    errorHtml += '<p class="font-bold text-red-700 mb-2">Daftar Baris Gagal:</p>';
                    errorHtml += '<ul class="list-disc pl-4 text-red-600 space-y-1">';
                    
                    errorList.forEach(err => {
                        errorHtml += `<li>${err}</li>`;
                    });
                    
                    errorHtml += '</ul></div>';

                    Swal.fire({
                        title: "Import Selesai dengan Catatan",
                        html: `${result.message} <br/> ${errorHtml}`,
                        icon: "warning",
                        width: '600px' // Lebarkan modal agar list terbaca
                    });
                    
                    // Tetap refresh data karena sebagian data mungkin berhasil masuk
                    this.toggleUpload(false);
                    this.fetchData(1);
                    return;
                }

                // -----------------------------------------------------------
                // SKENARIO 3: CRITICAL ERROR / VALIDASI FILE (HTTP 500 / 400)
                // -----------------------------------------------------------
                let errorMessage = result.message || "Terjadi kesalahan saat upload.";
                
                // Jika error validasi Laravel biasa (misal file type wrong)
                if (response.status === 422 && result.errors) {
                     errorMessage = Object.values(result.errors).flat().join('\n');
                }

                throw new Error(errorMessage);

            } catch (error) {
                console.error(error);
                Swal.fire("Gagal Import", error.message, "error");
            } finally {
                this.isImporting = false;
            }
        }
    };
}