<?php
require_once 'handler.php';
?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title fw-semibold mb-4 text-center">Input Data Destinasi Wisata</h4>
        <a href="index.php?page=destinasi/index" class="btn btn-outline-warning mb-3">
            <i class="fa-solid fa-circle-arrow-left me-2"></i> Kembali
        </a>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>
        <form action="index.php?page=destinasi/tambah_destinasi" method="POST" enctype="multipart/form-data">
            <input type="text" name="_method" value="post" hidden>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="nama_destinasi" class="col-form-label">Nama Destinasi</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="text" id="nama_destinasi" class="form-control" name="nama_destinasi">
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="lokasi" class="col-form-label">Lokasi</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="text" id="lokasi" class="form-control" name="lokasi">
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="latitude" class="col-form-label">Latitude</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="number" id="latitude" class="form-control" name="latitude" step="any" oninput="updateMap()">
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
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="kategori" class="col-form-label">Kategori</label>
                </div>
                <div class="col-12 col-lg-8">
                    <select class="form-select" aria-label="Default select example" id="kategori" name="kategori">
                        <option hidden>pilih kategori</option>
                        <?php foreach ($kategori_destinasi as $kategori) : ?>
                            <option value="<?= $kategori['id']; ?>"><?= $kategori['nama']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="deskripsi" class="col-form-label">Deskripsi</label>
                </div>
                <div class="col-12 col-lg-8">
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="gambar" class="col-form-label">Upload Gambar</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input class="form-control" type="file" id="gambar" name="gambar">
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
    const latitude = 1.0214452457361827;
    const longitude = 97.4450595500965;
    const namaDestinasi = "Nias Barat";
    initMap(latitude, longitude, namaDestinasi);

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

        if (!isNaN(lat) && !isNaN(lon)) {
            map.setView([lat, lon], 12);
            marker.setLatLng([lat, lon]);
            marker.bindPopup(namaDestinasi).openPopup();
        }
    }
</script>