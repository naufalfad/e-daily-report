// ===============================
//  SKP PAGE (PENILAI)
// ===============================

// Ganti 'export function' menjadi penugasan ke 'window'
// Agar bisa dipanggil oleh x-data di file Blade
window.skpPageData = function () {
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
            console.log("Init Penilai SKP Page");
            this.fetchProfile();
            this.fetchSkpList();
            // initDatePickers dihapus karena HTML menggunakan native input date tanpa ID khusus
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

            // Validasi sederhana sebelum kirim
            if (!this.formData.nama_skp || !this.formData.target) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Pastikan semua kolom wajib telah terisi.'
                });
                this.isLoading = false;
                return;
            }

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
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'SKP berhasil ditambahkan!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    this.resetForm();
                    this.fetchSkpList();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: json.message || 'Terjadi kesalahan validasi.'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                });
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
            // Deep copy object agar tidak merubah tampilan tabel realtime sebelum save
            this.editData = JSON.parse(JSON.stringify(this.detailData));

            // Format tanggal agar sesuai input type="date" (YYYY-MM-DD)
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Disimpan',
                        text: 'Perubahan berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    this.openEdit = false;
                    this.fetchSkpList();
                } else {
                    const json = await res.json();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Update',
                        text: json.message || 'Error Validasi'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                });
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
        }
    };
};

