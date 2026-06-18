<?php
$json = file_get_contents('extracted_data.json');
$data = json_decode($json, true);

$fp = fopen('absen_clean.csv', 'w');
// Write standard headers
fputcsv($fp, ['Nama', 'NIP', 'Pangkat_Golongan', 'Jabatan', 'Bidang_Atau_Sub_Bidang', 'Unit_Kerja']);

foreach ($data as $row) {
    if (empty($row['nama'])) continue;
    
    // We will put the original Jabatan string. The UserCsvImport will resolve canonical Jabatan, 
    // but wait! UserCsvImport Standard CSV mode does NOT extract Sub Bidang from Jabatan!
    // Let's do it here so the CSV is completely explicit and clean.
    
    $jabatanStr = $row['jabatan'];
    $bidangStr = $row['bidang_header'];
    
    // Extract Sub Bidang from Jabatan if possible
    if (preg_match('/(?:Plt\.\s*)?(?:KEPALA|PELAKSANA|SUB BIDANG)\s+(SUB\s+(?:BIDANG|BAGIAN)\s+.*)/i', $jabatanStr, $matches)) {
        $bidangStr = trim($matches[1]);
    }
    
    fputcsv($fp, [
        $row['nama'],
        $row['nip'],
        $row['pangkat'],
        $jabatanStr, // Leave as is, UserCsvImport's resolveJabatan will clean it up!
        $bidangStr,  // Now contains the exact Sub Bidang name!
        'Badan Pendapatan Daerah'
    ]);
}
fclose($fp);
echo "Berhasil membuat absen_clean.csv\n";
