<?php
$kategori = $pdo->query("SELECT * FROM kategori_destinasi");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['_method'] == 'post') {
    $nama = $_POST['nama_kategori'];
    if (empty($nama)) {
        $error = "Nama kategori harus diisi.";
    } else {
        $query = $pdo->prepare("INSERT INTO kategori_destinasi (nama, created_at, updated_at) VALUES (?, ?, ?)");
        $timestamp = date('Y-m-d H:i:s');
        if ($query->execute([$nama, $timestamp, $timestamp])) {
            echo "<script>window.location.href = 'index.php?page=kategori_destinasi/index';</script>";
        } else {
            $error = "Gagal menambahkan data kategori.";
        }
    }   
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['_method'] == 'delete'){
    $id = $_POST['id'];
    $query = $pdo->prepare("DELETE FROM kategori_destinasi WHERE id = ?");
    if ($query->execute([$id])) {
        echo "<script>window.location.href = 'index.php?page=kategori_destinasi/index';</script>";
    } else {
        $error = "Gagal menghapus data kategori.";
    }
}

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['page']) && $_GET['page'] == 'kategori_destinasi/edit_data') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $query = $pdo->prepare("SELECT * FROM kategori_destinasi WHERE id = ?");
        $query->execute([$id]);
        $kategoriEditData = $query->fetch(PDO::FETCH_ASSOC);
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['_method'] == 'put') {
    $id = $_GET['id'];
    $nama = $_POST['nama'];
    
    if (empty($nama)) {
        $error = "Nama kategori harus diisi.";
    } else {
        $query = $pdo->prepare("UPDATE kategori_destinasi SET nama = ?, updated_at = ? WHERE id = ?");
        $timestamp = date('Y-m-d H:i:s');
        if ($query->execute([$nama, $timestamp, $id])) {
            echo "<script>window.location.href = 'index.php?page=kategori_destinasi/index';</script>";
        } else {
            $error = "Gagal mengupdate data kategori.";
        }
    }
}