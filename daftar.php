<?php
session_start();

// Koneksi ke database
include 'koneksi.php';

// Menangani proses pendaftaran pengguna
if (isset($_POST['submit'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $kata_sandi = $_POST['kata_sandi'];
    $tanggal_daftar = date('Y-m-d H:i:s');

    // Proses upload gambar
    $gambar_name = $_FILES['gambar']['name'];
    $gambar_tmp = $_FILES['gambar']['tmp_name'];
    $gambar_error = $_FILES['gambar']['error'];
    $gambar_size = $_FILES['gambar']['size'];

    // Direktori untuk menyimpan gambar
    $upload_dir = 'uploads/';
    $gambar_unique_name = uniqid() . '_' . basename($gambar_name);
    $gambar_path = $upload_dir . $gambar_unique_name;

    // Validasi dan upload gambar
    if ($gambar_error === 0) {
        if ($gambar_size < 5000000) { // Batas ukuran file gambar 5MB
            if (move_uploaded_file($gambar_tmp, $gambar_path)) {
                // Hashing kata sandi sebelum disimpan
                $kata_sandi_hashed = password_hash($kata_sandi, PASSWORD_DEFAULT);

                // Query untuk menambahkan pengguna
                $sql = "INSERT INTO pengguna (nama, email, kata_sandi, gambar, tanggal_daftar) 
                        VALUES ('$nama', '$email', '$kata_sandi_hashed', '$gambar_unique_name', '$tanggal_daftar')";

                if ($conn->query($sql) === TRUE) {
                    $_SESSION['message'] = "Pendaftaran berhasil!";
                    header("Location: login.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Error: " . $conn->error;
                }
            } else {
                $_SESSION['message'] = "Gagal meng-upload gambar!";
            }
        } else {
            $_SESSION['message'] = "Ukuran gambar terlalu besar! Maksimal 5MB.";
        }
    } else {
        $_SESSION['message'] = "Terjadi kesalahan saat meng-upload gambar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Manajemen Tugas dan Proyek</title>

    <!-- Favicon -->
    <link rel="icon" href="assets/icon.png" type="image/x-icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
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

<!-- Daftar Form -->
<div class="container login-container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0 card-login">
                <div class="card-header text-center">
                    <h3>Daftar Pengguna Baru</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['message']; ?>
                            <?php unset($_SESSION['message']); // Menghapus pesan setelah ditampilkan ?>
                        </div>
                    <?php endif; ?>
                    <form action="daftar.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="kata_sandi" class="form-label">Kata Sandi</label>
                            <input type="password" class="form-control" id="kata_sandi" name="kata_sandi" placeholder="Masukkan kata sandi" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="gambar" class="form-label">Pilih Gambar Profil</label>
                            <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" required>
                        </div>
                        <button type="submit" name="submit" class="btn btn-login btn-block w-100">Daftar</button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <p class="text-white">Sudah punya akun? <a href="login.php" class="text-white">Login di sini</a></p>
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

</body>
</html>
