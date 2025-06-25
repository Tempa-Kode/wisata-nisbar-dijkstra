<?php
require_once '../config/db.php';

/**
 * Menghitung jarak menggunakan formula Haversine
 */
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius bumi dalam kilometer
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;

    return $distance;
}

/**
 * Mencari destinasi terdekat dari lokasi user
 */
function findNearestDestination($pdo, $userLat, $userLon, $excludeId = null) {
    $sql = "SELECT id, nama_destinasi, latitude, longitude, lokasi FROM destinasi";
    if ($excludeId !== null) {
        $sql .= " WHERE id != :exclude_id";
    }
    $stmt = $pdo->prepare($sql);
    if ($excludeId !== null) {
        $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
    }
    $stmt->execute();
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nearestDestination = null;
    $minDistance = PHP_FLOAT_MAX;

    foreach ($destinations as $destination) {
        $distance = haversineDistance($userLat, $userLon, $destination['latitude'], $destination['longitude']);
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $nearestDestination = $destination;
            $nearestDestination['distance_from_user'] = $distance; // Tambahkan jarak dari user
        }
    }
    return $nearestDestination;
}

/**
 * Mencari destinasi terdekat dari destinasi tertentu yang memiliki koneksi dalam graf
 */
function findNearestConnectedDestination($pdo, $graph, $destinationId) {
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM destinasi WHERE id = ?");
    $stmt->execute([$destinationId]);
    $originDestination = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$originDestination) {
        return null;
    }

    $nearestConnected = null;
    $minDistance = PHP_FLOAT_MAX;

    // Iterasi melalui semua node di graph
    foreach ($graph as $nodeId => $connections) {
        // Hanya pertimbangkan node yang terhubung langsung dengan destinationId
        if ($nodeId == $destinationId && !empty($connections)) {
            // Jika destinationId punya koneksi, cari yang terdekat di antara koneksi tersebut
            foreach ($connections as $connectedNodeId => $weight) {
                $stmt = $pdo->prepare("SELECT id, nama_destinasi, latitude, longitude, lokasi FROM destinasi WHERE id = ?");
                $stmt->execute([$connectedNodeId]);
                $destination = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($destination) {
                    $distance = haversineDistance($originDestination['latitude'], $originDestination['longitude'], $destination['latitude'], $destination['longitude']);
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $nearestConnected = $destination;
                        $nearestConnected['distance_from_origin'] = $distance;
                    }
                }
            }
            break; // Setelah menemukan koneksi terdekat dari destinationId, kita bisa berhenti
        }
    }
    
    // Jika destinasi awal tidak ada di graph atau tidak memiliki koneksi
    if (!isset($graph[$destinationId]) || empty($graph[$destinationId])) {
        // Cari destinasi terdekat di seluruh graph yang memiliki koneksi ke node lain
        $stmtAll = $pdo->prepare("SELECT id, nama_destinasi, latitude, longitude, lokasi FROM destinasi");
        $stmtAll->execute();
        $allDestinations = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

        foreach ($allDestinations as $dest) {
            if ($dest['id'] == $destinationId) continue; // Jangan bandingkan dengan dirinya sendiri

            // Pastikan destinasi ini ada di graf dan memiliki koneksi
            if (isset($graph[$dest['id']]) && !empty($graph[$dest['id']])) {
                $distance = haversineDistance($originDestination['latitude'], $originDestination['longitude'], $dest['latitude'], $dest['longitude']);
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestConnected = $dest;
                    $nearestConnected['distance_from_origin'] = $distance;
                }
            }
        }
    }
    
    return $nearestConnected;
}


/**
 * Mengambil semua data jarak antar destinasi dan bangun graf
 */
function buildGraph($pdo) {
    $graph = [];
    $sql = "SELECT titik_awal, titik_tujuan, jarak_km FROM jarak_antar_destinasi";
    $stmt = $pdo->query($sql);
    $distances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($distances as $row) {
        $from = $row['titik_awal'];
        $to = $row['titik_tujuan'];
        $distance = $row['jarak_km'];

        // Inisialisasi node jika belum ada
        if (!isset($graph[$from])) {
            $graph[$from] = [];
        }
        if (!isset($graph[$to])) {
            $graph[$to] = [];
        }

        // Tambahkan edge (sisi) ke graf (bidirectional)
        $graph[$from][$to] = $distance;
        $graph[$to][$from] = $distance;
    }
    return $graph;
}

