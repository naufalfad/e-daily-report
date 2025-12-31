import Swal from "sweetalert2";

export function akunPenggunaData() {
    // [KONFIGURASI]
    const BASE_URL = "/api/admin/akun"; 
    
    // Helper untuk mengambil token dari localStorage
    const getToken = () => localStorage.getItem("auth_token");

    return {
        // --- STATE ---
        items: [],
        isLoading: false,
        search: '',

        // Data Master (Disupply dari Window Object di Blade untuk Role)
        roleList: window.Laravel?.roles || [],

        // Modal States
        openCred: false, // Modal Credential (Username & Password)
        openRole: false, // Modal Role
        
        // Data Holders
        targetId: null,
        targetName: '',
        
        // Form Data Model
        formData: {
            username: '',
            password: '',
            password_confirmation: '',
            role_id: ''
        },

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

        // --- INIT ---
        async initPage() {
            console.log("🚀 Akun Pengguna: Initializing...");
            
            // Setup Listeners Pagination (Manual DOM karena tombol di luar x-for)
            this.setupPaginationListeners();

            // Load Data Awal
            await this.fetchData();
        },

        setupPaginationListeners() {
            const btnPrev = document.getElementById('prev-page');
            const btnNext = document.getElementById('next-page');
            
            if (btnPrev) btnPrev.addEventListener('click', () => { 
                if (this.pagination.prev_page_url) this.fetchData(this.pagination.current_page - 1); 
            });
            
            if (btnNext) btnNext.addEventListener('click', () => { 
                if (this.pagination.next_page_url) this.fetchData(this.pagination.current_page + 1); 
            });
        },

        // --- 1. READ (FETCH DATA UTAMA) ---
        async fetchData(page = 1) {
            this.isLoading = true;

            try {
                // Build Query Parameters
                const params = new URLSearchParams({
                    page: page,
                    search: this.search,
                    per_page: 10,
                    t: new Date().getTime() // Anti-cache
                });

                const response = await fetch(`${BASE_URL}?${params.toString()}`, {
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest" // Wajib agar Controller return JSON
                    }
                });

                // Handle Unauthorized
                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                if (!response.ok) throw new Error("Gagal mengambil data akun");

                const json = await response.json();

                this.items = json.data || [];
                
                // Update Pagination State & DOM
                this.updatePaginationState(json);

            } catch (error) {
                console.error(error);
                Swal.fire("Error API", "Gagal memuat data akun. Cek koneksi server.", "error");
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

            if (infoEl) {
                infoEl.textContent = `Menampilkan ${this.pagination.from || 0}-${this.pagination.to || 0} dari ${this.pagination.total || 0} data`;
            }

            if (btnPrev) {
                btnPrev.disabled = !this.pagination.prev_page_url;
                btnPrev.classList.toggle('opacity-50', !this.pagination.prev_page_url);
            }
            if (btnNext) {
                btnNext.disabled = !this.pagination.next_page_url;
                btnNext.classList.toggle('opacity-50', !this.pagination.next_page_url);
            }

            // Sliding Window Logic
            if (numbersEl) {
                numbersEl.innerHTML = ''; 
                const current = this.pagination.current_page;
                const last = this.pagination.last_page;

                const createBtn = (p, active) => {
                    const btn = document.createElement('button');
                    btn.className = `w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all ${active ? 'bg-[#1C7C54] text-white shadow-md' : 'border border-slate-300 text-slate-600 hover:bg-slate-100'}`;
                    btn.textContent = p;
                    if (!active) btn.onclick = () => this.fetchData(p);
                    return btn;
                };

                const createDots = () => {
                    const span = document.createElement('span');
                    span.className = "px-1 text-slate-400 text-xs self-center";
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

        // --- 2. SECURITY ACTIONS ---
        
        // A. Update Username & Password
        async submitCredentialUpdate() {
            const url = `${BASE_URL}/${this.targetId}/credentials`;
            
            const payload = {
                username: this.formData.username,
                // Hanya kirim password jika diisi
                ...(this.formData.password && { 
                    password: this.formData.password,
                    password_confirmation: this.formData.password_confirmation 
                })
            };

            // Validasi Sederhana Client-Side
            if (this.formData.password && this.formData.password !== this.formData.password_confirmation) {
                Swal.fire("Validasi Gagal", "Konfirmasi password tidak cocok.", "warning");
                return;
            }

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
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
                this.fetchData(this.pagination.current_page);

            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        },

        // B. Update Role
        async submitRoleUpdate() {
            const url = `${BASE_URL}/${this.targetId}/role`;
            
            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        "Authorization": `Bearer ${getToken()}`,
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({ role_id: this.formData.role_id })
                });

                const result = await response.json();

                if (!response.ok) throw new Error(result.message || "Gagal mengubah role");

                Swal.fire("Berhasil", "Hak akses (Role) berhasil diperbarui.", "success");
                this.toggleRole(false);
                this.fetchData(this.pagination.current_page);

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
                text: `Anda yakin ingin ${actionText.toLowerCase()} akses login untuk ${item.name}?`,
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
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: JSON.stringify({ is_active: newStatus })
                    });

                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || "Gagal mengubah status");

                    Swal.fire('Berhasil!', `Akun berhasil ${newStatus ? 'diaktifkan' : 'dinonaktifkan'}.`, 'success');
                    this.fetchData(this.pagination.current_page); // Refresh halaman saat ini

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
            // Ambil role_id pertama (jika ada, karena user bisa punya multiple role di spatie, tapi kita ambil [0])
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