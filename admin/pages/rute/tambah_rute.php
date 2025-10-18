<?php
require_once 'handler.php';
?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title fw-semibold mb-4 text-center">Input Data Rute</h4>
        <a href="index.php?page=rute/index" class="btn btn-outline-warning mb-3">
            <i class="fa-solid fa-circle-arrow-left me-2"></i> Kembali
        </a>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>
        <form action="index.php?page=rute/tambah_rute" method="POST" enctype="multipart/form-data">
            <input type="text" name="_method" value="post" hidden>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="titik_awal" class="col-form-label">Titik Awal</label>
                </div>
                <div class="col-12 col-lg-8">
                    <select class="form-control js-example-basic-single" name="titik_awal" id="titik_awal">
                        <option value="" hidden>pilih titik awal</option>
                        <?php foreach ($destinasi as $d) : ?>
                            <option value="<?= $d['id']; ?>"><?= $d['nama_destinasi']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="lokasi" class="col-form-label">Titik Tujuan</label>
                </div>
                <div class="col-12 col-lg-8">
                    <select class="form-control js-example-basic-single" name="titik_tujuan" id="titik_tujuan">
                        <option value="" hidden>pilih titik tujuan</option>
                        <?php foreach ($destinasi as $d) : ?>
                            <option value="<?= $d['id']; ?>"><?= $d['nama_destinasi']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="jarak" class="col-form-label">Jarak</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="number" id="jarak" class="form-control" name="jarak" step="any" placeholder="Masukkan jarak dalam kilometer" required>
                </div>
                <hr class="border border-black">
            </div>
            <h4 class="card-title fw-semibold mb-4 text-center">Estimasi Waktu Berdasarkan Kendaraan</h4>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="mobil" class="col-form-label">Mobil</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="number" id="mobil" class="form-control" name="mobil" step="any" placeholder="Masukkan jarak dalam kilometer">
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="motor" class="col-form-label">Motor</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="number" id="motor" class="form-control" name="motor" step="any" placeholder="Masukkan waktu dalam jam">
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="kapal" class="col-form-label">Kapal</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="number" id="kapal" class="form-control" name="kapal" step="any" placeholder="Masukkan waktu dalam jam">
                </div>
                <hr class="border border-black">
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="speedboot" class="col-form-label">Speedboot</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="number" id="speedboot" class="form-control" name="speedboot" step="any" placeholder="Masukkan waktu dalam jam">
                </div>
                <hr class="border border-black">
            </div>
            <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan</button>
        </form>
    </div>
</div>

<script type="text/javascript">
</script>