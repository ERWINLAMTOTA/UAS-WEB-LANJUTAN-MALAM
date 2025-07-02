<?php
session_start();

// Koneksi ke database
include 'koneksi.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // ID pengguna yang sedang login

// Menangani penambahan proyek baru
if (isset($_POST['submit'])) {
    $nama_proyek = $_POST['nama_proyek'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_tenggat = $_POST['tanggal_tenggat'];

    // Query untuk menambahkan proyek baru
    $sql = "INSERT INTO proyek (nama_proyek, deskripsi, tanggal_mulai, tanggal_tenggat, id_pengguna)
            VALUES ('$nama_proyek', '$deskripsi', '$tanggal_mulai', '$tanggal_tenggat', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Proyek berhasil ditambahkan!";
        header("Location: proyek.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        header("Location: proyek.php");
        exit();
    }
}

// Menangani proses pengeditan proyek
if (isset($_POST['edit_proyek'])) {
    $id_proyek = $_POST['id_proyek'];
    $nama_proyek = $_POST['nama_proyek'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_tenggat = $_POST['tanggal_tenggat'];

    // Query untuk memperbarui data proyek
    $sql = "UPDATE proyek SET nama_proyek = '$nama_proyek', deskripsi = '$deskripsi', tanggal_mulai = '$tanggal_mulai', tanggal_tenggat = '$tanggal_tenggat' WHERE id = '$id_proyek' AND id_pengguna = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Proyek berhasil diperbarui!";
        header("Location: proyek.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        header("Location: proyek.php");
        exit();
    }
}

// Menangani proses penghapusan proyek
if (isset($_GET['hapus'])) {
    $id_proyek = $_GET['hapus'];

    // Pertama, hapus tugas yang terkait dengan proyek ini
    $sql_tugas = "DELETE FROM tugas WHERE id_proyek = '$id_proyek' AND id_pengguna = '$user_id'";
    $conn->query($sql_tugas); // Hapus semua tugas yang terkait

    // Query untuk menghapus proyek
    $sql = "DELETE FROM proyek WHERE id = '$id_proyek' AND id_pengguna = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Proyek beserta tugas terkait berhasil dihapus!";
        header("Location: proyek.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        header("Location: proyek.php");
        exit();
    }
}

// Ambil data proyek yang ingin diedit
$project = null;
if (isset($_GET['edit'])) {
    $id_proyek = $_GET['edit'];
    $sql_edit = "SELECT * FROM proyek WHERE id = '$id_proyek' AND id_pengguna = '$user_id'";
    $result_edit = $conn->query($sql_edit);
    if ($result_edit->num_rows > 0) {
        $project = $result_edit->fetch_assoc();
    }
}

// Ambil semua proyek dari database
$sql = "SELECT * FROM proyek WHERE id_pengguna = '$user_id' ORDER BY tanggal_tenggat ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Proyek</title>

    <!-- Favicon -->
    <link rel="icon" href="assets/icon.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 30px;
        }
        .task-card {
            margin-bottom: 20px;
        }
        .alert {
            margin-top: 20px;
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
        <li class="nav-item active">
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
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                <h1 class="mb-4">Manajemen Proyek</h1>

                <!-- Menampilkan pesan sukses atau error -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <?php unset($_SESSION['message']); // Menghapus pesan setelah ditampilkan ?>
                    </div>
                <?php endif; ?>

                <!-- Form untuk menambah atau mengedit proyek -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?php echo isset($project) ? 'Edit Proyek' : 'Tambah Proyek Baru'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="proyek.php" method="POST">
                            <input type="hidden" name="id_proyek" value="<?php echo isset($project) ? $project['id'] : ''; ?>">
                            <div class="mb-3">
                                <label for="nama_proyek" class="form-label">Nama Proyek</label>
                                <input type="text" class="form-control" id="nama_proyek" name="nama_proyek" value="<?php echo isset($project) ? $project['nama_proyek'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?php echo isset($project) ? $project['deskripsi'] : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo isset($project) ? $project['tanggal_mulai'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_tenggat" class="form-label">Tanggal Tenggat</label>
                                <input type="date" class="form-control" id="tanggal_tenggat" name="tanggal_tenggat" value="<?php echo isset($project) ? $project['tanggal_tenggat'] : ''; ?>" required>
                            </div>
                            <button type="submit" name="<?php echo isset($project) ? 'edit_proyek' : 'submit'; ?>" class="btn btn-primary"><?php echo isset($project) ? 'Perbarui Proyek' : 'Tambah Proyek'; ?></button>
                        </form>
                    </div>
                </div>

                <!-- Menampilkan daftar proyek -->
                <h3>Daftar Proyek</h3>
                <div class="row">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($project = $result->fetch_assoc()): ?>
                            <div class="col-md-4 task-card">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><?php echo $project['nama_proyek']; ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Tanggal Mulai:</strong> <?php echo $project['tanggal_mulai']; ?></p>
                                        <p><strong>Tanggal Tenggat:</strong> <?php echo $project['tanggal_tenggat']; ?></p>
                                        <p><strong>Deskripsi:</strong> <?php echo $project['deskripsi']; ?></p>
                                        <a href="proyek.php?edit=<?php echo $project['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="proyek.php?hapus=<?php echo $project['id']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                Belum ada proyek yang ditambahkan.
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
