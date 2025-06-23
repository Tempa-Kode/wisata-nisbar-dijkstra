<?php
require_once 'handler.php';
?>

<div class="card">
    <div class="card-body">
        <h4 class="card-title fw-semibold mb-4">Data Rute Destinasi</h4>
         <?php if(isset($error)) :?>
            <div class="alert alert-warning" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>
        <a href="index.php?page=rute/tambah_rute" class="btn btn-primary">
            <i class="fa-solid fa-square-plus me-2"></i>
            Tambah Data
        </a>
        <div class="table-responsive mt-4">
            <table id="datatable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Titik Awal</th>
                        <th>Titik Tujuan</th>
                        <th>Jarak (Km)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($rute as $r) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $r['nama_titik_awal']; ?></td>
                            <td><?= $r['nama_titik_tujuan']; ?></td>
                            <td><?= $r['jarak_km']; ?> Km</td>
                            <td class="d-flex gap-2">
                                <a href="index.php?page=rute/edit_rute&id=<?= $r['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fa-solid fa-pencil-alt"></i>
                                </a>
                                <form action="" method="post">
                                    <input type="hidden" name="_method" value="delete">
                                    <input type="hidden" name="id" value="<?= $r['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>