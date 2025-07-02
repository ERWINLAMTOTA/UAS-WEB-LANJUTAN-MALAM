<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// Ambil data pengguna yang sedang login
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM pengguna WHERE id = '$user_id'";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();

// Menangani pembaruan biodata pengguna
if (isset($_POST['update_profile'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $kata_sandi = $_POST['kata_sandi'];
    
    // Proses upload gambar jika ada
    $gambar_name = $_FILES['gambar']['name'];
    $gambar_tmp = $_FILES['gambar']['tmp_name'];
    $gambar_error = $_FILES['gambar']['error'];
    $gambar_size = $_FILES['gambar']['size'];
    $gambar_path = '';

    // Jika ada gambar yang diupload
    if ($gambar_error === 0) {
        $gambar_unique_name = uniqid() . '_' . basename($gambar_name);
        $gambar_path = 'uploads/' . $gambar_unique_name;
        move_uploaded_file($gambar_tmp, $gambar_path);
    } else {
        // Jika tidak ada gambar yang diupload, gunakan gambar lama
        $gambar_path = $_POST['gambar_lama'];
    }

    // Jika kata sandi diubah
    if (!empty($kata_sandi)) {
        $kata_sandi_hashed = password_hash($kata_sandi, PASSWORD_DEFAULT);
    } else {
        // Jika kata sandi tidak diubah, tetap gunakan kata sandi lama
        $kata_sandi_hashed = $_POST['kata_sandi_lama'];
    }

    // Query untuk memperbarui data pengguna
    $sql_update = "UPDATE pengguna SET nama = '$nama', email = '$email', kata_sandi = '$kata_sandi_hashed', gambar = '$gambar_path' WHERE id = '$user_id'";

    if ($conn->query($sql_update) === TRUE) {
        $_SESSION['message'] = "Biodata berhasil diperbarui!";
        header("Location: profile.php"); // Redirect setelah berhasil update
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link rel="icon" href="assets/icon.png" type="image/x-icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 30px;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
        }
        .card-body {
            padding: 1.25rem;
        }
    </style>
</head>
<body id="page-top">

<!-- Sidebar -->
<div id="wrapper">
    <ul class="navbar-nav bg-dark sidebar sidebar-dark accordion" id="accordionSidebar">
        <div class="sidebar-brand d-flex align-items-center justify-content-center">
            <img src="assets/logo.jpg" alt="Logo" style="width: 80px; height: 80px;">
            <div class="sidebar-brand-text mx-3">Taks Project</div>
        </div>
        <hr class="sidebar-divider my-0">
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
            <li class="nav-item">
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

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $user['nama']; ?></span>
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
                <h1 class="mb-4">Edit Profil</h1>

                <!-- Menampilkan pesan sukses atau error -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <?php unset($_SESSION['message']); // Menghapus pesan setelah ditampilkan ?>
                    </div>
                <?php endif; ?>

                <!-- Form untuk mengedit profil -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Edit Biodata Pengguna</h5>
                    </div>
                    <div class="card-body">
                        <form action="profile.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_pengguna" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="gambar_lama" value="<?php echo $user['gambar']; ?>">
                            <input type="hidden" name="kata_sandi_lama" value="<?php echo $user['kata_sandi']; ?>">

                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Pengguna</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $user['nama']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="kata_sandi" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="kata_sandi" name="kata_sandi" placeholder="Kosongkan jika tidak mengubah kata sandi">
                            </div>
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*"><br>
                                <?php if ($user['gambar']): ?>
                                    <img src="uploads/<?php echo $user['gambar']; ?>" alt="Gambar Profil" width="300px" style="border-radius:30px;">
                                <?php endif; ?>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">Perbarui Profil</button>
                        </form>
                    </div>
                </div>
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



<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>

