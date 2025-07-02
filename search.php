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

// Menangani pencarian tugas dan proyek berdasarkan filter
$filter_tugas = "";
$filter_proyek = "";

// Jika form pencarian disubmit
if (isset($_POST['search'])) {
    $nama_tugas = $_POST['nama_tugas'];
    $status_tugas = $_POST['status_tugas'];
    $tanggal_tenggat_tugas = $_POST['tanggal_tenggat_tugas'];
    $nama_proyek = $_POST['nama_proyek'];

    // Menambahkan filter pencarian tugas
    if (!empty($nama_tugas)) {
        $filter_tugas .= " AND tugas.nama_tugas LIKE '%$nama_tugas%'";
    }
    if (!empty($status_tugas)) {
        $filter_tugas .= " AND tugas.status = '$status_tugas'";
    }
    if (!empty($tanggal_tenggat_tugas)) {
        $filter_tugas .= " AND tugas.tanggal_tenggat = '$tanggal_tenggat_tugas'";
    }

    // Menambahkan filter pencarian proyek
    if (!empty($nama_proyek)) {
        $filter_proyek .= " AND proyek.nama_proyek LIKE '%$nama_proyek%'";
    }
}

// Query untuk mencari tugas berdasarkan filter
$sql_tugas = "SELECT * FROM tugas
              JOIN proyek ON tugas.id_proyek = proyek.id
              WHERE tugas.id_pengguna = '$user_id' $filter_tugas
              ORDER BY tugas.tanggal_tenggat ASC";

$result_tugas = $conn->query($sql_tugas);

// Query untuk mencari proyek berdasarkan filter
$sql_proyek = "SELECT * FROM proyek
               WHERE proyek.id_pengguna = '$user_id' $filter_proyek
               ORDER BY proyek.tanggal_tenggat ASC";

$result_proyek = $conn->query($sql_proyek);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Tugas dan Proyek</title>

    <!-- Favicon -->
    <link rel="icon" href="assets/icon.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
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
        <li class="nav-item active">
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
                <h1 class="mb-4">Pencarian Tugas dan Proyek</h1>

                <!-- Form Pencarian -->
                <form action="search.php" method="POST">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="nama_tugas">Nama Tugas</label>
                            <input type="text" class="form-control" id="nama_tugas" name="nama_tugas" placeholder="Cari Tugas">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="status_tugas">Status Tugas</label>
                            <select class="form-control" id="status_tugas" name="status_tugas">
                                <option value="">Pilih Status</option>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tanggal_tenggat_tugas">Tanggal Tenggat Tugas</label>
                            <input type="date" class="form-control" id="tanggal_tenggat_tugas" name="tanggal_tenggat_tugas">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nama_proyek">Nama Proyek</label>
                            <input type="text" class="form-control" id="nama_proyek" name="nama_proyek" placeholder="Cari Proyek">
                        </div>
                    </div>
                    <button type="submit" name="search" class="btn btn-primary">Cari</button>
                </form>

                <hr>

                <!-- Menampilkan hasil pencarian Tugas -->
                <h3>Hasil Pencarian Tugas</h3>
                <div class="row">
                    <?php if ($result_tugas->num_rows > 0): ?>
                        <?php while ($task = $result_tugas->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5><?php echo $task['nama_tugas']; ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Status:</strong> <?php echo $task['status']; ?></p>
                                        <p><strong>Tanggal Mulai:</strong> <?php echo $task['tanggal_mulai']; ?></p>
                                        <p><strong>Tanggal Tenggat:</strong> <?php echo $task['tanggal_tenggat']; ?></p>
                                        <p><strong>Deskripsi:</strong> <?php echo $task['deskripsi']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                Tidak ada tugas yang ditemukan.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <hr>

                <!-- Menampilkan hasil pencarian Proyek -->
                <h3>Hasil Pencarian Proyek</h3>
                <div class="row">
                    <?php if ($result_proyek->num_rows > 0): ?>
                        <?php while ($project = $result_proyek->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5><?php echo $project['nama_proyek']; ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Tanggal Mulai:</strong> <?php echo $project['tanggal_mulai']; ?></p>
                                        <p><strong>Tanggal Tenggat:</strong> <?php echo $project['tanggal_tenggat']; ?></p>
                                        <p><strong>Deskripsi:</strong> <?php echo $project['deskripsi']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                Tidak ada proyek yang ditemukan.
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




<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
