import Swal from "sweetalert2";

export function systemSettingsData() {
    const API_URL = "/api/admin/settings";
    const getToken = () => localStorage.getItem("auth_token");

    return {
        // --- STATE ---
        activeTab: 'sistem',
        openResetModal: false,
        isLoading: false,

        // Data Utama Settings
        settings: {
            maintenance_mode: false, // [FIX] Default ke Boolean FALSE
            app_footer: '',
            timezone: '',
            session_timeout: '',
            login_limit: ''
        },

        menus: [
            { id: 'sistem', label: 'Pengaturan Sistem', title: 'Pengaturan Bawaan' },
            { id: 'role', label: 'Pengaturan Role dan Jabatan', title: 'Pengaturan Role dan Jabatan' },
            { id: 'keamanan', label: 'Pengaturan Keamanan', title: 'Pengaturan Keamanan' },
            { id: 'jam_kerja', label: 'Pengaturan Jam Kerja', title: 'Pengaturan Jam Kerja' },
            { id: 'reset', label: 'Reset Password', title: 'Reset Password' },
        ],
        
        async init() {
            console.log('System Settings: Initializing Alpine and fetching data...');
            await this.fetchSettings();
        },
        
        async fetchSettings() {
            this.isLoading = true;
            try {
                const res = await fetch(API_URL, {
                    headers: { "Authorization": `Bearer ${getToken()}`, "Accept": "application/json" }
                });
                
                if (res.ok) {
                    const data = await res.json();
                    
                    // Isi state settings dengan data yang dimuat
                    this.settings = { ...this.settings, ...data };
                    
                    // [PERBAIKAN UTAMA] Konversi String "1"/"0" ke Boolean true/false
                    // Agar toggle switch di UI menyala sesuai data DB
                    this.settings.maintenance_mode = (String(data.maintenance_mode) === '1');

                } else {
                    throw new Error("Gagal memuat pengaturan.");
                }
            } catch (e) {
                console.error("Error loading settings:", e);
                Swal.fire("Error", "Gagal memuat pengaturan sistem.", "error");
            } finally {
                this.isLoading = false;
            }
        },

        async submitGeneralSettings() {
            this.isLoading = true;

            // [PERBAIKAN UTAMA] Konversi Boolean true/false kembali ke String "1"/"0" untuk DB
            const payload = {
                'maintenance_mode': this.settings.maintenance_mode ? '1' : '0',
                'app_footer': this.settings.app_footer, 
                'timezone': this.settings.timezone,
            };

            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 
                        "Authorization": `Bearer ${getToken()}`, 
                        "Content-Type": "application/json",
                        "Accept": "application/json" 
                    },
                    body: JSON.stringify(payload)
                });
                
                const result = await res.json();

                if (!res.ok) throw new Error(result.message || "Gagal menyimpan perubahan.");
                
                Swal.fire("Berhasil", result.message, "success");
                
                // Refresh data untuk memastikan sinkronisasi
                await this.fetchSettings();
                
            } catch (e) {
                 Swal.fire("Gagal", e.message || "Terjadi kesalahan pada server.", "error");
            } finally {
                this.isLoading = false;
            }
        },
        
        toggleResetModal(show) {
            this.openResetModal = show;
        }
    }
}