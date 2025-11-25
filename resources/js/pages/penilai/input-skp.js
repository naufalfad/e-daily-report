// ===============================
//  SKP PAGE — SCRIPT TERPISAH
// ===============================

export function skpPageData() {
    return {
        // Data
        skpList: [],
        atasanName: 'Memuat...',
        isLoading: false,

        // Form Create Model
        formData: {
            nama_skp: '',
            periode_mulai: '',
            periode_selesai: '',
            indikator: '',
            rencana_aksi: '',
            target: ''
        },

        // Modal State
        openDetail: false,
        openEdit: false,
        detailData: null,
        editData: null,

        // Init
        initPage() {
            if (!localStorage.getItem('auth_token')) {
                window.location.href = '/login';
                return;
            }
            this.fetchProfile();
            this.fetchSkpList();
            this.initDatePickers();
        },

        // Fetch Profile
        async fetchProfile() {
            const token = localStorage.getItem('auth_token');
            try {
                const res = await fetch('/api/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('Gagal fetch profile');
                const json = await res.json();
                this.atasanName = json.atasan ? json.atasan.name : '- Tidak Ada Atasan -';
            } catch (e) {
                console.error(e);
                this.atasanName = 'Gagal memuat';
            }
        },

        // Fetch List SKP
        async fetchSkpList() {
            const token = localStorage.getItem('auth_token');
            try {
                const res = await fetch('/api/skp', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('Gagal fetch list SKP');
                const json = await res.json();
                this.skpList = json.data || [];
            } catch (e) {
                console.error(e);
                this.skpList = [];
            }
        },

        // Submit Create
        async submitCreate() {
            this.isLoading = true;
            const token = localStorage.getItem('auth_token');

            try {
                const res = await fetch('/api/skp', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.formData)
                });

                const json = await res.json();
                if (res.ok) {
                    alert('SKP Berhasil Ditambahkan!');
                    this.resetForm();
                    this.fetchSkpList();
                } else {
                    alert('Gagal: ' + (json.message || JSON.stringify(json.errors)));
                }
            } catch (e) {
                alert('Terjadi kesalahan. Cek koneksi.');
            }

            this.isLoading = false;
        },

        resetForm() {
            this.formData = {
                nama_skp: '',
                periode_mulai: '',
                periode_selesai: '',
                indikator: '',
                rencana_aksi: '',
                target: ''
            };
        },

        // Modal Logic
        openDetailModal(skp) {
            this.detailData = skp;
            this.openDetail = true;
        },

        openEditModal() {
            this.editData = JSON.parse(JSON.stringify(this.detailData));

            if (this.editData.periode_mulai)
                this.editData.periode_mulai = this.editData.periode_mulai.substring(0, 10);

            if (this.editData.periode_selesai)
                this.editData.periode_selesai = this.editData.periode_selesai.substring(0, 10);

            this.openDetail = false;
            this.openEdit = true;
        },

        // Submit Edit
        async submitEdit() {
            this.isLoading = true;
            const token = localStorage.getItem('auth_token');

            const payload = {
                nama_skp: this.editData.nama_skp,
                periode_mulai: this.editData.periode_mulai,
                periode_selesai: this.editData.periode_selesai,
                indikator: this.editData.indikator,
                rencana_aksi: this.editData.rencana_aksi,
                target: this.editData.target
            };

            try {
                const res = await fetch(`/api/skp/${this.editData.id}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    alert('Perubahan Disimpan!');
                    this.openEdit = false;
                    this.fetchSkpList();
                } else {
                    const json = await res.json();
                    alert('Gagal Update: ' + (json.message || 'Error Validasi'));
                }
            } catch (e) {
                alert('Error koneksi server');
            }

            this.isLoading = false;
        },

        // Helper
        formatDate(dateString) {
            if (!dateString) return '-';
            try {
                return new Date(dateString).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            } catch {
                return dateString;
            }
        },

        initDatePickers() {
            this.$nextTick(() => {
                const init = (inputId, btnId) => {
                    const i = document.getElementById(inputId);
                    const b = document.getElementById(btnId);
                    if (i && b) {
                        b.addEventListener('click', () => {
                            try {
                                i.showPicker();
                            } catch {
                                i.focus();
                            }
                        });
                    }
                };
                init('periode_awal', 'periode_awal_btn');
                init('periode_akhir', 'periode_akhir_btn');
            });
        }
    };
}
