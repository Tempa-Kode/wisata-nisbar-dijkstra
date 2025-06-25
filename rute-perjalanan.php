<?php
  require_once 'user-handler/rute-perjalanan.php';
  require_once 'user-handler/dijkstra-result.php';
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

	<!-- LeaftletJS -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
		integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
		integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

	<!-- Sudah ada Leaflet CSS & JS -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
	<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>


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
  				<li><a href="destinasi-wisata.php">Destinasi Wisata</a></li>
  				<li><a href="rute-perjalanan.php" class="active">Rute Perjalanan</a></li>
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
    					<h1>🧭 Temukan Rute Perjalanan Terdekat</h1>
    					<form action="" method="get" class="mt-4">
    						<input type="number" name="latitude" id="latitude" step="any" hidden>
    						<input type="number" name="longitude" id="longitude" step="any" hidden>
    						<div class="input-group">
    							<select class="form-select" name="titik_awal" id="titik_awal">
    								<option value="" selected hidden>pilih titik awal</option>
    								<option value="lokasi_sekarang" id="lokasi_sekarang" <?php if ($_GET['titik_awal'] === 'lokasi_sekarang') echo 'selected'; ?>>📍 Lokasi saat ini</option>
    								<?php foreach ($destinasi as $d) : ?>
    								<option value="<?= $d['id'] ?>" <?php if (isset($_GET['titik_awal']) && $_GET['titik_awal'] === $d['id']) echo 'selected'; ?>><?= $d['nama_destinasi'] ?></option>
    								<?php endforeach; ?>
    							</select>
    							<select class="form-select" name="titik_tujuan" id="titik_tujuan">
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
    </div><!-- End Page Title -->

    <div class="container">
    	<div class="row gy-4 mb-3">
    		<div class="card p-4">
    			<div id="map" style="width: 100%; height: 500px;"></div>
    		</div>
    	</div>
    </div>

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
  <script type="text/javascript">
  	getLocation();
  	const latitude = document.getElementById('latitude');
  	const longitude = document.getElementById('longitude');
  	const lokasiSekarang = document.getElementById('lokasi_sekarang');

  	function getLocation() {
  		if (navigator.geolocation) {
  			navigator.geolocation.watchPosition(success, error);
  		} else {
  			alert("geolocation tidak didukung oleh browser ini.");
  		}
  	}

  	function success(position) {
  		latitude.value = position.coords.latitude;
  		longitude.value = position.coords.longitude;
  		lokasiSekarang.textContent = `📍 Lokasi saat ini: ${position.coords.latitude}, ${position.coords.longitude}`;
  	}

  	function error(error) {
  		switch (error.code) {
  			case error.PERMISSION_DENIED:
  				alert("Anda menolak permintaan Geolokasi.");
  				break;
  			case error.POSITION_UNAVAILABLE:
  				alert("Informasi lokasi tidak tersedia.");
  				break;
  			case error.TIMEOUT:
  				alert("Permintaan untuk mendapatkan lokasi pengguna telah habis waktu.");
  				break;
  			case error.UNKNOWN_ERROR:
  				alert("Terjadi kesalahan yang tidak diketahui.");
  				break;
  		}
  	}
  </script>

<script>
    const rute = <?php echo json_encode($rute_terpendek); ?>;
    const daftarDestinasi = <?php echo json_encode($daftar_destinasi); ?>;

    const koordinatRute = [];
    const map = L.map('map');
    
    // Tambahkan tile layer terlebih dahulu
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Proses setiap titik dalam rute
    rute.forEach((id, index) => {
        let lat, lng, nama;

        if (id === "koordinat_user") {
            lat = <?php echo isset($lat_awal) ? $lat_awal : '0'; ?>;
            lng = <?php echo isset($lng_awal) ? $lng_awal : '0'; ?>;
            nama = "📍 Lokasi Anda";
        } else if (daftarDestinasi[id]) {
            lat = parseFloat(daftarDestinasi[id].lat);
            lng = parseFloat(daftarDestinasi[id].lng);
            nama = daftarDestinasi[id].nama_destinasi || daftarDestinasi[id].nama || `Destinasi ${id}`;
        } else {
            console.warn("ID tidak ditemukan dalam daftar destinasi:", id);
            return; // Skip jika tidak ditemukan
        }

        // Validasi koordinat
        if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) {
            console.warn("Koordinat tidak valid untuk:", nama, "lat:", lat, "lng:", lng);
            return;
        }

        const point = L.latLng(lat, lng);
        koordinatRute.push(point);

        // Buat marker dengan icon yang berbeda untuk lokasi user dan destinasi
        let markerIcon;
        if (id === "koordinat_user") {
            markerIcon = L.divIcon({
                html: '<div style="background-color: #ff4444; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
        } else {
            markerIcon = L.divIcon({
                html: '<div style="background-color: #4CAF50; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
        }

        const marker = L.marker(point).addTo(map);
        
        // Buat popup dengan informasi lebih detail
        let popupContent = `<div style="text-align: center; min-width: 150px;">
            <h6 style="margin: 0 0 8px 0; color: #333; font-weight: bold;">${nama}</h6>
            <p style="margin: 0; font-size: 12px; color: #666;">
                Lat: ${lat.toFixed(6)}<br>
                Lng: ${lng.toFixed(6)}
            </p>`;
        
        // Tambahkan nomor urut jika bukan lokasi user
        if (id !== "koordinat_user") {
            popupContent += `<p style="margin: 4px 0 0 0; font-size: 11px; color: #888;">
                Urutan ke-${index + 1} dalam rute
            </p>`;
        }
        
        popupContent += `</div>`;
        
        marker.bindPopup(popupContent);

        // Buka popup untuk titik awal
        if (index === 0) {
            marker.openPopup();
        }
    });

    // Set view peta dan tambahkan routing
    if (koordinatRute.length === 0) {
        console.error("Tidak ada koordinat rute yang valid.");
        // Set default view jika tidak ada koordinat
        map.setView([1.0, 97.0], 10); // Koordinat umum Nias Barat
    } else {
        // Set view berdasarkan bounds dari semua titik
        if (koordinatRute.length === 1) {
            map.setView(koordinatRute[0], 15);
        } else {
            const group = new L.featureGroup(koordinatRute.map(coord => L.marker(coord)));
            map.fitBounds(group.getBounds().pad(0.1));
        }

        // Tambahkan routing control
        const routingControl = L.Routing.control({
            waypoints: koordinatRute,
            routeWhileDragging: false,
            draggableWaypoints: false,
            addWaypoints: false,
            createMarker: function() { return null; }, // Jangan buat marker duplikat
            lineOptions: {
                styles: [{ color: '#2196F3', weight: 4, opacity: 0.8 }]
            },
            show: false, // Sembunyikan panel instruksi
            collapsible: true
        }).addTo(map);

        // Sembunyikan panel routing instructions jika tidak diperlukan
        setTimeout(() => {
            const routingContainer = document.querySelector('.leaflet-routing-container');
            if (routingContainer) {
                routingContainer.style.display = 'none';
            }
        }, 1000);
    }

    // Tambahkan event listener untuk marker clicks
    map.on('popupopen', function(e) {
        console.log('Popup dibuka untuk:', e.popup.getContent());
    });

    // Debug: tampilkan informasi di console
    console.log('Rute yang diproses:', rute);
    console.log('Daftar destinasi:', daftarDestinasi);
    console.log('Koordinat rute yang valid:', koordinatRute);
</script>

</body>

</html>