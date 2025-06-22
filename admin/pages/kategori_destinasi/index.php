<?php
require_once 'handler.php';
?>

<div class="card">
    <div class="card-body">
        <h4 class="card-title fw-semibold mb-4">Data Kategori Destinasi</h4>
         <?php if(isset($error)) :?>
            <div class="alert alert-warning" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
            <i class="fa-solid fa-square-plus me-2"></i>
            Tambah Data
        </button>
        <div class="table-responsive mt-4">
            <table id="datatable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Tanggal Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 0; ?>
                    <?php foreach ($kategori as $k) : ?>
                        <?php $no++; ?>
                        <tr>
                            <td><?= $no ?></td>
                            <td><?= htmlspecialchars($k['nama']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($k['created_at'])) ?></td>
                            <td class="d-flex justify-content-center">
                                <a href="index.php?id=<?= $k['id']?>&page=kategori_destinasi/edit_data" class="btn btn-sm btn-warning me-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="" method="post">
                                    <input type="text" name="_method" value="delete" hidden>
                                    <input type="number" name="id" id="id" value="<?= $k['id'] ?>" hidden>
                                    <button type="submit" class="btn btn-sm btn-danger">
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

<!-- Modal Tambah Data -->
<div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="tambahDataModalLabel">Input Data Kategori</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" name="_method" value="post" hidden>
                            <label for="nama_kategori" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Masukkan nama kategori">
                            <div id="namaKategoriHelp" class="form-text">masukan kategori dengan benar, cth: pantai, gunung, air terjun, dll.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-circle-xmark me-2"></i>Tutup</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan</button>
                    </div>
                </form>
            </div>
    </div>
</div>
<!-- Akhir Modal Tambah Data -->

<!-- Modal Edit Data -->
<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="editDataModalLabel">Edit Data Kategori</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" name="_method" value="put" hidden>
                            <label for="nama_kategori_edit" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Masukkan nama kategori">
                            <div id="namaKategoriHelp" class="form-text">masukan kategori dengan benar, cth: pantai, gunung, air terjun, dll.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-circle-xmark me-2"></i>Tutup</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan</button>
                    </div>
                </form>
            </div>
    </div>
</div>
<!-- Edit Modal Edit Data -->