<?php
require_once 'handler.php';
?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title fw-semibold mb-4 text-center">Input Data Node</h4>
        <a href="index.php?page=node/index" class="btn btn-outline-warning mb-3">
            <i class="fa-solid fa-circle-arrow-left me-2"></i> Kembali
        </a>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>
        <form action="index.php?page=node/tambah_node" method="POST">
            <input type="text" name="_method" value="post" hidden>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="nama_node" class="col-form-label">Nama Node</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="text" id="nama_node" class="form-control" name="nama_node" oninput="updateMap()" required>
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="latitude" class="col-form-label">Latitude</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="number" id="latitude" class="form-control" name="latitude" step="any" onchange="updateMap()">
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="longitude" class="col-form-label">Longitude</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="number" id="longitude" class="form-control" name="longitude" step="any" oninput="updateMap()">
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="map" class="col-form-label">Peta Leaflet</label>
                </div>
                <div class="col-12 col-lg-8">
                    <div id="map" style="height: 180px"></div>
                </div>
                <hr class="border border-black">
            </div>
            <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan</button>
        </form>
    </div>
</div>

<script type="text/javascript">
    let map;
    let marker;
    initMap(1.0214452457361827, 97.4450595500965, "Nias Barat");

    function initMap(latitude, longitude, namaDestinasi) {
        map = L.map('map').setView([latitude, longitude], 12);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">Wisata Nias Barat</a>'
        }).addTo(map);

        marker = L.marker([latitude, longitude]).addTo(map);
        marker.bindPopup(namaDestinasi).openPopup();
    }

    function updateMap() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lon = parseFloat(document.getElementById('longitude').value);
        const namaDestinasi = document.getElementById('nama_node').value;

        if (!isNaN(lat) && !isNaN(lon)) {
            map.setView([lat, lon], 12);
            marker.setLatLng([lat, lon]);
            marker.bindPopup(namaDestinasi).openPopup();
        }
    }
</script>