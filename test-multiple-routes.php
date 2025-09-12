<?php
require_once 'config/db.php';
require_once 'user-handler/dijkstra.php';

echo "<h2>Test Multiple Routes Function</h2>";

// Test parameters
$titikAwal = 1; // Ganti dengan ID destinasi yang valid
$titikTujuan = 3; // Ganti dengan ID destinasi yang valid

echo "<p>Testing routes from destination ID $titikAwal to destination ID $titikTujuan</p>";

try {
    $result = findMultipleRoutes($pdo, $titikAwal, $titikTujuan);
    
    echo "<h3>Results:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<h3>Found " . count($result['data']['routes']) . " route(s):</h3>";
        foreach ($result['data']['routes'] as $index => $route) {
            echo "<h4>Route " . ($index + 1) . ": " . $route['route_name'] . "</h4>";
            echo "<ul>";
            echo "<li>Distance: " . round($route['distance'], 2) . " km</li>";
            echo "<li>Type: " . $route['route_type'] . "</li>";
            echo "<li>Path: " . implode(' → ', $route['path']) . "</li>";
            echo "<li>Destinations: ";
            foreach ($route['route_details'] as $dest) {
                echo $dest['nama_destinasi'] . " ";
            }
            echo "</li>";
            echo "</ul>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Show available destinations
echo "<h3>Available Destinations:</h3>";
$destinations = $pdo->query("SELECT id, nama_destinasi FROM destinasi ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "<ul>";
foreach ($destinations as $dest) {
    echo "<li>ID {$dest['id']}: {$dest['nama_destinasi']}</li>";
}
echo "</ul>";

// Show graph connections
echo "<h3>Graph Connections:</h3>";
$connections = $pdo->query("SELECT titik_awal, titik_tujuan, jarak_km FROM jarak_antar_destinasi ORDER BY titik_awal")->fetchAll(PDO::FETCH_ASSOC);
echo "<ul>";
foreach ($connections as $conn) {
    echo "<li>ID {$conn['titik_awal']} ↔ ID {$conn['titik_tujuan']} ({$conn['jarak_km']} km)</li>";
}
echo "</ul>";
?>