/**
 * Implementasi algoritma Dijkstra
 */
function dijkstra($graph, $start, $end) {
    // Pastikan titik awal dan akhir ada di graf
    if (!isset($graph[$start]) || !isset($graph[$end])) {
        return ['found' => false, 'distance' => null, 'path' => []];
    }

    $distances = []; // Jarak terpendek dari start ke setiap node
    $previous = [];  // Node sebelumnya dalam jalur terpendek
    $unvisited = []; // Set node yang belum dikunjungi

    // Inisialisasi semua jarak ke tak terhingga dan previous ke null
    foreach ($graph as $node => $neighbors) {
        $distances[$node] = PHP_FLOAT_MAX;
        $previous[$node] = null;
        $unvisited[$node] = true;
    }

    // Jarak dari start ke start adalah 0
    $distances[$start] = 0;

    while (!empty($unvisited)) {
        // Cari node dengan jarak terkecil di antara yang belum dikunjungi
        $currentNode = null;
        $minDistance = PHP_FLOAT_MAX;
        foreach ($unvisited as $node => $value) {
            if ($distances[$node] < $minDistance) {
                $minDistance = $distances[$node];
                $currentNode = $node;
            }
        }

        // Jika tidak ada node yang bisa dijangkau atau sudah sampai tujuan
        if ($currentNode === null) {
            break;
        }

        // Hapus node saat ini dari set yang belum dikunjungi
        unset($unvisited[$currentNode]);

        // Jika sudah sampai node tujuan, berhenti
        if ($currentNode == $end) {
            break;
        }

        // Perbarui jarak ke tetangga
        if (isset($graph[$currentNode])) {
            foreach ($graph[$currentNode] as $neighbor => $weight) {
                // Hanya pertimbangkan tetangga yang belum dikunjungi
                if (isset($unvisited[$neighbor])) {
                    $newDistance = $distances[$currentNode] + $weight;

                    // Jika jalur baru lebih pendek
                    if ($newDistance < $distances[$neighbor]) {
                        $distances[$neighbor] = $newDistance;
                        $previous[$neighbor] = $currentNode;
                    }
                }
            }
        }
    }

    // Rekonstruksi jalur
    $path = [];
    $current = $end;
    while ($current !== null) {
        array_unshift($path, $current);
        if (!isset($previous[$current])) { // Hindari loop tak terbatas jika tidak ada jalur
            break;
        }
        $current = $previous[$current];
    }

    // Cek apakah jalur ditemukan dan dimulai dari titik awal yang benar
    if (empty($path) || $path[0] != $start) {
        return ['found' => false, 'distance' => null, 'path' => []];
    }

    $finalDistance = isset($distances[$end]) && $distances[$end] !== PHP_FLOAT_MAX ? $distances[$end] : null;

    return ['found' => true, 'distance' => $finalDistance, 'path' => $path];
}

/**
 * Mendapatkan detail rute berdasarkan path
 */
function getRouteDetails($pdo, $path) {
    if (empty($path)) {
        return [];
    }

    // Buat placeholder untuk query IN
    $placeholders = str_repeat('?,', count($path) - 1) . '?';
    $sql = "SELECT id, nama_destinasi, lokasi, latitude, longitude FROM destinasi WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($path);
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Urutkan detail destinasi sesuai dengan urutan di path
    $orderedDetails = [];
    foreach ($path as $id) {
        foreach ($destinations as $dest) {
            if ($dest['id'] == $id) {
                $orderedDetails[] = $dest;
                break;
            }
        }
    }
    return $orderedDetails;
}


/**
 * Fungsi utama untuk mencari rute dengan rute alternatif
 */
