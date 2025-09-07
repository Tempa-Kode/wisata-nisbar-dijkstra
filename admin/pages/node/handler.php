<?php
/*
* Handler untuk menampilkan data kategori destinasi
*/
$node = $pdo->query(
    "SELECT 
        destinasi.id, destinasi.nama_destinasi, 
        destinasi.latitude, destinasi.longitude
    FROM destinasi WHERE destinasi.jenis = 'node'"
    )->fetchAll(PDO::FETCH_ASSOC);

/*
* Handler untuk menambahkan data destinasi wisata
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_method']) && $_POST['_method'] == 'post') {
    $nama_node = $_POST['nama_node'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Validasi input
    if (empty($nama_node) || empty($latitude) || empty($longitude)) {
        $error = "Semua field harus diisi.";
    } else {
        $timestamp = date("Y-m-d H:i:s");
        $query = $pdo->prepare("INSERT INTO destinasi (nama_destinasi, latitude, longitude, jenis, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
        if ($query->execute([$nama_node, $latitude, $longitude, 'node', $timestamp, $timestamp])) {
            echo "<script>window.location.href = 'index.php?page=node/index';</script>";
        } else {
            $error = "Gagal menyimpan data.";
        }
    }
}

/*
* Handler untuk menghapus data destinasi wisata
*/
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_method']) && $_POST['_method'] == 'delete') {
    $id = $_POST['id'];
    $query = $pdo->prepare("DELETE FROM destinasi WHERE id = ?");
    if ($query->execute([$id])) {
        echo "<script>window.location.href = 'index.php?page=node/index';</script>";
    } else {
        $error = "Gagal menghapus data.";
    }
}

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['page']) && $_GET['page'] == 'node/edit_node') {
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $node = nodeById($pdo, $id);
        if (!$node) {
            header("Location: index.php?page=node/index");
            exit;
        }
    } 
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['_method'] == 'put') {
    $id = $_POST['id'];
    $nama_node = $_POST['nama_node'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Validasi input
    if (empty($nama_node) || empty($latitude) || empty($longitude)) {
        $error = "Semua field harus diisi.";
    } else {
        $query = $pdo->prepare("UPDATE destinasi SET nama_destinasi = ?, latitude = ?, longitude = ?, updated_at = ? " . " WHERE id = ?");
        $params = [$nama_node, $latitude, $longitude, date("Y-m-d H:i:s")];
        $params[] = $id;

        if ($query->execute($params)) {
            echo "<script>window.location.href = 'index.php?page=node/index';</script>";
        } else {
            $error = "Gagal memperbarui data.";
        }
    }
}

function nodeById($pdo, $id) {
    $query = $pdo->prepare(
            "SELECT destinasi.*
            FROM destinasi 
            WHERE destinasi.id = ?"
        );
    $query->execute([$id]);
    $node = $query->fetch(PDO::FETCH_ASSOC);
    return $node;
}