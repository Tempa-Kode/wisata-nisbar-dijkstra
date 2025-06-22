<?php
require_once 'handler.php';
?>

<div class="card">
    <div class="card-body">
        <h4 class="card-title fw-semibold mb-4">Data Destinasi</h4>
        <a href="index.php?page=destinasi/tambah_destinasi" class="btn btn-primary">
            <i class="fa-solid fa-square-plus me-2"></i>
            Tambah Destinasi
        </a>
        <div class="table-responsive mt-4">
            <table id="datatable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Destinasi</th>
                        <th>Lokasi</th>
                        <th>Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($destinasi as $item) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($item['nama_destinasi']); ?></td>
                            <td><?= htmlspecialchars($item['lokasi']); ?></td>
                            <td><?= htmlspecialchars($item['kategori']); ?></td>
                            <td class="d-flex gap-2">
                                <a href="index.php?id=<?= $item['id']; ?>&page=destinasi/detail_destinasi" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="index.php?id=<?= $item['id']; ?>&page=destinasi/edit_destinasi" class="btn btn-warning btn-sm">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="" method="post">
                                    <input type="text" name="_method" value="delete" hidden>
                                    <input type="number" name="id" id="" value="<?= $item['id']; ?>" hidden>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?');">
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