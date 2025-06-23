<?php
require_once 'handler.php';
?>

<div class="card">
    <div class="card-body">
        <h4 class="card-title fw-semibold mb-4 text-center">Edit Data Rute</h4>
        <a href="index.php?page=rute/index" class="btn btn-outline-warning mb-3">
            <i class="fa-solid fa-circle-arrow-left me-2"></i> Kembali
        </a>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>
        <form action="index.php?page=rute/edit_rute&id=<?= $rute['id']; ?>" method="POST" enctype="multipart/form-data">
            <input type="number" name="id" id="" value="<?= $rute['id']; ?>" hidden>
            <input type="text" name="_method" value="put" hidden>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="titik_awal" class="col-form-label">Titik Awal</label>
                </div>
                <div class="col-12 col-lg-8">
                    <select class="form-select js-example-basic-single" name="titik_awal" id="titik_awal">
                        <option value="" hidden>pilih titik awal</option>
                        <?php foreach ($destinasi as $d) : ?>
                            <option value="<?= $d['id']; ?>" <?= $d['id'] === $rute['titik_awal'] ? 'selected' : ''; ?>><?= $d['nama_destinasi']; ?></option>
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
                    <select class="form-select js-example-basic-single" name="titik_tujuan" id="titik_tujuan">
                        <option value="" hidden>pilih titik tujuan</option>
                        <?php foreach ($destinasi as $d) : ?>
                            <option value="<?= $d['id']; ?>" <?= $d['id'] === $rute['titik_tujuan'] ? 'selected' : ''; ?>><?= $d['nama_destinasi']; ?></option>
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
                    <input type="number" id="jarak" class="form-control" name="jarak" step="any" placeholder="Masukkan jarak dalam kilometer" required value="<?= $rute['jarak_km']; ?>">
                </div>
                <hr class="border border-black">
            </div>
            <button type="submit" class="btn btn-success float-end"><i class="fa-solid fa-pen-to-square me-2"></i>Edit</button>
        </form>
    </div>
</div>
