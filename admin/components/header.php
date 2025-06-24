<?php
session_start();
require_once '../config/db.php';

// cek apakah admin sudah login
if (!isset($_SESSION['id'])) {
    header('Location: /admin/login.php');
    exit;
}

$dataDestinations = $pdo->query("SELECT COUNT(*) as count FROM destinasi")->fetch(PDO::FETCH_ASSOC);
$dataRoutes = $pdo->query("SELECT COUNT(*) as count FROM jarak_antar_destinasi")->fetch(PDO::FETCH_ASSOC);