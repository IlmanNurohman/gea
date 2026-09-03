<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    die('Akses ditolak');
}

$user_id = $_SESSION['user_id'] ?? 0;

// Ambil ID guru yang sedang login
$q_guru = mysqli_query(
    $conn,
    "SELECT id, nama_guru, nip
     FROM guru
     WHERE user_id = '$user_id'
     LIMIT 1"
);

$d_guru = mysqli_fetch_assoc($q_guru);

if (!$d_guru) {
    die('Data guru tidak ditemukan.');
}

$guru_id = (int) $d_guru['id'];

$today = date('Y-m-d');

$kelas_id = $_GET['kelas_id'] ?? '';

// Ambil daftar kelas
$data_kelas = mysqli_query(
    $conn,
    "SELECT *
     FROM kelas
     ORDER BY nama_kelas ASC"
);
$mapel_id = $_GET['mapel_id'] ?? '';

$data_mapel = mysqli_query(
    $conn,
    "SELECT id, nama_mapel
     FROM mapel
     ORDER BY nama_mapel ASC"
);
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
                        <h3 class="fw-bold mb-3">Users</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="fas fa-users"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Manajemen users</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Data Users</a>
                            </li>
                        </ul>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Input Absensi Siswa</h4>
                                    <span>Guru: <strong><?= htmlspecialchars($d_guru['nama_guru']) ?></span>
                                </div>
                                <div class="card-body">

                                    <!-- Pilih Kelas -->
                                    <form method="GET" class="row g-3 mb-4">

                                        <div class="col-md-5">

                                            <label class="form-label">
                                                Pilih Kelas
                                            </label>

                                            <select name="kelas_id" class="form-select" onchange="this.form.submit()">

                                                <option value="">
                                                    -- Pilih Kelas --
                                                </option>

                                                <?php while ($k = mysqli_fetch_assoc($data_kelas)) : ?>

                                                <option value="<?= $k['id'] ?>"
                                                    <?= $kelas_id == $k['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                                </option>

                                                <?php endwhile; ?>

                                            </select>

                                        </div>
                                        <?php if (!empty($kelas_id)) : ?>

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Pilih Mata Pelajaran
                                            </label>

                                            <select name="mapel_id" class="form-select" onchange="this.form.submit()">

                                                <option value="">
                                                    -- Pilih Mata Pelajaran --
                                                </option>

                                                <?php while ($m = mysqli_fetch_assoc($data_mapel)) : ?>

                                                <option value="<?= $m['id'] ?>"
                                                    <?= $mapel_id == $m['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($m['nama_mapel']) ?>
                                                </option>

                                                <?php endwhile; ?>

                                            </select>

                                        </div>

                                        <?php endif; ?>


                                    </form>
                                </div>
                            </div>


                            <?php if (!empty($kelas_id) && !empty($mapel_id)) : ?>

                            <?php

                                // Ambil data siswa berdasarkan kelas
                                $data_siswa = mysqli_query(
                                    $conn,
                                    "SELECT *
                        FROM siswa
                        WHERE kelas_id = '$kelas_id'
                        ORDER BY nama_lengkap ASC"
                                ); ?>

                            <form action="simpan_absensi.php" method="POST">

                                <!-- ID guru -->
                                <input type="hidden" name="guru_id" value="<?= $guru_id ?>">

                                <!-- ID kelas -->
                                <input type="hidden" name="kelas_id" value="<?= htmlspecialchars($kelas_id) ?>">

                                <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">

                                <!-- Tanggal absensi -->
                                <input type="hidden" name="tanggal" value="<?= $today ?>">


                                <div class="card">

                                    <div class="card-header">


                                        <h4 class="card-title">Absensi Siswa</h4>
                                        <span class="text-muted">
                                            (<?= date('d-m-Y') ?>)
                                        </span>
                                    </div>

                                    <div class="card-body">

                                        <?php if (mysqli_num_rows($data_siswa) === 0) : ?>

                                        <div class="alert alert-warning">
                                            Belum ada siswa pada kelas ini.
                                        </div>

                                        <?php else : ?>

                                        <div class="table-responsive">

                                            <table id="basic-datatables"
                                                class="display table table-striped table-hover">

                                                <thead>

                                                    <tr>
                                                        <th width="15%">NISN</th>
                                                        <th width="25%">Nama Siswa</th>
                                                        <th width="35%">Status Kehadiran</th>
                                                        <th width="25%">Keterangan</th>
                                                    </tr>

                                                </thead>

                                                <tbody>

                                                    <?php while ($s = mysqli_fetch_assoc($data_siswa)) : ?>

                                                    <tr>

                                                        <td>
                                                            <?= htmlspecialchars($s['nisn']) ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($s['nama_lengkap']) ?>
                                                        </td>

                                                        <td>

                                                            <div class="form-check form-check-inline">

                                                                <input class="form-check-input" type="radio"
                                                                    name="absensi[<?= $s['id'] ?>]" value="Hadir"
                                                                    checked>

                                                                <label class="form-check-label">
                                                                    Hadir
                                                                </label>

                                                            </div>


                                                            <div class="form-check form-check-inline">

                                                                <input class="form-check-input" type="radio"
                                                                    name="absensi[<?= $s['id'] ?>]" value="Izin">

                                                                <label class="form-check-label">
                                                                    Izin
                                                                </label>

                                                            </div>


                                                            <div class="form-check form-check-inline">

                                                                <input class="form-check-input" type="radio"
                                                                    name="absensi[<?= $s['id'] ?>]" value="Sakit">

                                                                <label class="form-check-label">
                                                                    Sakit
                                                                </label>

                                                            </div>


                                                            <div class="form-check form-check-inline">

                                                                <input class="form-check-input" type="radio"
                                                                    name="absensi[<?= $s['id'] ?>]" value="Alfa">

                                                                <label class="form-check-label">
                                                                    Alfa
                                                                </label>

                                                            </div>

                                                        </td>


                                                        <td>

                                                            <input type="text" name="keterangan[<?= $s['id'] ?>]"
                                                                class="form-control form-control-sm"
                                                                placeholder="Catatan (Opsional)">

                                                        </td>

                                                    </tr>

                                                    <?php endwhile; ?>

                                                </tbody>

                                            </table>

                                        </div>


                                        <button type="submit" name="simpan" class="btn btn-success mt-3">
                                            <i class="fa fa-save"></i>
                                            Simpan Absensi
                                        </button>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </form>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
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