function findRoute($pdo, $titikAwal, $titikTujuan, $userLat = null, $userLon = null) {
    $result = ['success' => false, 'message' => '', 'data' => []];

    // Simpan koordinat awal yang dipilih (bisa berupa lokasi_sekarang atau ID destinasi)
    $startLatLng = null;
    $endLatLng = null;

    // Ambil detail titik tujuan
    $stmtTujuan = $pdo->prepare("SELECT id, nama_destinasi, lokasi, latitude, longitude FROM destinasi WHERE id = ?");
    $stmtTujuan->execute([$titikTujuan]);
    $targetDestination = $stmtTujuan->fetch(PDO::FETCH_ASSOC);
    if (!$targetDestination) {
        $result['message'] = 'Titik tujuan tidak ditemukan.';
        return $result;
    }
    $result['data']['target_destination_details'] = $targetDestination;
    $endLatLng = ['latitude' => $targetDestination['latitude'], 'longitude' => $targetDestination['longitude']];


    if ($titikAwal === 'lokasi_sekarang') {
        if ($userLat === null || $userLon === null) {
            $result['message'] = 'Lokasi user tidak valid';
            return $result;
        }
        $result['data']['user_location'] = ['latitude' => $userLat, 'longitude' => $userLon];
        $startLatLng = ['latitude' => $userLat, 'longitude' => $userLon];

        $nearestDestination = findNearestDestination($pdo, $userLat, $userLon);
        if (!$nearestDestination) {
            $result['message'] = 'Tidak dapat menemukan destinasi terdekat dari lokasi Anda.';
            return $result;
        }
        $titikAwalGraph = $nearestDestination['id']; // ID destinasi terdekat untuk perhitungan Dijkstra
        $result['data']['nearest_destination'] = $nearestDestination; // Detail destinasi terdekat
        $result['data']['start_destination_details_for_display'] = $nearestDestination; // Untuk ditampilkan di info rute
        
    } else {
        $titikAwalGraph = intval($titikAwal); // Gunakan ID destinasi langsung untuk Dijkstra
        $stmt = $pdo->prepare("SELECT id, nama_destinasi, lokasi, latitude, longitude FROM destinasi WHERE id = ?");
        $stmt->execute([$titikAwalGraph]);
        $selectedStartDestination = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$selectedStartDestination) {
            $result['message'] = 'Titik awal tidak ditemukan.';
            return $result;
        }
        $result['data']['start_destination_details'] = $selectedStartDestination; // Detail titik awal yang dipilih
        $result['data']['start_destination_details_for_display'] = $selectedStartDestination; // Untuk ditampilkan di info rute
        $startLatLng = ['latitude' => $selectedStartDestination['latitude'], 'longitude' => $selectedStartDestination['longitude']];
    }

    if (empty($titikTujuan)) {
        $result['message'] = 'Titik tujuan harus dipilih';
        return $result;
    }
    if ($titikAwalGraph == $titikTujuan) {
        $result['message'] = 'Titik awal dan tujuan tidak boleh sama';
        return $result;
    }

    $graph = buildGraph($pdo);

    // Coba cari rute langsung dari titik awal yang digunakan di graf ke titik tujuan
    if (isset($graph[$titikAwalGraph]) && isset($graph[$titikTujuan])) {
        $dijkstraResult = dijkstra($graph, $titikAwalGraph, $titikTujuan);

        if ($dijkstraResult['found']) {
            $routeDetails = getRouteDetails($pdo, $dijkstraResult['path']);
            $result['success'] = true;
            $result['message'] = 'Rute berhasil ditemukan';
            $result['data']['route'] = [
                'distance' => $dijkstraResult['distance'],
                'path' => $dijkstraResult['path'],
                'route_details' => $routeDetails,
                'route_type' => 'direct'
            ];
            // Tambahkan koordinat untuk Leaflet Routing Machine
            $result['data']['waypoints_for_map'] = [];
            if ($titikAwal === 'lokasi_sekarang' && isset($result['data']['user_location'])) {
                // Tambahkan lokasi user sebagai waypoint pertama
                $result['data']['waypoints_for_map'][] = $result['data']['user_location'];
                // Kemudian destinasi terdekat sebagai waypoint kedua (yang merupakan start di Dijkstra)
                $result['data']['waypoints_for_map'][] = ['latitude' => $nearestDestination['latitude'], 'longitude' => $nearestDestination['longitude']];
            } else {
                // Jika titik awal bukan lokasi_sekarang, tambahkan titik awal yang dipilih
                $result['data']['waypoints_for_map'][] = ['latitude' => $selectedStartDestination['latitude'], 'longitude' => $selectedStartDestination['longitude']];
            }

            foreach($routeDetails as $detail) {
                // Pastikan tidak menambahkan duplikat jika nearest_destination sama dengan routeDetails[0]
                // atau jika start_destination_details sama dengan routeDetails[0]
                $isDuplicate = false;
                if ($titikAwal === 'lokasi_sekarang' && $detail['id'] == $nearestDestination['id'] && count($result['data']['waypoints_for_map']) > 1 && $result['data']['waypoints_for_map'][1]['latitude'] == $detail['latitude']) {
                    $isDuplicate = true;
                } elseif ($titikAwal !== 'lokasi_sekarang' && $detail['id'] == $selectedStartDestination['id'] && count($result['data']['waypoints_for_map']) > 0 && $result['data']['waypoints_for_map'][0]['latitude'] == $detail['latitude']) {
                     $isDuplicate = true;
                }

                if (!$isDuplicate) {
                    $result['data']['waypoints_for_map'][] = ['latitude' => $detail['latitude'], 'longitude' => $detail['longitude']];
                }
            }
            return $result;
        }
    }

    // Jika rute langsung tidak ditemukan, coba rute alternatif
    $nearestConnected = findNearestConnectedDestination($pdo, $graph, $titikAwalGraph);

    if (!$nearestConnected) {
        $result['message'] = 'Tidak ada rute yang tersedia dari titik awal ke destinasi manapun.';
        return $result;
    }

    // Cari rute dari nearestConnected ke titik tujuan
    $dijkstraResult = dijkstra($graph, $nearestConnected['id'], $titikTujuan);

    if ($dijkstraResult['found']) {
        // Gabungkan jalur: titikAwal (jika beda dengan nearestConnected) -> nearestConnected -> jalur_dijkstra
        $fullPath = [];
        // Hanya tambahkan titikAwal jika berbeda dari nearestConnected
        if ($titikAwalGraph != $nearestConnected['id']) {
            $fullPath[] = $titikAwalGraph;
        }
        $fullPath = array_merge($fullPath, $dijkstraResult['path']);

        $routeDetails = getRouteDetails($pdo, $fullPath);

        // Hitung total jarak
        $totalDistance = 0;
        if ($titikAwalGraph != $nearestConnected['id']) {
            // Jarak dari titik awal asli ke nearestConnected
            $totalDistance += haversineDistance(
                ($titikAwal === 'lokasi_sekarang' && isset($userLat)) ? $userLat : $selectedStartDestination['latitude'],
                ($titikAwal === 'lokasi_sekarang' && isset($userLon)) ? $userLon : $selectedStartDestination['longitude'],
                $nearestConnected['latitude'],
                $nearestConnected['longitude']
            );
        }
        $totalDistance += $dijkstraResult['distance'];

        $result['success'] = true;
        $result['message'] = 'Rute alternatif berhasil ditemukan.';
        $result['data']['route'] = [
            'distance' => $totalDistance,
            'path' => $fullPath,
            'route_details' => $routeDetails,
            'route_type' => 'alternative'
        ];
        $result['data']['alternative_info'] = [
            'original_start' => ($titikAwal === 'lokasi_sekarang' && isset($result['data']['user_location'])) ? $result['data']['user_location'] : $selectedStartDestination,
            'intermediate_destination' => $nearestConnected,
            'distance_to_intermediate' => ($titikAwalGraph != $nearestConnected['id']) ? haversineDistance(
                ($titikAwal === 'lokasi_sekarang' && isset($userLat)) ? $userLat : $selectedStartDestination['latitude'],
                ($titikAwal === 'lokasi_sekarang' && isset($userLon)) ? $userLon : $selectedStartDestination['longitude'],
                $nearestConnected['latitude'],
                $nearestConnected['longitude']
            ) : 0
        ];
        
        // Siapkan waypoints untuk Leaflet Routing Machine
        $result['data']['waypoints_for_map'] = [];
        if ($titikAwal === 'lokasi_sekarang' && isset($result['data']['user_location'])) {
            $result['data']['waypoints_for_map'][] = $result['data']['user_location'];
        } else if (isset($selectedStartDestination)) {
            $result['data']['waypoints_for_map'][] = ['latitude' => $selectedStartDestination['latitude'], 'longitude' => $selectedStartDestination['longitude']];
        }

        foreach($routeDetails as $detail) {
             $result['data']['waypoints_for_map'][] = ['latitude' => $detail['latitude'], 'longitude' => $detail['longitude']];
        }

    } else {
        $result['message'] = 'Tidak ada rute yang tersedia antara titik awal dan tujuan, bahkan melalui rute alternatif.';
    }
    return $result;
}

