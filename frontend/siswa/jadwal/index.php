<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    die('Akses ditolak');
}

if (!isset($_SESSION['user_id'])) {
    die('Sesi siswa tidak valid. Silakan login ulang.');
}
$user_id = $_SESSION['user_id'];

$q_siswa = mysqli_query($conn, "SELECT kelas_id, nama_lengkap FROM siswa WHERE user_id = '" . mysqli_real_escape_string($conn, $user_id) . "'");
$siswa_row = mysqli_fetch_assoc($q_siswa);

if (!$siswa_row || empty($siswa_row['kelas_id'])) {
    die('Data siswa tidak ditemukan atau belum terhubung ke kelas manapun. Hubungi admin/operator.');
}
$kelas_id = $siswa_row['kelas_id'];

// Ambil nama kelas untuk ditampilkan di header
$q_kelas = mysqli_query($conn, "SELECT nama_kelas FROM kelas WHERE id = '" . mysqli_real_escape_string($conn, $kelas_id) . "'");
$kelas_row = mysqli_fetch_assoc($q_kelas);
$nama_kelas = $kelas_row['nama_kelas'] ?? 'Kelas';

// Fetch jadwal khusus kelas siswa yang login
$query_jadwal = "SELECT jadwal.*, kelas.nama_kelas, mapel.nama_mapel, guru.nama_guru 
                 FROM jadwal 
                 JOIN kelas ON jadwal.kelas_id = kelas.id 
                 JOIN mapel ON jadwal.mapel_id = mapel.id 
                 JOIN guru ON jadwal.guru_id = guru.id 
                 WHERE jadwal.kelas_id = '" . mysqli_real_escape_string($conn, $kelas_id) . "'
                 ORDER BY FIELD(jadwal.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jadwal.jam_masuk ASC";
$res_jadwal = mysqli_query($conn, $query_jadwal);
$jadwal_list = mysqli_fetch_all($res_jadwal, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Jadwal Pelajaran</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="../../../assets/img/kaiadmin/favicon.ico" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="../../../assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
    WebFont.load({
        google: {
            families: ["Public Sans:300,400,500,600,700"]
        },
        custom: {
            families: [
                "Font Awesome 5 Solid",
                "Font Awesome 5 Regular",
                "Font Awesome 5 Brands",
                "simple-line-icons",
            ],
            urls: ["../../../assets/css/fonts.min.css"],
        },
        active: function() {
            sessionStorage.fonts = true;
        },
    });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="../../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../../../assets/css/kaiadmin.min.css" />
</head>

<body>
    <div class="wrapper">
        <div class="sidebar" data-background-color="dark">
            <div class="sidebar-logo">
                <!-- Logo Header -->
                <div class="logo-header" data-background-color="dark">
                    <a href="" class="logo">
                        <img src="../../../assets/img/logo.png" alt="navbar brand"
                            style="height: 30px; margin-right: 10px;" />
                    </a>
                    <div class="nav-toggle">
                        <button class="btn btn-toggle toggle-sidebar">
                            <i class="gg-menu-right"></i>
                        </button>
                        <button class="btn btn-toggle sidenav-toggler">
                            <i class="gg-menu-left"></i>
                        </button>
                    </div>
                    <button class="topbar-toggler more">
                        <i class="gg-more-vertical-alt"></i>
                    </button>
                </div>
                <!-- End Logo Header -->
            </div>
            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    <ul class="nav nav-secondary">

                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="../dashboardSiswa.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Menu</h4>
                        </li>

                        <!-- Materi -->
                        <li class="nav-item">
                            <a href="../materi/index.php">
                                <i class="fas fa-book-open"></i>
                                <p>Materi</p>
                            </a>
                        </li>

                        <!-- Tugas -->
                        <li class="nav-item">
                            <a href="../tugas/index.php">
                                <i class="fas fa-tasks"></i>
                                <p>Tugas</p>
                            </a>
                        </li>

                        <!-- Nilai -->
                        <li class="nav-item">
                            <a href="../nilai/index.php">
                                <i class="fas fa-star"></i>
                                <p>Nilai</p>
                            </a>
                        </li>

                        <!-- Jadwal -->
                        <li class="nav-item">
                            <a href="../adwal/index.php">
                                <i class="fas fa-calendar-alt"></i>
                                <p>Jadwal</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End Sidebar -->

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <div class="logo-header" data-background-color="dark">
                        <a href="" class="logo">
                            <img src="../../../assets/img/logo.png" alt="navbar brand" class="navbar-brand"
                                height="20" />
                        </a>
                        <div class="nav-toggle">
                            <button class="btn btn-toggle toggle-sidebar">
                                <i class="gg-menu-right"></i>
                            </button>
                            <button class="btn btn-toggle sidenav-toggler">
                                <i class="gg-menu-left"></i>
                            </button>
                        </div>
                        <button class="topbar-toggler more">
                            <i class="gg-more-vertical-alt"></i>
                        </button>
                    </div>
                </div>
                <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                    <div class="container-fluid">
                        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                            <li class="nav-item topbar-user dropdown hidden-caret">
                                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#"
                                    aria-expanded="false">
                                    <div class="avatar-sm">
                                        <img src="../../../assets/img/cs admin.png" alt="..."
                                            class="avatar-img rounded-circle" />
                                    </div>
                                    <span class="profile-username">
                                        <span class="fw-bold"><?= $_SESSION ['username']?></span>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-user animated fadeIn">
                                    <div class="dropdown-user-scroll scrollbar-outer">
                                        <li>
                                            <div class="user-box">
                                                <div class="avatar-lg">
                                                    <img src="../../../assets/img/cs admin.png" alt="image profile"
                                                        class="avatar-img rounded" />
                                                </div>
                                                <div class="u-text">
                                                    <h4><?= $_SESSION ['username'] ?></h4>
                                                    <p class="text-muted"><?= $_SESSION ['email'] ?></p>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="../../../logout.php">Logout</a>
                                        </li>
                                    </div>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>

            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Jadwal Pelajaran</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#"><i class="fas fa-calendar-alt"></i></a>
                            </li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#"></a>Manajemen Jadwal</li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Jadwal Kelas Saya</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Jadwal Pelajaran - <?= htmlspecialchars($nama_kelas) ?></h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="display table table-striped table-hover basic-datatables">
                                            <thead>
                                                <tr>
                                                    <th>Hari</th>
                                                    <th>Jam</th>
                                                    <th>Mata Pelajaran</th>
                                                    <th>Guru Pengajar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($jadwal_list) === 0) : ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Belum ada jadwal pelajaran</td>
                                                </tr>
                                                <?php else : ?>
                                                <?php foreach ($jadwal_list as $j) : ?>
                                                <tr>
                                                    <td><span
                                                            class="badge bg-info"><?= htmlspecialchars($j['hari']) ?></span>
                                                    </td>
                                                    <td><?= date('H:i', strtotime($j['jam_masuk'])) ?> -
                                                        <?= date('H:i', strtotime($j['jam_keluar'])) ?></td>
                                                    <td><strong><?= htmlspecialchars($j['nama_mapel']) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($j['nama_guru']) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid d-flex justify-content-center">
                    <div class="copyright">&copy; 2026 All rights reserved.</div>
                </div>
            </footer>
        </div>
    </div>

    <script src="../../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../../assets/js/core/popper.min.js"></script>
    <script src="../../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="../../../assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="../../../assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="../../../assets/js/kaiadmin.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.basic-datatables').DataTable({});
    });
    </script>
</body>

</html>