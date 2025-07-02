<?php
session_start();

// Koneksi ke database
include 'koneksi.php';

// Cek jika pengguna sudah login, maka langsung dialihkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Menangani proses login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Gunakan prepared statement untuk mencegah SQL Injection
    $sql = "SELECT * FROM pengguna WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email); // "s" untuk string
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek jika data pengguna ditemukan
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $user['kata_sandi'])) {
            // Jika login berhasil, simpan data pengguna di session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            // Alihkan ke halaman utama
            header("Location: index.php");
            exit();
        } else {
            $message = "Email atau password salah!";
        }
    } else {
        $message = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manajemen Tugas dan Proyek</title>

    <!-- Favicon -->
    <link rel="icon" href="assets/icon.png" type="image/x-icon">
 <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        body {
            background-image: url('assets/login-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100%;
        }
        .card-login {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .login-container {
            margin-top: 10%;
        }
        .card-header {
            background-color: #343a40;
            color: white;
            font-size: 1.5rem;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .btn-login {
            background-color: #4e73df;
            border-color: #4e73df;
            color: white;
        }
        .btn-login:hover {
            background-color: #375a7f;
            border-color: #375a7f;
        }
        .alert {
            width: 100%;
        }
    </style>
</head>
<body>


<!-- Login Form -->
<div class="container login-container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0 card-login">
                <div class="card-header text-center">
                    <h3>Login</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <form action="login.php" method="POST">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-login btn-block w-100">Login</button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <p class="text-white">Belum punya akun? <a href="daftar.php" class="text-white">Daftar di sini</a></p>
            </div>
        </div>
    </div>
</div>
  <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>
</body>
</html>
