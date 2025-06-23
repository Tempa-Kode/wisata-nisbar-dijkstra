<?php
if($_GET['page'] === 'rute/index') {
    $rute = $pdo->query(
    "SELECT 
        jad.id, awal.nama_destinasi AS nama_titik_awal,
        tujuan.nama_destinasi AS nama_titik_tujuan, 
        jad.jarak_km, jad.created_at, jad.updated_at 
    FROM jarak_antar_destinasi AS jad 
    JOIN destinasi AS awal 
    ON jad.titik_awal = awal.id 
    JOIN destinasi AS tujuan ON jad.titik_tujuan = tujuan.id 
    ORDER BY jad.id ASC;"
    )->fetchAll(PDO::FETCH_ASSOC);
}

$destinasi = $pdo->query("SELECT * FROM destinasi")->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'post') {
    $titik_awal = $_POST['titik_awal'];
    $titik_tujuan = $_POST['titik_tujuan'];
    $jarak = $_POST['jarak'];
    try {
        $stmt = $pdo->prepare("INSERT INTO jarak_antar_destinasi (titik_awal, titik_tujuan, jarak_km, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titik_awal, $titik_tujuan, $jarak, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
        echo "<script>window.location.href = 'index.php?page=rute/index';</script>";
    } catch (\Throwable $th) {
        $error = "Gagal menambahkan data rute: " . $th->getMessage();
    }
}

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page']) && $_GET['page'] === 'rute/edit_rute' && isset($_GET['id'])) {
    $id = $_GET['id'];
     try {
        $rute = $pdo->query("SELECT * FROM jarak_antar_destinasi WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
        if (!$rute) {
            throw new Exception("Rute tidak ditemukan.");
        }
    } catch (\Throwable $th) {
        $error = "Data Tidak ditemukan : " . $th->getMessage();
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'put') {
    $titik_awal = $_POST['titik_awal'];
    $titik_tujuan = $_POST['titik_tujuan'];
    $jarak = $_POST['jarak'];
    try {
        $stmt = $pdo->prepare("UPDATE jarak_antar_destinasi SET titik_awal = ?, titik_tujuan = ?, jarak_km = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$titik_awal, $titik_tujuan, $jarak, date('Y-m-d H:i:s'), $_POST['id']]);
        echo "<script>window.location.href = 'index.php?page=rute/index';</script>";
    } catch (\Throwable $th) {
        $error = "Gagal mengupdate data rute: " . $th->getMessage();
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_method'] === 'delete' && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM jarak_antar_destinasi WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>window.location.href = 'index.php?page=rute/index';</script>";
    } catch (\Throwable $th) {
        $error = "Gagal menghapus data rute: " . $th->getMessage();
    }
}