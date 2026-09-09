<?php
session_start();
include '../../backend/koneksi.php';

if ($_SESSION['role'] != 'guru') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die('User belum login');
}

// ===================================================== // AMBIL DATA GURU // =====================================================
$stmt = mysqli_prepare($conn, "SELECT id, nama_guru FROM guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$guru   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$guru) {
    die('Data guru tidak ditemukan');
}
$guru_id = (int) $guru['id'];

$today = date('Y-m-d');

// Nama hari dalam bahasa Indonesia
$hari_map = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];
$hari_ini = $hari_map[date('l')];

// ===================================================== // 1. JADWAL MENGAJAR HARI INI (JUMLAH) // =====================================================
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM jadwal WHERE guru_id = ? AND hari = ?");
mysqli_stmt_bind_param($stmt, "is", $guru_id, $hari_ini);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$jadwal_hari_ini_total = (int) (mysqli_fetch_assoc($result)['total'] ?? 0);
mysqli_stmt_close($stmt);

// ===================================================== // 2. TOTAL SISWA YANG DIAJAR // =====================================================
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(DISTINCT siswa.id) AS total
    FROM jadwal
    INNER JOIN siswa ON siswa.kelas_id = jadwal.kelas_id
    WHERE jadwal.guru_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_siswa_diajar = (int) (mysqli_fetch_assoc($result)['total'] ?? 0);
mysqli_stmt_close($stmt);

// ===================================================== // 3. ABSENSI SAYA BULAN INI (HADIR) // =====================================================
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS total
    FROM absensi_guru
    WHERE guru_id = ?
      AND status = 'H'
      AND MONTH(tanggal) = MONTH(CURDATE())
      AND YEAR(tanggal) = YEAR(CURDATE())
");
mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$absensi_saya_bulan_ini = (int) (mysqli_fetch_assoc($result)['total'] ?? 0);
mysqli_stmt_close($stmt);

// ===================================================== // 4. TIDAK ABSENSI BULAN INI (S/I/A) // =====================================================
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS total
    FROM absensi_guru
    WHERE guru_id = ?
      AND status != 'H'
      AND MONTH(tanggal) = MONTH(CURDATE())
      AND YEAR(tanggal) = YEAR(CURDATE())
");
mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tidak_absensi_bulan_ini = (int) (mysqli_fetch_assoc($result)['total'] ?? 0);
mysqli_stmt_close($stmt);

// ===================================================== // JADWAL MENGAJAR HARI INI (DETAIL) // =====================================================
$stmt = mysqli_prepare($conn, "
    SELECT
        jadwal.id AS jadwal_id,
        jadwal.kelas_id,
        jadwal.mapel_id,
        jadwal.jam_masuk,
        jadwal.jam_keluar,
        mapel.nama_mapel,
        kelas.nama_kelas,
        EXISTS (
            SELECT 1 FROM absensi
            INNER JOIN siswa ON siswa.id = absensi.siswa_id
            WHERE siswa.kelas_id = jadwal.kelas_id
              AND absensi.mapel_id = jadwal.mapel_id
              AND absensi.tanggal = ?
        ) AS sudah_diabsen
    FROM jadwal
    INNER JOIN mapel ON mapel.id = jadwal.mapel_id
    INNER JOIN kelas ON kelas.id = jadwal.kelas_id
    WHERE jadwal.guru_id = ? AND jadwal.hari = ?
    ORDER BY jadwal.jam_masuk ASC
");
mysqli_stmt_bind_param($stmt, "sis", $today, $guru_id, $hari_ini);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$jadwal_hari_ini = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// ===================================================== // PENGUMUMAN TERBARU // =====================================================
$q_pengumuman = mysqli_query(
    $conn,
    "SELECT pengumuman FROM pengumuman ORDER BY created_at DESC LIMIT 1"
);
$pengumuman_row = mysqli_fetch_assoc($q_pengumuman);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Guru Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="../../assets/js/plugin/webfont/webfont.min.js"></script>
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
            urls: ["../../assets/css/fonts.min.css"],
        },
        active: function() {
            sessionStorage.fonts = true;
        },
    });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css" />


