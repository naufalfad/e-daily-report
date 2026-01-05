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
        filterUnitKerja: "", // Tambahan filter unit kerja di tabel utama

        fileUpload: null,
        isImporting: false,

        // Pagination Meta
        pagination: {
            current_page: 1,
            last_page: 1,
            next_page_url: null,
            prev_page_url: null,
            from: 0,
            to: 0,
            total: 0
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
        atasanList: [],

        // Form Data (Model)
        formData: {
            name: "",
            nip: "",
            unit_kerja_id: "",
            bidang_id: "",
            jabatan_id: "",
            atasan_id: "",
        },

        // Loading Flags
        isFetchingAtasan: false,

        // --- LIFECYCLE ---
        async initPage() {
            console.log("🚀 Manajemen Pegawai: Initializing...");

            this.openAdd = false;
            this.openEdit = false;
            this.openUpload = false;

            // 1. Load Data Master (Dropdown awal)
            await this.fetchMasterData();

            // 2. Setup Listeners
            this.setupEventListeners();

            // 3. Load Data Pegawai Pertama Kali
            await this.fetchData();

            // 4. Watchers untuk Cascading Dropdown di Form
            this.$watch("formData.unit_kerja_id", () => this.onUnitKerjaChange());
            this.$watch("formData.bidang_id", () => this.fetchCalonAtasan());
            this.$watch("formData.jabatan_id", () => this.fetchCalonAtasan());
        },

        setupEventListeners() {
            // Filter Unit Kerja Utama (di Toolbar Atas)
            const filterUnit = document.getElementById('filterUnitKerja');
            if (filterUnit) {
                filterUnit.addEventListener('change', (e) => {
                    this.filterUnitKerja = e.target.value;
                    this.fetchData(1); // Reset ke halaman 1
                });
            }

            // Pagination Buttons (Mapping ke DOM ID yang dibuat di Blade Fase 3)
            const btnPrev = document.getElementById('prev-page');
            const btnNext = document.getElementById('next-page');
            
            if (btnPrev) btnPrev.addEventListener('click', () => { 
                if (this.pagination.prev_page_url) this.fetchData(this.pagination.current_page - 1); 
            });
            
            if (btnNext) btnNext.addEventListener('click', () => { 
                if (this.pagination.next_page_url) this.fetchData(this.pagination.current_page + 1); 
            });
        },

        // --- FETCH DATA (READ) ---
        async fetchData(page = 1) {
            this.isLoading = true;

            try {
                // Build Query String
                const params = new URLSearchParams({
                    page: page,
                    search: this.search,
                    unit_kerja_id: this.filterUnitKerja, // Kirim filter unit kerja
                    per_page: 10,
                    t: new Date().getTime() // Anti-cache
                });

                const response = await fetch(`${BASE_URL}?${params.toString()}`, {
                    headers: {
                        Authorization: `Bearer ${getToken()}`,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest" // [PENTING] Agar Controller return JSON
                    },
                });

                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                if (!response.ok) throw new Error("Gagal mengambil data pegawai");

                const json = await response.json();

                // Update Data Items
                this.items = json.data || [];

                // Update Pagination Meta
                this.updatePaginationState(json);

            } catch (error) {
                console.error(error);
                Swal.fire("Error", "Gagal memuat data pegawai.", "error");
            } finally {
                this.isLoading = false;
            }
        },

        updatePaginationState(json) {
            this.pagination = {
                current_page: json.current_page,
                last_page: json.last_page,
                next_page_url: json.next_page_url,
                prev_page_url: json.prev_page_url,
                from: json.from,
                to: json.to,
                total: json.total
            };

            // Update DOM Pagination Info & Numbers secara manual (karena di luar x-for loop utama)
            this.renderPaginationDOM();
        },

        renderPaginationDOM() {
            const infoEl = document.getElementById('pagination-info');
            const numbersEl = document.getElementById('pagination-numbers');
            const btnPrev = document.getElementById('prev-page');
            const btnNext = document.getElementById('next-page');

            if (infoEl) {
                infoEl.textContent = `Menampilkan ${this.pagination.from || 0}-${this.pagination.to || 0} dari ${this.pagination.total || 0} data`;
            }

            // Update Buttons State
            if (btnPrev) {
                btnPrev.disabled = !this.pagination.prev_page_url;
                btnPrev.classList.toggle('opacity-30', !this.pagination.prev_page_url);
                btnPrev.classList.toggle('cursor-not-allowed', !this.pagination.prev_page_url);
            }
            if (btnNext) {
                btnNext.disabled = !this.pagination.next_page_url;
                btnNext.classList.toggle('opacity-30', !this.pagination.next_page_url);
                btnNext.classList.toggle('cursor-not-allowed', !this.pagination.next_page_url);
            }

            // Render Sliding Window Numbers
            if (numbersEl) {
                numbersEl.innerHTML = ''; // Reset
                
                const current = this.pagination.current_page;
                const last = this.pagination.last_page;

                const createBtn = (p, active) => {
                    const btn = document.createElement('button');
                    btn.className = `w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-all ${active ? 'bg-[#1C7C54] text-white shadow-sm' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}`;
                    btn.textContent = p;
                    if (!active) {
                        btn.onclick = () => this.fetchData(p);
                    }
                    return btn;
                };

                const createDots = () => {
                    const span = document.createElement('span');
                    span.className = "px-1 text-slate-400 text-sm";
                    span.textContent = "...";
                    return span;
                };

                if (last <= 7) {
                    for (let i = 1; i <= last; i++) numbersEl.appendChild(createBtn(i, i === current));
                } else {
                    numbersEl.appendChild(createBtn(1, 1 === current));
                    if (current > 4) numbersEl.appendChild(createDots());
                    
                    let start = Math.max(2, current - 1);
                    let end = Math.min(last - 1, current + 1);
                    if (current <= 4) end = 5;
                    if (current >= last - 3) start = last - 4;

                    for (let i = start; i <= end; i++) numbersEl.appendChild(createBtn(i, i === current));
                    
                    if (current < last - 3) numbersEl.appendChild(createDots());
                    numbersEl.appendChild(createBtn(last, last === current));
                }
            }
        },

        // --- MASTER DATA & CASCADING DROPDOWN ---
        async fetchMasterData() {
            try {
                const headers = { Authorization: `Bearer ${getToken()}`, Accept: "application/json" };
                const [resUnit, resJab] = await Promise.all([
                    fetch(`${MASTER_URL}/unit-kerja`, { headers }),
                    fetch(`${MASTER_URL}/jabatan`, { headers }),
                ]);
                this.unitKerjaList = await resUnit.json();
                this.jabatanList = await resJab.json();
            } catch (e) {
                console.error("Gagal load master data", e);
            }
        },

        async onUnitKerjaChange() {
            const unitId = this.formData.unit_kerja_id;
            this.bidangList = []; 
            if (!unitId) return;

            try {
                const res = await fetch(`${MASTER_URL}/bidang-by-unit-kerja/${unitId}`, {
                    headers: { Authorization: `Bearer ${getToken()}`, Accept: "application/json" }
                });
                this.bidangList = await res.json();
            } catch (e) { console.error("Gagal load bidang", e); }
        },

        async fetchCalonAtasan() {
            const { unit_kerja_id, jabatan_id } = this.formData;
            this.atasanList = [];
            
            if (!unit_kerja_id || !jabatan_id) return;

            this.isFetchingAtasan = true;
            try {
                const params = new URLSearchParams({ 
                    unit_kerja_id, 
                    bidang_id: this.formData.bidang_id || "", 
                    jabatan_id 
                });
                const res = await fetch(`${MASTER_URL}/calon-atasan?${params}`, {
                    headers: { Authorization: `Bearer ${getToken()}`, Accept: "application/json" }
                });
                const candidates = await res.json();
                this.atasanList = candidates;

                // Auto select jika cuma 1 kandidat dan form masih kosong atasan_id nya
                if (candidates.length === 1 && !this.formData.atasan_id) {
                    this.formData.atasan_id = candidates[0].id;
                }
            } catch (e) {
                console.error("Gagal cari atasan", e);
            } finally {
                this.isFetchingAtasan = false;
            }
        },

        // --- CRUD ACTIONS ---
        toggleAdd(val) {
            this.openAdd = val;
            if (val) {
                // Reset Form for Add
                this.formData = {
                    name: "", nip: "", unit_kerja_id: "", bidang_id: "", jabatan_id: "", atasan_id: "",
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
            
            // Isi data awal
            this.formData = {
                name: item.name,
                nip: item.nip,
                unit_kerja_id: item.unit_kerja_id,
                bidang_id: item.bidang_id,
                jabatan_id: item.jabatan_id,
                atasan_id: item.atasan_id,
            };

            // Trigger load data relasi (Bidang & Atasan)
            // Kita pakai await satu per satu untuk memastikan urutan data benar
            if (item.unit_kerja_id) {
                await this.onUnitKerjaChange();
                // Set ulang bidang_id karena onUnitKerjaChange meresetnya (biasanya)
                // Tapi di sini onUnitKerjaChange hanya reset list, formData aman.
            }
            if (item.jabatan_id) {
                await this.fetchCalonAtasan();
            }

            this.openEdit = true;
        },

        async submitForm(type) {
            const isEdit = type === "edit";
            const url = isEdit ? `${BASE_URL}/${this.editId}` : BASE_URL;
            // Gunakan POST untuk semua karena Laravel kadang rewel dengan PUT form-data, 
            // tapi karena kita kirim JSON, PUT native fetch aman. 
            // Namun untuk konsistensi dengan pola Laravel Resource Controller:
            const method = isEdit ? "PUT" : "POST";

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        Authorization: `Bearer ${getToken()}`,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify(this.formData),
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        const errors = Object.values(result.errors).flat().join("\n");
                        throw new Error(errors || "Validasi gagal");
                    }
                    throw new Error(result.message || "Terjadi kesalahan");
                }

                Swal.fire("Berhasil", result.message, "success");
                
                if (isEdit) this.toggleEdit(false);
                else this.toggleAdd(false);

                this.fetchData(this.pagination.current_page); // Refresh halaman saat ini

            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        },

        async deleteItem(id) {
            const confirm = await Swal.fire({
                title: "Hapus Pegawai?",
                text: "Akun dan data pegawai ini akan dihapus permanen.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
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
                            "X-Requested-With": "XMLHttpRequest",
                            // CSRF Token header jika diperlukan (biasanya sanctum handle via cookie, tapi good practice)
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content
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

        // --- UPLOAD EXCEL ---
        toggleUpload(val) {
            this.openUpload = val;
            if (val) this.fileUpload = null;
        },

        async submitImport() {
            if (!this.fileUpload) {
                Swal.fire("Peringatan", "Pilih file Excel dulu.", "warning");
                return;
            }

            this.isImporting = true;
            const formData = new FormData();
            formData.append('csv_file', this.fileUpload);

            try {
                const response = await fetch(IMPORT_URL, {
                    method: 'POST',
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: formData // Browser otomatis set Content-Type multipart/form-data
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        // Handle validasi import
                        const errors = result.errors ? Object.values(result.errors).flat().join('\n') : result.message;
                        throw new Error(errors);
                    }
                    throw new Error(result.message || "Import gagal");
                }

                // Handle Partial Success (jika backend support return list error baris)
                if (result.errors && result.errors.length > 0) {
                    const errorMsg = result.errors.join('<br>');
                    Swal.fire({
                        title: "Import Sebagian",
                        html: `Berhasil import sebagian data. Error pada:<br><div class="text-left text-sm mt-2 max-h-40 overflow-y-auto">${errorMsg}</div>`,
                        icon: "warning"
                    });
                } else {
                    Swal.fire("Import Berhasil", result.message, "success");
                }

                this.toggleUpload(false);
                this.fetchData(1); // Refresh ke halaman 1

            } catch (error) {
                Swal.fire("Gagal Import", error.message, "error");
            } finally {
                this.isImporting = false;
            }
        }
    };
}