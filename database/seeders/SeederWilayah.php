<?php
// Konfigurasi Database Yang Mulia
$host = '127.0.0.1';
$db   = 'DB_E-Daily_Report_Kab_Mimika'; // Ganti dengan nama DB Yang Mulia
$user = 'postgres';           // User default postgres
$pass = '123';      // Password postgres Yang Mulia
$port = "5432";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    echo "--- MEMULAI KONEKSI KE DATABASE ---\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Koneksi Berhasil.\n\n";

    // Daftar File dan Query Insert yang bersesuaian
    // Urutan PENTING karena Foreign Key (Prov -> Kab -> Kec -> Kel)
    $files = [
        'master_data_provinsi.csv' => [
            'table' => 'm_provinsi',
            'sql' => "INSERT INTO m_provinsi (id_provinsi, nama_provinsi) VALUES (?, ?)"
        ],
        'master_data_kabupaten.csv' => [
            'table' => 'm_kabupaten',
            'sql' => "INSERT INTO m_kabupaten (id_kabupaten, id_provinsi, nama_kabupaten) VALUES (?, ?, ?)"
        ],
        'master_data_kecamatan.csv' => [
            'table' => 'm_kecamatan',
            'sql' => "INSERT INTO m_kecamatan (id_kecamatan, id_kabupaten, nama_kecamatan) VALUES (?, ?, ?)"
        ],
        'master_data_kelurahan.csv' => [
            'table' => 'm_kelurahan',
            'sql' => "INSERT INTO m_kelurahan (id_kelurahan, id_kecamatan, nama_kelurahan) VALUES (?, ?, ?)"
        ]
    ];

    foreach ($files as $fileName => $config) {
        if (!file_exists($fileName)) {
            die("Error: File $fileName tidak ditemukan di folder ini.\n");
        }

        echo "Sedang memproses: $fileName -> Tabel {$config['table']}...\n";
        
        // Membuka file CSV
        $handle = fopen($fileName, "r");
        
        // Memulai Transaksi (Kunci Kecepatan)
        $pdo->beginTransaction();
        
        // Siapkan Statement
        $stmt = $pdo->prepare($config['sql']);
        
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            try {
                // LOGIC CLEANING DATA
                // Trim spasi di nama wilayah
                
                if ($fileName == 'master_data_provinsi.csv') {
                    // Format: ID, NAMA
                    $stmt->execute([$data[0], trim($data[1])]);
                } 
                elseif ($fileName == 'master_data_kabupaten.csv' || $fileName == 'master_data_kecamatan.csv') {
                    // Format: ID, PARENT_ID, NAMA
                    $stmt->execute([$data[0], $data[1], trim($data[2])]);
                } 
                elseif ($fileName == 'master_data_kelurahan.csv') {
                    // Format: ID, PARENT_ID, NAMA, (SAMPAH), (SAMPAH)
                    // Kita hanya ambil index 0, 1, 2
                    $stmt->execute([$data[0], $data[1], trim($data[2])]);
                }
                
                $row++;
                // Feedback visual setiap 1000 baris agar Yang Mulia tidak bosan menunggu
                if ($row % 1000 == 0) echo "."; 
                
            } catch (PDOException $e) {
                // Skip error duplicate key jika file dijalankan 2x
                echo "\nError pada baris $row: " . $e->getMessage() . "\n";
            }
        }
        
        // Commit Transaksi (Simpan permanen)
        $pdo->commit();
        fclose($handle);
        echo "\nSelesai! Total $row data berhasil di-import ke {$config['table']}.\n\n";
    }

    echo "--- SEMUA PROSES SELESAI, YANG MULIA ---";

} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>