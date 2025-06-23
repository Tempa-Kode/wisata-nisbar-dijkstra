<?php
require_once 'user-handler/destinasi-wisata.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Wisata Nias Barat</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/images/favicon.png" rel="icon">
  <link href="assets/images/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: FlexStart
  * Template URL: https://bootstrapmade.com/flexstart-bootstrap-startup-template/
  * Updated: Nov 01 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="blog-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <img src="assets/images/logo.png" alt="">
        <h1 class="sitename">Wisata Nias Barat</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Beranda<br></a></li>
          <li><a href="destinasi-wisata.php" class="active">Destinasi Wisata</a></li>
          <li><a href="rute-perjalanan.php">Rute Perjalanan</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted flex-md-shrink-0" href="admin/login.php">Login Admin</a>

    </div>
  </header>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title">
      <div class="heading">
        <div class="container">
          <div class="row d-flex justify-content-center text-center">
            <div class="col-lg-8">
              <h1>Destinasi Wisata Nias Barat</h1>
              <form action="" method="get">
                <div class="input-group">
                  <input type="text" class="form-control" placeholder="cari berdasarkan nama, kategori dan lokasi" name="search" aria-describedby="button-addon2">
                  <button type="submit" class="btn btn-outline-primary" id="button-addon2">Cari</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div><!-- End Page Title -->

    <div class="container">
      <div class="row gy-4 mb-3">
        <?php foreach ($destinasi as $item) : ?>
            <div class="col-md-4">
            <div class="card h-100">
              <img src="/uploads/<?php echo $item['gambar']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="...">
                <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo $item['nama_destinasi']; ?></h5>
                <div>
                  <span class="badge rounded-pill text-bg-info text-white"><?php echo $item['kategori']; ?></span>
                  <p class="mt-2">Lokasi : <?php echo $item['lokasi']; ?></p>
                </div>
              </div>
            </div>
            </div>
        <?php endforeach; ?>
      </div>
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
          <a href="index.html" class="d-flex align-items-center">
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

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>