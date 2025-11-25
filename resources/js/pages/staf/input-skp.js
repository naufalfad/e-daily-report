// resources/js/pages/staf/skp.js

// AlpineJS global (jika dipakai via CDN tetap aman)
window.skpPageData = function () {
    return {
        // Data
        skpList: [],
        atasanName: 'Memuat...',
        isLoading: false,

        formData: {
            nama_skp: '',
            periode_mulai: '',
            periode_selesai: '',
            indikator: '',
            rencana_aksi: '',
            target: ''
        },

        openDetail: false,
        openEdit: false,
        detailData: null,
        editData: null,

        // Init halaman
        initPage() {
            if (!localStorage.getItem('auth_token')) {
                window.location.href = '/login';
                return;
            }
            this.fetchProfile();
            this.fetchSkpList();
            this.initDatePickers();
        },

        // ============================================================
        // FETCH PROFILE
        // ============================================================
        async fetchProfile() {
            const token = localStorage.getItem('auth_token');

            try {
                const res = await fetch('/api/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) throw new Error('Gagal memuat profil');

                const json = await res.json();
                this.atasanName = json.atasan ? json.atasan.name : '- Tidak Ada Atasan -';

            } catch (e) {
                console.error(e);
                this.atasanName = 'Gagal memuat';
            }
        },

        // ============================================================
        // FETCH LIST SKP
        // ============================================================
        async fetchSkpList() {
            const token = localStorage.getItem('auth_token');

            try {
                const res = await fetch('/api/skp', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) throw new Error('Gagal memuat SKP');

                const json = await res.json();
                this.skpList = json.data || [];

            } catch (e) {
                console.error(e);
                this.skpList = [];
            }
        },

        // ============================================================
        // CREATE SKP
        // ============================================================
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
                alert('Terjadi kesalahan koneksi');
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

        // ============================================================
        // DETAIL MODAL
        // ============================================================
        openDetailModal(skp) {
            this.detailData = skp;
            this.openDetail = true;
        },

        // ============================================================
        // EDIT MODAL
        // ============================================================
        openEditModal() {
            this.editData = JSON.parse(JSON.stringify(this.detailData));

            if (this.editData.periode_mulai)
                this.editData.periode_mulai = this.editData.periode_mulai.substring(0, 10);

            if (this.editData.periode_selesai)
                this.editData.periode_selesai = this.editData.periode_selesai.substring(0, 10);

            this.openDetail = false;
            this.openEdit = true;
        },

        // ============================================================
        // UPDATE SKP
        // ============================================================
        async submitEdit() {
            this.isLoading = true;
            const token = localStorage.getItem('auth_token');

            try {
                const payload = {
                    nama_skp: this.editData.nama_skp,
                    periode_mulai: this.editData.periode_mulai,
                    periode_selesai: this.editData.periode_selesai,
                    indikator: this.editData.indikator,
                    rencana_aksi: this.editData.rencana_aksi,
                    target: this.editData.target
                };

                const res = await fetch(`/api/skp/${this.editData.id}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                if (res.ok) {
                    alert('Perubahan Disimpan!');
                    this.openEdit = false;
                    this.fetchSkpList();
                } else {
                    alert('Gagal Update: ' + (json.message || 'Error'));
                }

            } catch (e) {
                alert('Kesalahan koneksi server');
            }

            this.isLoading = false;
        },

        // ============================================================
        // HELPERS UI
        // ============================================================
        formatDate(dateString) {
            if (!dateString) return '-';
            try {
                return new Date(dateString).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            } catch (e) {
                return dateString;
            }
        },

        initDatePickers() {
            this.$nextTick(() => {
                const initDatePicker = (inputId, buttonId) => {
                    const input = document.getElementById(inputId);
                    const button = document.getElementById(buttonId);
                    if (input && button) {
                        button.addEventListener('click', () => {
                            try { input.showPicker(); }
                            catch { input.focus(); }
                        });
                    }
                };
                initDatePicker('periode_awal', 'periode_awal_btn');
                initDatePicker('periode_akhir', 'periode_akhir_btn');
            });
        },

        initSelectPlaceholders() {}
    };
};
