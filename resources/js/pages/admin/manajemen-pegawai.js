import Swal from "sweetalert2";

export function manajemenPegawaiData() {
    const BASE_URL = "/e-daily-report/api/admin/pegawai";
    const MASTER_URL = "/e-daily-report/api/admin/master-dropdown";
    const getToken = () => localStorage.getItem("auth_token");

    return {
        items: [], isLoading: false, search: '',
        pagination: { current_page: 1, last_page: 1, next_page_url: null, prev_page_url: null },
        
        openAdd: false, openEdit: false, openUpload: false,
        editId: null,
        
        // Master Data Holders
        unitKerjaList: [], bidangList: [], jabatanList: [], roleList: [], atasanList: [],

        formData: {
            name: '', username: '', nip: '', password: '',
            unit_kerja_id: '', bidang_id: '', jabatan_id: '', role_id: '', atasan_id: ''
        },
        isFetchingAtasan: false,

        async initPage() {
            console.log("🚀 Manajemen Pegawai: Init...");
            await this.fetchMasterData();
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
            } catch (e) { console.error("Gagal master data", e); }
        },

        async onUnitKerjaChange() {
            const unitId = this.formData.unit_kerja_id;
            this.bidangList = [];
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
            } catch (e) { console.error(e); } 
            finally { this.isFetchingAtasan = false; }
        },

        async fetchData(url = BASE_URL) {
            this.isLoading = true;
            let targetUrl = url;
            if (this.search) targetUrl += (targetUrl.includes('?') ? '&' : '?') + `search=${encodeURIComponent(this.search)}`;
            
            try {
                const res = await fetch(targetUrl, { headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" } });
                const json = await res.json();
                this.items = json.data || [];
                this.pagination = { ...json }; // Simplified
            } catch (e) { console.error(e); } 
            finally { this.isLoading = false; }
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
                const json = await res.json();
                
                if (!res.ok) throw new Error(json.status === 422 ? Object.values(json.errors).flat().join('\n') : json.message);
                
                Swal.fire("Berhasil", json.message, "success");
                this.toggleAdd(false); this.toggleEdit(false);
                this.fetchData();
            } catch (e) { Swal.fire("Gagal", e.message, "error"); }
        },

        async deleteItem(id) {
            const c = await Swal.fire({ title: 'Hapus?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Hapus' });
            if (!c.isConfirmed) return;
            
            try {
                const res = await fetch(`${BASE_URL}/${id}`, { method: "DELETE", headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" } });
                if (!res.ok) throw new Error("Gagal menghapus");
                Swal.fire('Terhapus!', '', 'success');
                this.fetchData();
            } catch (e) { Swal.fire("Error", e.message, "error"); }
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
        changePage(url) { if (url) this.fetchData(url); }
    };
}