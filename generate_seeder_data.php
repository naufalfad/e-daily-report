<?php
$data = json_decode(file_get_contents('extracted_data.json'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // maybe utf-16 issue, let's fix it by reading it correctly
    $content = file_get_contents('extracted_data.json');
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
    $data = json_decode($content, true);
}

$bidangs = [];
$jabatans = [];
$sub_bidangs = [];

foreach ($data as $row) {
    $bidang_header = $row['bidang_header'] ?? 'Unknown';
    $jabatan = $row['jabatan'] ?? '';
    
    // Default sub bidang is null
    $sub_bidang = null;
    $jabatan_inti = $jabatan;
    
    // Try to extract Sub Bidang from Jabatan
    // Pattern 1: KEPALA SUB BIDANG / KEPALA SUB BAGIAN
    if (preg_match('/(?:Plt\.\s*)?KEPALA\s+(SUB\s+(?:BIDANG|BAGIAN)\s+.*)/i', $jabatan, $matches)) {
        $sub_bidang = trim($matches[1]);
        $jabatan_inti = trim(str_replace($matches[1], '', $jabatan));
    }
    // Pattern 2: PELAKSANA SUB BIDANG / PELAKSANA SUB BAGIAN
    else if (preg_match('/PELAKSANA\s+(SUB\s+(?:BIDANG|BAGIAN)\s+.*)/i', $jabatan, $matches)) {
        $sub_bidang = trim($matches[1]);
        $jabatan_inti = 'PELAKSANA';
    }
    
    if (!isset($bidangs[$bidang_header])) {
        $bidangs[$bidang_header] = [];
    }
    
    if ($sub_bidang) {
        $bidangs[$bidang_header][$sub_bidang] = true;
    }
    
    $jabatans[$jabatan_inti] = true;
    // We also want to keep the raw jabatan just in case we seed exactly the raw jabatan
    $jabatans[$jabatan] = true;
}

echo "=== BIDANG HIERARCHY ===\n";
foreach ($bidangs as $b => $subs) {
    echo "- $b\n";
    foreach ($subs as $s => $_) {
        echo "  * $s\n";
    }
}

echo "\n=== ALL UNIQUE JABATAN ===\n";
foreach (array_keys($jabatans) as $j) {
    echo "$j\n";
}
