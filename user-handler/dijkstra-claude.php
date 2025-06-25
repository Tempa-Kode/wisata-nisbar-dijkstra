<?php
require_once '../config/db.php';

/**
 * Menghitung jarak menggunakan formula Haversine
 * @param float $lat1 Latitude titik 1
 * @param float $lon1 Longitude titik 1
 * @param float $lat2 Latitude titik 2
 * @param float $lon2 Longitude titik 2
 * @return float Jarak dalam kilometer
 */
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius bumi dalam km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + 
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;
    
    return $distance;
}

/**
 * Mencari destinasi terdekat dari lokasi user
 * @param PDO $pdo Koneksi database
 * @param float $userLat Latitude user
 * @param float $userLon Longitude user
 * @param int $excludeId ID destinasi yang ingin dikecualikan
 * @return array|null Destinasi terdekat
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
        $distance = haversineDistance(
            $userLat, $userLon, 
            $destination['latitude'], $destination['longitude']
        );
        
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $nearestDestination = $destination;
            $nearestDestination['distance_from_user'] = $distance;
        }
    }
    
    return $nearestDestination;
}

/**
 * Mencari destinasi terdekat dari destinasi tertentu yang memiliki koneksi dalam graf
 * @param PDO $pdo Koneksi database
 * @param array $graph Graf dalam bentuk adjacency list
 * @param int $destinationId ID destinasi
 * @return array|null Destinasi terdekat yang terhubung
 */
function findNearestConnectedDestination($pdo, $graph, $destinationId) {
    // Ambil koordinat destinasi asal
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM destinasi WHERE id = ?");
    $stmt->execute([$destinationId]);
    $originDestination = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$originDestination) {
        return null;
    }
    
    $nearestDestination = null;
    $minDistance = PHP_FLOAT_MAX;
    
    // Cari destinasi terdekat yang ada dalam graf
    foreach ($graph as $nodeId => $connections) {
        if ($nodeId == $destinationId) continue; // Skip destinasi asal
        
        // Ambil koordinat destinasi ini
        $stmt = $pdo->prepare("SELECT id, nama_destinasi, latitude, longitude, lokasi FROM destinasi WHERE id = ?");
        $stmt->execute([$nodeId]);
        $destination = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($destination) {
            $distance = haversineDistance(
                $originDestination['latitude'], $originDestination['longitude'],
                $destination['latitude'], $destination['longitude']
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestDestination = $destination;
                $nearestDestination['distance_from_origin'] = $distance;
            }
        }
    }
    
    return $nearestDestination;
}

/**
 * Mengambil semua data jarak antar destinasi dan bangun graf
 * @param PDO $pdo Koneksi database
 * @return array Graph dalam bentuk adjacency list
 */
function buildGraph($pdo) {
    $graph = [];
    
    // Ambil semua jarak antar destinasi
    $sql = "SELECT titik_awal, titik_tujuan, jarak_km FROM jarak_antar_destinasi";
    $stmt = $pdo->query($sql);
    $distances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($distances as $row) {
        $from = $row['titik_awal'];
        $to = $row['titik_tujuan'];
        $distance = $row['jarak_km'];
        
        // Graf tidak berarah (bidirectional)
        if (!isset($graph[$from])) {
            $graph[$from] = [];
        }
        if (!isset($graph[$to])) {
            $graph[$to] = [];
        }
        
        $graph[$from][$to] = $distance;
        $graph[$to][$from] = $distance; // Karena jarak bolak-balik sama
    }
    
    return $graph;
}

/**
 * Implementasi algoritma Dijkstra
 * @param array $graph Graf dalam bentuk adjacency list
 * @param int $start Node awal
 * @param int $end Node tujuan
 * @return array Hasil pencarian rute
 */
