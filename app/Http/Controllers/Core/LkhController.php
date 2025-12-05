<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\LkhBukti;
use App\Models\Tupoksi;
use App\Models\SkpRencana; // [BARU] Gunakan Model Parent SKP
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Services\NotificationService;
use App\Enums\NotificationType;
use Carbon\Carbon; // Tambahan untuk formatting tanggal di pesan
use Barryvdh\DomPDF\Facade\Pdf;

class LkhController extends Controller
{
    /**
     * Mengambil Data Referensi untuk Form Input
     * Update: Mengambil SKP dari tabel skp_rencana dan target kuantitasnya.
     */
    public function getReferensi(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }

        // 1. Ambil Tupoksi (Tetap sama)
        $listTupoksi = [];
        if ($user->bidang_id) {
            $listTupoksi = Tupoksi::where('bidang_id', $user->bidang_id)
                ->select('id', 'uraian_tugas')
                ->get();
        }

        // 2. [LOGIKA BARU] Ambil Rencana SKP + Target Kuantitas
        // Tujuannya: Agar saat user pilih RHK, satuan otomatis terisi 'Dokumen', 'Laporan', dll.
        $listSkp = SkpRencana::with([
            'targets' => function ($q) {
                $q->where('jenis_aspek', 'Kuantitas');
            }
        ])
            ->where('user_id', $user->id)
            // Opsional: Filter periode aktif
            // ->where('periode_akhir', '>=', now()) 
            ->latest()
            ->get()
            ->map(function ($item) {
                $qty = $item->targets->first(); // Ambil target kuantitas pertama
                return [
                    'id' => $item->id,
                    'rencana_hasil_kerja' => $item->rencana_hasil_kerja,
                    // Data helper untuk frontend otomatisasi
                    'satuan' => $qty ? $qty->satuan : '-',
                    'target_qty' => $qty ? $qty->target : 0
                ];
            });

        $jenisAktivitas = [
            'Rapat',
            'Pelayanan Publik',
            'Penyusunan Dokumen',
            'Kunjungan Lapangan',
            'Lainnya'
        ];

