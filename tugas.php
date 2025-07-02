<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// Ambil data pengguna
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM pengguna WHERE id = '$user_id'";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();

// Menangani penambahan tugas baru beserta upload gambar
if (isset($_POST['submit'])) {
    $nama_tugas = $_POST['nama_tugas'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_tenggat = $_POST['tanggal_tenggat'];
    $status = $_POST['status'];
    $id_proyek = $_POST['id_proyek'];
    
    // Menangani upload gambar
    $gambar_name = $_FILES['gambar']['name'];
    $gambar_tmp = $_FILES['gambar']['tmp_name'];
    $gambar_error = $_FILES['gambar']['error'];
    $gambar_size = $_FILES['gambar']['size'];

    // Menyimpan gambar ke direktori 'uploads'
    $upload_dir = 'uploads/';
    $gambar_unique_name = uniqid() . '_' . basename($gambar_name);
    $gambar_path = $upload_dir . $gambar_unique_name;

    if ($gambar_error === 0) {
        if ($gambar_size < 5000000) { // Batas ukuran file gambar 5MB
            if (move_uploaded_file($gambar_tmp, $gambar_path)) {
                // Query untuk menambahkan tugas beserta gambar
                $sql = "INSERT INTO tugas (nama_tugas, deskripsi, tanggal_mulai, tanggal_tenggat, status, id_proyek, id_pengguna, gambar)
                        VALUES ('$nama_tugas', '$deskripsi', '$tanggal_mulai', '$tanggal_tenggat', '$status', '$id_proyek', '$user_id', '$gambar_unique_name')";

                if ($conn->query($sql) === TRUE) {
                    // Jika status adalah 'Pending', simpan riwayat status
                    if ($status === 'Pending') {
                        $id_tugas = $conn->insert_id; // Ambil ID tugas yang baru ditambahkan
                        $sql_riwayat = "INSERT INTO riwayat_status_tugas (id_tugas, status_lama, status_baru)
                                        VALUES ('$id_tugas', 'None', '$status')";
                        $conn->query($sql_riwayat);
                    }

                    $_SESSION['message'] = "Tugas berhasil ditambahkan dengan gambar!";
                    header("Location: tugas.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Error: " . $conn->error;
                    header("Location: tugas.php");
                    exit();
                }
            } else {
                $_SESSION['message'] = "Gagal meng-upload gambar!";
                header("Location: tugas.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Ukuran gambar terlalu besar! Maksimal 5MB.";
            header("Location: tugas.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Terjadi kesalahan saat meng-upload gambar!";
        header("Location: tugas.php");
        exit();
    }
}

// Menangani proses pengeditan tugas
if (isset($_POST['edit_tugas'])) {
    $id_tugas = $_POST['id_tugas'];
    $nama_tugas = $_POST['nama_tugas'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_tenggat = $_POST['tanggal_tenggat'];
    $status = $_POST['status'];
    $id_proyek = $_POST['id_proyek'];

    // Ambil status lama dari database
    $sql_status_lama = "SELECT status FROM tugas WHERE id = '$id_tugas'";
    $result_status_lama = $conn->query($sql_status_lama);
    $status_lama = $result_status_lama->fetch_assoc()['status'];

    // Query untuk memperbarui data tugas
    $sql = "UPDATE tugas SET nama_tugas = '$nama_tugas', deskripsi = '$deskripsi', tanggal_mulai = '$tanggal_mulai', tanggal_tenggat = '$tanggal_tenggat', status = '$status', id_proyek = '$id_proyek' WHERE id = '$id_tugas' AND id_pengguna = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        // Cek apakah status berubah
        if ($status_lama !== $status) {
            // Jika status berubah, simpan riwayat perubahan status
            $sql_riwayat = "INSERT INTO riwayat_status_tugas (id_tugas, status_lama, status_baru)
                            VALUES ('$id_tugas', '$status_lama', '$status')";
            $conn->query($sql_riwayat);
        }

        $_SESSION['message'] = "Tugas berhasil diperbarui!";
        header("Location: tugas.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        header("Location: tugas.php");
        exit();
    }
}

// Menangani proses penghapusan tugas
if (isset($_GET['hapus'])) {
    $id_tugas = $_GET['hapus'];

    // Query untuk menghapus tugas
    $sql = "DELETE FROM tugas WHERE id = '$id_tugas' AND id_pengguna = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Tugas berhasil dihapus!";
        header("Location: tugas.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        header("Location: tugas.php");
        exit();
    }
}

// Ambil semua proyek dari database
$sql_proyek = "SELECT * FROM proyek WHERE id_pengguna = '$user_id'";
$result_proyek = $conn->query($sql_proyek);

// Ambil tugas yang akan diedit
$task_to_edit = null;
if (isset($_GET['edit'])) {
    $id_tugas = $_GET['edit'];
    $sql_edit = "SELECT * FROM tugas WHERE id = '$id_tugas' AND id_pengguna = '$user_id'";
    $result_edit = $conn->query($sql_edit);
    if ($result_edit->num_rows > 0) {
        $task_to_edit = $result_edit->fetch_assoc();
    }
}

// Ambil semua tugas dari database
$sql_tugas = "SELECT * FROM tugas WHERE id_pengguna = '$user_id' ORDER BY tanggal_tenggat ASC";
$result_tugas = $conn->query($sql_tugas);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tugas</title>

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
<body id="page-top">

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
        <li class="nav-item active">
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
                <h1 class="mb-4">Manajemen Tugas dan Proyek</h1>

                <!-- Menampilkan pesan sukses atau error -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <?php unset($_SESSION['message']); // Menghapus pesan setelah ditampilkan ?>
                    </div>
                <?php endif; ?>

                <!-- Form untuk menambah tugas baru atau mengedit tugas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?php echo isset($task_to_edit) ? 'Edit Tugas' : 'Tambah Tugas Baru'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="tugas.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_tugas" value="<?php echo isset($task_to_edit) ? $task_to_edit['id'] : ''; ?>">
                            <div class="mb-3">
                                <label for="nama_tugas" class="form-label">Nama Tugas</label>
                                <input type="text" class="form-control" id="nama_tugas" name="nama_tugas" value="<?php echo isset($task_to_edit) ? $task_to_edit['nama_tugas'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?php echo isset($task_to_edit) ? $task_to_edit['deskripsi'] : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo isset($task_to_edit) ? $task_to_edit['tanggal_mulai'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_tenggat" class="form-label">Tanggal Tenggat</label>
                                <input type="date" class="form-control" id="tanggal_tenggat" name="tanggal_tenggat" value="<?php echo isset($task_to_edit) ? $task_to_edit['tanggal_tenggat'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="Pending" <?php echo isset($task_to_edit) && $task_to_edit['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo isset($task_to_edit) && $task_to_edit['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo isset($task_to_edit) && $task_to_edit['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="id_proyek" class="form-label">Pilih Proyek</label>
                                <select class="form-control" id="id_proyek" name="id_proyek" required>
                                    <?php while ($project = $result_proyek->fetch_assoc()): ?>
                                        <option value="<?php echo $project['id']; ?>" <?php echo isset($task_to_edit) && $task_to_edit['id_proyek'] == $project['id'] ? 'selected' : ''; ?>><?php echo $project['nama_proyek']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Pilih Gambar</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*"><br>
                                <?php if (isset($task_to_edit) && $task_to_edit['gambar']): ?>
                                    <img src="uploads/<?php echo $task_to_edit['gambar']; ?>" class="gambar-thumbnail" alt="Gambar tugas" style="height: 100px; border-radius:20px;">
                                <?php endif; ?>
                            </div>
                            <button type="submit" name="<?php echo isset($task_to_edit) ? 'edit_tugas' : 'submit'; ?>" class="btn btn-primary"><?php echo isset($task_to_edit) ? 'Perbarui Tugas' : 'Tambah Tugas'; ?></button>
                        </form>
                    </div>
                </div>

                <!-- Menampilkan daftar tugas -->
                <h3>Daftar Tugas</h3>
                <div class="row">
                    <?php if ($result_tugas->num_rows > 0): ?>
                        <?php while ($task = $result_tugas->fetch_assoc()): ?>
                            <div class="col-md-4 task-card">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><?php echo $task['nama_tugas']; ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Status:</strong> <?php echo $task['status']; ?></p>
                                        <p><strong>Tanggal Mulai:</strong> <?php echo $task['tanggal_mulai']; ?></p>
                                        <p><strong>Tanggal Tenggat:</strong> <?php echo $task['tanggal_tenggat']; ?></p>
                                        <p><strong>Deskripsi:</strong> <?php echo $task['deskripsi']; ?></p>
                                        <?php if ($task['gambar']): ?>
                                            <p><strong>Gambar:</strong> <img src="uploads/<?php echo $task['gambar']; ?>" class="gambar-thumbnail" alt="Gambar tugas" style="height: 100px; border-radius:20px;"></p>
                                        <?php endif; ?>
                                        <a href="tugas.php?edit=<?php echo $task['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="tugas.php?hapus=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                Belum ada tugas yang ditambahkan.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
<br>
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