function dijkstra($graph, $start, $end) {
    // Validasi input
    if (!isset($graph[$start])) {
        return [
            'found' => false,
            'distance' => null,
            'path' => []
        ];
    }
    
    if (!isset($graph[$end])) {
        return [
            'found' => false,
            'distance' => null,
            'path' => []
        ];
    }
    
    // Inisialisasi
    $distances = [];
    $previous = [];
    $unvisited = [];
    
    // Set jarak awal ke semua node sebagai infinity
    foreach ($graph as $node => $neighbors) {
        $distances[$node] = PHP_FLOAT_MAX;
        $previous[$node] = null;
        $unvisited[$node] = true;
    }
    
    // Jarak ke node awal adalah 0
    $distances[$start] = 0;
    
    while (!empty($unvisited)) {
        // Cari node dengan jarak terkecil yang belum dikunjungi
        $currentNode = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($unvisited as $node => $value) {
            if ($distances[$node] < $minDistance) {
                $minDistance = $distances[$node];
                $currentNode = $node;
            }
        }
        
        // Jika tidak ada node yang bisa dikunjungi
        if ($currentNode === null) {
            break;
        }
        
        // Hapus node dari unvisited
        unset($unvisited[$currentNode]);
        
        // Jika sudah mencapai tujuan, hentikan
        if ($currentNode == $end) {
            break;
        }
        
        // Update jarak ke tetangga
        if (isset($graph[$currentNode])) {
            foreach ($graph[$currentNode] as $neighbor => $weight) {
                if (isset($unvisited[$neighbor])) {
                    $newDistance = $distances[$currentNode] + $weight;
                    
                    if ($newDistance < $distances[$neighbor]) {
                        $distances[$neighbor] = $newDistance;
                        $previous[$neighbor] = $currentNode;
                    }
                }
            }
        }
    }
    
    // Bangun path dari start ke end
    $path = [];
    $current = $end;
    
    while ($current !== null) {
        array_unshift($path, $current);
        
        // Validasi untuk mencegah undefined offset error
        if (!isset($previous[$current])) {
            // Jika current node tidak ada dalam $previous, berarti tidak ada path
            break;
        }
        
        $current = $previous[$current];
    }
    
    // Jika path tidak dimulai dari start, berarti tidak ada rute
    if (empty($path) || $path[0] != $start) {
        return [
            'found' => false,
            'distance' => null,
            'path' => []
        ];
    }
    
    // Validasi distance untuk end node
    $finalDistance = isset($distances[$end]) && $distances[$end] !== PHP_FLOAT_MAX ? $distances[$end] : null;
    
    return [
        'found' => true,
        'distance' => $finalDistance,
        'path' => $path
    ];
}

/**
 * Mendapatkan detail rute berdasarkan path
 * @param PDO $pdo Koneksi database
 * @param array $path Array ID destinasi
 * @return array Detail rute
 */
