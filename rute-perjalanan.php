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
        
        .route-option {
            transition: all 0.3s ease;
        }
        
        .route-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .route-option.selected {
            border-color: #007bff !important;
            box-shadow: 0 0 10px rgba(0,123,255,0.3);
        }

        /* Custom marker styles */
        .custom-div-icon {
            background: transparent !important;
            border: none !important;
        }

        /* Route info styling */
        .route-info-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                            <input type="hidden" name="multiple_routes" value="1">
                            <div class="input-group">
                                <select class="form-select" name="titik_awal" id="titik_awal" required>
                                    <option value="" selected hidden>pilih titik awal</option>
                                    <option value="lokasi_sekarang" id="lokasi_sekarang" <?php if (isset($_GET['titik_awal']) && $_GET['titik_awal'] === 'lokasi_sekarang') echo 'selected'; ?>>📍 Lokasi saat ini</option>
                                    <?php foreach ($dataWisata as $d) : ?>
                                    <option value="<?= $d['id'] ?>" <?php if (isset($_GET['titik_awal']) && $_GET['titik_awal'] === $d['id']) echo 'selected'; ?>><?= $d['nama_destinasi'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="form-select" name="titik_tujuan" id="titik_tujuan" required>
                                    <option value="" selected hidden>pilih titik tujuan</option>
                                    <?php foreach ($dataWisata as $d) : ?>
                                    <option value="<?= $d['id'] ?>" <?php if (isset($_GET['titik_tujuan']) && $_GET['titik_tujuan'] === $d['id']) echo 'selected'; ?>><?= $d['nama_destinasi'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-outline-primary" id="button-addon2">Cari Rute</button>
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
                    <h5><i class="fas fa-check-circle"></i> <?= htmlspecialchars($routeResult['message']) ?></h5>
                </div>

                <div class="mb-3">
                    <h6><i class="fas fa-map-marker-alt"></i> Titik Awal Anda</h6>
                    <?php if (isset($routeResult['data']['user_location'])): ?>
                        <p class="mb-0">
                            Dari: <strong>Lokasi Saat Ini</strong>.
                            <br>
                            <small class="text-muted">Perjalanan dimulai dari destinasi terdekat yaitu <strong><?= htmlspecialchars($routeResult['data']['start_destination_details']['nama_destinasi']) ?></strong>.</small>
                        </p>
                    <?php elseif (isset($routeResult['data']['start_destination_details'])): ?>
                        <p class="mb-0">
                            Dari: <strong><?= htmlspecialchars($routeResult['data']['start_destination_details']['nama_destinasi']) ?></strong>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (isset($routeResult['data']['routes']) && count($routeResult['data']['routes']) > 1): ?>
                <div class="mb-4">
                    <h5><i class="fas fa-route"></i> Pilihan Rute (<?= count($routeResult['data']['routes']) ?> rute tersedia)</h5>
                    <div class="row">
                        <?php foreach ($routeResult['data']['routes'] as $index => $route): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card route-option" data-route-index="<?= $index ?>" style="cursor: pointer; border: 2px solid <?= $index === 0 ? '#007bff' : '#dee2e6' ?>;">
                                <div class="card-body text-center">
                                    <h6 class="card-title"><?= htmlspecialchars($route['route_name']) ?></h6>
                                    <p class="card-text">
                                        <strong><?= round($route['distance'], 2) ?> km</strong><br>
                                        <small class="text-muted"><?= count($route['path']) ?> destinasi</small>
                                    </p>
                                    <span class="badge <?= $route['route_type'] === 'direct' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $route['route_type'] === 'direct' ? 'Langsung' : 'Alternatif' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="position: relative;">
                    <div id="map"></div>
                    <div id="route-info-badge" class="route-info-badge" style="display: none;">
                        <small id="route-info-text"></small>
                    </div>
                </div>

                <div class="mt-4" id="route-details">
                    <!-- Route details will be populated by JavaScript -->
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
        let currentRouteIndex = 0;
        let routeControls = [];
        let markers = [];
        let map;

        // Initialize map
        function initializeMap() {
            map = L.map('map');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
        }

        // Clear all markers and routes
        function clearMap() {
            // Remove all markers
            markers.forEach(marker => {
                map.removeLayer(marker);
            });
            markers = [];

            // Remove all route controls
            routeControls.forEach(control => {
                if (map.hasLayer(control)) {
                    map.removeControl(control);
                }
            });
            routeControls = [];
        }

        // Create custom marker icons
        function createMarkerIcon(type, index) {
            const colors = {
                'start': '#28a745',
                'end': '#dc3545', 
                'waypoint': '#007bff',
                'user': '#fd7e14'
            };
            
            const color = colors[type] || '#6c757d';
            
            return L.divIcon({
                html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    ${type === 'user' ? '📍' : (type === 'start' ? 'S' : (type === 'end' ? 'E' : index))}
                </div>`,
                className: 'custom-div-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });
        }

        // Display route on map
        function displayRoute(routeIndex) {
            clearMap();

            const route = resultData.routes[routeIndex];
            const waypoints = [];
            
            // Add user location marker and waypoint if applicable
            if (resultData.user_location) {
                const userMarker = L.marker(
                    [resultData.user_location.latitude, resultData.user_location.longitude],
                    { icon: createMarkerIcon('user') }
                ).addTo(map).bindPopup('<b>📍 Lokasi Anda Saat Ini</b>');
                markers.push(userMarker);
                waypoints.push(L.latLng(resultData.user_location.latitude, resultData.user_location.longitude));
            }

            // Add destination markers and waypoints
            route.route_details.forEach((dest, i) => {
                const isStart = i === 0;
                const isEnd = i === route.route_details.length - 1;
                const markerType = isStart ? 'start' : (isEnd ? 'end' : 'waypoint');
                
                let popupText = `<b>${dest.nama_destinasi}</b><br>
                    <small>${dest.lokasi || ''}</small><br>
                    <small class="text-muted">Lat: ${dest.latitude}, Lng: ${dest.longitude}</small>`;
                
                if (isStart) {
                    popupText = `<b>🚩 Titik Awal</b><br><b>${dest.nama_destinasi}</b><br>
                        <small>${dest.lokasi || ''}</small><br>
                        <small class="text-muted">Lat: ${dest.latitude}, Lng: ${dest.longitude}</small>`;
                } else if (isEnd) {
                    popupText = `<b>🏁 Tujuan Akhir</b><br><b>${dest.nama_destinasi}</b><br>
                        <small>${dest.lokasi || ''}</small><br>
                        <small class="text-muted">Lat: ${dest.latitude}, Lng: ${dest.longitude}</small>`;
                } else {
                    popupText = `<b>📍 Destinasi ${i}</b><br><b>${dest.nama_destinasi}</b><br>
                        <small>${dest.lokasi || ''}</small><br>
                        <small class="text-muted">Lat: ${dest.latitude}, Lng: ${dest.longitude}</small>`;
                }

                const marker = L.marker(
                    [dest.latitude, dest.longitude],
                    { icon: createMarkerIcon(markerType, i + 1) }
                ).addTo(map).bindPopup(popupText);
                
                markers.push(marker);
                waypoints.push(L.latLng(dest.latitude, dest.longitude));
            });

            // Create route line
            const routeControl = L.Routing.control({
                waypoints: waypoints,
                fitSelectedRoutes: true,
                routeWhileDragging: false,
                addWaypoints: false,
                lineOptions: { 
                    styles: [{
                        color: getRouteColor(routeIndex), 
                        opacity: 0.8, 
                        weight: 5,
                        dashArray: route.route_type === 'alternative' ? '10, 5' : null
                    }] 
                },
                createMarker: () => null // Don't create default markers
            }).on('routesfound', function(e) {
                const summary = e.routes[0].summary;
                const distance = (summary.totalDistance / 1000).toFixed(2);
                const time = Math.round(summary.totalTime / 60);
                
                // Update route info badge
                const routeInfoBadge = document.getElementById('route-info-badge');
                const routeInfoText = document.getElementById('route-info-text');
                routeInfoText.innerHTML = `
                    <strong style="color: ${getRouteColor(routeIndex)};">${route.route_name}</strong><br>
                    📏 ${distance} km | ⏱️ ${time} menit
                `;
                routeInfoBadge.style.display = 'block';
            });

            routeControls.push(routeControl);
            routeControl.addTo(map);

            // Fit map to show all markers
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        // Get color for route
        function getRouteColor(index) {
            const colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1'];
            return colors[index % colors.length];
        }

        // Update route details display
        function updateRouteDetails(routeIndex) {
            const route = resultData.routes[routeIndex];
            const detailsHtml = `
                <h4><i class="fas fa-route"></i> Detail ${route.route_name}</h4>
                <div class="route-details mt-3">
                    <h6><i class="fas fa-list-ol"></i> Jalur Perjalanan:</h6>
                    <div class="list-group">
                        ${resultData.user_location ? `
                        <div class="list-group-item d-flex align-items-center" style="background-color: #fff3cd;">
                            <div class="me-3">
                                <span style="background-color: #fd7e14; width: 30px; height: 30px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px;">📍</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Lokasi Anda Saat Ini</h6>
                                <small class="text-muted">Koordinat: ${resultData.user_location.latitude}, ${resultData.user_location.longitude}</small>
                            </div>
                            <div class="ms-2">
                                <i class="fas fa-arrow-down text-primary"></i>
                            </div>
                        </div>
                        ` : ''}
                        ${route.route_details.map((destination, index) => {
                            const isStart = index === 0;
                            const isEnd = index === route.route_details.length - 1;
                            const markerColor = isStart ? '#28a745' : (isEnd ? '#dc3545' : '#007bff');
                            const badgeText = isStart ? 'S' : (isEnd ? 'E' : (index + 1));
                            const badgeClass = isStart ? 'bg-success' : (isEnd ? 'bg-danger' : 'bg-primary');
                            
                            return `
                                <div class="list-group-item d-flex align-items-center">
                                    <div class="me-3">
                                        <span style="background-color: ${markerColor}; width: 30px; height: 30px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">${badgeText}</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            ${destination.nama_destinasi}
                                            ${isStart ? ' <span class="badge bg-success">Start</span>' : ''}
                                            ${isEnd ? ' <span class="badge bg-danger">Finish</span>' : ''}
                                        </h6>
                                        <small class="text-muted">${destination.lokasi || ''}</small>
                                    </div>
                                    ${!isEnd ? '<div class="ms-2"><i class="fas fa-arrow-down text-primary"></i></div>' : ''}
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
            document.getElementById('route-details').innerHTML = detailsHtml;
        }

        // Handle route selection
        function selectRoute(routeIndex) {
            currentRouteIndex = routeIndex;
            
            // Update route option styling
            document.querySelectorAll('.route-option').forEach((option, index) => {
                option.style.border = index === routeIndex ? '2px solid #007bff' : '2px solid #dee2e6';
                if (index === routeIndex) {
                    option.classList.add('selected');
                } else {
                    option.classList.remove('selected');
                }
            });
            
            displayRoute(routeIndex);
            updateRouteDetails(routeIndex);
        }

        // Initialize everything
        initializeMap();
        
        // Add click handlers to route options
        document.querySelectorAll('.route-option').forEach((option, index) => {
            option.addEventListener('click', () => selectRoute(index));
        });

        // Display first route by default
        if (resultData.routes && resultData.routes.length > 0) {
            selectRoute(0);
        }
        <?php endif; ?>
    });
  </script>
</body>
</html>