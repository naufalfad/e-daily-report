<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserCsvImport;

class UserImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt, xls, xlsx'
        ]);

        $import = new UserCsvImport;
        Excel::import($import, $request->file('csv_file'));

        return response()->json([
            'message' => 'Import selesai',
            'errors'  => $import->errors,
        ]);
    }
}