function getRouteDetails($pdo, $path) {
    $details = [];
    
    if (empty($path)) {
        return $details;
    }
    
    $placeholders = str_repeat('?,', count($path) - 1) . '?';
    $sql = "SELECT id, nama_destinasi, lokasi, latitude, longitude 
            FROM destinasi 
            WHERE id IN ($placeholders)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($path);
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Urutkan sesuai dengan urutan di path
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
 * @param PDO $pdo Koneksi database
 * @param mixed $titikAwal ID destinasi atau 'lokasi_sekarang'
 * @param int $titikTujuan ID destinasi tujuan
 * @param float $userLat Latitude user (jika titik awal = lokasi_sekarang)
 * @param float $userLon Longitude user (jika titik awal = lokasi_sekarang)
 * @return array Hasil pencarian rute
 */
function findRoute($pdo, $titikAwal, $titikTujuan, $userLat = null, $userLon = null) {
    $result = [
        'success' => false,
        'message' => '',
        'data' => []
    ];
    
    // Jika titik awal adalah lokasi sekarang
    if ($titikAwal === 'lokasi_sekarang') {
        if ($userLat === null || $userLon === null) {
            $result['message'] = 'Lokasi user tidak valid';
            return $result;
        }
        
        $nearestDestination = findNearestDestination($pdo, $userLat, $userLon);
        
        if (!$nearestDestination) {
            $result['message'] = 'Tidak dapat menemukan destinasi terdekat';
            return $result;
        }
        
        $titikAwal = $nearestDestination['id'];
        $result['data']['nearest_destination'] = $nearestDestination;
    }
    
    // Validasi titik tujuan
    if (empty($titikTujuan)) {
        $result['message'] = 'Titik tujuan harus dipilih';
        return $result;
    }
    
    // Jika titik awal sama dengan titik tujuan
    if ($titikAwal == $titikTujuan) {
        $result['message'] = 'Titik awal dan tujuan tidak boleh sama';
        return $result;
    }
    
    // Bangun graph dan cari rute
    $graph = buildGraph($pdo);
    
    // Coba cari rute langsung terlebih dahulu
    if (isset($graph[$titikAwal]) && isset($graph[$titikTujuan])) {
        $dijkstraResult = dijkstra($graph, $titikAwal, $titikTujuan);
        
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
            
            return $result;
        }
    }
    
    // Jika tidak ada rute langsung, cari rute alternatif
    // Cari destinasi terdekat dari titik awal yang memiliki koneksi dalam graf
    $nearestConnected = findNearestConnectedDestination($pdo, $graph, $titikAwal);
    
    if (!$nearestConnected) {
        $result['message'] = 'Tidak ada rute yang tersedia dari titik awal ke destinasi manapun';
        return $result;
    }
    
    // Coba cari rute dari destinasi terdekat ke tujuan
    $dijkstraResult = dijkstra($graph, $nearestConnected['id'], $titikTujuan);
    
    if ($dijkstraResult['found']) {
        // Dapatkan detail destinasi awal
        $stmt = $pdo->prepare("SELECT id, nama_destinasi, lokasi, latitude, longitude FROM destinasi WHERE id = ?");
        $stmt->execute([$titikAwal]);
        $originalStart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Gabungkan rute: titik awal -> destinasi terdekat -> path ke tujuan
        $fullPath = array_merge([$titikAwal], $dijkstraResult['path']);
        $routeDetails = getRouteDetails($pdo, $fullPath);
        
        // Hitung total jarak (jarak ke destinasi terdekat + jarak rute)
        $totalDistance = $nearestConnected['distance_from_origin'] + $dijkstraResult['distance'];
        
        $result['success'] = true;
        $result['message'] = 'Rute alternatif berhasil ditemukan';
        $result['data']['route'] = [
            'distance' => $totalDistance,
            'path' => $fullPath,
            'route_details' => $routeDetails,
            'route_type' => 'alternative'
        ];
        $result['data']['alternative_info'] = [
            'original_start' => $originalStart,
            'intermediate_destination' => $nearestConnected,
            'distance_to_intermediate' => $nearestConnected['distance_from_origin']
        ];
    } else {
        $result['message'] = 'Tidak ada rute yang tersedia antara titik awal dan tujuan, bahkan melalui rute alternatif';
    }
    
    return $result;
}

// Proses pencarian rute jika ada request
$routeResult = null;
if (isset($_GET['titik_awal']) && isset($_GET['titik_tujuan']) && 
    !empty($_GET['titik_awal']) && !empty($_GET['titik_tujuan'])) {
    
    try {
        $titikAwal = $_GET['titik_awal'];
        $titikTujuan = intval($_GET['titik_tujuan']);
        $userLat = isset($_GET['latitude']) && !empty($_GET['latitude']) ? floatval($_GET['latitude']) : null;
        $userLon = isset($_GET['longitude']) && !empty($_GET['longitude']) ? floatval($_GET['longitude']) : null;
        
        $routeResult = findRoute($pdo, $titikAwal, $titikTujuan, $userLat, $userLon);
        
    } catch (Exception $e) {
        $routeResult = [
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            'data' => []
        ];
    }
}