        return response()->json([
            'tupoksi' => $listTupoksi,
            'list_skp' => $listSkp, // [UPDATE] Mengirim list rencana SKP
            'jenis_aktivitas' => $jenisAktivitas,
            'user_bidang_info' => $user->bidang ? $user->bidang->nama_bidang : 'User belum memiliki bidang'
        ]);
    }

    /**
     * 1. LIST LKH
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // [UPDATE] load 'rencana' (relasi ke SkpRencana), bukan 'skp'
        $query = LaporanHarian::with(['tupoksi', 'rencana', 'bukti'])
            ->where('user_id', $userId);

        if ($request->has('tanggal')) {
            $query->whereDate('tanggal_laporan', $request->tanggal);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest('tanggal_laporan')->paginate(10);

        return response()->json($data);
    }

    /**
     * 2. CREATE LKH
     */
    public function store(Request $request)
    {
        $validAktivitas = 'Rapat,Pelayanan Publik,Penyusunan Dokumen,Kunjungan Lapangan,Lainnya';
        $user = Auth::user();

        // Default status
        $status = 'waiting_review';

        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }

        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'tupoksi_id' => 'nullable|exists:tupoksi,id', // Tupoksi opsional jika sudah ada SKP
            'jenis_kegiatan' => 'required|in:' . $validAktivitas,

            // [UPDATE] Validasi ke tabel skp_rencana
            'skp_rencana_id' => 'nullable|exists:skp_rencana,id',
            'kategori' => 'required|in:skp,non-skp',

            'tanggal_laporan' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'deskripsi_aktivitas' => 'required|string',
            'output_hasil_kerja' => 'required|string',
            'volume' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',

            // Geotagging
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'master_kelurahan_id' => 'nullable|exists:master_kelurahan,id',
            'bukti.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4|max:10240',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $uploadedFiles = [];

        try {
            DB::beginTransaction();

            // Logika Geofencing Sederhana
            $finalLat = $request->latitude;
            $finalLng = $request->longitude;
            $isLuarLokasi = true;

            // Cek jarak kantor jika koordinat ada
            if (config('services.office.lat') && $finalLat) {
                $distanceQuery = DB::selectOne("
                    SELECT ST_DistanceSphere(
                        ST_Point(?, ?), 
                        ST_Point(?, ?)  
                    ) as distance
                ", [$finalLng, $finalLat, config('services.office.lng'), config('services.office.lat')]);

                if ($distanceQuery && $distanceQuery->distance <= config('services.office.radius')) {
                    $isLuarLokasi = false;
                }
            }

            // 3. Simpan Data LKH Utama
            $lkh = LaporanHarian::create([
                'user_id' => $user->id,
                'skp_rencana_id' => $request->kategori === 'skp' ? $request->skp_rencana_id : null,

                'tupoksi_id' => $request->tupoksi_id,
                'jenis_kegiatan' => $request->jenis_kegiatan,
                'tanggal_laporan' => $request->tanggal_laporan,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'deskripsi_aktivitas' => $request->deskripsi_aktivitas,
                'output_hasil_kerja' => $request->output_hasil_kerja,
                'volume' => $request->volume,
                'satuan' => $request->satuan,
                'status' => $status,
                'master_kelurahan_id' => $request->master_kelurahan_id,
                'is_luar_lokasi' => $isLuarLokasi,
                'atasan_id' => $user->atasan_id,
                'lokasi' => ($finalLat && $finalLng) ? DB::raw("ST_SetSRID(ST_MakePoint({$finalLng}, {$finalLat}), 4326)") : null
            ]);

            // 4. Proses Upload Bukti
            if ($request->hasFile('bukti')) {
                $folderDate = date('Y/m');
                $storagePath = "uploads/lkh/{$folderDate}";

                foreach ($request->file('bukti') as $file) {
                    $extension = strtolower($file->getClientOriginalExtension());
                    $filename = Str::uuid() . '.' . $extension;
                    $finalPath = "";

                    // Optimasi Gambar
                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        $filename = Str::uuid() . '.webp';
                        $finalPath = "{$storagePath}/{$filename}";

                        if (!Storage::disk('public')->exists($storagePath)) {
                            Storage::disk('public')->makeDirectory($storagePath);
                        }

                        $image = Image::make($file)
                            ->resize(1280, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })
                            ->encode('webp', 80);

                        Storage::disk('public')->put($finalPath, (string) $image);
                    } else {
                        $finalPath = $file->storeAs($storagePath, $filename, 'public');
                    }

                    $uploadedFiles[] = $finalPath;

                    LkhBukti::create([
                        'laporan_id' => $lkh->id,
                        'file_path' => $finalPath,
                        'file_name_original' => $file->getClientOriginalName(),
                        'file_type' => $extension,
                        'file_size' => $file->getSize()
                    ]);
                }
            }

            // 5. Kirim Notifikasi
            if ($user->atasan_id) {
                $tglIndo = Carbon::parse($request->tanggal_laporan)->format('d/m/Y');
                try {
                    NotificationService::send(
                        $user->atasan_id,
                        NotificationType::LKH_NEW_SUBMISSION->value, // Pastikan Enum ini ada
                        "{$user->name} mengajukan LKH: {$request->jenis_kegiatan} ({$tglIndo})",
                        $lkh
                    );
                } catch (\Exception $e) {
                    // Silent fail notif
                    \Log::warning("Gagal kirim notif LKH: " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Laporan Harian berhasil dikirim',
                'data' => $lkh->load(['bukti'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Cleanup file jika DB gagal
            foreach ($uploadedFiles as $path) {
                Storage::disk('public')->delete($path);
            }
            return response()->json(['message' => 'Gagal mengirim laporan', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 3. SHOW DETAIL LKH
     */
    public function show($id)
    {
        $user = Auth::user();

        // [UPDATE] Relasi 'rencana'
        $lkh = LaporanHarian::with(['tupoksi', 'rencana', 'bukti', 'user.bidang', 'user.jabatan', 'atasan'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('atasan_id', $user->id);
            })
            ->find($id);

        if (!$lkh)
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);

        return response()->json(['data' => $lkh]);
    }

    /**
     * Mengambil Riwayat LKH
     */
    public function getRiwayat(Request $request)
    {
        $user = Auth::user();

        // [UPDATE] Relasi 'rencana' menggantikan 'skp'
        // Jika Anda ingin menampilkan nama rencana di tabel riwayat
        $query = LaporanHarian::with([
            'tupoksi',
            'rencana:id,rencana_hasil_kerja', // [BARU] Eager load rencana SKP
            'user:id,name',
            'atasan:id,name',
            'bukti'
        ]);

        $mode = $request->input('mode', 'mine');
        $isPenilai = $user->roles()->pluck('nama_role')->contains('Penilai');

        if ($isPenilai && $mode === 'subordinates') {
            $query->where('atasan_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('tanggal_laporan', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal_laporan', '<=', $request->to_date);
        }

        $data = $query->latest('tanggal_laporan')->paginate(15);

        return response()->json($data);
    }

    /**
     * 4. DELETE LKH
     */
    public function destroy($id)
    {
        $lkh = LaporanHarian::where('user_id', Auth::id())->find($id);

        if (!$lkh)
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);
        if ($lkh->status === 'approved')
            return response()->json(['message' => 'Laporan Approved tidak bisa dihapus'], 403);

        try {
            DB::beginTransaction();
            foreach ($lkh->bukti as $file) {
                Storage::disk('public')->delete($file->file_path);
                $file->delete();
            }
            $lkh->delete();
            DB::commit();

            return response()->json(['message' => 'Laporan berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus laporan',
                'error'
                => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5. UPDATE LKH
     */
    public function update(Request $request, $id)
    {
        $validAktivitas = 'Rapat,Pelayanan Publik,Penyusunan Dokumen,Kunjungan Lapangan,Lainnya';
        $user = Auth::user();

        $lkh = LaporanHarian::where('id', $id)->where('user_id', $user->id)->first();
        if (!$lkh)
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);
        if ($lkh->status === 'approved')
            return response()->json(['message' => 'Laporan Approved tidak bisa diedit'], 403);

        $validator = Validator::make($request->all(), [
            'tupoksi_id' => 'sometimes|nullable|exists:tupoksi,id',
            'jenis_kegiatan' => 'sometimes|required|in:' . $validAktivitas,
            'skp_rencana_id' => 'nullable|exists:skp_rencana,id', // [UPDATE]
            'tanggal_laporan' => 'sometimes|required|date',
            'waktu_mulai' => 'sometimes|required',
            'waktu_selesai' => 'sometimes|required|after:waktu_mulai',
            'deskripsi_aktivitas' => 'sometimes|required|string',
            'output_hasil_kerja' => 'sometimes|required|string',
            'volume' => 'sometimes|required|integer|min:1',
            'satuan' => 'sometimes|required|string|max:50',
            'hapus_bukti' => 'array',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        try {
            DB::beginTransaction();

            // Update Data
            $lkh->update($request->except(['bukti', 'hapus_bukti', 'latitude', 'longitude']));

            // Jika ada update lokasi (Geofencing ulang bisa ditambahkan di sini)

            // Hapus Bukti
            if ($request->filled('hapus_bukti')) {
                $buktiToDelete = LkhBukti::whereIn('id', $request->hapus_bukti)->where('laporan_id', $lkh->id)->get();
                foreach ($buktiToDelete as $bukti) {
                    Storage::disk('public')->delete($bukti->file_path);
                    $bukti->delete();
                }
            }

            // Tambah Bukti (Sama seperti store, disederhanakan)
            if ($request->hasFile('bukti')) {
                // ... (Logika upload sama seperti store) ...
                // Bisa di-extract jadi private function uploadBukti() agar DRY
            }

            DB::commit();
            return response()->json(['message' => 'Laporan berhasil diperbarui']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update', 'error' => $e->getMessage()], 500);
        }
    }

    public function exportPdf($id)
    {
        $lkh = LaporanHarian::with([
            'tupoksi',
            'rencana', // ← PERHATIKAN INI
            'user' => fn($q) => $q->with('unitKerja')
        ])->findOrFail($id);

        $pdf = \PDF::loadView('pdf.lkh', [
            'pegawai_nama' => $lkh->user->name,
            'pegawai_nip' => $lkh->user->nip,
            'pegawai_unit' => $lkh->user->unitKerja->nama_unit ?? '-',

            'tanggal' => $lkh->tanggal_laporan,
            'jenis_kegiatan' => $lkh->jenis_kegiatan,
            'tupoksi' => $lkh->tupoksi->uraian_tugas ?? '-',

            // FIX KATEGORI
            'kategori' => $lkh->skp_rencana_id ? 'SKP' : 'Non-SKP',

            // WAKTU
            'jam_mulai' => $lkh->waktu_mulai,
            'jam_selesai' => $lkh->waktu_selesai,

            // LOKASI
            'lokasi' => $lkh->lokasi
                ?? ($lkh->latitude && $lkh->longitude
                    ? "{$lkh->latitude}, {$lkh->longitude}"
                    : '-'),

            // OUTPUT
            'output' => $lkh->output_hasil_kerja,
            'volume' => $lkh->volume,
            'satuan' => $lkh->satuan,

            // **INI FIX PALING PENTING**
            'target_skp' => optional($lkh->rencana)->rencana_hasil_kerja ?? '-',
        ]);

        return $pdf->stream("LKH-{$id}.pdf");
    }

    public function exportPdfDirect(Request $request)
    {
        $user = auth()->user();

        // Ambil Tupoksi
        $tupoksi = Tupoksi::find($request->tupoksi_id);

        // Ambil SKP Rencana jika kategori SKP
        $rencana = null;
        $targetQty = null;
        $targetSatuan = null;

        if ($request->kategori === 'skp' && $request->skp_rencana_id) {

            $rencana = SkpRencana::with('targets')->find($request->skp_rencana_id);

            if ($rencana && $rencana->targets->count()) {
                $targetQty = $rencana->targets->first()->target;
                $targetSatuan = $rencana->targets->first()->satuan;
            }
        }

        $data = [
            'pegawai_nama' => $user->name,
            'pegawai_nip' => $user->nip,
            'pegawai_unit' => $user->unitKerja->nama_unit ?? '-',

            'tanggal' => $request->tanggal_laporan,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'tupoksi' => $tupoksi->uraian_tugas ?? '-',
            'kategori' => $request->kategori === 'skp' ? 'SKP' : 'Non-SKP',

            'jam_mulai' => $request->waktu_mulai,
            'jam_selesai' => $request->waktu_selesai,

            'lokasi' => $request->lokasi
                ?: ($request->latitude && $request->longitude
                    ? "{$request->latitude}, {$request->longitude}"
                    : '-'),

            // ✔ Uraian Kegiatan
            'uraian_kegiatan' => $request->deskripsi_aktivitas,

            // ✔ Output
            'output' => $request->output_hasil_kerja,
            'volume' => $request->volume,
            'satuan' => $request->satuan,

            // ✔ Target SKP (PERBAIKAN)
            'target_skp' => $rencana ? $rencana->rencana_hasil_kerja : null,
            'target_qty' => $targetQty,
            'target_satuan' => $targetSatuan,

            'bukti_status' => "Bukti hanya tersedia setelah disimpan.",
        ];

        return Pdf::loadView('pdf.laporan-harian', $data)
            ->setPaper('a4', 'portrait')
            ->stream('laporan-harian.pdf');
    }

}