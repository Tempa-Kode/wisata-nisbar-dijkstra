
<?php
require_once 'config/db.php';
// Ambil id dari query string
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$destinasi = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT destinasi.*, kategori_destinasi.nama as kategori FROM destinasi LEFT JOIN kategori_destinasi ON destinasi.kategori_destinasi_id = kategori_destinasi.id WHERE destinasi.id = ?");
    $stmt->execute([$id]);
    $destinasi = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Detail Destinasi - Wisata Nias Barat</title>
  <link href="assets/images/favicon.png" rel="icon">
  <link href="assets/images/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="assets/images/logo.png" alt="">
        <h1 class="sitename">Wisata Nias Barat</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Beranda</a></li>
          <li><a href="destinasi-wisata.php" class="active">Destinasi Wisata</a></li>
          <li><a href="rute-perjalanan.php">Rute Perjalanan</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
      <a class="btn-getstarted flex-md-shrink-0" href="admin/login.php">Login Admin</a>
    </div>
  </header>

  <main class="main">
    <div class="container py-5">
      <?php if ($destinasi): ?>
        <div class="row mb-4">
          <div class="col-lg-6 mb-3 mb-lg-0">
            <img src="/uploads/<?php echo htmlspecialchars($destinasi['gambar']); ?>" class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($destinasi['nama_destinasi']); ?>">
          </div>
          <div class="col-lg-6">
            <h2 class="mb-2"><?php echo htmlspecialchars($destinasi['nama_destinasi']); ?></h2>
            <span class="badge bg-info text-white mb-2"><?php echo htmlspecialchars($destinasi['kategori']); ?></span>
            <p class="mb-1"><i class="bi bi-geo-alt"></i> <strong>Lokasi:</strong> <?php echo htmlspecialchars($destinasi['lokasi']); ?></p>
            <p class="mb-1"><i class="bi bi-map"></i> <strong>Latitude, Longitude:</strong> <?php echo htmlspecialchars($destinasi['latitude']); ?>, <?php echo htmlspecialchars($destinasi['longitude']); ?></p>
            <p class="mt-3"><strong>Deskripsi:</strong><br><?php echo nl2br(htmlspecialchars($destinasi['deskripsi'])); ?></p>
            <a href="destinasi-wisata.php" class="btn btn-outline-primary mt-3"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Destinasi</a>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <h4 class="mb-3">Peta Lokasi</h4>
            <div id="map" style="height: 350px; border-radius: 12px; overflow: hidden;"></div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-danger mt-5">Data destinasi tidak ditemukan.</div>
      <?php endif; ?>
    </div>
  </main>

  <footer id="footer" class="footer">
    <div class="footer-newsletter">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-6">
            <h4>🎯 Tujuan Kami</h4>
            <p>Membantu meningkatkan pariwisata di Nias Barat melalui sistem digital yang informatif dan terarah, mempermudah pengunjung dalam merencanakan perjalanan wisata mereka dengan efisien dan menyenangkan.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="container footer-top">
      <div class="row gy-4 d-flex justify-content-between">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="d-flex align-items-center">
            <span class="sitename">Wisata Nias Barat</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Nias Barat</p>
            <p>Sumatera Utara, IDN</p>
            <p><strong>Email:</strong> <span>info@niaskab.go.id</span></p>
          </div>
        </div>
        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Links</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="index.php">Beranda</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="destinasi-wisata.php">Destinasi Wisata</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="rute-perjalanan.php">Rute Perjalanan</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="admin/login.php">Login sebagai admin</a></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <?php if ($destinasi && $destinasi['latitude'] && $destinasi['longitude']): ?>
  <script>
    const latitude = <?php echo json_encode($destinasi['latitude']); ?>;
    const longitude = <?php echo json_encode($destinasi['longitude']); ?>;
    const namaDestinasi = <?php echo json_encode($destinasi['nama_destinasi']); ?>;
    const map = L.map('map').setView([latitude, longitude], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">Wisata Nias Barat</a>'
    }).addTo(map);
    const marker = L.marker([latitude, longitude]).addTo(map);
    marker.bindPopup(namaDestinasi).openPopup();
  </script>
  <?php endif; ?>
</body>
</html>
