<?php
require_once 'config/db.php';

$stmt = $pdo->prepare(
    "SELECT 
        *
    FROM destinasi
    WHERE jenis = :jenis
    ORDER BY id DESC"
);
$stmt->execute(['jenis' => 'wisata']);
$dataWisata = $stmt->fetchAll(PDO::FETCH_ASSOC);