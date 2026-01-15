<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserCsvImport;
use Illuminate\Support\Facades\Log;
use Exception;

class UserImportController extends Controller
{
    /**
     * Handle Import Pegawai via CSV/Excel
     *
     * Flow Proses:
     * 1. Validasi File Upload
     * 2. Load Master Data ke Memory (via Constructor Import Class)
     * 3. Proses Looping & Insert DB
     * 4. Tangkap Error per Baris (jika ada)
     * 5. Return Feedback ke Frontend
     */
    public function import(Request $request): JsonResponse
    {
        // 1. Validasi Input File (Max 10MB)
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xls,xlsx|max:10240'
        ]);

        try {
            // Instansiasi object Import.
            // Saat ini dijalankan, constructor akan meload data master (Unit, Jabatan, dll) ke RAM.
            $import = new UserCsvImport();
            
            // Eksekusi Import
            Excel::import($import, $request->file('csv_file'));

            // Ambil log error yang dikumpulkan oleh UserCsvImport
            $importErrors = $import->errors;
            $errorCount = count($importErrors);

            // Log aktivitas untuk Audit Trail
            Log::info("User ID " . auth()->id() . " melakukan import pegawai. Gagal: {$errorCount} baris.");

            // 2. Skenario: Ada baris yang gagal (Partial Success / Warning)
            // Kita return status 422 agar frontend menampilkan modal warning (List Error)
            if ($errorCount > 0) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => "Proses selesai dengan catatan. Terdapat {$errorCount} baris data yang gagal diproses.",
                    'data'    => [
                        'success' => false, // Flag untuk trigger alert warning di FE
                        'errors'  => $importErrors // List string error per baris
                    ]
                ], 422); 
            }

            // 3. Skenario: Sukses Sempurna (Tidak ada error sama sekali)
            return response()->json([
                'status'  => 'success',
                'message' => 'Data pegawai berhasil di-import sepenuhnya tanpa error.',
                'data'    => [
                    'success' => true,
                    'errors'  => []
                ]
            ], 200);

        } catch (Exception $e) {
            // 4. Skenario: Critical Error (File Corrupt / Database Down / Memory Limit)
            Log::error("Critical Error Import User: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan fatal pada sistem saat memproses file. Silakan cek log.',
                'error_debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}