<?php
// Menampilkan semua rute
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

// Menambahkan rute baru
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'post') {
    $titik_awal = $_POST['titik_awal'];
    $titik_tujuan = $_POST['titik_tujuan'];
    $jarak = $_POST['jarak'];

    // Validasi dan format data kendaraan
    $mobil = !empty($_POST['mobil']) ? round(floatval($_POST['mobil']), 2) : null;
    $motor = !empty($_POST['motor']) ? round(floatval($_POST['motor']), 2) : null;
    $kapal = !empty($_POST['kapal']) ? round(floatval($_POST['kapal']), 2) : null;
    $speedboot = !empty($_POST['speedboot']) ? round(floatval($_POST['speedboot']), 2) : null;
    try {
        $stmt = $pdo->prepare("INSERT INTO jarak_antar_destinasi (titik_awal, titik_tujuan, jarak_km, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titik_awal, $titik_tujuan, $jarak, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);

        // Ambil ID rute yang baru saja ditambahkan
        $lastInsertId = $pdo->lastInsertId();

        // Simpan data jarak berdasarkan kendaraan
        $stmtKendaraan = $pdo->prepare("INSERT INTO jarak_berdasarkan_kendaraan (jarak_antar_destinasi_id, mobil, motor, kapal, speedboot) VALUES (?, ?, ?, ?, ?)");
        $stmtKendaraan->execute([$lastInsertId, $mobil, $motor, $kapal, $speedboot]);

        echo "<script>window.location.href = 'index.php?page=rute/index';</script>";
    } catch (\Throwable $th) {
        $error = "Gagal menambahkan data rute: " . $th->getMessage();
    }
}

// Menampilkan data rute untuk diedit
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page']) && $_GET['page'] === 'rute/edit_rute' && isset($_GET['id'])) {
    $id = $_GET['id'];
     try {
        $rute = $pdo->query("SELECT * FROM jarak_antar_destinasi WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
        $kendaraan = $pdo->query("SELECT * FROM jarak_berdasarkan_kendaraan WHERE jarak_antar_destinasi_id = {$rute['id']}")->fetch(PDO::FETCH_ASSOC);
        if (!$rute) {
            throw new Exception("Rute tidak ditemukan.");
        }
    } catch (\Throwable $th) {
        $error = "Data Tidak ditemukan : " . $th->getMessage();
    }
}

// Memperbarui data rute
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'put') {
    $titik_awal = $_POST['titik_awal'];
    $titik_tujuan = $_POST['titik_tujuan'];
    $jarak = $_POST['jarak'];
    $informasi_transportasi = $_POST['info_transportasi'] ?? "-";

    // Validasi dan format data kendaraan
    $mobil = !empty($_POST['mobil']) ? round(floatval($_POST['mobil']), 2) : null;
    $motor = !empty($_POST['motor']) ? round(floatval($_POST['motor']), 2) : null;
    $kapal = !empty($_POST['kapal']) ? round(floatval($_POST['kapal']), 2) : null;
    $speedboot = !empty($_POST['speedboot']) ? round(floatval($_POST['speedboot']), 2) : null;
    try {
        $stmt = $pdo->prepare("UPDATE jarak_antar_destinasi SET titik_awal = ?, titik_tujuan = ?, jarak_km = ?, info_transportasi = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$titik_awal, $titik_tujuan, $jarak, $informasi_transportasi, date('Y-m-d H:i:s'), $_POST['id']]);

        // Cek apakah data jarak kendaraan sudah ada
        $checkKendaraan = $pdo->prepare("SELECT id FROM jarak_berdasarkan_kendaraan WHERE jarak_antar_destinasi_id = ?");
        $checkKendaraan->execute([$_POST['id']]);
        $existingKendaraan = $checkKendaraan->fetch(PDO::FETCH_ASSOC);
        
        if ($existingKendaraan) {
            // Data sudah ada, lakukan UPDATE
            $stmtKendaraan = $pdo->prepare("UPDATE jarak_berdasarkan_kendaraan SET mobil = ?, motor = ?, kapal = ?, speedboot = ? WHERE jarak_antar_destinasi_id = ?");
            $stmtKendaraan->execute([$mobil, $motor, $kapal, $speedboot, $_POST['id']]);
        } else {
            // Data belum ada, lakukan INSERT
            $stmtKendaraan = $pdo->prepare("INSERT INTO jarak_berdasarkan_kendaraan (jarak_antar_destinasi_id, mobil, motor, kapal, speedboot) VALUES (?, ?, ?, ?, ?)");
            $stmtKendaraan->execute([$_POST['id'], $mobil, $motor, $kapal, $speedboot]);
        }

        echo "<script>window.location.href = 'index.php?page=rute/index';</script>";
    } catch (\Throwable $th) {
        $error = "Gagal mengupdate data rute: " . $th->getMessage();
    }
}

// Menghapus data rute
if($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_method'] === 'delete' && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        // Hapus juga data jarak berdasarkan kendaraan yang terkait
        $stmtKendaraan = $pdo->prepare("DELETE FROM jarak_berdasarkan_kendaraan WHERE jarak_antar_destinasi_id = ?");
        $stmtKendaraan->execute([$id]);
        
        $stmt = $pdo->prepare("DELETE FROM jarak_antar_destinasi WHERE id = ?");
        $stmt->execute([$id]);


        echo "<script>window.location.href = 'index.php?page=rute/index';</script>";
    } catch (\Throwable $th) {
        $error = "Gagal menghapus data rute: " . $th->getMessage();
    }
}