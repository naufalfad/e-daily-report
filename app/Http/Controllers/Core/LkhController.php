<?php

namespace App\Http\Controllers\Core;

use App\Actions\Lkh\SubmitLkhAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lkh\StoreLkhRequest;
use App\Models\LaporanHarian;
use App\Models\LkhBukti;
use App\Models\SkpRencana;
use App\Models\Tupoksi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Added: Import Rule untuk validasi Kategori
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

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

        $listSkp = SkpRencana::with([
            'targets' => function ($q) {
                $q->where('jenis_aspek', 'Kuantitas');
            }
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($item) {
                $qty = $item->targets->first();
                return [
                    'id' => $item->id,
                    'rencana_hasil_kerja' => $item->rencana_hasil_kerja,
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
            'list_skp' => $listSkp,
            'jenis_aktivitas' => $jenisAktivitas,
            'user_bidang_info' => $user->bidang ? $user->bidang->nama_bidang : 'User belum memiliki bidang'
        ]);
    }

    /**
     * 1. LIST LKH (Utama / Tabel Dashboard Staf)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = LaporanHarian::with(['tupoksi', 'rencana', 'bukti'])
            ->where('user_id', $userId);

        $query->when($request->month, function ($q, $month) {
            $q->whereMonth('tanggal_laporan', $month);
        });

        $query->when($request->year, function ($q, $year) {
            $q->whereYear('tanggal_laporan', $year);
        });

        $query->when(
            $request->filled('status') && $request->status !== 'all',
            fn($q) => $q->where('status', $request->status)
        );

        $data = $query->latest('tanggal_laporan')->paginate(10);

        return response()->json($data);
    }

    /**
     * 2. CREATE LKH
     */
    public function store(StoreLkhRequest $request, SubmitLkhAction $action)
    {
        try {
            $lkh = $action->execute($request, Auth::user());

            return response()->json([
                'message' => 'Laporan Harian berhasil dikirim',
                'data' => $lkh->load('bukti')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. SHOW DETAIL LKH
     */
    public function show($id)
    {
        $user = Auth::user();

        $lkh = LaporanHarian::with(['tupoksi', 'rencana', 'bukti', 'user.bidang', 'user.jabatan', 'atasan'])
            ->select('*')
            ->selectRaw('ST_Y(lokasi) AS latitude')
            ->selectRaw('ST_X(lokasi) AS longitude')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('atasan_id', $user->id);
            })
            ->find($id);

        if (!$lkh) {
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);
        }

        return response()->json(['data' => $lkh]);
    }

    /**
     * Mengambil Riwayat LKH (Khusus Halaman Riwayat)
     */
    public function getRiwayat(Request $request)
    {
        $user = Auth::user();

        $query = LaporanHarian::with([
            'tupoksi',
            'rencana:id,rencana_hasil_kerja',
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

        $query->when($request->month, function ($q, $month) {
            $q->whereMonth('tanggal_laporan', $month);
        });

        $query->when($request->year, function ($q, $year) {
            $q->whereYear('tanggal_laporan', $year);
        });

        $query->when($request->status && $request->status !== 'all', function ($q, $status) {
            $q->where('status', $status);
        });

        $query->when($request->search, function ($q, $search) {
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $q->where(function ($sub) use ($search, $like) {
                $sub->where('deskripsi_aktivitas', $like, "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search, $like) {
                        $u->where('name', $like, "%{$search}%");
                    });
            });
        });

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
                'error' => $e->getMessage()
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
            
            // [UPDATE] Validasi Kategori Lokasi disisipkan di sini
            'kategori_lokasi' => [
                'sometimes', 
                'required', 
                Rule::in([LaporanHarian::KAT_WFO, LaporanHarian::KAT_WFH, LaporanHarian::KAT_WFA, LaporanHarian::KAT_DL])
            ],

            'skp_rencana_id' => 'nullable|exists:skp_rencana,id',
            'tanggal_laporan' => 'sometimes|required|date',
            'waktu_mulai' => 'sometimes|required',
            'waktu_selesai' => 'sometimes|required|after:waktu_mulai',
            'deskripsi_aktivitas' => 'sometimes|required|string',
            'output_hasil_kerja' => 'sometimes|required|string',
            'volume' => 'sometimes|required|integer|min:1',
            'satuan' => 'sometimes|required|string|max:50',
            'hapus_bukti' => 'array',
            'mode_lokasi' => 'sometimes|nullable|string', 
            'lokasi_teks' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        try {
            DB::beginTransaction();

            $updateData = $request->except(['bukti', 'hapus_bukti', 'latitude', 'longitude']);

            if ($request->has('latitude') && $request->has('longitude') && $request->latitude && $request->longitude) {

                $finalLat = (float) $request->latitude;
                $finalLng = (float) $request->longitude;
                $isLuarLokasi = true;

                if (config('services.office.lat')) {
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

                $updateData['lokasi'] = DB::raw("ST_SetSRID(ST_MakePoint({$finalLng}, {$finalLat}), 4326)");
                $updateData['is_luar_lokasi'] = $isLuarLokasi;
                
                if ($request->has('location_provider')) {
                    $updateData['location_provider'] = $request->location_provider;
                }
                if ($request->has('location_accuracy')) {
                    $updateData['location_accuracy'] = $request->location_accuracy;
                }
            }

            $lkh->update($updateData);

            if ($request->filled('hapus_bukti')) {
                $buktiToDelete = LkhBukti::whereIn('id', $request->hapus_bukti)->where('laporan_id', $lkh->id)->get();
                foreach ($buktiToDelete as $bukti) {
                    Storage::disk('public')->delete($bukti->file_path);
                    $bukti->delete();
                }
            }

            if ($request->hasFile('bukti')) {
                $folderDate = date('Y/m');
                $storagePath = "uploads/lkh/{$folderDate}";

                foreach ($request->file('bukti') as $file) {
                    $extension = strtolower($file->getClientOriginalExtension());
                    $filename = Str::uuid() . '.' . $extension;
                    $finalPath = "";

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
            'rencana',
            'user.unitKerja'
        ])
        ->select('*')
        ->selectRaw('ST_Y(lokasi) AS latitude')
        ->selectRaw('ST_X(lokasi) AS longitude')
        ->findOrFail($id);

        $missingFields = [];

        if (empty($lkh->output_hasil_kerja))
            $missingFields[] = 'Output Hasil Kerja';
        if (empty($lkh->volume))
            $missingFields[] = 'Volume';
        if (empty($lkh->satuan))
            $missingFields[] = 'Satuan';
        if (empty($lkh->deskripsi_aktivitas))
            $missingFields[] = 'Uraian Kegiatan';

        if (empty($lkh->lokasi_teks) && (empty($lkh->latitude) || empty($lkh->longitude))) {
            $missingFields[] = 'Lokasi (GPS/Teks)';
        }

        if ($lkh->kategori === 'skp' && empty($lkh->skp_rencana_id)) {
            $missingFields[] = 'Target SKP';
        }

        if (count($missingFields) > 0) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Tidak dapat export PDF. Data belum lengkap:',
                'details' => $missingFields
            ], 422);
        }

        $pdf = Pdf::loadView('pdf.lkh', [
            'pegawai_nama' => $lkh->user->name,
            'pegawai_nip' => $lkh->user->nip,
            'pegawai_unit' => $lkh->user->unitKerja->nama_unit ?? '-',
            'tanggal' => $lkh->tanggal_laporan,
            'jenis_kegiatan' => $lkh->jenis_kegiatan,
            
            // [UPDATE] Tampilkan Kategori Lokasi di PDF
            'kategori_lokasi' => $lkh->kategori_lokasi, 
            
            'tupoksi' => $lkh->tupoksi->uraian_tugas ?? '-',
            'kategori' => $lkh->skp_rencana_id ? 'SKP' : 'Non-SKP',
            'jam_mulai' => $lkh->waktu_mulai,
            'jam_selesai' => $lkh->waktu_selesai,

            'lokasi' => $lkh->lokasi_teks
                ?? ($lkh->latitude ? "{$lkh->latitude}, {$lkh->longitude}" : '-'),
            'lokasi_teks' => $lkh->lokasi_teks,

            'output' => $lkh->output_hasil_kerja,
            'volume' => $lkh->volume,
            'satuan' => $lkh->satuan,
            'target_skp' => optional($lkh->rencana)->rencana_hasil_kerja ?? '-',

            'uraian_kegiatan' => $lkh->deskripsi_aktivitas 
        ]);

        return $pdf->stream("LKH-{$id}.pdf");
    }

    public function exportPdfDirect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_laporan' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'jenis_kegiatan' => 'required',
            'tupoksi_id' => 'required',
            
            // [UPDATE] Penambahan validasi untuk eksport Direct
            'kategori_lokasi' => [
                'required', 
                Rule::in([LaporanHarian::KAT_WFO, LaporanHarian::KAT_WFH, LaporanHarian::KAT_WFA, LaporanHarian::KAT_DL])
            ],

            'deskripsi_aktivitas' => 'required|string|min:5',
            'output_hasil_kerja' => 'required',
            'volume' => 'required|numeric|min:1',
            'satuan' => 'required',
        ], [
            'required' => ':attribute wajib diisi.',
            'min' => ':attribute terlalu pendek.',
            'numeric' => ':attribute harus berupa angka.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Data belum lengkap',
                'details' => $validator->errors()->all()
            ], 422);
        }

        $customErrors = [];

        if ($request->kategori === 'skp' && empty($request->skp_rencana_id)) {
            $customErrors[] = 'Kategori dipilih "SKP", namun Target SKP belum dipilih.';
        }

        $hasGPS = !empty($request->latitude) && !empty($request->longitude);
        $hasText = !empty($request->lokasi_teks);

        if (!$hasGPS && !$hasText) {
            $customErrors[] = 'Lokasi wajib diisi (Pastikan GPS aktif atau cari lokasi di peta).';
        }

        if (count($customErrors) > 0) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Validasi Data Gagal',
                'details' => $customErrors
            ], 422);
        }

        $user = auth()->user();
        $tupoksi = Tupoksi::find($request->tupoksi_id);

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
            
            // [UPDATE] Parsing ke cetakan PDF
            'kategori_lokasi' => $request->kategori_lokasi,
            
            'tupoksi' => $tupoksi->uraian_tugas ?? '-',
            'kategori' => $request->kategori === 'skp' ? 'SKP' : 'Non-SKP',
            'jam_mulai' => $request->waktu_mulai,
            'jam_selesai' => $request->waktu_selesai,

            'lokasi' => $request->lokasi_teks
                ?: ($hasGPS ? "{$request->latitude}, {$request->longitude}" : '-'),
            'lokasi_teks' => $request->lokasi_teks,

            'uraian_kegiatan' => $request->deskripsi_aktivitas,
            'output' => $request->output_hasil_kerja,
            'volume' => $request->volume,
            'satuan' => $request->satuan,
            'target_skp' => $rencana ? $rencana->rencana_hasil_kerja : null,
            'target_qty' => $targetQty,
            'target_satuan' => $targetSatuan,
            'bukti_status' => "Preview Draft",
        ];

        $pdf = Pdf::loadView('pdf.laporan-harian', $data);
        return $pdf->setPaper('a4', 'portrait')->stream('laporan-harian.pdf');
    }
}