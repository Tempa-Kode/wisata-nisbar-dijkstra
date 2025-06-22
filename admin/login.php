<?php
session_start();
require_once '../config/db.php';

// check apakah sudah login
if (isset($_SESSION['id'])) {
    header("Location: /admin/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($email) || empty($password)) {
        $error = "email dan password harus diisi.";
    } else {
        $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $query->execute([$email]);
        $admin = $query->fetch();
        echo $admin['email'];
        if ($admin && md5($password) === $admin['password']) {
            $_SESSION['id'] = $admin['id'];
            $_SESSION['nama'] = $admin['nama'];
            $_SESSION['email'] = $admin['email'];
            header('Location: index.php');
        } else {
            $error = "email atau password salah.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <div
            class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="#" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                    <img src="../assets/images/logos/dark-logo.svg" width="180" alt="">
                                </a>
                                <p class="text-center">Perutean Destinasi Wisata Nias Barat</p>
                                <p class="text-center fw-bold">Login sebagai admin</p>
                                <?php if(isset($error)) :?>
                                    <div class="alert alert-warning" role="alert">
                                        <?= $error; ?>
                                    </div>
                                <?php endif; ?>
                                <form action="login.php" method="POST">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" aria-describedby="emailHelp" name="email" placeholder="email" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="********" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Login</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>