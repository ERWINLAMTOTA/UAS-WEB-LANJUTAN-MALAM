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

// Ambil data pengguna
$sql_user = "SELECT * FROM pengguna WHERE id = '$user_id'";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();

// Ambil data proyek dan tugas dari database
$sql_proyek = "SELECT COUNT(*) AS total_proyek FROM proyek WHERE id_pengguna = '$user_id'";
$result_proyek = $conn->query($sql_proyek);
$total_proyek = $result_proyek->fetch_assoc()['total_proyek'];

$sql_tugas = "SELECT COUNT(*) AS total_tugas, 
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed
                    FROM tugas WHERE id_pengguna = '$user_id'";
$result_tugas = $conn->query($sql_tugas);
$tugas = $result_tugas->fetch_assoc();

// Menangani To-Do List
if (isset($_POST['add_todo'])) {
    $todo = $_POST['todo'];
    $sql_todo = "INSERT INTO todo (todo, id_pengguna) VALUES ('$todo', '$user_id')";
    $conn->query($sql_todo);
}

if (isset($_GET['delete_todo'])) {
    $todo_id = $_GET['delete_todo'];
    $sql_delete_todo = "DELETE FROM todo WHERE id = '$todo_id' AND id_pengguna = '$user_id'";
    $conn->query($sql_delete_todo);
}

// Ambil semua to-do list
$sql_todo_list = "SELECT * FROM todo WHERE id_pengguna = '$user_id'";
$result_todo_list = $conn->query($sql_todo_list);

// Logika untuk menambahkan alert jika tugas mendekati tenggat atau sudah lewat
$sql_tugas = "SELECT * FROM tugas WHERE id_pengguna = '$user_id'";
$result_tugas = $conn->query($sql_tugas);

while ($task = $result_tugas->fetch_assoc()) {
    $tanggal_tenggat = strtotime($task['tanggal_tenggat']);
    $tanggal_sekarang = time();
    $selisih_waktu = $tanggal_tenggat - $tanggal_sekarang;

    // Hitung selisih dalam hari, jam, dan menit
    $selisih_hari = floor($selisih_waktu / (60 * 60 * 24)); // Hitung hari
    $selisih_jam = floor(($selisih_waktu % (60 * 60 * 24)) / (60 * 60)); // Hitung jam
    $selisih_menit = floor(($selisih_waktu % (60 * 60)) / 60); // Hitung menit

    if ($selisih_hari <= 3 && $selisih_hari > 0) {
        // Tugas mendekati tenggat waktu
        $_SESSION['alert_message'] = "Tugas '" . $task['nama_tugas'] . "' akan segera tenggat dalam " . $selisih_hari . " hari, " . $selisih_jam . " jam, " . $selisih_menit . " menit.";
        $_SESSION['alert_type'] = 'warning'; // Pesan peringatan
    } elseif ($selisih_hari < 0) {
        // Tugas sudah lewat tenggat waktu
        $_SESSION['alert_message'] = "Tugas '" . $task['nama_tugas'] . "' telah melewati tenggat waktu.";
        $_SESSION['alert_type'] = 'danger'; // Pesan kesalahan
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Favicon -->
    <link rel="icon" href="assets/icon.png" type="image/x-icon">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .card-body {
            padding: 1.25rem;
        }
        .content-wrapper {
            margin-top: 30px;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
        }
        .card-footer {
            background-color: #f8f9fc;
        }
        .text-primary {
            color: #4e73df;
        }
    </style>
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
        <li class="nav-item active">
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
            <div class="container-fluid">
                <h1 class="mb-4">Dashboard</h1>

    <!-- Menampilkan pesan alert -->
                <?php if (isset($_SESSION['alert_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['alert_type']; ?>" role="alert">
                        <?php echo $_SESSION['alert_message']; ?>
                        <?php unset($_SESSION['alert_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Total Proyek & Total Tugas -->
                <div class="row">
                    <!-- Total Proyek -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="fas fa-briefcase fa-2x text-primary"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Proyek</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_proyek; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tugas -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="fas fa-book fa-2x text-success"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Tugas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $tugas['total_tugas']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tasks -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Tugas Pending</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $tugas['pending']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- In Progress -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="fas fa-spinner fa-2x text-info"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Tugas In Progress</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $tugas['in_progress']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- In Progress -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="fas fa-check fa-2x text-success"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Tugas Completed</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $tugas['completed']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik Status Tugas -->
                <div class="row">
                    <div class="col-xl-12 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Grafik Status Tugas</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="taskStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- To-Do List -->
                <div class="row">
                    <div class="col-xl-12 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">To-Do List</h6>
                            </div>
                            <div class="card-body">
                                <form action="index.php" method="POST">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="todo" placeholder="Tambah tugas baru..." required>
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit" name="add_todo">
                                                <i class="fas fa-plus"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Daftar To-Do -->
                                <ul class="list-group">
                                    <?php while ($todo = $result_todo_list->fetch_assoc()): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?php echo $todo['todo']; ?>
                                            <a href="index.php?delete_todo=<?php echo $todo['id']; ?>" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
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
    </div>
</div>



<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>

<!-- Chart.js for visualizations -->
<script src="vendor/chart.js/Chart.min.js"></script>

<script>
// Chart.js setup for Task Status
var ctx = document.getElementById('taskStatusChart').getContext('2d');
var taskStatusChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Pending', 'In Progress', 'Completed'],
        datasets: [{
            label: 'Status Tugas',
            data: [<?php echo $tugas['pending']; ?>, <?php echo $tugas['in_progress']; ?>, <?php echo $tugas['completed']; ?>],
            backgroundColor: ['#ff9999', '#66b3ff', '#99ff99'],
            borderWidth: 1
        }]
    }
});
</script>

</body>
</html>
