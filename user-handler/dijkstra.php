<?php
/**
 * Priority Queue sederhana berbasis SPL MinHeap
 */
class MinHeap extends SplMinHeap {
    #[\ReturnTypeWillChange]
    protected function compare($value1, $value2) {
        // Urutkan berdasarkan jarak (distance) terkecil
        return $value2['distance'] <=> $value1['distance'];
    }
}

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
        // $unvisited[$node] = true;
    }

    // Jarak dari start ke start adalah 0
    $distances[$start] = 0;

    $queue = new MinHeap();
    $queue->insert(['node' => $start, 'distance' => 0]);

    // while (!empty($unvisited)) {
    //     // Cari node dengan jarak terkecil di antara yang belum dikunjungi
    //     $currentNode = null;
    //     $minDistance = PHP_FLOAT_MAX;
    //     foreach ($unvisited as $node => $value) {
    //         if ($distances[$node] < $minDistance) {
    //             $minDistance = $distances[$node];
    //             $currentNode = $node;
    //         }
    //     }

    //     // Jika tidak ada node yang bisa dijangkau atau sudah sampai tujuan
    //     if ($currentNode === null) {
    //         break;
    //     }

    //     // Hapus node saat ini dari set yang belum dikunjungi
    //     unset($unvisited[$currentNode]);

    //     // Jika sudah sampai node tujuan, berhenti
    //     if ($currentNode == $end) {
    //         break;
    //     }

    //     // Perbarui jarak ke tetangga
    //     if (isset($graph[$currentNode])) {
    //         foreach ($graph[$currentNode] as $neighbor => $weight) {
    //             // Hanya pertimbangkan tetangga yang belum dikunjungi
    //             if (isset($unvisited[$neighbor])) {
    //                 $newDistance = $distances[$currentNode] + $weight;

    //                 // Jika jalur baru lebih pendek
    //                 if ($newDistance < $distances[$neighbor]) {
    //                     $distances[$neighbor] = $newDistance;
    //                     $previous[$neighbor] = $currentNode;
    //                 }
    //             }
    //         }
    //     }
    // }
    while (!$queue->isEmpty()) {
        $current = $queue->extract();
        $currentNode = $current['node'];
        if (isset($visited[$currentNode])) continue;
        $visited[$currentNode] = true;

        if ($currentNode == $end) break;

        foreach ($graph[$currentNode] as $neighbor => $weight) {
            if (isset($visited[$neighbor])) continue;
            $newDistance = $distances[$currentNode] + $weight;
            if ($newDistance < $distances[$neighbor]) {
                $distances[$neighbor] = $newDistance;
                $previous[$neighbor] = $currentNode;
                $queue->insert(['node' => $neighbor, 'distance' => $newDistance]);
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
 * Fungsi untuk mencari beberapa rute alternatif
 */
function findMultipleRoutes($pdo, $titikAwal, $titikTujuan, $userLat = null, $userLon = null, $maxRoutes = 3) {
    $graph = buildGraph($pdo);
    $routes = [];
    
    // Determine the actual start node for graph calculations
    $titikAwalGraph = null;
    $startDestination = null;
    
    if ($titikAwal === 'lokasi_sekarang') {
        if ($userLat === null || $userLon === null) {
            return ['success' => false, 'message' => 'Lokasi user tidak valid', 'data' => []];
        }
        $nearestDestination = findNearestDestination($pdo, $userLat, $userLon);
        if (!$nearestDestination) {
            return ['success' => false, 'message' => 'Tidak dapat menemukan destinasi terdekat dari lokasi Anda.', 'data' => []];
        }
        $titikAwalGraph = $nearestDestination['id'];
        $startDestination = $nearestDestination;
    } else {
        $titikAwalGraph = intval($titikAwal);
        $stmt = $pdo->prepare("SELECT id, nama_destinasi, lokasi, latitude, longitude FROM destinasi WHERE id = ?");
        $stmt->execute([$titikAwalGraph]);
        $startDestination = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$startDestination) {
            return ['success' => false, 'message' => 'Titik awal tidak ditemukan.', 'data' => []];
        }
    }

    if ($titikAwalGraph == $titikTujuan) {
        return ['success' => false, 'message' => 'Titik awal dan tujuan tidak boleh sama', 'data' => []];
    }

    // Rute pertama: rute terpendek langsung
    $firstRoute = dijkstra($graph, $titikAwalGraph, $titikTujuan);
    if ($firstRoute['found']) {
        $routeDetails = getRouteDetails($pdo, $firstRoute['path']);
        $routes[] = [
            'distance' => $firstRoute['distance'],
            'path' => $firstRoute['path'],
            'route_details' => $routeDetails,
            'route_type' => 'direct',
            'route_name' => 'Rute Terpendek'
        ];
    }

    // Rute alternatif 1: hapus edge pertama dari rute terpendek
    if ($firstRoute['found'] && count($firstRoute['path']) > 2 && count($routes) < $maxRoutes) {
        $altGraph1 = $graph;
        $from = $firstRoute['path'][0];
        $to = $firstRoute['path'][1];
        if (isset($altGraph1[$from][$to])) {
            unset($altGraph1[$from][$to]);
            unset($altGraph1[$to][$from]);
            
            $secondRoute = dijkstra($altGraph1, $titikAwalGraph, $titikTujuan);
            if ($secondRoute['found']) {
                $routeDetails = getRouteDetails($pdo, $secondRoute['path']);
                $routes[] = [
                    'distance' => $secondRoute['distance'],
                    'path' => $secondRoute['path'],
                    'route_details' => $routeDetails,
                    'route_type' => 'alternative',
                    'route_name' => 'Rute Alternatif 1'
                ];
            }
        }
    }

    // Rute alternatif 2: hapus edge di tengah rute terpendek
    if ($firstRoute['found'] && count($firstRoute['path']) > 3 && count($routes) < $maxRoutes) {
        $altGraph2 = $graph;
        $midIndex = floor(count($firstRoute['path']) / 2);
        $from = $firstRoute['path'][$midIndex - 1];
        $to = $firstRoute['path'][$midIndex];
        if (isset($altGraph2[$from][$to])) {
            unset($altGraph2[$from][$to]);
            unset($altGraph2[$to][$from]);
            
            $thirdRoute = dijkstra($altGraph2, $titikAwalGraph, $titikTujuan);
            if ($thirdRoute['found']) {
                $routeDetails = getRouteDetails($pdo, $thirdRoute['path']);
                $routes[] = [
                    'distance' => $thirdRoute['distance'],
                    'path' => $thirdRoute['path'],
                    'route_details' => $routeDetails,
                    'route_type' => 'alternative',
                    'route_name' => 'Rute Alternatif 2'
                ];
            }
        }
    }

    // Jika tidak ada rute langsung, cari rute melalui node intermediate
    if (empty($routes)) {
        $nearestConnected = findNearestConnectedDestination($pdo, $graph, $titikAwalGraph);
        if ($nearestConnected) {
            $dijkstraResult = dijkstra($graph, $nearestConnected['id'], $titikTujuan);
            if ($dijkstraResult['found']) {
                $fullPath = [$titikAwalGraph];
                if ($titikAwalGraph != $nearestConnected['id']) {
                    $fullPath[] = $nearestConnected['id'];
                }
                $fullPath = array_merge($fullPath, array_slice($dijkstraResult['path'], 1));
                
                $routeDetails = getRouteDetails($pdo, $fullPath);
                $totalDistance = haversineDistance(
                    $startDestination['latitude'], $startDestination['longitude'],
                    $nearestConnected['latitude'], $nearestConnected['longitude']
                ) + $dijkstraResult['distance'];
                
                $routes[] = [
                    'distance' => $totalDistance,
                    'path' => $fullPath,
                    'route_details' => $routeDetails,
                    'route_type' => 'alternative',
                    'route_name' => 'Rute Alternatif'
                ];
            }
        }
    }

    // Prepare result with multiple routes
    if (!empty($routes)) {
        // Sort routes by distance
        usort($routes, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        $result = [
            'success' => true,
            'message' => count($routes) > 1 ? 'Beberapa rute berhasil ditemukan' : 'Rute berhasil ditemukan',
            'data' => [
                'routes' => $routes,
                'start_destination_details' => $startDestination,
                'target_destination_details' => null
            ]
        ];

        // Get target destination details
        $stmtTujuan = $pdo->prepare("SELECT id, nama_destinasi, lokasi, latitude, longitude FROM destinasi WHERE id = ?");
        $stmtTujuan->execute([$titikTujuan]);
        $targetDestination = $stmtTujuan->fetch(PDO::FETCH_ASSOC);
        $result['data']['target_destination_details'] = $targetDestination;

        // Add user location if applicable
        if ($titikAwal === 'lokasi_sekarang') {
            $result['data']['user_location'] = [
                'latitude' => $userLat,
                'longitude' => $userLon,
                'nama_destinasi' => 'Lokasi Saat Ini'
            ];
        }

        return $result;
    }

    return ['success' => false, 'message' => 'Tidak ada rute yang tersedia antara titik awal dan tujuan.', 'data' => []];
}

/**
 * Fungsi utama untuk mencari rute dengan rute alternatif (backward compatibility)
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
        $result['data']['user_location'] = [
            'latitude' => $userLat, 
            'longitude' => $userLon,
            'nama_destinasi' => 'Lokasi Saat Ini'
        ];
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
                $result['data']['waypoints_for_map'][] = [
                    'latitude' => $nearestDestination['latitude'], 
                    'longitude' => $nearestDestination['longitude'],
                    'nama_destinasi' => $nearestDestination['nama_destinasi']
                ];
            } else {
                // Jika titik awal bukan lokasi_sekarang, tambahkan titik awal yang dipilih
                $result['data']['waypoints_for_map'][] = [
                    'latitude' => $selectedStartDestination['latitude'], 
                    'longitude' => $selectedStartDestination['longitude'],
                    'nama_destinasi' => $selectedStartDestination['nama_destinasi']
                ];
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
                    $result['data']['waypoints_for_map'][] = [
                        'latitude' => $detail['latitude'], 
                        'longitude' => $detail['longitude'],
                        'nama_destinasi' => $detail['nama_destinasi']
                    ];
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
            $result['data']['waypoints_for_map'][] = [
                'latitude' => $selectedStartDestination['latitude'], 
                'longitude' => $selectedStartDestination['longitude'],
                'nama_destinasi' => $selectedStartDestination['nama_destinasi']
            ];
        }

        foreach($routeDetails as $detail) {
             $result['data']['waypoints_for_map'][] = [
                'latitude' => $detail['latitude'], 
                'longitude' => $detail['longitude'],
                'nama_destinasi' => $detail['nama_destinasi']
            ];
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
        
        // Use multiple routes function if requested, otherwise use single route
        if (isset($_GET['multiple_routes']) && $_GET['multiple_routes'] == '1') {
            $routeResult = findMultipleRoutes($pdo, $titikAwal, $titikTujuan, $userLat, $userLon);
        } else {
            $routeResult = findRoute($pdo, $titikAwal, $titikTujuan, $userLat, $userLon);
        }
    } catch (Exception $e) {
        $routeResult = ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage(), 'data' => []];
    }
}

// Ambil data destinasi untuk dropdown
$destinasi = $pdo->query("SELECT id, nama_destinasi FROM destinasi ORDER BY nama_destinasi")->fetchAll(PDO::FETCH_ASSOC);
?>