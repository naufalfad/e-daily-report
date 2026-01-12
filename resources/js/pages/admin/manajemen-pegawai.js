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
        atasanList: [], // List dinamis berdasarkan bidang/jabatan yang dipilih

        // Form Data (Model)
        form: {
            name: "",
            nip: "",
            email: "",
            username: "",
            password: "",
            unit_kerja_id: "",
            bidang_id: "",
            jabatan_id: "",
            role: "",
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

            // 1. Load Data Master Basic (Dropdown Unit & Jabatan)
            await this.fetchMasterData();

            // 2. Setup Listeners untuk Filter & Pagination
            this.setupEventListeners();

            // 3. Load Data Table Pegawai
            await this.fetchData();

            // 4. Watchers: Deteksi perubahan dropdown untuk load data bertingkat
            // Saat Unit Kerja berubah -> Load Bidang
            this.$watch("form.unit_kerja_id", (val) => {
                if (val && (this.openAdd || this.openEdit)) {
                    this.onUnitKerjaChange(val);
                }
            });

            // Saat Bidang atau Jabatan berubah -> Cari Atasan yang relevan
            this.$watch("form.bidang_id", (val) => {
                if (val && (this.openAdd || this.openEdit)) this.fetchCalonAtasan();
            });
            
            this.$watch("form.jabatan_id", (val) => {
                if (val && (this.openAdd || this.openEdit)) this.fetchCalonAtasan();
            });
        },

        setupEventListeners() {
            // Filter Unit Kerja di Toolbar
            const filterUnit = document.getElementById('filterUnitKerja');
            if (filterUnit) {
                filterUnit.addEventListener('change', (e) => {
                    this.filterUnitKerja = e.target.value;
                    this.fetchData(1); // Reset ke halaman 1 saat filter berubah
                });
            }

            // Pagination Buttons
            const btnPrev = document.getElementById('prev-page');
            const btnNext = document.getElementById('next-page');
            
            if (btnPrev) btnPrev.addEventListener('click', () => { 
                if (this.pagination.prev_page_url) this.fetchData(this.pagination.current_page - 1); 
            });
            
            if (btnNext) btnNext.addEventListener('click', () => { 
                if (this.pagination.next_page_url) this.fetchData(this.pagination.current_page + 1); 
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
                    per_page: 10,
                    t: new Date().getTime() // Anti-cache parameter
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
                this.items = json.data || [];
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
            this.renderPaginationDOM();
        },

        renderPaginationDOM() {
            const infoEl = document.getElementById('pagination-info');
            const numbersEl = document.getElementById('pagination-numbers');
            const btnPrev = document.getElementById('prev-page');
            const btnNext = document.getElementById('next-page');

            if (infoEl) infoEl.textContent = `Menampilkan ${this.pagination.from || 0}-${this.pagination.to || 0} dari ${this.pagination.total || 0} data`;

            if (btnPrev) {
                btnPrev.disabled = !this.pagination.prev_page_url;
                btnPrev.classList.toggle('opacity-50', !this.pagination.prev_page_url);
            }
            if (btnNext) {
                btnNext.disabled = !this.pagination.next_page_url;
                btnNext.classList.toggle('opacity-50', !this.pagination.next_page_url);
            }

            if (numbersEl) {
                numbersEl.innerHTML = '';
                const current = this.pagination.current_page;
                const last = this.pagination.last_page;
                
                const createBtn = (p, active) => {
                    const btn = document.createElement('button');
                    btn.className = `w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-all ${active ? 'bg-[#1C7C54] text-white shadow-sm' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}`;
                    btn.textContent = p;
                    if (!active) btn.onclick = () => this.fetchData(p);
                    return btn;
                };

                // Logic Simple Pagination (First, Prev, Current, Next, Last)
                if (last <= 5) {
                    for (let i = 1; i <= last; i++) numbersEl.appendChild(createBtn(i, i === current));
                } else {
                    numbersEl.appendChild(createBtn(1, 1 === current));
                    if (current > 3) numbersEl.appendChild(document.createTextNode('...'));
                    
                    let start = Math.max(2, current - 1);
                    let end = Math.min(last - 1, current + 1);
                    
                    for (let i = start; i <= end; i++) numbersEl.appendChild(createBtn(i, i === current));
                    
                    if (current < last - 2) numbersEl.appendChild(document.createTextNode('...'));
                    numbersEl.appendChild(createBtn(last, last === current));
                }
            }
        },

        // --- MASTER DATA LOGIC ---
        async fetchMasterData() {
            try {
                const headers = { Authorization: `Bearer ${getToken()}`, Accept: "application/json" };
                // Load Unit Kerja dan Jabatan di awal
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

        async onUnitKerjaChange(unitId) {
            // Reset bidang list saat unit kerja berubah
            this.bidangList = []; 
            if (!unitId) return;

            try {
                const res = await fetch(`${MASTER_URL}/bidang-by-unit-kerja/${unitId}`, {
                    headers: { Authorization: `Bearer ${getToken()}`, Accept: "application/json" }
                });
                // Backend harus return struktur hierarki (parent with children) atau flat list
                // Karena di view kita pakai @foreach (server side), data ini sebenarnya untuk re-population via JS jika needed,
                // tapi view Blade modal-pegawai.blade.php sebenarnya merender $bidang dari Controller.
                // Jika ingin dropdown dinamis via JS (Client Side), pastikan endpoint ini mereturn JSON yang tepat.
                this.bidangList = await res.json();
            } catch (e) { console.error("Gagal load bidang", e); }
        },

        async fetchCalonAtasan() {
            const { unit_kerja_id, bidang_id, jabatan_id } = this.form;
            this.atasanList = [];
            
            if (!unit_kerja_id || !jabatan_id || !bidang_id) return;

            this.isFetchingAtasan = true;
            try {
                const params = new URLSearchParams({ 
                    unit_kerja_id, 
                    bidang_id, 
                    jabatan_id 
                });
                
                // Endpoint ini harus pintar:
                // Jika user di Sub-Bidang, cari user dengan jabatan Pimpinan di Parent Bidang-nya.
                const res = await fetch(`${MASTER_URL}/calon-atasan?${params}`, {
                    headers: { Authorization: `Bearer ${getToken()}`, Accept: "application/json" }
                });
                
                const candidates = await res.json();
                this.atasanList = candidates;

                // UX: Auto select jika cuma ada 1 kandidat atasan (misal cuma ada 1 Kabid)
                // Hanya lakukan jika field atasan masih kosong
                if (candidates.length === 1 && !this.form.atasan_id) {
                    this.form.atasan_id = candidates[0].id;
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
                // Reset Form Clean
                this.form = {
                    name: "", nip: "", email: "", username: "", password: "",
                    unit_kerja_id: "", bidang_id: "", jabatan_id: "", role: "", atasan_id: "",
                };
                this.atasanList = [];
            }
        },

        toggleEdit(val) {
            this.openEdit = val;
            this.editId = null;
        },

        // [CRITICAL] Handling Edit Data dengan Dropdown Bertingkat
        async openModalEdit(item) {
            this.editId = item.id;
            
            // 1. Copy data item ke form
            // Kita gunakan spread operator untuk copy value
            this.form = {
                name: item.name,
                nip: item.nip,
                email: item.email,
                username: item.username,
                password: "", // Kosongkan password saat edit
                unit_kerja_id: item.unit_kerja_id,
                bidang_id: item.bidang_id,
                jabatan_id: item.jabatan_id,
                role: item.roles?.[0]?.name || "", // Ambil role pertama
                atasan_id: item.atasan_id,
            };

            // 2. Load Data Relasi (Async) secara berurutan
            // Penting: Kita harus load list Bidang dulu sebelum set selected value-nya
            // Meskipun di Blade view sudah ada data awal, tapi kalau user ganti Unit Kerja, list harus refresh.
            // Untuk Edit, kita trigger manual fetch agar list terisi jika menggunakan mekanisme JS.
            
            // Note: Karena view Blade modal-pegawai.blade.php me-render opsi <select> via PHP (@foreach),
            // sebenarnya kita tidak perlu fetch list bidang via JS kecuali kita merender ulang opsi tersebut pakai Alpine (x-for).
            // Namun, fetchCalonAtasan WAJIB dipanggil untuk mengisi dropdown Atasan yang kosong.

            if (item.unit_kerja_id && item.bidang_id && item.jabatan_id) {
                await this.fetchCalonAtasan();
            }
            
            // Restore atasan_id setelah fetch selesai (karena fetch me-reset list)
            this.form.atasan_id = item.atasan_id;

            this.openEdit = true;
        },

        async submitForm() {
            const isEdit = this.openEdit;
            const url = isEdit ? `${BASE_URL}/${this.editId}` : BASE_URL;
            const method = isEdit ? "PUT" : "POST"; // Laravel Resource support PUT

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
                    throw new Error(result.message || "Terjadi kesalahan");
                }

                Swal.fire("Berhasil", result.message, "success");
                
                if (isEdit) this.toggleEdit(false);
                else this.toggleAdd(false);

                this.fetchData(this.pagination.current_page); // Refresh data

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
                            "X-Requested-With": "XMLHttpRequest",
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

        // --- IMPORT DATA ---
        async submitImport() {
            if (!this.fileUpload) {
                Swal.fire("Peringatan", "Pilih file CSV/Excel dulu.", "warning");
                return;
            }

            this.isImporting = true;
            const form = new form();
            form.append('csv_file', this.fileUpload);

            try {
                const response = await fetch(IMPORT_URL, {
                    method: 'POST',
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: form
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        const errors = result.errors ? Object.values(result.errors).flat().join('\n') : result.message;
                        throw new Error(errors);
                    }
                    throw new Error(result.message || "Import gagal");
                }

                if (result.errors && result.errors.length > 0) {
                    const errorMsg = result.errors.join('<br>');
                    Swal.fire({
                        title: "Import Sebagian",
                        html: `Berhasil import, namun ada error:<br><div class="text-left text-xs mt-2 p-2 bg-red-50 text-red-600 rounded max-h-40 overflow-y-auto">${errorMsg}</div>`,
                        icon: "warning"
                    });
                } else {
                    Swal.fire("Import Berhasil", result.message, "success");
                }

                this.toggleUpload(false);
                this.fetchData(1);

            } catch (error) {
                Swal.fire("Gagal Import", error.message, "error");
            } finally {
                this.isImporting = false;
            }
        }
    };
}