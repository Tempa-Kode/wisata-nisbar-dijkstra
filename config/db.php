<?php
$provider = 'mysql';
$host = 'localhost'; 
$port = '3306';
$dbname = 'db_wisata_nisbar_new_v3';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("$provider:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>