<?php
/*
* Handler untuk menampilkan data kategori destinasi
*/
$kategori_destinasi = $pdo->query("SELECT * FROM kategori_destinasi")->fetchAll(PDO::FETCH_ASSOC);
$destinasi = $pdo->query(
    "SELECT 
        destinasi.id, destinasi.nama_destinasi, 
        destinasi.lokasi, destinasi.kategori_destinasi_id, 
        kategori_destinasi.nama as kategori
    FROM destinasi 
    join kategori_destinasi 
    on destinasi.kategori_destinasi_id = kategori_destinasi.id; "
    )->fetchAll(PDO::FETCH_ASSOC);

/*
* Handler untuk menambahkan data destinasi wisata
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_method']) && $_POST['_method'] == 'post') {
    $nama_destinasi = $_POST['nama_destinasi'];
    $lokasi = $_POST['lokasi'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];

    $namafile = $_FILES["gambar"]["name"];
    $eksGambar = pathinfo($namafile, PATHINFO_EXTENSION);
    $tmpName = $_FILES["gambar"]["tmp_name"];

    // generate nama gambar baru
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $eksGambar;

    // Validasi tipe file
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($eksGambar, $allowedTypes)) {
        $error = "Tipe file tidak diizinkan. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
    } elseif ($_FILES["gambar"]["size"] > 2000000) { // Maksimal 2MB
        $error = "Ukuran file terlalu besar. Maksimal 2MB.";
    }

    // Validasi input
    if (empty($nama_destinasi) || empty($lokasi) || empty($latitude) || empty($longitude) || empty($kategori) || empty($deskripsi)) {
        $error = "Semua field harus diisi.";
    } else {
        // Pastikan direktori uploads ada
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        move_uploaded_file($tmpName, $uploadDir . $namaFileBaru);
        $timestamp = date("Y-m-d H:i:s");
        $query = $pdo->prepare("INSERT INTO destinasi (nama_destinasi, lokasi, latitude, longitude, kategori_destinasi_id, deskripsi, gambar, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($query->execute([$nama_destinasi, $lokasi, $latitude, $longitude, $kategori, $deskripsi, $namaFileBaru, $timestamp, $timestamp])) {
            echo "<script>window.location.href = 'index.php?page=destinasi/index';</script>";
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
        echo "<script>window.location.href = 'index.php?page=destinasi/index';</script>";
    } else {
        $error = "Gagal menghapus data.";
    }
}

/*
* Handler untuk menampilkan detail destinasi wisata
*/
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['page']) && $_GET['page'] == 'destinasi/detail_destinasi') {
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $destinasi = kategoriById($pdo, $id);
        if (!$destinasi) {
            header("Location: index.php?page=destinasi/index");
            exit;
        }
    } 
}

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['page']) && $_GET['page'] == 'destinasi/edit_destinasi') {
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $destinasi = kategoriById($pdo, $id);
        if (!$destinasi) {
            header("Location: index.php?page=destinasi/index");
            exit;
        }
    } 
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['_method'] == 'put') {
    $id = $_POST['id'];
    $nama_destinasi = $_POST['nama_destinasi'];
    $lokasi = $_POST['lokasi'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];

    if(isset($_FILES["gambar"]["name"])){
        $namafile = $_FILES["gambar"]["name"];
        $eksGambar = pathinfo($namafile, PATHINFO_EXTENSION);
        $tmpName = $_FILES["gambar"]["tmp_name"];

        // generate nama gambar baru
        $namaFileBaru = uniqid();
        $namaFileBaru .= '.';
        $namaFileBaru .= $eksGambar;

        // Validasi tipe file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($eksGambar, $allowedTypes)) {
            $error = "Tipe file tidak diizinkan. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
        } elseif ($_FILES["gambar"]["size"] > 2000000) { // Maksimal 2MB
            $error = "Ukuran file terlalu besar. Maksimal 2MB.";
        } else {
            // Pastikan direktori uploads ada
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            move_uploaded_file($tmpName, $uploadDir . $namaFileBaru);
            $gambar = $namaFileBaru;
        }
    } else {
        $gambar = null; // Tidak ada gambar baru
    }

    // Validasi input
    if (empty($nama_destinasi) || empty($lokasi) || empty($latitude) || empty($longitude) || empty($kategori) || empty($deskripsi)) {
        $error = "Semua field harus diisi.";
    } else {
        $query = $pdo->prepare("UPDATE destinasi SET nama_destinasi = ?, lokasi = ?, latitude = ?, longitude = ?, kategori_destinasi_id = ?, deskripsi = ?, updated_at = ? " . ($gambar ? ", gambar = ?" : "") . " WHERE id = ?");
        $params = [$nama_destinasi, $lokasi, $latitude, $longitude, $kategori, $deskripsi, date("Y-m-d H:i:s")];
        if ($gambar) {
            $params[] = $gambar;
        }
        $params[] = $id;

        if ($query->execute($params)) {
            echo "<script>window.location.href = 'index.php?page=destinasi/index';</script>";
        } else {
            $error = "Gagal memperbarui data.";
        }
    }
}

function kategoriById($pdo, $id) {
    $query = $pdo->prepare(
            "SELECT destinasi.*, kategori_destinasi.nama as kategori 
            FROM destinasi 
            JOIN kategori_destinasi 
            ON destinasi.kategori_destinasi_id = kategori_destinasi.id 
            WHERE destinasi.id = ?"
        );
    $query->execute([$id]);
    $destinasi = $query->fetch(PDO::FETCH_ASSOC);
    return $destinasi;
}