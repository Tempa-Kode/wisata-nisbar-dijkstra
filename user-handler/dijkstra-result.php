<?php
require_once 'config/db.php';

// ==============================
// Ambil semua destinasi
// ==============================
$daftar_destinasi = [];
$perintah = $pdo->query("SELECT id, nama_destinasi, latitude, longitude FROM destinasi");
while ($baris = $perintah->fetch(PDO::FETCH_ASSOC)) {
    $daftar_destinasi[$baris['id']] = [
        'nama' => $baris['nama_destinasi'],
        'lat'  => $baris['latitude'],
        'lng'  => $baris['longitude'],
    ];
}

// ==============================
// Buat graf berdasarkan tabel jarak
// ==============================
$graf = [];
foreach ($daftar_destinasi as $id => $data) {
    $graf[$id] = [];
}

$perintah = $pdo->query("SELECT * FROM jarak_antar_destinasi");
while ($baris = $perintah->fetch(PDO::FETCH_ASSOC)) {
    $dari  = $baris['titik_awal'];
    $ke    = $baris['titik_tujuan'];
    $jarak = $baris['jarak_km'];

    $graf[$dari][$ke] = $jarak;
    $graf[$ke][$dari] = $jarak; // dua arah
}

// ==============================
// Fungsi Haversine
// ==============================
function haversine($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Kilometer

    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo   = deg2rad($lat2);
    $lonTo   = deg2rad($lon2);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos($latFrom) * cos($latTo) *
         sin($lonDelta / 2) * sin($lonDelta / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}


// ==============================
// Fungsi Dijkstra
// ==============================
function dijkstra($graf, $awal, $tujuan) {
    $jarak_terpendek = [];
    $sebelumnya = [];
    $antrian = [];

    foreach ($graf as $simpul => $tujuan_terhubung) {
        $jarak_terpendek[$simpul] = INF;
        $sebelumnya[$simpul] = null;
        $antrian[$simpul] = INF;
    }

    $jarak_terpendek[$awal] = 0;
    $antrian[$awal] = 0;

    while (!empty($antrian)) {
        $simpul_terdekat = array_search(min($antrian), $antrian);
        unset($antrian[$simpul_terdekat]);

        if ($simpul_terdekat == $tujuan) break;
        if (!isset($graf[$simpul_terdekat])) continue;

        foreach ($graf[$simpul_terdekat] as $tujuan_simpul => $biaya) {
            $alternatif = $jarak_terpendek[$simpul_terdekat] + $biaya;
            if ($alternatif < $jarak_terpendek[$tujuan_simpul]) {
                $jarak_terpendek[$tujuan_simpul] = $alternatif;
                $sebelumnya[$tujuan_simpul] = $simpul_terdekat;
                $antrian[$tujuan_simpul] = $alternatif;
            }
        }
    }

    // Bangun rute
    $rute = [];
    $simpul_sekarang = $tujuan;
    while (isset($sebelumnya[$simpul_sekarang])) {
        array_unshift($rute, $simpul_sekarang);
        $simpul_sekarang = $sebelumnya[$simpul_sekarang];
    }

    if (!empty($rute)) {
        array_unshift($rute, $awal);
    }

    return [$rute, $jarak_terpendek[$tujuan]];
}

if(isset($_GET['titik_awal']) && isset($_GET['titik_tujuan'])) {// ==============================
    // Input: ID awal & tujuan
    // ==============================
    $id_awal_asli = $_GET['titik_awal'];
    $id_tujuan    = $_GET['titik_tujuan'];

    $gunakan_titik_terdekat = false;

    // ==============================
    // Tangani input 'lokasi_sekarang'
    // ==============================
    if ($id_awal_asli === 'lokasi_sekarang') {
        $lat_user = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
        $lng_user = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

        if ($lat_user === null || $lng_user === null) {
            $pesan = "Latitude dan longitude dari lokasi sekarang harus disediakan.";
        }

        $id_terdekat = null;
        $jarak_terdekat = INF;

        foreach ($daftar_destinasi as $id => $data) {
            $lat = $data['lat'];
            $lng = $data['lng'];

            $jarak = haversine($lat_user, $lng_user, $lat, $lng);
            if ($jarak < $jarak_terdekat) {
                $jarak_terdekat = $jarak;
                $id_terdekat = $id;
            }
        }

        if ($id_terdekat === null) {
            $pesan = "Tidak ditemukan lokasi terdekat dari posisi saat ini.";
        }

        $pesan = "<p><strong>Lokasi saat ini terdeteksi terdekat ke:</strong> {$daftar_destinasi[$id_terdekat]['nama']}</p>";
        $id_awal_asli = $id_terdekat;
        $gunakan_titik_terdekat = true;
    }

    // ==============================
    // Validasi ID
    // ==============================
    if (!isset($daftar_destinasi[$id_awal_asli]) || !isset($daftar_destinasi[$id_tujuan])) {
        $pesan = "<p>destinasi tidak ditemukan.</p>";
    }

    $id_awal = $id_awal_asli;

    // Cek apakah titik awal punya koneksi langsung
    if (!isset($graf[$id_awal_asli]) || empty($graf[$id_awal_asli])) {
        $pesan = "<p><strong>Peringatan:</strong> Titik awal tidak memiliki jalur langsung. Mencari titik terdekat...</p>";

        $lat_awal = $daftar_destinasi[$id_awal_asli]['lat'];
        $lng_awal = $daftar_destinasi[$id_awal_asli]['lng'];

        $id_terdekat = null;
        $jarak_terdekat = INF;

        foreach ($graf as $id => $node) {
            if ($id == $id_awal_asli || empty($node)) continue;

            $lat = $daftar_destinasi[$id]['lat'];
            $lng = $daftar_destinasi[$id]['lng'];

            $jarak = haversine($lat_awal, $lng_awal, $lat, $lng);
            if ($jarak < $jarak_terdekat) {
                $jarak_terdekat = $jarak;
                $id_terdekat = $id;
            }
        }

        if ($id_terdekat === null) {
            $pesan = "Tidak ditemukan titik terdekat yang terhubung.";
        }

        $pesan .= "<p><strong>Titik awal diganti ke:</strong> {$daftar_destinasi[$id_terdekat]['nama']} (ID: $id_terdekat)</p>";
        $id_awal = $id_terdekat;
        $gunakan_titik_terdekat = true;
    }

    // ==============================
    // Hitung rute terpendek
    // ==============================
    list($rute_terpendek, $total_jarak) = dijkstra($graf, $id_awal, $id_tujuan);

    // Tambahkan titik awal asli jika sempat diganti
    if ($gunakan_titik_terdekat && $id_awal_asli !== $id_awal) {
        array_unshift($rute_terpendek, $id_awal_asli);
    }

    // ==============================
    // Tampilkan hasil
    // ==============================
    $rute = "<h3>Rute Terpendek dari <b>{$daftar_destinasi[$id_awal_asli]['nama']}</b> ke <b>{$daftar_destinasi[$id_tujuan]['nama']}</b>:</h3>";

    // if (empty($rute_terpendek)) {
    //     echo "Tidak ada rute yang tersedia.";
    // } else {
    //     echo "<ol>";
    //     foreach ($rute_terpendek as $id) {
    //         $nama = $daftar_destinasi[$id]['nama'];
    //         $lat  = $daftar_destinasi[$id]['lat'];
    //         $lng  = $daftar_destinasi[$id]['lng'];
    //         echo "<li><b>$nama</b> (Lat: $lat, Lng: $lng)</li>";
    //     }
    //     echo "</ol>";
    //     echo "<p><strong>Total Jarak:</strong> " . round($total_jarak, 2) . " km</p>";
    // }
} 
?>
