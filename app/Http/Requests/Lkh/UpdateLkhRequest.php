<?php

declare(strict_types=1);

namespace App\Http\Requests\Lkh;

use App\Enums\LocationProvider;
use App\Models\LaporanHarian; // Added: Import Model LaporanHarian
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Closure;

class UpdateLkhRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan LKH yang akan diedit adalah milik user yang sedang login
        // Logika ini bisa dikuatkan lagi di Controller (menggunakan Policy), 
        // namun untuk lapisan FormRequest, otentikasi dasar sudah cukup.
        return Auth::check();
    }

    public function rules(): array
    {
        $isSubmit = $this->input('status') !== 'draft';

        return [
            'tanggal_laporan' => ['required', 'date', 'before_or_equal:today'],
            'waktu_mulai'     => ['required', 'date_format:H:i'],
            'waktu_selesai'   => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'jenis_kegiatan'  => ['required', 'string', 'max:100'],
            'deskripsi_aktivitas' => ['required', 'string', 'min:10'],
            
            // --- NEW: VALIDASI KATEGORI LOKASI ---
            'kategori_lokasi' => [
                'required', 
                Rule::in([
                    LaporanHarian::KAT_WFO, 
                    LaporanHarian::KAT_WFH, 
                    LaporanHarian::KAT_WFA, 
                    LaporanHarian::KAT_DL
                ])
            ],

            'tupoksi_id' => ['nullable', 'exists:tupoksi,id'],
            'skp_rencana_id' => ['nullable', 'exists:skp_rencana,id'],
            
            'output_hasil_kerja' => [$isSubmit ? 'required' : 'nullable', 'string', 'max:255'],
            'satuan'             => [$isSubmit ? 'required' : 'nullable', 'string', 'max:50'],
            'volume'             => [$isSubmit ? 'required' : 'nullable', 'numeric', 'min:0'],

            'location_provider' => [
                'required', 
                Rule::enum(LocationProvider::class)
            ],

            'latitude' => [
                Rule::requiredIf(fn() => $isSubmit || $this->input('location_provider') === LocationProvider::GPS_DEVICE->value),
                'nullable', 
                'numeric', 
                'between:-90,90'
            ],
            
            'longitude' => [
                Rule::requiredIf(fn() => $isSubmit || $this->input('location_provider') === LocationProvider::GPS_DEVICE->value),
                'nullable', 
                'numeric', 
                'between:-180,180'
            ],
            
            'location_accuracy' => ['nullable', 'numeric', 'min:0'],
            'lokasi_teks'       => ['nullable', 'string', 'max:500'],
            'address_auto'      => ['nullable', 'string', 'max:500'],

            // Validasi file (Opsional saat update jika pengguna tidak memilih file baru)
            'bukti' => ['nullable', 'array', 'max:5'], 
            'bukti.*' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    $this->validateEvidenceFile($attribute, $value, $fail);
                },
            ],
            
            // Array ID file bukti lama yang akan dihapus
            'delete_bukti' => ['nullable', 'array'],
            'delete_bukti.*' => ['integer', 'exists:lkh_bukti,id'],
        ];
    }

    protected function validateEvidenceFile(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail("Atribut {$attribute} harus berupa file yang valid.");
            return;
        }

        $maxSizeKb = 5120; 
        if ($value->getSize() > $maxSizeKb * 1024) {
            $fail("Ukuran file {$value->getClientOriginalName()} tidak boleh lebih dari 5MB.");
            return;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $clientExtension = strtolower($value->getClientOriginalExtension());

        if (! in_array($clientExtension, $allowedExtensions)) {
            $fail("File {$value->getClientOriginalName()} harus berformat: JPG, PNG, atau PDF.");
            return;
        }

        $validMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        $clientMime = $value->getMimeType();

        if (! in_array($clientMime, $validMimes)) {
            if ($clientExtension === 'pdf' && ($clientMime === 'application/octet-stream' || $clientMime === 'binary/octet-stream')) {
                return;
            }
            $fail("Tipe file {$value->getClientOriginalName()} tidak valid atau rusak.");
        }
    }

    public function attributes(): array
    {
        return [
            'tupoksi_id' => 'Referensi Tupoksi',
            'skp_rencana_id' => 'Target SKP',
            'kategori_lokasi' => 'Kategori Lokasi',
            'latitude' => 'Koordinat Lintang',
            'longitude' => 'Koordinat Bujur',
            'location_provider' => 'Sumber Lokasi',
            'output_hasil_kerja' => 'Output Kegiatan',
            'bukti.*' => 'Lampiran Bukti Baru',
            'address_auto' => 'Alamat Otomatis',
            'delete_bukti.*' => 'ID Bukti Hapus',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location_accuracy' => $this->location_accuracy === 'null' || $this->location_accuracy === '' 
                ? null 
                : $this->location_accuracy,
            
            'location_provider' => $this->location_provider ?? LocationProvider::MANUAL_PIN->value,
        ]);
    }
}