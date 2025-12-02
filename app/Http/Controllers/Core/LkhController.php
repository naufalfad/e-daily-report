<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\LkhBukti;
use App\Models\Tupoksi;
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
     */
    public function getReferensi(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }

        $listTupoksi = [];
        if ($user->bidang_id) {
            $listTupoksi = Tupoksi::where('bidang_id', $user->bidang_id)
                ->select('id', 'uraian_tugas')
                ->get();
        }

        $jenisAktivitas = [
            'Rapat',
            'Pelayanan Publik',
            'Penyusunan Dokumen',
            'Kunjungan Lapangan',
            'Lainnya'
        ];

        return response()->json([
            'tupoksi' => $listTupoksi,
            'jenis_aktivitas' => $jenisAktivitas,
            'user_bidang_info' => $user->bidang ? $user->bidang->nama_bidang : 'User belum memiliki bidang'
        ]);
    }

    /**
     * 1. LIST LKH (Digunakan untuk Halaman Input LKH Staf)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = LaporanHarian::with(['tupoksi', 'skp', 'bukti'])
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
     * 2. CREATE LKH (BEST PRACTICE VERSION)
     */
    public function store(Request $request)
    {
        $validAktivitas = 'Rapat,Pelayanan Publik,Penyusunan Dokumen,Kunjungan Lapangan,Lainnya';
        $user = Auth::user();
        $status = $request->status;

        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }

        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'tupoksi_id' => 'required|exists:tupoksi,id',
            'jenis_kegiatan' => 'required|in:' . $validAktivitas,
            'skp_id' => 'nullable|exists:skp,id',
            'tanggal_laporan' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'deskripsi_aktivitas' => 'required|string',
            'output_hasil_kerja' => 'required|string',
            'volume' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'latitude' => 'nullable|numeric|required_without:master_kelurahan_id',
            'longitude' => 'nullable|numeric|required_without:master_kelurahan_id',
            'master_kelurahan_id' => 'nullable|exists:master_kelurahan,id|required_without:latitude',
            'bukti.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4|max:10240',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $uploadedFiles = [];

        try {
            // 2. Mulai Transaksi Database (Atomic Operation)
            DB::beginTransaction();

            $finalLat = $request->latitude;
            $finalLng = $request->longitude;
            $isLuarLokasi = true;

            if (config('services.office.lat') && config('services.office.lng') && $finalLat && $finalLng) {
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
                'skp_id' => $request->skp_id,
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

            // 4. Proses Upload Filen
            if ($request->hasFile('bukti')) {
                $folderDate = date('Y/m');
                $storagePath = "uploads/lkh/{$folderDate}";

                foreach ($request->file('bukti') as $file) {
                    $extension = strtolower($file->getClientOriginalExtension());
                    $filename = Str::uuid() . '.' . $extension;
                    $finalPath = "";

                    // A. Optimasi Gambar (JPG/PNG -> Resize & WebP)
                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        $filename = Str::uuid() . '.webp'; // Ubah ekstensi jadi webp
                        $finalPath = "{$storagePath}/{$filename}";

                        // Pastikan folder ada
                        if (!Storage::disk('public')->exists($storagePath)) {
                            Storage::disk('public')->makeDirectory($storagePath);
                        }

                        $image = Image::make($file)
                            ->resize(1280, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize(); // Jangan perbesar jika gambar asli kecil
                            })
                            ->encode('webp', 80);

                        // Simpan ke disk public
                        Storage::disk('public')->put($finalPath, (string) $image);
                    }
                    // B. File Dokumen/Video (Simpan Langsung)
                    else {
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

            // 5. Kirim Notifikasi (DI DALAM TRANSAKSI)
            // Jika ini error, maka create LKH di atas ikut ter-rollback otomatis
            if ($user->atasan_id) {
                // Formatting tanggal agar lebih humanis
                $tglIndo = Carbon::parse($request->tanggal_laporan)->format('d/m/Y');

                try {
                    NotificationService::send(
                        $user->atasan_id,
                        NotificationType::LKH_NEW_SUBMISSION->value,
                        "Pegawai {$user->name} mengajukan LKH baru kegiatan '{$request->jenis_kegiatan}' untuk tanggal {$tglIndo}.",
                        $lkh // Object untuk Polymorphic Redirect)
                    );
                } catch (\Exception $e) {
                    \Log::error("Gagal kirim notif LKH: " . $e->getMessage());
                }
            }

            // 6. Commit Transaksi (Simpan Permanen)
            DB::commit();

            return response()->json([
                'message' => 'Laporan Harian berhasil dikirim',
                'is_luar_lokasi' => $isLuarLokasi,
                'data' => $lkh->load(['bukti', 'tupoksi'])
            ], 201);

        } catch (\Exception $e) {
            // 7. Rollback jika ada error apapun (DB atau Notif)
            DB::rollBack();
            foreach ($uploadedFiles as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
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

        if (!is_numeric($id)) {
            return response()->json(['message' => 'ID Laporan tidak valid.'], 400);
        }

        $lkh = LaporanHarian::with(['tupoksi', 'skp', 'bukti', 'user.bidang', 'user.jabatan', 'atasan'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id) // Laporan miliknya
                    ->orWhere('atasan_id', $user->id); // Laporan bawahan
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
        if (!$user) {
            return response()->json(['message' => 'User belum login'], 401);
        }

        $query = LaporanHarian::with([
            'tupoksi',
            'user:id,name',
            'atasan:id,name',
            'bukti'
        ]);

        $mode = $request->input('mode', 'mine');
        $isPenilai = $user->roles()->pluck('nama_role')->contains('Penilai');

        // Filter Mode
        if ($isPenilai && $mode === 'subordinates') {
            $query->where('atasan_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        // Filter Tanggal
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

        if ($lkh->status === 'approved') {
            return response()->json(['message' => 'Laporan yang sudah disetujui tidak bisa dihapus'], 403);
        }

        try {
            DB::beginTransaction();

            foreach ($lkh->bukti as $file) {
                if (Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }

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

        if (!$user) {
            return response()->json(['message' => 'User belum login / token invalid'], 401);
        }

        $lkh = LaporanHarian::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$lkh)
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);

        if ($lkh->status === 'approved') {
            return response()->json(['message' => 'Laporan yang sudah disetujui tidak bisa diedit'], 403);
        }

        // VALIDASI — semua optional kecuali yang wajib
        $validator = Validator::make($request->all(), [
            'tupoksi_id' => 'sometimes|required|exists:tupoksi,id',
            'jenis_kegiatan' => 'sometimes|required|in:' . $validAktivitas,
            'skp_id' => 'nullable|exists:skp,id',
            'tanggal_laporan' => 'sometimes|required|date',
            'waktu_mulai' => 'sometimes|required',
            'waktu_selesai' => 'sometimes|required|after:waktu_mulai',
            'deskripsi_aktivitas' => 'sometimes|required|string',
            'output_hasil_kerja' => 'sometimes|required|string',
            'volume' => 'sometimes|required|integer|min:1',
            'satuan' => 'sometimes|required|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'master_kelurahan_id' => 'nullable|exists:master_kelurahan,id',

            // upload optional
            'bukti.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',

            // jika ingin hapus bukti tertentu
            'hapus_bukti' => 'array',
            'hapus_bukti.*' => 'integer|exists:lkh_bukti,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Logika GIS (Hanya jalan jika ada input koordinat baru)
            $updateData = $request->only([
                'skp_id',
                'tupoksi_id',
                'jenis_kegiatan',
                'tanggal_laporan',
                'waktu_mulai',
                'waktu_selesai',
                'deskripsi_aktivitas',
                'output_hasil_kerja',
                'volume',
                'satuan',
                'status',
                'master_kelurahan_id'
            ]);

            // Jika user mengirim koordinat baru, hitung ulang lokasi & geofencing
            if ($request->has('latitude') && $request->has('longitude') && $request->latitude && $request->longitude) {
                $finalLat = $request->latitude;
                $finalLng = $request->longitude;
                $isLuarLokasi = true;

                $officeLat = config('services.office.lat');
                $officeLng = config('services.office.lng');

                // Cek Jarak
                if ($officeLat && $officeLng) {
                    $distanceQuery = DB::selectOne("
                        SELECT ST_DistanceSphere(
                            ST_Point(?, ?), ST_Point(?, ?)
                        ) as distance
                    ", [$finalLng, $finalLat, $officeLng, $officeLat]);

                    if ($distanceQuery && $distanceQuery->distance <= config('services.office.radius')) {
                        $isLuarLokasi = false;
                    }
                }

                // Masukkan data GIS ke array update
                $updateData['is_luar_lokasi'] = $isLuarLokasi;
                $updateData['lokasi'] = DB::raw("ST_GeomFromText('POINT({$finalLng} {$finalLat})', 4326)");
            }

            // Lakukan Update Data LKH
            $lkh->update($updateData);

            // 2. HAPUS BUKTI LAMA (Jika diminta)
            if ($request->filled('hapus_bukti')) {
                $buktiToDelete = LkhBukti::whereIn('id', $request->hapus_bukti)
                    ->where('laporan_id', $lkh->id)
                    ->get();

                foreach ($buktiToDelete as $bukti) {
                    // Hapus fisik
                    if (Storage::disk('public')->exists($bukti->file_path)) {
                        Storage::disk('public')->delete($bukti->file_path);
                    }
                    // Hapus DB
                    $bukti->delete();
                }
            }

            // 3. TAMBAH BUKTI BARU (Dengan Optimasi WebP)
            if ($request->hasFile('bukti')) {
                $folderDate = date('Y/m'); // Masukkan ke folder bulan berjalan
                $storagePath = "uploads/lkh/{$folderDate}";

                foreach ($request->file('bukti') as $file) {
                    $extension = strtolower($file->getClientOriginalExtension());
                    $filename = Str::uuid() . '.' . $extension;
                    $finalPath = "";

                    // A. Optimasi Gambar
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
                    }
                    // B. Dokumen/Video
                    else {
                        $finalPath = $file->storeAs($storagePath, $filename, 'public');
                    }

                    // Catat path untuk rollback
                    $uploadedFiles[] = $finalPath;

                    // Simpan DB
                    LkhBukti::create([
                        'laporan_id' => $lkh->id,
                        'file_path' => $finalPath,
                        'file_name_original' => $file->getClientOriginalName(),
                        'file_type' => $extension,
                        'file_size' => $file->getSize()
                    ]);
                }
            }

            DB::commit();

            // Notifikasi update
            if ($user->atasan_id && $request->status) {
                try {
                    NotificationService::send(
                        $user->atasan_id,
                        'lkh_update_submission', // Pastikan Enum/String sesuai logic Anda
                        "Pegawai {$user->name} memperbarui laporan: {$lkh->jenis_kegiatan}",
                        $lkh
                    );
                } catch (\Exception $n) {
                    // Ignore error notif
                }
            }

            return response()->json([
                'message' => 'Laporan Harian berhasil diperbarui',
                'data' => $lkh->load(['bukti']) // Load bukti terbaru
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            // CLEANUP: Hapus file baru yang terlanjur ke-upload jika DB error
            foreach ($uploadedFiles as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return response()->json([
                'message' => 'Gagal memperbarui laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf($id)
    {
        $lkh = LaporanHarian::with(['tupoksi', 'skp', 'user'])->findOrFail($id);

        $pdf = \PDF::loadView('pdf.lkh', [
            'pegawai_nama' => $lkh->user->name,
            'pegawai_nip' => $lkh->user->nip,
            'pegawai_unit' => optional($lkh->user->unit_kerja_id)->nama_unit ?? '-',

            'tanggal' => $lkh->tanggal_laporan,
            'jenis_kegiatan' => $lkh->jenis_kegiatan,
            'tupoksi' => optional($lkh->tupoksi_id)->uraian_tugas,
            'kategori' => $lkh->skp_id ? 'SKP' : 'Non-SKP',

            'jam_mulai' => $lkh->waktu_mulai,
            'jam_selesai' => $lkh->waktu_selesai,

            'lokasi' => $lkh->lokasi,
            'uraian_kegiatan' => $lkh->deskripsi_aktivitas,

            'output' => $lkh->output_hasil_kerja,
            'volume' => $lkh->volume,
            'satuan' => $lkh->satuan,

            'target_skp' => optional($lkh->skp)->rencana_aksi,
        ]);

        return $pdf->stream("LKH-{$id}.pdf");
    }

    public function exportPdfDirect(Request $request)
    {
        $user = auth()->user();

        $data = [
            'pegawai_nama' => $user->name,
            'pegawai_nip' => $user->nip,
            'pegawai_unit' => $user->unit_kerja_id->nama ?? '-',

            'tanggal' => $request->tanggal_laporan,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'tupoksi' => $request->tupoksi_id,
            'kategori' => $request->kategori,

            'jam_mulai' => $request->waktu_mulai,
            'jam_selesai' => $request->waktu_selesai,

            'lokasi' => $request->lokasi,
            'uraian_kegiatan' => $request->deskripsi_aktivitas,

            'output' => $request->output_hasil_kerja,
            'volume' => $request->volume,
            'satuan' => $request->satuan,

            // khusus SKP
            'target_skp' => $request->kategori === 'SKP'
                ? $request->target_skp
                : null,

            // placeholder bukti
            'bukti_status' => "Bukti hanya tersedia setelah disimpan.",
        ];

        $pdf = Pdf::loadView('pdf.laporan-harian', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-harian.pdf');
    }

}