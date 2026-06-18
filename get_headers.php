<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('Absen Perbidang 2026.xlsx');
$sheet = $spreadsheet->getActiveSheet();
$headers = [];
$all_data = [];
$current_bidang = null;
$row_index = 0;

foreach ($sheet->getRowIterator() as $row) {
    $row_index++;
    $row_data = [];
    $is_empty = true;
    
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ($cellIterator as $cell) {
        $val = $cell->getCalculatedValue();
        $row_data[] = trim((string)$val);
        if (!empty(trim((string)$val))) {
            $is_empty = false;
        }
    }
    
    if ($is_empty) continue;
    
    // Check if it is a Bidang header (only column 0 has text, and it doesn't look like an ID/No)
    if (!empty($row_data[0]) && empty($row_data[1]) && empty($row_data[2]) && empty($row_data[4])) {
        if ($row_data[0] !== 'NO' && !is_numeric($row_data[0])) {
            $current_bidang = $row_data[0];
            continue;
        }
    }
    
    // Check if it is a user data row
    if (is_numeric($row_data[0]) && !empty($row_data[1])) {
        $all_data[] = [
            'bidang_header' => $current_bidang,
            'no' => $row_data[0],
            'nama' => $row_data[1],
            'nip' => $row_data[2],
            'pangkat' => $row_data[3],
            'jabatan' => $row_data[4],
        ];
    }
}
file_put_contents('extracted_data.json', json_encode($all_data, JSON_PRETTY_PRINT));
