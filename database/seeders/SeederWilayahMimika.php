<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MasterProvinsi;
use App\Models\MasterKabupaten;
use App\Models\MasterKecamatan;
use App\Models\MasterKelurahan;

class SeederWilayahMimika extends Seeder
{
    public function run()
    {
        // ID BPS Papua Tengah (94) & Mimika (9412)
        $provinsiData = ['id' => '94', 'nama' => 'PAPUA TENGAH'];
        $kabupatenData = ['id' => '9412', 'nama' => 'MIMIKA'];

        // Data JSON dengan ID Kecamatan Eksplisit (Format: 9412 + XX)
        $dataWilayah = [
            [
                "id_distrik" => "941201", // ID Manual
                "nama_distrik" => "MIMIKA BARU",
                "daftar_desa" => [
                    ["nama_desa" => "Koperapoka", "lat" => -4.551627, "lon" => 136.880476],
                    ["nama_desa" => "Timika Jaya", "lat" => -4.517801, "lon" => 136.847296],
                    ["nama_desa" => "Kwamki Baru", "lat" => -4.546326, "lon" => 136.889500],
                    ["nama_desa" => "Nayaro", "lat" => -4.562857, "lon" => 136.831425],
                    ["nama_desa" => "Timika Indah", "lat" => -4.544044, "lon" => 136.882519],
                    ["nama_desa" => "Otomona", "lat" => -4.553950, "lon" => 136.883369],
                    ["nama_desa" => "Dingonarama", "lat" => 4.548347, "lon" => 136.894865],
                    ["nama_desa" => "Pasar Sentral", "lat" => -4.565140, "lon" => 136.870528],
                    ["nama_desa" => "Wanagon", "lat" => -4.520004, "lon" => 136.847401],
                    ["nama_desa" => "Sempan", "lat" => -4.566302, "lon" => 136.893142],
                    ["nama_desa" => "Hangaitji", "lat" => -4.521782, "lon" => 136.855730],
                    ["nama_desa" => "Ninabua", "lat" => -4.540915, "lon" => 136.863431],
                    ["nama_desa" => "Kebun Sirih", "lat" => -4.544017, "lon" => 136.897614],
                    ["nama_desa" => "Perintis", "lat" => -4.550348, "lon" => 136.879919],
                ]
            ],
            [
                "id_distrik" => "941202",
                "nama_distrik" => "AGIMUGA",
                "daftar_desa" => [
                    ["nama_desa" => "Amungun", "lat" => -4.675174, "lon" => 137.605886],
                ]
            ],
            [
                "id_distrik" => "941203",
                "nama_distrik" => "MIMIKA TIMUR",
                "daftar_desa" => [
                    ["nama_desa" => "Pomako", "lat" => -4.774241, "lon" => 136.775387],
                    ["nama_desa" => "Tipuka", "lat" => -4.707209, "lon" => 136.832406],
                    ["nama_desa" => "Kaugapu", "lat" => -4.670981, "lon" => 136.812535],
                    ["nama_desa" => "Wania", "lat" => -4.673208, "lon" => 136.827697],
                    ["nama_desa" => "Mwapi", "lat" => -4.664944, "lon" => 136.824634],
                ]
            ],
            [
                "id_distrik" => "941204",
                "nama_distrik" => "MIMIKA BARAT",
                "daftar_desa" => [
                    ["nama_desa" => "Kokonao", "lat" => -4.711988, "lon" => 136.436418],
                    ["nama_desa" => "Migiwia", "lat" => -4.713642, "lon" => 136.442142],
                    ["nama_desa" => "Apuri", "lat" => -4.712654, "lon" => 136.435031],
                    ["nama_desa" => "Atapo", "lat" => -4.715134, "lon" => 136.448139],
                    ["nama_desa" => "Aparuka", "lat" => -4.363270, "lon" => 136.402400],
                ]
            ],
            [
                "id_distrik" => "941205",
                "nama_distrik" => "JITA",
                "daftar_desa" => [
                    ["nama_desa" => "Sumapro", "lat" => -5.057512, "lon" => 137.578297],
                    ["nama_desa" => "Wapu", "lat" => -4.925878, "lon" => 137.770009],
                    ["nama_desa" => "Noema", "lat" => -4.885651, "lon" => 137.552385],
                    ["nama_desa" => "Sempan Timur", "lat" => -4.872395, "lon" => 137.700272],
                    ["nama_desa" => "Waituku", "lat" => -4.925433, "lon" => 137.527606],
                ]
            ],
            [
                "id_distrik" => "941206",
                "nama_distrik" => "JILA",
                "daftar_desa" => [
                    ["nama_desa" => "Jila", "lat" => -4.247217, "lon" => 137.599546],
                    ["nama_desa" => "Diloa", "lat" => -4.298306, "lon" => 137.636918],
                    ["nama_desa" => "Noemun", "lat" => -4.246580, "lon" => 137.429396],
                    ["nama_desa" => "Pilikogom", "lat" => -4.243821, "lon" => 137.611951],
                    ["nama_desa" => "Diola II", "lat" => -4.245558, "lon" => 137.598920],
                ]
            ],
            [
                "id_distrik" => "941207",
                "nama_distrik" => "MIMIKA TIMUR JAUH",
                "daftar_desa" => [
                    ["nama_desa" => "Omawita", "lat" => -4.758433, "lon" => 137.141833],
                    ["nama_desa" => "Amamapare", "lat" => -4.849439, "lon" => 136.892851],
                ]
            ],
            [
                "id_distrik" => "941208",
                "nama_distrik" => "MIMIKA TENGAH",
                "daftar_desa" => [
                    ["nama_desa" => "Keakwa", "lat" => -4.763115, "lon" => 136.519100],
                    ["nama_desa" => "Atuka", "lat" => -4.793841, "lon" => 136.570842],
                    ["nama_desa" => "Aikawapuka", "lat" => -4.681320, "lon" => 136.583767],
                ]
            ],
            [
                "id_distrik" => "941209",
                "nama_distrik" => "KUALA KENCANA",
                "daftar_desa" => [
                    ["nama_desa" => "Karang Senang", "lat" => -4.479010, "lon" => 136.870203],
                    ["nama_desa" => "Kuala Kencana", "lat" => -4.461932, "lon" => 136.860793],
                    ["nama_desa" => "Utikini Baru", "lat" => -4.467790, "lon" => 136.819242],
                    ["nama_desa" => "Bhintuka", "lat" => -4.456648, "lon" => 136.802062],
                    ["nama_desa" => "Pioka Kencana", "lat" => -4.445852, "lon" => 136.818988],
                    ["nama_desa" => "Karya Kencana", "lat" => -4.461787, "lon" => 136.853754],
                    ["nama_desa" => "Utikini II", "lat" => -4.472166, "lon" => 136.828248],
                    ["nama_desa" => "Utikini III", "lat" => -4.471796, "lon" => 136.824308],
                ]
            ],
            [
                "id_distrik" => "941210",
                "nama_distrik" => "TEMBAGAPURA",
                "daftar_desa" => [
                    ["nama_desa" => "Tembagapura", "lat" => -4.142602, "lon" => 137.091319],
                    ["nama_desa" => "Waa", "lat" => -4.157287, "lon" => 137.054798],
                    ["nama_desa" => "Arwandop", "lat" => -4.349765, "lon" => 136.985202],
                    ["nama_desa" => "Tsinga", "lat" => -4.214535, "lon" => 137.202034],
                    ["nama_desa" => "Jagamin", "lat" => -4.313182, "lon" => 137.093877],
                    ["nama_desa" => "Opitawak", "lat" => -4.264234, "lon" => 137.004417],
                    ["nama_desa" => "Doliningokngin", "lat" => -4.387258, "lon" => 137.266227],
                    ["nama_desa" => "Banigogom", "lat" => -4.456453, "lon" => 137.135446],
                    ["nama_desa" => "Banti II", "lat" => -4.156732, "lon" => 137.055698],
                ]
            ],
            [
                "id_distrik" => "941211",
                "nama_distrik" => "MIMIKA BARAT JAUH",
                "daftar_desa" => [
                    ["nama_desa" => "Potowayburu", "lat" => -4.283979, "lon" => 134.967522],
                    ["nama_desa" => "Yapakopa", "lat" => -4.438571, "lon" => 135.483889],
                    ["nama_desa" => "Aindua", "lat" => -4.460724, "lon" => 135.197954],
                    ["nama_desa" => "Tapormai", "lat" => -4.392772, "lon" => 135.369444],
                ]
            ],
            [
                "id_distrik" => "941212",
                "nama_distrik" => "MIMIKA BARAT TENGAH",
                "daftar_desa" => [
                    ["nama_desa" => "Kipia", "lat" => -4.487310, "lon" => 135.761218],
                    ["nama_desa" => "Akar", "lat" => -4.441481, "lon" => 135.882232],
                    ["nama_desa" => "Kapiraya", "lat" => -4.471719, "lon" => 136.068711],
                    ["nama_desa" => "Uta", "lat" => -4.549291, "lon" => 136.003960],
                    ["nama_desa" => "Wumuka", "lat" => -4.463786, "lon" => 135.901648],
                    ["nama_desa" => "Wakia", "lat" => -4.442580, "lon" => 136.041684],
                ]
            ],
            [
                "id_distrik" => "941213",
                "nama_distrik" => "KWAMKI NARAMA",
                "daftar_desa" => [
                    ["nama_desa" => "Mekurima", "lat" => -4.520499, "lon" => 136.900122],
                    ["nama_desa" => "Olaroa", "lat" => -4.491280, "lon" => 136.880391],
                    ["nama_desa" => "Bintang Lima", "lat" => -4.479132, "lon" => 136.883200],
                    ["nama_desa" => "Damai", "lat" => -4.485882, "lon" => 136.890497],
                    ["nama_desa" => "Walani", "lat" => -4.455868, "lon" => 137.135327],
                    ["nama_desa" => "Amole", "lat" => -4.501652, "lon" => 136.881447],
                    ["nama_desa" => "Tunas Matoa", "lat" => -4.479084, "lon" => 136.872694],
                ]
            ],
            [
                "id_distrik" => "941214",
                "nama_distrik" => "HOYA",
                "daftar_desa" => [
                    ["nama_desa" => "Hoya", "lat" => -4.361415, "lon" => 137.444256],
                    ["nama_desa" => "Jinoni", "lat" => -4.193879, "lon" => 137.588878],
                    ["nama_desa" => "Mamontoga", "lat" => -4.181965, "lon" => 137.412962],
                    ["nama_desa" => "Puti", "lat" => -4.181965, "lon" => 137.412962],
                ]
            ],
            [
                "id_distrik" => "941215",
                "nama_distrik" => "IWAKA",
                "daftar_desa" => [
                    ["nama_desa" => "Limau Asri", "lat" => -4.530561, "lon" => 136.774862],
                    ["nama_desa" => "Limau Asri Barat", "lat" => -4.530176, "lon" => 136.759608],
                    ["nama_desa" => "Wangirja", "lat" => -4.503197, "lon" => 136.787745],
                    ["nama_desa" => "Iwaka", "lat" => -4.409647, "lon" => 136.862064],
                    ["nama_desa" => "Naena Muktipura", "lat" => -4.571047, "lon" => 136.736734],
                    ["nama_desa" => "Mulia Kencana", "lat" => -4.488817, "lon" => 136.782014],
                    ["nama_desa" => "Pigapu", "lat" => -4.868531, "lon" => 136.815009],
                ]
            ],
            [
                "id_distrik" => "941216",
                "nama_distrik" => "WANIA",
                "daftar_desa" => [
                    ["nama_desa" => "Kamoro Jaya", "lat" => -4.585456, "lon" => 136.853074],
                    ["nama_desa" => "Monokau Jaya", "lat" => -4.579838, "lon" => 136.885076],
                    ["nama_desa" => "Wonosari Jaya", "lat" => -4.596470, "lon" => 136.878999],
                    ["nama_desa" => "Inauga", "lat" => 4.577059, "lon" => 136.902211],
                    ["nama_desa" => "Nawaripi", "lat" => -4.559926, "lon" => 136.882800],
                ]
            ],
            [
                "id_distrik" => "941217",
                "nama_distrik" => "AMAR",
                "daftar_desa" => [
                    ["nama_desa" => "Ipiri", "lat" => -4.476356, "lon" => 136.279551],
                    ["nama_desa" => "Paripi", "lat" => -4.433302, "lon" => 136.333943],
                    ["nama_desa" => "Yaraya", "lat" => -4.460386, "lon" => 136.385044],
                    ["nama_desa" => "Amar", "lat" => -4.623305, "lon" => 136.146626],
                    ["nama_desa" => "Kawar", "lat" => -4.643247, "lon" => 136.219440],
                    ["nama_desa" => "Manuare", "lat" => -4.623305, "lon" => 136.146626],
                ]
            ],
            [
                "id_distrik" => "941218",
                "nama_distrik" => "ALAMA",
                "daftar_desa" => [
                    ["nama_desa" => "Enggin", "lat" => -4.376939, "lon" => 137.618054],
                    ["nama_desa" => "Geselama", "lat" => -4.346021, "lon" => 137.515071],
                    ["nama_desa" => "Alama", "lat" => -4.535990, "lon" => 136.823271],
                ]
            ],
        ];

        DB::beginTransaction();
        try {
            // STEP 1: Provinsi
            $provinsi = MasterProvinsi::updateOrCreate(
                ['id' => $provinsiData['id']],
                ['nama' => strtoupper($provinsiData['nama'])]
            );
            $this->command->info("CHECK: Provinsi {$provinsi->nama} OK.");

            // STEP 2: Kabupaten
            $kabupaten = MasterKabupaten::updateOrCreate(
                ['id' => $kabupatenData['id']],
                [
                    'provinsi_id' => $provinsi->id,
                    'nama' => strtoupper($kabupatenData['nama'])
                ]
            );
            $this->command->info("CHECK: Kabupaten {$kabupaten->nama} OK.");

            $totalDistrik = 0;
            $totalDesa = 0;

            foreach ($dataWilayah as $distrik) {
                // STEP 3: Kecamatan (FIX: Dengan ID Explicit)
                $kecamatan = MasterKecamatan::updateOrCreate(
                    [
                        // Kita gunakan ID sebagai kunci utama agar tidak error 'not null'
                        'id' => $distrik['id_distrik'] 
                    ],
                    [
                        'kabupaten_id' => $kabupaten->id,
                        'nama' => strtoupper($distrik['nama_distrik'])
                    ]
                );

                $totalDistrik++;
                $counterDesa = 1;

                foreach ($distrik['daftar_desa'] as $desa) {
                    // STEP 4: Kelurahan
                    // Antisipasi jika table kelurahan juga tidak auto-increment.
                    // Kita generate ID: Kode Kecamatan + 3 digit urut (misal: 941201001)
                    
                    // Note: Jika tipe data ID kelurahan integer, pastikan field-nya BigInteger.
                    // Jika string, format ini aman.
                    $generatedIdDesa = $kecamatan->id . str_pad($counterDesa, 4, '0', STR_PAD_LEFT);

                    // Cek logika: Jika user mau update by Nama, kita cari dulu.
                    // Tapi jika insert baru, kita butuh ID.
                    
                    // Strategi Aman: Cek exist by Nama & Kecamatan dulu.
                    $existingDesa = MasterKelurahan::where('kecamatan_id', $kecamatan->id)
                        ->where('nama', strtoupper($desa['nama_desa']))
                        ->first();

                    if ($existingDesa) {
                        $existingDesa->update([
                            'latitude' => $desa['lat'],
                            'longitude' => $desa['lon']
                        ]);
                    } else {
                        // Jika Create Baru, sertakan ID
                        MasterKelurahan::create([
                            'id' => $generatedIdDesa, // Inject ID Manual
                            'kecamatan_id' => $kecamatan->id,
                            'nama' => strtoupper($desa['nama_desa']),
                            'latitude' => $desa['lat'],
                            'longitude' => $desa['lon']
                        ]);
                    }
                    
                    $counterDesa++;
                    $totalDesa++;
                }
            }

            DB::commit();
            $this->command->info("-------------------------------------------------------");
            $this->command->info("SUKSES SEEDING LENGKAP WILAYAH MIMIKA");
            $this->command->info("Provinsi   : " . $provinsi->nama);
            $this->command->info("Kabupaten  : " . $kabupaten->nama);
            $this->command->info("Distrik    : " . $totalDistrik . " Distrik (ID Fixed)");
            $this->command->info("Desa       : " . $totalDesa . " Desa terproses");
            $this->command->info("-------------------------------------------------------");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("ERROR CRITICAL: " . $e->getMessage());
        }
    }
}
