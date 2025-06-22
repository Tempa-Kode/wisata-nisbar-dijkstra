<?php
require_once 'handler.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title text-center text-uppercase">Detail Destinasi</h3>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between ">
            <a href="index.php?page=destinasi/index" class="btn btn-outline-warning mb-3">
                <i class="fa-solid fa-circle-arrow-left me-2"></i> Kembali
            </a>
            <a href="index.php?page=destinasi/edit_destinasi&id=<?php echo $destinasi['id']; ?>" class="btn btn-primary mb-3">
                <i class="fa-solid fa-pen-to-square me-2"></i> Edit Data
            </a>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h5>Kategori/Nama Destinasi:</h5>
                <p><?php echo htmlspecialchars($destinasi['kategori']); ?> / <?php echo htmlspecialchars($destinasi['nama_destinasi']); ?></p>
            </div>
            <div class="col-md-6">
                <h5>Lokasi:</h5>
                <p><?php echo htmlspecialchars($destinasi['lokasi']); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h5>Deskripsi:</h5>
                <p><?php echo nl2br(htmlspecialchars($destinasi['deskripsi'])); ?></p>
            </div>
            <div class="col-md-6">
                <h5>Latitude & Longitude:</h5>
                <p><?php echo nl2br(htmlspecialchars($destinasi['latitude'])); ?>, <?php echo nl2br(htmlspecialchars($destinasi['longitude'])); ?></p>
            </div>
            </div>
        <div class="row">
            <div class="col-md-6">
                <h5>Gambar:</h5>
                <?php if ($destinasi['gambar']): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($destinasi['gambar']); ?>" alt="Gambar Destinasi" class="img-thumbnail w-100">
                <?php else: ?>
                    <p>Tidak ada gambar tersedia.</p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <h5>Peta Leaflet:</h5>
                   <div id="map" style="height: 300px"></div>
                </div>
            </div>
        </div>
    </div>
</div> 

<script type="text/javascript">
    let map;
    let marker;
    const latitude = <?php echo json_encode($destinasi['latitude']); ?>;
    const longitude = <?php echo json_encode($destinasi['longitude']); ?>;
    const namaDestinasi = <?php echo json_encode($destinasi['nama_destinasi']); ?>;

    map = L.map('map').setView([latitude, longitude], 12);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">Wisata Nias Barat</a>'
    }).addTo(map);

    marker = L.marker([latitude, longitude]).addTo(map);
    marker.bindPopup(namaDestinasi).openPopup();
</script>