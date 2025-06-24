<div class="row">
    <!-- Data Destinasi -->
    <div class="col-lg-6">
        <div class="card overflow-hidden">
            <div class="card-body p-4">
                <h5 class="card-title mb-9 fw-semibold">Data Destinasi</h5>
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="fw-semibold mb-3"><?php echo $dataDestinations['count'] ?> Data</h4>
                        <div class="d-flex align-items-center mb-3">
                            <span
                                class="me-1 rounded-circle bg-light-success round-20 d-flex align-items-center justify-content-center">
                                <i class="ti ti-arrow-up-left text-success"></i>
                            </span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex justify-content-center">
                            <div id="breakup"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Akhir Data Destinasi -->

    <!-- Data Rute -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="row alig n-items-start">
                    <div class="col-8">
                        <h5 class="card-title mb-9 fw-semibold"> Data Rute </h5>
                        <h4 class="fw-semibold mb-3"><?php echo $dataRoutes['count'] ?> Data</h4>
                        <div class="d-flex align-items-center pb-1">
                            <span
                                class="me-2 rounded-circle bg-light-danger round-20 d-flex align-items-center justify-content-center">
                                <i class="ti ti-arrow-down-right text-danger"></i>
                            </span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex justify-content-end">
                            <div
                                class="text-white bg-secondary rounded-circle p-6 d-flex align-items-center justify-content-center">
                                <i class="ti ti-currency-dollar fs-6"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="earning"></div>
        </div>
    </div>
    <!-- Akhir Data Rute -->
</div>

<div class="card p-5 text-center">
    <h1>👋 Selamat Datang <?php echo $_SESSION['nama']?> di Dashboard Admin</h1>
    <div class="row">
        <p>Kelola informasi wisata dan rute perjalanan dengan mudan dan efisien</p>
        <p>Di sini Anda dapat menambahkan, mengedit, atau menghapus data destinasi wisata, seta</p>
        <p>mengatur perjalanan terbaik bagi para pengunjung</p>
    </div>
    <hr>
    <h2 class="fst-italic">"Data yang baik adalah awal dari kunjungan yang berkesan."</h2>
</div>