// Ambil data destinasi untuk dropdown
try {
    $stmt = $pdo->query("SELECT id, nama_destinasi FROM destinasi ORDER BY nama_destinasi");
    $destinasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $destinasi = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencari Rute Wisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-route"></i> Pencari Rute Wisata Terpendek</h4>
                    </div>
                    <div class="card-body">
                        <!-- Form Pencarian -->
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
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Hasil Pencarian -->
                        <?php if ($routeResult): ?>
                        <div class="mt-4">
                            <?php if ($routeResult['success']): ?>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> Rute Ditemukan!</h5>
                                
                                <!-- Informasi jenis rute -->
                                <?php if (isset($routeResult['data']['route']['route_type']) && $routeResult['data']['route']['route_type'] === 'alternative'): ?>
                                <div class="alert alert-info mb-3">
                                    <h6><i class="fas fa-info-circle"></i> Rute Alternatif</h6>
                                    <p class="mb-1">Tidak ada rute langsung dari titik awal ke tujuan. Sistem menemukan rute alternatif melalui destinasi terdekat:</p>
                                    <strong><?= htmlspecialchars($routeResult['data']['alternative_info']['intermediate_destination']['nama_destinasi']) ?></strong>
                                    <span class="badge bg-warning ms-2">Jarak ke destinasi perantara: <?= round($routeResult['data']['alternative_info']['distance_to_intermediate'], 2) ?> km</span>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Jika menggunakan lokasi sekarang -->
                                <?php if (isset($routeResult['data']['nearest_destination'])): ?>
                                <div class="mb-3">
                                    <h6><i class="fas fa-map-marker-alt"></i> Destinasi Terdekat dari Lokasi Anda:</h6>
                                    <p><strong><?= htmlspecialchars($routeResult['data']['nearest_destination']['nama_destinasi']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($routeResult['data']['nearest_destination']['lokasi'] ?? '') ?></small><br>
                                    <span class="badge bg-info">Jarak: <?= round($routeResult['data']['nearest_destination']['distance_from_user'], 2) ?> km</span>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <h6><i class="fas fa-road"></i> Detail Rute:</h6>
                                    <p><span class="badge bg-success fs-6">Total Jarak: <?= round($routeResult['data']['route']['distance'], 2) ?> km</span></p>
                                </div>
                                
                                <div class="route-details">
                                    <h6><i class="fas fa-list-ol"></i> Jalur Perjalanan:</h6>
                                    <div class="list-group">
                                        <?php foreach ($routeResult['data']['route']['route_details'] as $index => $destination): ?>
                                        <?php 
                                        $isStart = $index === 0;
                                        $isEnd = $index === count($routeResult['data']['route']['route_details']) - 1;
                                        $isIntermediate = isset($routeResult['data']['route']['route_type']) && 
                                                        $routeResult['data']['route']['route_type'] === 'alternative' && 
                                                        $index === 1;
                                        ?>
                                        <div class="list-group-item d-flex align-items-center">
                                            <div class="me-3">
                                                <?php if ($isStart): ?>
                                                <span class="badge bg-primary rounded-pill">Start</span>
                                                <?php elseif ($isEnd): ?>
                                                <span class="badge bg-danger rounded-pill">Finish</span>
                                                <?php elseif ($isIntermediate): ?>
                                                <span class="badge bg-warning rounded-pill">Transit</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill"><?= $isIntermediate ? 'T' : $index ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= htmlspecialchars($destination['nama_destinasi']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($destination['lokasi'] ?? '') ?></small>
                                                <?php if ($isIntermediate): ?>
                                                <br><small class="text-warning"><i class="fas fa-exchange-alt"></i> Destinasi perantara (rute alternatif)</small>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!$isEnd): ?>
                                            <div class="ms-2">
                                                <i class="fas fa-arrow-down text-primary"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const titikAwal = document.getElementById('titik_awal');
        const latitude = document.getElementById('latitude');
        const longitude = document.getElementById('longitude');
        
        // Handle pemilihan lokasi sekarang
        titikAwal.addEventListener('change', function() {
            if (this.value === 'lokasi_sekarang') {
                if (navigator.geolocation) {
                    // Tampilkan loading
                    this.disabled = true;
                    const originalText = this.options[this.selectedIndex].text;
                    this.options[this.selectedIndex].text = '📍 Mendapatkan lokasi...';
                    
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            latitude.value = position.coords.latitude;
                            longitude.value = position.coords.longitude;
                            
                            // Restore text
                            titikAwal.options[titikAwal.selectedIndex].text = originalText;
                            titikAwal.disabled = false;
                            
                            console.log('Lokasi berhasil didapat:', position.coords.latitude, position.coords.longitude);
                        },
                        function(error) {
                            alert('Gagal mendapatkan lokasi: ' + error.message);
                            titikAwal.value = '';
                            titikAwal.options[titikAwal.selectedIndex].text = originalText;
                            titikAwal.disabled = false;
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 60000
                        }
                    );
                } else {
                    alert('Browser Anda tidak mendukung geolocation');
                    this.value = '';
                }
            }
        });
        
        // Set selected values from URL
        <?php if (isset($_GET['titik_awal']) && $_GET['titik_awal'] === 'lokasi_sekarang'): ?>
        titikAwal.value = 'lokasi_sekarang';
        <?php if (isset($_GET['latitude']) && isset($_GET['longitude'])): ?>
        latitude.value = <?= $_GET['latitude'] ?>;
        longitude.value = <?= $_GET['longitude'] ?>;
        <?php endif; ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>