</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" data-background-color="dark">
            <div class="sidebar-logo">
                <!-- Logo Header -->
                <div class="logo-header" data-background-color="dark">
                    <a href="../dashboardGuru.php" class="logo">
                        <img src="../../assets/img/logo.png" alt="navbar brand"
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
                        <li class="nav-item active">
                            <a href="../dashboardGuru.php" class="collapsed" aria-expanded="false">
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
                            <a href="materi/index.php">
                                <i class="fas fa-book-open"></i>
                                <p>Materi</p>
                            </a>
                        </li>

                        <!-- Tugas -->
                        <li class="nav-item">
                            <a href="tugas/index.php">
                                <i class="fas fa-tasks"></i>
                                <p>Tugas</p>
                            </a>
                        </li>

                        <!-- Nilai -->
                        <li class="nav-item">
                            <a href="nilai/index.php">
                                <i class="fas fa-star"></i>
                                <p>Nilai</p>
                            </a>
                        </li>

                        <!-- Jadwal -->
                        <li class="nav-item">
                            <a href="jadwal/index.php">
                                <i class="fas fa-calendar-alt"></i>
                                <p>Jadwal</p>
                            </a>
                        </li>

                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Absensi</h4>
                        </li>

                        <!-- Absensi Siswa -->
                        <li class="nav-item">
                            <a href="absensi/index.php">
                                <i class="fas fa-user-check"></i>
                                <p>Absensi Siswa</p>
                            </a>
                        </li>

                        <!-- Absensi Mandiri -->
                        <li class="nav-item">
                            <a href="absensi_mandiri/index.php">
                                <i class="fas fa-user-clock"></i>
                                <p>Absensi Mandiri</p>
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
                        <a href="../dashboardGuru.php" class="logo">
                            <img src="../../assets/img/logo.png" alt="navbar brand" class="navbar-brand" height="20" />
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
                            <li class="nav-item topbar-user dropdown hidden-caret">
                                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#"
                                    aria-expanded="false">
                                    <div class="avatar-sm">
                                        <img src="../../assets/img/cs admin.png" alt="..."
                                            class="avatar-img rounded-circle" />
                                    </div>
                                    <span class="profile-username">
                                        <span class="fw-bold"><?= $_SESSION['username']; ?></span>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-user animated fadeIn">
                                    <div class="dropdown-user-scroll scrollbar-outer">
                                        <li>
                                            <div class="user-box">
                                                <div class="avatar-lg">
                                                    <img src="../../assets/img/cs admin.png" alt="..."
                                                        class="avatar-img rounded" />
                                                </div>
                                                <div class="u-text">
                                                    <h4><?= $_SESSION['username']; ?></h4>
                                                    <p class="text-muted"><?= $_SESSION['email']; ?></p>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="../../logout.php">Logout</a>
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
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h3 class="fw-bold mb-3">Dashboard</h3>
                            <h6 class="op-7 mb-2">
                                Halo, <?= htmlspecialchars($_SESSION['username']) ?>
                            </h6>

                        </div>

                    </div>
                    <div class="row">
                        <!-- Jadwal Hari Ini -->
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Jadwal Hari Ini</p>
                                                <h4 class="card-title">
                                                    <?= $jadwal_hari_ini_total ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Siswa Diajar -->
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Total Siswa Diajar</p>
                                                <h4 class="card-title">
                                                    <?= $total_siswa_diajar ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Absensi Saya (Bulan Ini) -->
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                                <i class="fas fa-user-check"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Absensi Saya (Bulan Ini)</p>
                                                <h4 class="card-title">
                                                    <?= $absensi_saya_bulan_ini ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tidak Absensi (Bulan Ini) -->
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                                <i class="fas fa-user-clock"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Tidak Absensi (Bulan Ini)</p>
                                                <h4 class="card-title">
                                                    <?= $tidak_absensi_bulan_ini ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Mengajar Hari Ini & Pengumuman -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-calendar-day me-2"></i>Jadwal Mengajar Hari Ini
                                        (<?= $hari_ini ?>)
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($jadwal_hari_ini)): ?>
                                    <p class="text-muted mb-0">Tidak ada jadwal mengajar hari ini.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Jam</th>
                                                    <th>Mata Pelajaran</th>
                                                    <th>Kelas</th>
                                                    <th>Status Absensi</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($jadwal_hari_ini as $j): ?>
                                                <tr>
                                                    <td>
                                                        <?= substr($j['jam_masuk'], 0, 5) ?> -
                                                        <?= substr($j['jam_keluar'], 0, 5) ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($j['nama_mapel']) ?></td>
                                                    <td><?= htmlspecialchars($j['nama_kelas']) ?></td>
                                                    <td>
                                                        <?php if ($j['sudah_diabsen']): ?>
                                                        <span class="badge bg-success">Sudah Diabsen</span>
                                                        <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Belum Diabsen</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="absensi/index.php?kelas_id=<?= $j['kelas_id'] ?>&mapel_id=<?= $j['mapel_id'] ?>"
                                                            class="btn btn-sm btn-primary">
                                                            <?= $j['sudah_diabsen'] ? 'Lihat/Edit' : 'Isi Absensi' ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-bullhorn me-2"></i>Pengumuman
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">
                                        <?= htmlspecialchars($pengumuman_row['pengumuman'] ?? 'Belum ada pengumuman.') ?>
                                    </p>
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


        <!-- End Custom template -->
    </div>



    <!--   Core JS Files   -->
    <script src="../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="../../assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="../../assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="../../assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="../../assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="../../assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="../../assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="../../assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="../../assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="../../assets/js/kaiadmin.min.js"></script>


</body>

</html>