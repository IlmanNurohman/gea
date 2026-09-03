<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

$today = date('Y-m-d');

// Buka / Tutup Akses Absensi Guru
if (isset($_POST['toggle_sesi'])) {
    $status_baru = $_POST['status'];

    $cek = mysqli_query($conn, "SELECT id FROM sesi_absensi_guru WHERE tanggal = '$today'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE sesi_absensi_guru SET status = '$status_baru' WHERE tanggal = '$today'");
    } else {
        mysqli_query($conn, "INSERT INTO sesi_absensi_guru (tanggal, status) VALUES ('$today', '$status_baru')");
    }

    $pesan = ($status_baru === 'buka') ? 'Absensi Guru Berhasil DIBUKA' : 'Absensi Guru Berhasil DITUTUP';
    $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => $pesan, 'type' => 'success'];
    header("Location: index.php");
    exit;
}

// Cek Status Hari Ini
$q_status = mysqli_query($conn, "SELECT status FROM sesi_absensi_guru WHERE tanggal = '$today'");
$d_status = mysqli_fetch_assoc($q_status);
$status_hari_ini = $d_status['status'] ?? 'tutup';

// Fetch Rekap Absensi Guru Hari Ini
$q_rekap = mysqli_query($conn, "SELECT guru.nama_guru, guru.nip, absensi_guru.jam_masuk, absensi_guru.jam_keluar, absensi_guru.status, absensi_guru.keterangan 
                                FROM guru 
                                LEFT JOIN absensi_guru ON guru.id = absensi_guru.guru_id AND absensi_guru.tanggal = '$today' 
                                ORDER BY guru.nama_guru ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Users</title>
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
        <!-- Sidebar -->
        <div class="sidebar" data-background-color="dark">
            <div class="sidebar-logo">
                <!-- Logo Header -->
                <div class="logo-header" data-background-color="dark">
                    <a href="../index.html" class="logo">
                        <img src="../../../assets/img/" alt="navbar brand" class="navbar-brand" height="20" />
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
                        <li class="nav-item">
                            <a href="../dashboardOperator.php" class="collapsed" aria-expanded="false">
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

                        <!-- Absensi Guru -->
                        <li class="nav-item">
                            <a href="../absensi_guru/index.php">
                                <i class="fas fa-user-check"></i>
                                <p>Absensi Guru</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="../guru/index.php">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <p>Data Guru</p>
                            </a>
                        </li>

                        <!-- Data Siswa -->
                        <li class="nav-item">
                            <a href="../siswa/index.php">
                                <i class="fas fa-user-graduate"></i>
                                <p>Data Siswa</p>
                            </a>
                        </li>

                        <!-- Data Kelas -->
                        <li class="nav-item">
                            <a href="../kelas/index.php">
                                <i class="fas fa-chalkboard"></i>
                                <p>Data Kelas</p>
                            </a>
                        </li>

                        <!-- Users -->
                        <li class="nav-item">
                            <a href="../users/index.php">
                                <i class="fas fa-users-cog"></i>
                                <p>Users</p>
                            </a>
                        </li>

                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Jadwal</h4>
                        </li>

                        <!-- Jadwal Siswa -->
                        <li class="nav-item">
                            <a href="../jadwal/index.php">
                                <i class="fas fa-calendar-alt"></i>
                                <p>Jadwal</p>
                            </a>
                        </li>

                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Pengumuman</h4>
                        </li>

                        <!-- Pengumuman -->
                        <li class="nav-item">
                            <a href="../pengumuman/index.php">
                                <i class="fas fa-bullhorn"></i>
                                <p>Pengumuman</p>
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
                    <!-- Logo Header -->
                    <div class="logo-header" data-background-color="dark">
                        <a href="../dashboard_superadmin.php" class="logo">
                            <img src="../../../assets/img/" alt="navbar brand" class="navbar-brand" height="20" />
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
                <!-- Navbar Header -->
                <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                    <div class="container-fluid">

                        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                            <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                                    aria-expanded="false" aria-haspopup="true">
                                    <i class="fa fa-search"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-search animated fadeIn">
                                    <form class="navbar-left navbar-form nav-search">
                                        <div class="input-group">
                                            <input type="text" placeholder="Search ..." class="form-control" />
                                        </div>
                                    </form>
                                </ul>
                            </li>

                            <li class="nav-item topbar-user dropdown hidden-caret">
                                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#"
                                    aria-expanded="false">
                                    <div class="avatar-sm">
                                        <img src="../../../assets/img/cs admin.png" alt="..."
                                            class="avatar-img rounded-circle" />
                                    </div>
                                    <span class="profile-username">

                                        <span class="fw-bold">Super Admin</span>
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
                                                    <h4>Super Admin</h4>
                                                    <p class="text-muted">superadmin@gmail.com</p>
                                                    <a href="profile.html" class="btn btn-xs btn-secondary btn-sm">View
                                                        Profile</a>
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
                <!-- End Navbar -->
            </div>
            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Absensi Guru</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="fas fa-user-check"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Manajemen Absensi</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Absensi Guru</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">


                            <!-- Panel Tombol Buka/Tutup -->
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Kontrol Absensi Guru (<?= date('d-m-Y') ?>)</h4>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5>Status Akses Absensi Guru Hari Ini:
                                            <?php if ($status_hari_ini === 'buka') : ?>
                                            <span class="badge bg-success">DIBUKA</span>
                                            <?php else : ?>
                                            <span class="badge bg-danger">DITUTUP</span>
                                            <?php endif; ?>
                                        </h5>
                                    </div>
                                    <div>
                                        <form method="POST">
                                            <?php if ($status_hari_ini === 'buka') : ?>
                                            <input type="hidden" name="status" value="tutup">
                                            <button type="submit" name="toggle_sesi" class="btn btn-danger">
                                                <i class="fa fa-lock"></i> Tutup Absensi Guru
                                            </button>
                                            <?php else : ?>
                                            <input type="hidden" name="status" value="buka">
                                            <button type="submit" name="toggle_sesi" class="btn btn-success">
                                                <i class="fa fa-key"></i> Buka Akses Absensi Guru
                                            </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabel Monitoring Kehadiran Guru -->
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Daftar Kehadiran Guru Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>NIP</th>
                                                    <th>Nama Guru</th>
                                                    <th>Jam Masuk</th>
                                                    <th>Jam Pulang</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($r = mysqli_fetch_assoc($q_rekap)) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($r['nip']) ?></td>
                                                    <td><?= htmlspecialchars($r['nama_guru']) ?></td>
                                                    <td><?= $r['jam_masuk'] ?? '-' ?></td>
                                                    <td><?= $r['jam_keluar'] ?? '-' ?></td>
                                                    <td>
                                                        <?php if ($r['status'] === 'Hadir') : ?>
                                                        <span class="badge bg-success">Hadir</span>
                                                        <?php elseif ($r['status'] === 'Izin' || $r['status'] === 'Sakit') : ?>
                                                        <span class="badge bg-warning"><?= $r['status'] ?>
                                                            (<?= htmlspecialchars($r['keterangan']) ?>)</span>
                                                        <?php else : ?>
                                                        <span class="badge bg-secondary">Belum Absen</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
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

                    <div class="copyright ">
                        &copy; 2026 All rights reserved.
                    </div>

                </div>
            </footer>
        </div>
    </div>

    <!--   Core JS Files   -->
    <script src="../../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../../assets/js/core/popper.min.js"></script>
    <script src="../../../assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="../../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <!-- Datatables -->
    <script src="../../../assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Sweet Alert -->
    <script src="../../../assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <!-- Kaiadmin JS -->
    <script src="../../../assets/js/kaiadmin.min.js"></script>
</body>

</html>