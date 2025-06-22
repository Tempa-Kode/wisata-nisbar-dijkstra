<?php
require_once 'handler.php';
?>

<div class="card">
    <div class="card-body">
        <h4 class="card-title fw-semibold mb-4 text-center">Edit Data Kategori Destinasi</h4>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="text" name="_method" value="put" hidden>
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <label for="nama_destinasi" class="col-form-label">Nama Kategori</label>
                </div>
                <div class="col-12 col-lg-8">
                    <input type="text" id="nama_destinasi" class="form-control" name="nama" value="<?= htmlspecialchars($kategoriEditData['nama']) ?>">
                </div>
                <hr class="border border-black">
            </div>
            <div class="float-end">
                <a href="index.php?page=kategori_destinasi/index" class="btn btn-warning me-2"><i class="fa-solid fa-backward me-2"></i>Kembali</a>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-2"></i>Edit</button>
            </div>
        </form>
    </div>
</div>