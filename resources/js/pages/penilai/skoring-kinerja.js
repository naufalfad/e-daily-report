import { showToast } from "../../global/notification";

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('table-body');
    const searchInput = document.getElementById('search-input');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    // Statistik Elements
    const statTotal = document.getElementById("stat-total");
    const statAvg = document.getElementById("stat-avg");
    const statSangatBaik = document.getElementById("stat-sb");
    const statPembinaan = document.getElementById("stat-pembinaan");

    let subordinateData = [];

    // --- 1. FETCH DATA ---
    async function fetchData() {
        try {
            loadingState.classList.remove('hidden');
            tableBody.innerHTML = '';
            emptyState.classList.add('hidden');

            // [PERBAIKAN UTAMA] Gunakan URL API (/api/...), bukan URL Web (/penilai/...)
            const url = `/api/skoring-kinerja?t=${new Date().getTime()}`;
            
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            });

            console.log(
                "[3] Status Response:",
                response.status,
                response.statusText
            );

            // A. BACA RAW TEXT DULU (Untuk Cek Isi Asli)
            const rawText = await response.text();
            console.log("[4] RAW RESPONSE BODY:", rawText);

            // B. COBA PARSE KE JSON
            let result;
            try {
                result = JSON.parse(rawText);
                console.log(
                    "%c[5] JSON SUKSES DIPARSE:",
                    "color: green; font-weight: bold;",
                    result
                );
            } catch (e) {
                console.error(
                    "%c[FATAL] Gagal Parse JSON. Server mungkin mengirim HTML/Error!",
                    "color: red; font-weight: bold;",
                    e
                );
                showToast("Error: Respons server bukan JSON valid", "error");
                return; // Stop jika bukan JSON
            }

            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

            // C. CEK STRUKTUR DATA
            if (result.kinerja_bawahan) {
                console.log(
                    "%c[6] DATA BAWAHAN DITEMUKAN!",
                    "color: green;",
                    result.kinerja_bawahan
                );
                subordinateData = result.kinerja_bawahan;
            } else {
                console.warn(
                    "%c[WARNING] Key 'kinerja_bawahan' TIDAK ADA. Struktur JSON mungkin salah.",
                    "color: orange;",
                    result
                );
                // Fallback: Coba cari array di root atau 'data'
                subordinateData = Array.isArray(result.data) ? result.data : [];
            }

            calculateStats(subordinateData);
            renderTable(subordinateData);
        } catch (error) {
            console.error("%c[ERROR UTAMA]:", "color: red;", error);
            emptyState.classList.remove("hidden");
        } finally {
            loadingState.classList.add("hidden");
            console.log("%c[7] SELESAI.", "color: blue;");
        }
    }

    // --- 2. RENDER TABEL ---
    function renderTable(data) {
        console.log("[8] Merender Tabel dengan jumlah data:", data.length);
        tableBody.innerHTML = "";

        if (!data || data.length === 0) {
            emptyState.classList.remove("hidden");
            return;
        }
        emptyState.classList.add("hidden");

        data.forEach((pegawai) => {
            const badgeColor = getBadgeColor(pegawai.predikat);
            const avatar = pegawai.avatar_url || '/assets/icon/avatar.png';

            const row = `
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition duration-150">
                    <td class="py-4 px-6 text-left whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="mr-3">
                                <img class="w-10 h-10 rounded-full border border-slate-200 object-cover bg-slate-100" 
                                     src="${avatar}" 
                                     onerror="this.src='/assets/icon/avatar.png'"/>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">${
                                    pegawai.name || "Tanpa Nama"
                                }</div>
                                <div class="text-xs text-gray-500 mt-0.5">${
                                    pegawai.jabatan || "-"
                                }</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6 text-left">
                        <span class="bg-gray-100 text-gray-600 py-1 px-3 rounded-full text-xs font-medium">
                            ${pegawai.unit_kerja || "-"}
                        </span>
                    </td>
                    <td class="py-3 px-6 text-center text-gray-600">
                        <span class="font-bold text-green-600">${
                            pegawai.approved_lkh ?? 0
                        }</span> 
                        <span class="text-gray-400 mx-1">/</span> 
                        ${pegawai.total_lkh ?? 0}
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full bg-slate-100 rounded-full h-2 w-24 overflow-hidden">
                                <div class="bg-[#1C7C54] h-2 rounded-full transition-all duration-500" 
                                     style="width: ${Math.min(pegawai.capaian, 100)}%"></div>
                            </div>
                            <span class="text-xs font-bold text-[#1C7C54]">${pegawai.capaian}%</span>
                        </div>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <span class="${badgeColor} py-1 px-3 rounded-full text-xs font-bold shadow-sm inline-block min-w-[80px]">
                            ${pegawai.predikat || "-"}
                        </span>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML("beforeend", row);
        });
    }

    // --- 3. SEARCH ---
    searchInput.addEventListener("input", (e) => {
        const keyword = e.target.value.toLowerCase();
        console.log("Mencari:", keyword);
        const filtered = subordinateData.filter((p) => {
            return (
                (p.name && p.name.toLowerCase().includes(keyword)) ||
                (p.unit_kerja && p.unit_kerja.toLowerCase().includes(keyword))
            );
        });
        renderTable(filtered);
    });

    // --- 4. STATS ---
    function calculateStats(data) {
        if (statTotal) statTotal.innerText = data.length;

        if (statAvg) {
            if (data.length > 0) {
                const sum = data.reduce(
                    (acc, curr) => acc + parseFloat(curr.total_nilai || 0),
                    0
                );
                statAvg.innerText = (sum / data.length).toFixed(1) + "%";
            } else {
                statAvg.innerText = "0%";
            }
        }

        if (statSangatBaik)
            statSangatBaik.innerText = data.filter(
                (p) => p.predikat === "Sangat Baik"
            ).length;
        if (statPembinaan)
            statPembinaan.innerText = data.filter((p) =>
                ["Kurang", "Sangat Kurang"].includes(p.predikat)
            ).length;
    }

    function getBadgeColor(predikat) {
        switch (predikat) {
            case 'Sangat Baik': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            case 'Baik': return 'bg-blue-50 text-blue-700 border-blue-200';
            case 'Cukup': return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            default: return 'bg-red-50 text-red-700 border-red-200';
        }
    }

    // Jalankan
    fetchData();

    // ============================================================
    // 5. EXPORT PDF
    // ============================================================

    const exportBtn = document.getElementById("export-pdf");
    if (exportBtn) {
        exportBtn.addEventListener("click", () => {
            Swal.fire({
                title: "Export Laporan?",
                text: "PDF akan dibuat berdasarkan data skoring kinerja pegawai Anda.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Export",
                cancelButtonText: "Batal",
                confirmButtonColor: "#4F46E5",
            }).then((result) => {
                if (result.isConfirmed) {
                    // buka file PDF di tab baru
                    window.open("/penilai/skoring/export-pdf", "_blank");

                    showToast("Laporan PDF sedang dimuat...", "success");
                }
            });
        });
    }
});
