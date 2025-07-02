<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_name'] !== 'Admin') {
    header("Location: login.php"); 
    exit();
}

// Koneksi ke database
include 'koneksi.php';

// Cek jika pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // ID pengguna yang sedang login

// Menangani proses penambahan pengguna
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

    // Validasi dan upload gambar
    if ($gambar_error === 0) {
        if ($gambar_size < 10000000) { // Batas ukuran file gambar 5MB
            if (move_uploaded_file($gambar_tmp, $upload_dir . $gambar_unique_name)) {
                // Hashing kata sandi sebelum disimpan
                $kata_sandi_hashed = password_hash($kata_sandi, PASSWORD_DEFAULT);

                // Query untuk menambahkan pengguna
                $sql = "INSERT INTO pengguna (nama, email, kata_sandi, gambar, tanggal_daftar) 
                        VALUES ('$nama', '$email', '$kata_sandi_hashed', '$gambar_unique_name', '$tanggal_daftar')";

                if ($conn->query($sql) === TRUE) {
                    $_SESSION['message'] = "Pengguna berhasil ditambahkan!";
                    // Tidak perlu redirect, cukup menampilkan pesan dan daftar pengguna di halaman yang sama
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

// Menangani pengeditan pengguna
if (isset($_POST['edit_user'])) {
    $id_pengguna = $_POST['id_pengguna'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $tanggal_daftar = date('Y-m-d H:i:s');

    // Proses upload gambar jika ada
    $gambar_name = $_FILES['gambar']['name'];
    $gambar_tmp = $_FILES['gambar']['tmp_name'];
    $gambar_error = $_FILES['gambar']['error'];
    $gambar_size = $_FILES['gambar']['size'];
    $gambar_path = '';

    // Jika gambar diupload
    if ($gambar_error === 0) {
        $gambar_unique_name = uniqid() . '_' . basename($gambar_name);
        $gambar_path = 'uploads/' . $gambar_unique_name;
        move_uploaded_file($gambar_tmp, $gambar_path);
    } else {
        // Jika tidak ada gambar baru, gunakan gambar lama
        $gambar_path = $_POST['gambar_lama'];
    }

    // Jika kata sandi diubah
    if (!empty($_POST['kata_sandi'])) {
        $kata_sandi = $_POST['kata_sandi'];
        $kata_sandi_hashed = password_hash($kata_sandi, PASSWORD_DEFAULT);
    } else {
        // Jika kata sandi tidak diubah, tetap gunakan kata sandi lama
        $kata_sandi_hashed = $_POST['kata_sandi_lama'];
    }

    // Query untuk memperbarui data pengguna
    $sql = "UPDATE pengguna SET nama = '$nama', email = '$email', kata_sandi = '$kata_sandi_hashed', gambar = '$gambar_path' WHERE id = '$id_pengguna'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Pengguna berhasil diperbarui!";
        header("Location: user.php"); // Redirect setelah berhasil edit
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
    }
}

// Menangani penghapusan pengguna
if (isset($_GET['delete_user'])) {
    $id_pengguna = $_GET['delete_user'];

    // Query untuk menghapus pengguna
    $sql = "DELETE FROM pengguna WHERE id = '$id_pengguna'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Pengguna berhasil dihapus!";
        header("Location: user.php"); // Redirect setelah berhasil hapus
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
    }
}

// Ambil data pengguna yang akan diedit
$user_to_edit = null;
if (isset($_GET['edit_user'])) {
    $id_pengguna = $_GET['edit_user'];
    $sql_edit = "SELECT * FROM pengguna WHERE id = '$id_pengguna'";
    $result_edit = $conn->query($sql_edit);
    if ($result_edit->num_rows > 0) {
        $user_to_edit = $result_edit->fetch_assoc();
    }
}

// Ambil semua pengguna dari database
$sql_users = "SELECT * FROM pengguna";
$result_users = $conn->query($sql_users);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna</title>

    <!-- Favicon -->
    <link rel="icon" href="assets/icon.png" type="image/x-icon">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .container {
            margin-top: 30px;
        }
        .card-body {
            padding: 1.25rem;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div id="wrapper">
    <ul class="navbar-nav bg-dark sidebar sidebar-dark accordion" id="accordionSidebar">
        <!-- Logo -->
        <div class="sidebar-brand d-flex align-items-center justify-content-center">
            <img src="assets/logo.jpg" alt="Logo" style="width: 80px; height: 80px;">
            <div class="sidebar-brand-text mx-3">Taks Project</div>
        </div>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

  
        <!-- Sidebar Menu -->
        <li class="nav-item">
            <a class="nav-link" href="index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="search.php">
                <i class="fas fa-fw fa-search"></i>
                <span>Cari Proyek & Tugas</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="proyek.php">
                <i class="fas fa-fw fa-briefcase"></i>
                <span>Manajemen Proyek</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="tugas.php">
                <i class="fas fa-fw fa-book"></i>
                <span>Manajemen Tugas</span>
            </a>
        </li>
        <!-- Menu Pengguna hanya tampil jika yang login adalah admin@gmail.com -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_name'] === 'Admin'): ?>
            <li class="nav-item active">
                <a class="nav-link" href="user.php">
                    <i class="fas fa-fw fa-user-plus"></i>
                    <span>Pengguna</span>
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="riwayat_status.php">
                <i class="fas fa-fw fa-history"></i>
                <span>Riwayat Status Tugas</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
     <hr class="sidebar-divider">
    

        <!-- Sidebar Collapse Button -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Navbar Username & Logout -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['user_name']; ?></span>
                         <img class="img-profile rounded-circle" 
     src="uploads/<?php echo isset($user['gambar']) && !empty($user['gambar']) ? $user['gambar'] : 'default-profile.png'; ?>" 
     alt="Profile Picture">

                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Profil
                            </a>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Page Content -->
            <div class="container">
                <h1 class="mb-4"><?php echo isset($user_to_edit) ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?></h1>

                <!-- Menampilkan pesan sukses atau error -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Form untuk menambah atau mengedit pengguna -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?php echo isset($user_to_edit) ? 'Edit Pengguna' : 'Formulir Pengguna Baru'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="user.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_pengguna" value="<?php echo isset($user_to_edit) ? $user_to_edit['id'] : ''; ?>">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Pengguna</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($user_to_edit) ? $user_to_edit['nama'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user_to_edit) ? $user_to_edit['email'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="kata_sandi" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="kata_sandi" name="kata_sandi" placeholder="Kosongkan jika tidak mengubah kata sandi">
                            </div>
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*"><br>
                                <?php if (isset($user_to_edit) && $user_to_edit['gambar']): ?>
                                    <img src="uploads/<?php echo $user_to_edit['gambar']; ?>" alt="Gambar Profil" width="100" height="100" style="border-radius:20px;">
                                <?php endif; ?>
                            </div>
                            <button type="submit" name="<?php echo isset($user_to_edit) ? 'edit_user' : 'submit'; ?>" class="btn btn-primary"><?php echo isset($user_to_edit) ? 'Perbarui Pengguna' : 'Tambah Pengguna'; ?></button>
                        </form>
                    </div>
                </div>

                <!-- Daftar Pengguna -->
                <h3>Daftar Pengguna</h3>
                <div class="row">
                    <?php if ($result_users->num_rows > 0): ?>
                        <?php while ($user = $result_users->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5><?php echo $user['nama']; ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                                        <p><strong>Foto Profil:</strong> <img src="uploads/<?php echo $user['gambar']; ?>" alt="Gambar Profil" width="100" height="100" style="border-radius:20px;"></p>
                                        <p><strong>Tanggal Daftar:</strong> <?php echo $user['tanggal_daftar']; ?></p>
                                        <a href="user.php?edit_user=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="user.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                Belum ada pengguna yang ditambahkan.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>© 2025 Manajemen Tugas dan Proyek</span>
        </div>
    </div>
</footer>
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
