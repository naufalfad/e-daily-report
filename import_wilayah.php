<?php

// Konfigurasi Database
$host = '127.0.0.1';
$db   = 'DB_E-Daily_Report_Kab_Mimika';
$user = 'postgres';
$pass = '123';
$port = "5432";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

// BASE PATH CSV
$base = "C:/Users/ayubl/Documents/Aplikasi e-Daily Report Kab. Mimika";

$files = [
    "$base/master_data_provinsi.csv" => [
        'table' => 'm_provinsi',
        'sql' => "INSERT INTO m_provinsi (id_provinsi, nama_provinsi) VALUES (?, ?)"
    ],
    "$base/master_data_kabupaten.csv" => [
        'table' => 'm_kabupaten',
        'sql' => "INSERT INTO m_kabupaten (id_kabupaten, id_provinsi, nama_kabupaten) VALUES (?, ?, ?)"
    ],
    "$base/master_data_kecamatan.csv" => [
        'table' => 'm_kecamatan',
        'sql' => "INSERT INTO m_kecamatan (id_kecamatan, id_kabupaten, nama_kecamatan) VALUES (?, ?, ?)"
    ],
    "$base/master_data_kelurahan.csv" => [
        'table' => 'm_kelurahan',
        'sql' => "INSERT INTO m_kelurahan (id_kelurahan, id_kecamatan, nama_kelurahan) VALUES (?, ?, ?)"
    ]
];

try {

    echo "--- MEMULAI KONEKSI KE DATABASE ---\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Koneksi Berhasil.\n\n";

    foreach ($files as $file => $config) {

        if (!file_exists($file)) {
            echo "File tidak ditemukan: $file\n";
            continue;
        }

        echo "Sedang memproses: $file -> {$config['table']}...\n";

        $handle = fopen($file, "r");
        $stmt   = $pdo->prepare($config['sql']);

        $row = 0;

        while (($data = fgetcsv($handle, 5000, ",")) !== false) {

            try {
                if (strpos($file, 'provinsi') !== false) {
                    $stmt->execute([$data[0], trim($data[1])]);

                } elseif (strpos($file, 'kabupaten') !== false) {
                    $stmt->execute([$data[0], $data[1], trim($data[2])]);

                } elseif (strpos($file, 'kecamatan') !== false) {
                    $stmt->execute([$data[0], $data[1], trim($data[2])]);

                } elseif (strpos($file, 'kelurahan') !== false) {
                    $stmt->execute([$data[0], $data[1], trim($data[2])]);
                }

            } catch (PDOException $e) {
                echo "\n(SKIP) Baris $row: " . $e->getMessage() . "\n";
            }

            $row++;

            if ($row % 1000 == 0) echo ".";
        }

        fclose($handle);

        echo "\nSelesai! Total $row data masuk ke {$config['table']}\n\n";
    }

    echo "--- SEMUA PROSES SELESAI ---\n";

} catch (PDOException $e) {
    die("FATAL ERROR: " . $e->getMessage());
}

