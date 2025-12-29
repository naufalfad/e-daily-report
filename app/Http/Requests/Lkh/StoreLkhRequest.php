<?php

declare(strict_types=1);

namespace App\Http\Requests\Lkh;

use App\Enums\LocationProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Closure;

/**
 * Class StoreLkhRequest
 *
 * Bertindak sebagai Gatekeeper Validation Layer.
 * Mengimplementasikan prinsip Defensive Programming untuk mencegah 'Garbage In'.
 * Menangani logika validasi kondisional (Draft vs Submit) dan Custom File Validation.
 */
class StoreLkhRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Penerapan: Security Layer (Authorization).
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     * Penerapan: Declarative Validation logic.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Deteksi apakah user ingin menyimpan Draft atau Submit Final
        // Jika Draft, validasi dilonggarkan.
        $isSubmit = $this->input('status') !== 'draft';

        return [
            // --- 1. CORE DATA VALIDATION ---
            'tanggal_laporan' => ['required', 'date', 'before_or_equal:today'],
            'waktu_mulai'       => ['required', 'date_format:H:i'],
            'waktu_selesai'     => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'jenis_kegiatan'  => ['required', 'string', 'max:100'],
            'deskripsi_aktivitas' => ['required', 'string', 'min:10'],

            // Relasi (Tupoksi / SKP)
            'tupoksi_id' => ['nullable', 'exists:tupoksi,id'],
            'skp_rencana_id' => ['nullable', 'exists:skp_rencana,id'],
            
            // Output Kinerja (Wajib jika Submit)
            'output_hasil_kerja' => [$isSubmit ? 'required' : 'nullable', 'string', 'max:255'],
            'satuan'             => [$isSubmit ? 'required' : 'nullable', 'string', 'max:50'],
            'volume'             => [$isSubmit ? 'required' : 'nullable', 'numeric', 'min:0'],

            // --- 2. GEOSPATIAL VALIDATION (STRICT) ---
            // Validasi koordinat wajib numerik dan berada dalam range bumi yang valid.
            'latitude' => [
                $isSubmit ? 'required' : 'nullable', 
                'numeric', 
                'between:-90,90'
            ],
            'longitude' => [
                $isSubmit ? 'required' : 'nullable', 
                'numeric', 
                'between:-180,180'
            ],
            
            // Type Safety: Menggunakan Enum untuk mencegah Magic Strings attack
            'location_provider' => [
                'required', 
                Rule::enum(LocationProvider::class)
            ],
            
            'location_accuracy' => ['nullable', 'numeric', 'min:0'],
            'lokasi_teks'       => ['nullable', 'string', 'max:500'],

            // --- 3. FILE VALIDATION (ROBUST) ---
            // Menggunakan Custom Closure untuk menangani edge case MIME Type
            'bukti' => ['nullable', 'array', 'max:5'], // Max 5 files
            'bukti.*' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    $this->validateEvidenceFile($attribute, $value, $fail);
                },
            ],
        ];
    }

    /**
     * Custom Validator Logic untuk File Bukti.
     * Mengatasi masalah umum di mana browser kadang mengirim PDF sebagai 'application/octet-stream'.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    protected function validateEvidenceFile(string $attribute, mixed $value, Closure $fail): void
    {
        // Defensive: Pastikan ini adalah object UploadedFile
        if (! $value instanceof UploadedFile) {
            $fail("Atribut {$attribute} harus berupa file yang valid.");
            return;
        }

        // 1. Validasi Ukuran (Max 5MB)
        $maxSizeKb = 5120; 
        if ($value->getSize() > $maxSizeKb * 1024) {
            $fail("Ukuran file {$value->getClientOriginalName()} tidak boleh lebih dari 5MB.");
            return;
        }

        // 2. Validasi Ekstensi (Whitelist Approach)
        // Kita tidak bergantung 100% pada guessExtension() milik Laravel yang kadang strict pada mime headers.
        // Kita cek ekstensi asli file user sebagai layer pertama (UX friendly), lalu mime type sebagai backup.
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $clientExtension = strtolower($value->getClientOriginalExtension());

        if (! in_array($clientExtension, $allowedExtensions)) {
            $fail("File {$value->getClientOriginalName()} harus berformat: JPG, PNG, atau PDF.");
            return;
        }

        // 3. Validasi MIME Type (Security Layer)
        // Mencegah user rename 'virus.exe' menjadi 'image.jpg'
        // Namun kita izinkan octet-stream JIKA ekstensinya .pdf (kasus browser legacy/mobile tertentu)
        $validMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        $clientMime = $value->getMimeType();

        if (! in_array($clientMime, $validMimes)) {
            // Exception handling untuk PDF yang terdeteksi sebagai binary
            if ($clientExtension === 'pdf' && ($clientMime === 'application/octet-stream' || $clientMime === 'binary/octet-stream')) {
                return; // Allow logic bypass
            }
            
            $fail("Tipe file {$value->getClientOriginalName()} tidak valid atau rusak.");
        }
    }

    /**
     * Kustomisasi pesan error agar lebih humanis (User Experience).
     */
    public function attributes(): array
    {
        return [
            'tupoksi_id' => 'Referensi Tupoksi',
            'skp_rencana_id' => 'Target SKP',
            'latitude' => 'Koordinat Lintang',
            'longitude' => 'Koordinat Bujur',
            'location_provider' => 'Sumber Lokasi',
            'output_hasil_kerja' => 'Output Kegiatan',
            'bukti.*' => 'Lampiran Bukti',
        ];
    }

    /**
     * Persiapan data sebelum validasi.
     * Membersihkan input yang mungkin null string atau format salah.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Pastikan null string dikonversi jadi null beneran
            'location_accuracy' => $this->location_accuracy === 'null' || $this->location_accuracy === '' 
                ? null 
                : $this->location_accuracy,
            
            // Default provider jika kosong (Defensive)
            'location_provider' => $this->location_provider ?? LocationProvider::MANUAL_PIN->value,
        ]);
    }
}