// Proses pencarian rute jika ada request
$routeResult = null;
if (isset($_GET['titik_awal']) && isset($_GET['titik_tujuan']) && !empty($_GET['titik_awal']) && !empty($_GET['titik_tujuan'])) {
    try {
        $titikAwal = $_GET['titik_awal'];
        $titikTujuan = intval($_GET['titik_tujuan']);
        $userLat = isset($_GET['latitude']) && !empty($_GET['latitude']) ? floatval($_GET['latitude']) : null;
        $userLon = isset($_GET['longitude']) && !empty($_GET['longitude']) ? floatval($_GET['longitude']) : null;
        $routeResult = findRoute($pdo, $titikAwal, $titikTujuan, $userLat, $userLon);
    } catch (Exception $e) {
        $routeResult = ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage(), 'data' => []];
    }
}

// Ambil data destinasi untuk dropdown
$destinasi = $pdo->query("SELECT id, nama_destinasi FROM destinasi ORDER BY nama_destinasi")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencari Rute Wisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <style>
        #map { height: 450px; width: 100%; border-radius: .25rem; margin-top: 1rem; border: 1px solid #dee2e6; }
        /* Optional: hide Leaflet Routing Machine's default instructions if you prefer your own list */
        .leaflet-routing-container.leaflet-control {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h4 class="mb-0"><i class="fas fa-route"></i> Pencari Rute Wisata Terpendek</h4></div>
                    <div class="card-body">
                        <form action="" method="get" class="mt-4">
                            <input type="number" name="latitude" id="latitude" step="any" hidden>
                            <input type="number" name="longitude" id="longitude" step="any" hidden>
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="titik_awal" class="form-label">Titik Awal</label>
                                    <select class="form-select" name="titik_awal" id="titik_awal" required>
                                        <option value="" selected hidden>Pilih titik awal</option>
                                        <option value="lokasi_sekarang" id="lokasi_sekarang">📍 Lokasi saat ini</option>
                                        <?php foreach ($destinasi as $d) : ?>
                                        <option value="<?= $d['id'] ?>" <?= (isset($_GET['titik_awal']) && $_GET['titik_awal'] == $d['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['nama_destinasi']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="titik_tujuan" class="form-label">Titik Tujuan</label>
                                    <select class="form-select" name="titik_tujuan" id="titik_tujuan" required>
                                           <option value="" selected hidden>Pilih titik tujuan</option>
                                        <?php foreach ($destinasi as $d) : ?>
                                        <option value="<?= $d['id'] ?>" <?= (isset($_GET['titik_tujuan']) && $_GET['titik_tujuan'] == $d['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['nama_destinasi']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Cari</button>
                                </div>
                            </div>
                        </form>

                        <?php if ($routeResult): ?>
                        <div class="mt-4">
                            <?php if ($routeResult['success']): ?>
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle"></i> Rute Ditemukan!</h5>
                                    Total estimasi jarak: <strong><?= round($routeResult['data']['route']['distance'], 2) ?> km</strong>
                                </div>

                                <div class="mb-3">
                                    <h6><i class="fas fa-map-marker-alt"></i> Titik Awal Anda</h6>
                                    <?php if (isset($routeResult['data']['user_location'])): ?>
                                        <p class="mb-0">
                                            Dari: <strong>Lokasi Saat Ini</strong>.
                                            <br>
                                            <small class="text-muted">Perjalanan dimulai dari destinasi terdekat yaitu <strong><?= htmlspecialchars($routeResult['data']['nearest_destination']['nama_destinasi']) ?></strong> (sekitar <?= round($routeResult['data']['nearest_destination']['distance_from_user'], 2) ?> km dari Anda).</small>
                                        </p>
                                    <?php elseif (isset($routeResult['data']['start_destination_details'])): ?>
                                        <p class="mb-0">
                                            Dari: <strong><?= htmlspecialchars($routeResult['data']['start_destination_details']['nama_destinasi']) ?></strong>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($routeResult['data']['route']['route_type']) && $routeResult['data']['route']['route_type'] === 'alternative'): ?>
                                <div class="alert alert-info mb-3">
                                    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Ini adalah Rute Alternatif</h6>
                                    <p class="mb-1">Tidak ada rute langsung. Sistem mengarahkan Anda melalui <strong><?= htmlspecialchars($routeResult['data']['alternative_info']['intermediate_destination']['nama_destinasi']) ?></strong> (sekitar <?= round($routeResult['data']['alternative_info']['distance_to_intermediate'], 2) ?> km dari titik awal Anda).</p>
                                </div>
                                <?php endif; ?>
                                
                                <div id="map"></div>

                                <div class="mt-4">
                                    <h6><i class="fas fa-list-ol"></i> Jalur Perjalanan:</h6>
                                    <div class="list-group">
                                        <?php 
                                        $displayRouteDetails = $routeResult['data']['route']['route_details'];
                                        // Jika ada lokasi_sekarang, tambahkan "Lokasi Saat Ini" di awal daftar jalur
                                        if (isset($routeResult['data']['user_location'])) {
                                            array_unshift($displayRouteDetails, [
                                                'id' => 'user_loc',
                                                'nama_destinasi' => 'Lokasi Saat Ini',
                                                'latitude' => $routeResult['data']['user_location']['latitude'],
                                                'longitude' => $routeResult['data']['user_location']['longitude'],
                                                'lokasi' => 'Lokasi GPS Anda'
                                            ]);
                                        }
                                        ?>
                                        <?php foreach ($displayRouteDetails as $index => $destination): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge bg-secondary me-2"><?= $index + 1 ?></span>
                                                    <strong><?= htmlspecialchars($destination['nama_destinasi']) ?></strong>
                                                </div>
                                                <div>
                                                <?php 
                                                    if ($destination['id'] === 'user_loc') {
                                                        echo '<span class="badge bg-primary">Lokasi Anda</span>';
                                                    } else if ($index === 0 && !isset($routeResult['data']['user_location'])) {
                                                        echo '<span class="badge bg-primary">Titik Mulai Rute</span>';
                                                    } else if ($index === count($displayRouteDetails) - 1) {
                                                        echo '<span class="badge bg-danger">Tujuan Akhir</span>';
                                                    } else if ($routeResult['data']['route']['route_type'] === 'alternative' && isset($routeResult['data']['alternative_info']['intermediate_destination']) && $destination['id'] == $routeResult['data']['alternative_info']['intermediate_destination']['id']) {
                                                         echo '<span class="badge bg-info">Titik Perantara</span>';
                                                    }
                                                ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <h5><i class="fas fa-exclamation-triangle"></i> Rute Tidak Ditemukan</h5>
                                    <p><?= htmlspecialchars($routeResult['message']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const titikAwal = document.getElementById('titik_awal');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');

        // Geolocation handler
        titikAwal.addEventListener('change', function() {
            if (this.value === 'lokasi_sekarang') {
                if (navigator.geolocation) {
                    this.disabled = true;
                    const originalText = this.options[this.selectedIndex].text;
                    this.options[this.selectedIndex].text = '📍 Mendapatkan lokasi...';
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            latitudeInput.value = position.coords.latitude;
                            longitudeInput.value = position.coords.longitude;
                            titikAwal.options[titikAwal.selectedIndex].text = originalText;
                            titikAwal.disabled = false;
                        },
                        function(error) {
                            alert('Gagal mendapatkan lokasi: ' + error.message);
                            titikAwal.value = '';
                            latitudeInput.value = '';
                            longitudeInput.value = '';
                            titikAwal.options[titikAwal.selectedIndex].text = originalText;
                            titikAwal.disabled = false;
                        },
                        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                    );
                } else {
                    alert('Browser Anda tidak mendukung geolocation.');
                    this.value = '';
                }
            } else {
                // Clear lat/lon if not 'lokasi_sekarang'
                latitudeInput.value = '';
                longitudeInput.value = '';
            }
        });

        // Set initial state for geolocation if already selected
        <?php if (isset($_GET['titik_awal']) && $_GET['titik_awal'] === 'lokasi_sekarang' && isset($_GET['latitude']) && isset($_GET['longitude'])): ?>
        titikAwal.value = 'lokasi_sekarang';
        latitudeInput.value = <?= $_GET['latitude'] ?>;
        longitudeInput.value = <?= $_GET['longitude'] ?>;
        // Optionally, if you want to keep the "Mendapatkan lokasi..." text on reload, you could set it here
        // or just let it show the default selected option as it's already set by PHP.
        <?php endif; ?>

        // --- Logika untuk Peta Leaflet dengan Leaflet Routing Machine ---
        <?php if ($routeResult && $routeResult['success']): ?>
        const resultData = <?= json_encode($routeResult['data']) ?>;
        const waypointsForMap = resultData.waypoints_for_map;

        const map = L.map('map');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const leafletWaypoints = waypointsForMap.map(wp => L.latLng(wp.latitude, wp.longitude));

        if (leafletWaypoints.length > 1) {
            L.Routing.control({
                waypoints: leafletWaypoints,
                routeWhileDragging: true,
                // geocoder: L.Control.Geocoder.nominatim(), // Uncomment if you add leaflet-control-geocoder.js
                showAlternatives: true,
                fitSelectedRoutes: 'smart',
                plan: L.Routing.plan(leafletWaypoints, {
                    createMarker: function(i, wp, n) {
                        let marker;
                        let popupText = '';
                        
                        // Find the corresponding detail from routeDetails based on waypoint index
                        let originalDetail;
                        if (resultData.user_location && i === 0) {
                            popupText = '<b>📍 Lokasi Anda</b>';
                        } else {
                            // Adjust index to match routeDetails for display purposes
                            let routeDetailIndex = i;
                            if (resultData.user_location) { // If user_location was added as first waypoint
                                routeDetailIndex = i - 1; 
                            }
                            
                            if (resultData.route.route_details[routeDetailIndex]) {
                                originalDetail = resultData.route.route_details[routeDetailIndex];
                                popupText = `<b>${originalDetail.nama_destinasi}</b>`;
                                if (i === 0 && !resultData.user_location) { // Starting point if not user_location
                                    popupText += '<br><small>Titik Mulai Rute</small>';
                                } else if (i === n - 1) { // End point
                                    popupText += '<br><small>Tujuan Akhir</small>';
                                } else if (resultData.route.route_type === 'alternative' && originalDetail.id == resultData.alternative_info.intermediate_destination.id) {
                                    popupText += '<br><small>Titik Perantara Rute Alternatif</small>';
                                }
                            } else {
                                // Fallback if no matching detail (e.g., if there's a point very close to user_location)
                                popupText = `<b>Waypoint ${i+1}</b>`;
                            }
                        }

                        marker = L.marker(wp.latLng);
                        marker.bindPopup(popupText);
                        
                        return marker;
                    }
                })
            }).addTo(map);
        } else if (leafletWaypoints.length === 1) {
            // If only one waypoint, just set view and add a single marker
            map.setView(leafletWaypoints[0], 13);
            let singleMarkerText = '';
            if (resultData.user_location && waypointsForMap[0].latitude === resultData.user_location.latitude) {
                singleMarkerText = '<b>📍 Lokasi Anda</b>';
            } else if (resultData.start_destination_details) {
                singleMarkerText = `<b>${resultData.start_destination_details.nama_destinasi}</b>`;
            }
            L.marker(leafletWaypoints[0]).addTo(map).bindPopup(singleMarkerText).openPopup();
        } else {
            // Default view if no waypoints
            map.setView([0.5, 98.7], 8); // Example: Centered on North Sumatra / Medan area
        }
        <?php else: ?>
            // Default map view when no route is displayed (e.g., on initial load or no route found)
            const map = L.map('map').setView([0.5, 98.7], 8); // Example: Centered on North Sumatra / Medan area
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
        <?php endif; ?>
    });
    </script>
</body>
</html>