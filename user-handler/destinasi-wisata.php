<?php
require_once 'config/db.php';
if($_SERVER['REQUEST_METHOD'] == 'GET' && empty($_GET['search'])) {
    $destinasi = $pdo->query(
        "SELECT 
            destinasi.*, kategori_destinasi.nama as kategori
        FROM destinasi 
        LEFT JOIN 
            kategori_destinasi 
        ON 
            destinasi.kategori_destinasi_id = kategori_destinasi.id 
        ORDER BY id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $search = $_GET['search'];
    $destinasi = $pdo->prepare(
        "SELECT 
            destinasi.*, kategori_destinasi.nama as kategori
        FROM destinasi 
        LEFT JOIN 
            kategori_destinasi 
        ON 
            destinasi.kategori_destinasi_id = kategori_destinasi.id 
        WHERE 
            destinasi.nama_destinasi LIKE :search
        OR 
            destinasi.lokasi LIKE :search
        OR
            kategori_destinasi.nama LIKE :search
        ORDER BY id DESC"
    );
    $destinasi->execute(['search' => "%$search%"]);
    $destinasi = $destinasi->fetchAll(PDO::FETCH_ASSOC);
}