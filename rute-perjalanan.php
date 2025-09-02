<?php
  require_once 'user-handler/rute-perjalanan.php';
  require_once 'user-handler/dijkstra.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Wisata Nias Barat</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <link href="assets/images/favicon.png" rel="icon">
    <link href="assets/images/apple-touch-icon.png" rel="apple-touch-icon">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <link href="assets/css/main.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        #map {
            width: 100%;
            height: 500px;
        }
        /* Menyembunyikan panel instruksi default dari Leaflet Routing Machine */
        .leaflet-routing-container {
            display: none;
        }
    </style>
</head>

<body class="blog-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
        <a href="index.php" class="logo d-flex align-items-center me-auto">
            <img src="assets/images/logo.png" alt="">
            <h1 class="sitename">Wisata Nias Barat</h1>
        </a>
        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="index.php">Beranda<br></a></li>
                <li><a href="destinasi-wisata.php">Destinasi Wisata</a></li>
                <li><a href="rute-perjalanan.php" class="active">Rute Perjalanan</a></li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
        <a class="btn-getstarted flex-md-shrink-0" href="admin/login.php">Login Admin</a>
    </div>
  </header>

  <main class="main">
    <div class="page-title">
        <div class="heading">
            <div class="container">
                <div class="row d-flex justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1>🧭 Temukan Rute Perjalanan Terdekat</h1>
                        <form action="" method="get" class="mt-4">
                            <input type="number" name="latitude" id="latitude" step="any" hidden>
                            <input type="number" name="longitude" id="longitude" step="any" hidden>
                            <div class="input-group">
                                <select class="form-select" name="titik_awal" id="titik_awal" required>
                                    <option value="" selected hidden>pilih titik awal</option>
                                    <option value="lokasi_sekarang" id="lokasi_sekarang" <?php if (isset($_GET['titik_awal']) && $_GET['titik_awal'] === 'lokasi_sekarang') echo 'selected'; ?>>📍 Lokasi saat ini</option>
                                    <?php foreach ($destinasi as $d) : ?>
                                    <option value="<?= $d['id'] ?>" <?php if (isset($_GET['titik_awal']) && $_GET['titik_awal'] === $d['id']) echo 'selected'; ?>><?= $d['nama_destinasi'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="form-select" name="titik_tujuan" id="titik_tujuan" required>
                                    <option value="" selected hidden>pilih titik tujuan</option>
                                    <?php foreach ($destinasi as $d) : ?>
                                    <option value="<?= $d['id'] ?>" <?php if (isset($_GET['titik_tujuan']) && $_GET['titik_tujuan'] === $d['id']) echo 'selected'; ?>><?= $d['nama_destinasi'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-outline-primary" id="button-addon2">Cari</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($routeResult): ?>
    <div class="container mb-3">
        <div class="card p-4 shadow-sm">
            <?php if ($routeResult['success']): ?>
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> Rute Ditemukan!</h5>
                </div>

                <div class="mb-3">
                    <h6><i class="fas fa-map-marker-alt"></i> Titik Awal Anda</h6>
                    <?php if (isset($routeResult['data']['user_location'])): ?>
                        <p class="mb-0">
                            Dari: <strong>Lokasi Saat Ini</strong>.
                            <br>
                            <small class="text-muted">Perjalanan dimulai dari destinasi terdekat yaitu <strong><?= htmlspecialchars($routeResult['data']['nearest_destination']['nama_destinasi']) ?></strong> (sekitar <?= round($routeResult['data']['nearest_destination']['distance_from_user'], 2) ?> km dari Anda).</small>
                        </p>
                    <?php elseif (isset($routeResult['data']['start_destination_details'])): ?>
                        <p class="mb-0">
                            Dari: <strong><?= htmlspecialchars($routeResult['data']['start_destination_details']['nama_destinasi']) ?></strong>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (isset($routeResult['data']['route']['route_type']) && $routeResult['data']['route']['route_type'] === 'alternative'): ?>
                    <div class="alert alert-info mb-3">
                        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Ini adalah Rute Alternatif</h6>
                        <p class="mb-1">Tidak ada rute langsung. Sistem mengarahkan Anda melalui <strong><?= htmlspecialchars($routeResult['data']['alternative_info']['intermediate_destination']['nama_destinasi']) ?></strong> (sekitar <?= round($routeResult['data']['alternative_info']['distance_to_intermediate'], 2) ?> km dari titik awal Anda).</p>
                    </div>
                <?php endif; ?>

                <div id="map"></div>

                <div class="mt-4">
                    <h4>Detail Rute</h4>
                    <ul class="list-group">
                        <li class="list-group-item" id="info-transit">Memuat info rute transit...</li>
                        <li class="list-group-item" id="info-langsung" style="display: none;">Memuat info rute alternatif...</li>
                    </ul>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="toggleRuteLangsung">
                        <label class="form-check-label" for="toggleRuteLangsung">Tampilkan Rute Alternatif</label>
                    </div>
                </div>

                <div class="route-details mt-3">
                    <h6><i class="fas fa-list-ol"></i> Jalur Perjalanan:</h6>
                    <div class="list-group">
                        <?php foreach ($routeResult['data']['route']['route_details'] as $index => $destination): ?>
                        <?php 
                        $isStart = $index === 0;
                        $isEnd = $index === count($routeResult['data']['route']['route_details']) - 1;
                        $isIntermediate = isset($routeResult['data']['route']['route_type']) && 
                                        $routeResult['data']['route']['route_type'] === 'alternative' && 
                                        $index === 1;
                        ?>
                        <div class="list-group-item d-flex align-items-center">
                            <div class="me-3">
                                <?php if ($isStart): ?>
                                <span class="badge bg-primary rounded-pill">Start</span>
                                <?php elseif ($isEnd): ?>
                                <span class="badge bg-danger rounded-pill">Finish</span>
                                <?php elseif ($isIntermediate): ?>
                                <span class="badge bg-warning rounded-pill">Transit</span>
                                <?php else: ?>
                                <span class="badge bg-secondary rounded-pill"><?= $isIntermediate ? 'T' : $index ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($destination['nama_destinasi']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($destination['lokasi'] ?? '') ?></small>
                                <?php if ($isIntermediate): ?>
                                <br><small class="text-warning"><i class="fas fa-exchange-alt"></i> Destinasi perantara (rute alternatif)</small>
                                <?php endif; ?>
                            </div>
                            <?php if (!$isEnd): ?>
                            <div class="ms-2">
                                <i class="fas fa-arrow-down text-primary"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Rute Tidak Ditemukan</h5>
                    <p><?= htmlspecialchars($routeResult['message']) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
  </main>

  <footer id="footer" class="footer">

  	<div class="footer-newsletter">
  		<div class="container">
  			<div class="row justify-content-center text-center">
  				<div class="col-lg-6">
  					<h4>🎯 Tujuan Kami</h4>
  					<p>Membantu meningkatkan pariwisata di Nias Barat melalui sistem digital yang informatif dan terarah,
  						mempermudah pengunjung dalam merencanakan perjalanan wisata mereka dengan efisien dan menyenangkan.</p>
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

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const titikAwal = document.getElementById('titik_awal');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        if (titikAwal) {
            titikAwal.addEventListener('change', function() {
                if (this.value === 'lokasi_sekarang') {
                    if (navigator.geolocation) {
                        this.disabled = true;
                        const originalText = this.options[this.selectedIndex].text;
                        this.options[this.selectedIndex].text = '📍 Mendapatkan lokasi...';
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                latitudeInput.value = position.coords.latitude;
                                longitudeInput.value = position.coords.longitude;
                                titikAwal.options[titikAwal.selectedIndex].text = originalText;
                                titikAwal.disabled = false;
                            },
                            function(error) {
                                alert('Gagal mendapatkan lokasi: ' + error.message);
                                titikAwal.value = '';
                                latitudeInput.value = '';
                                longitudeInput.value = '';
                                titikAwal.options[titikAwal.selectedIndex].text = originalText;
                                titikAwal.disabled = false;
                            },
                            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                        );
                    }
                }
            });
        }
        <?php if (isset($_GET['titik_awal']) && $_GET['titik_awal'] === 'lokasi_sekarang' && isset($_GET['latitude']) && isset($_GET['longitude'])): ?>
            latitudeInput.value = <?= json_encode($_GET['latitude']) ?>;
            longitudeInput.value = <?= json_encode($_GET['longitude']) ?>;
        <?php endif; ?>

        <?php if ($routeResult && $routeResult['success']): ?>
        const resultData = <?= json_encode($routeResult['data']) ?>;
        const waypointsData = resultData.waypoints_for_map;
        const allWaypoints = waypointsData.map(wp => L.latLng(wp.latitude, wp.longitude));
        const map = L.map('map');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        waypointsData.forEach((wp, i) => {
            let popupText = `<b>${wp.nama_destinasi}</b>`;
            if (i === 0) popupText = `<b>Titik Awal:</b><br>${wp.nama_destinasi}`;
            if (i === waypointsData.length - 1) popupText = `<b>Tujuan Akhir:</b><br>${wp.nama_destinasi}`;
            if (resultData.route.route_type === 'alternative' && i > 0 && i < waypointsData.length - 1) popupText += `<br><small>Titik Transit</small>`;
            L.marker([wp.latitude, wp.longitude]).addTo(map).bindPopup(popupText);
        });

        // Rute 1: Transit (Dijkstra) - Selalu ditampilkan
        L.Routing.control({
            waypoints: allWaypoints,
            fitSelectedRoutes: true,
            routeWhileDragging: false,
            addWaypoints: false,
            lineOptions: { styles: [{color: '#0d6efd', opacity: 0.8, weight: 6}] },
            createMarker: () => null
        }).on('routesfound', function(e) {
            const summary = e.routes[0].summary;
            document.getElementById('info-transit').innerHTML = `<i class="fas fa-circle me-2" style="color: #0d6efd;"></i><strong>Rute Dijkstra:</strong> Jarak ${(summary.totalDistance / 1000).toFixed(2)} km, Estimasi ${Math.round(summary.totalTime / 60)} menit`;
        }).addTo(map);

        // Rute 2: Langsung - Dikontrol oleh toggle
        const directWaypoints = [allWaypoints[0], allWaypoints[allWaypoints.length - 1]];
        const directRouteControl = L.Routing.control({
            waypoints: directWaypoints,
            fitSelectedRoutes: false,
            routeWhileDragging: false,
            addWaypoints: false,
            lineOptions: { styles: [{color: '#198754', opacity: 0.8, weight: 6}] },
            createMarker: () => null
        }).on('routesfound', function(e) {
            const summary = e.routes[0].summary;
            document.getElementById('info-langsung').innerHTML = `<i class="fas fa-circle me-2" style="color: #198754;"></i><strong>Rute Alternatif:</strong> Jarak ${(summary.totalDistance / 1000).toFixed(2)} km, Estimasi ${Math.round(summary.totalTime / 60)} menit`;
        });

        // Logika Toggle
        const toggleSwitch = document.getElementById('toggleRuteLangsung');
        const infoLangsung = document.getElementById('info-langsung');

        toggleSwitch.addEventListener('change', function() {
            if (this.checked) {
                directRouteControl.addTo(map);
                infoLangsung.style.display = 'block';
            } else {
                map.removeControl(directRouteControl);
                infoLangsung.style.display = 'none';
            }
        });
        <?php endif; ?>
    });
  </script>
</body>
</html>