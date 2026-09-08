<?php
session_start();
include '../../backend/koneksi.php';

if ($_SESSION['role'] != 'siswa') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die('User belum login');
}

// AMBIL DATA SISWA 
$stmt =
        mysqli_prepare($conn, "SELECT id, kelas_id, nama_lengkap FROM siswa WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        
$result = mysqli_stmt_get_result($stmt);
$siswa =
        mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$siswa) {
            die('Data siswa tidak ditemukan');
        }
        
$siswa_id = (int) $siswa['id'];
$kelas_id = (int) $siswa['kelas_id'];

//  TOTAL TUGAS 
$stmt =
        mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM tugas WHERE kelas_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);
        
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$total_tugas = (int) $data['total'];
mysqli_stmt_close($stmt);

// TUGAS SUDAH DIKERJAKAN 
$stmt =
        mysqli_prepare($conn, "SELECT COUNT(DISTINCT t.id) AS total FROM tugas t INNER JOIN pengumpulan_tugas pt ON pt.tugas_id = t.id WHERE t.kelas_id = ? AND pt.siswa_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $siswa_id);
        mysqli_stmt_execute($stmt);
$result =
        mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$sudah_dikerjakan = (int) $data['total'];
mysqli_stmt_close($stmt);

//  TUGAS BELUM DIKERJAKAN 
$belum_dikerjakan = $total_tugas - $sudah_dikerjakan;

// JADWAL HARI INI 
$hari_map = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
];
$hari_ini = $hari_map[date('l')];

$stmt = mysqli_prepare(
    $conn,
    "SELECT j.jam_masuk, j.jam_keluar, m.nama_mapel
     FROM jadwal j
     INNER JOIN mapel m ON m.id = j.mapel_id
     WHERE j.kelas_id = ? AND j.hari = ?
     ORDER BY j.jam_masuk ASC"
);
mysqli_stmt_bind_param($stmt, "is", $kelas_id, $hari_ini);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$jadwal_hari_ini = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// TUGAS TERBARU 
$stmt = mysqli_prepare(
    $conn,
    "SELECT t.id, t.judul, t.deadline, m.nama_mapel,
        EXISTS (
            SELECT 1 FROM pengumpulan_tugas pt
            WHERE pt.tugas_id = t.id AND pt.siswa_id = ?
        ) AS sudah_kumpul
     FROM tugas t
     INNER JOIN mapel m ON m.id = t.mapel_id
     WHERE t.kelas_id = ?
     ORDER BY t.deadline ASC
     LIMIT 5"
);
mysqli_stmt_bind_param($stmt, "ii", $siswa_id, $kelas_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$daftar_tugas = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Helper: format deadline jadi teks relatif (Besok, 2 hari lagi, Terlambat, dst)
function formatDeadline($deadline)
{
    $today = new DateTime('today');
    $due   = new DateTime($deadline);
    $diff  = (int) $today->diff($due)->format('%r%a'); // signed day diff

    if ($diff < 0)  return '<span class="text-danger">Terlambat</span>';
    if ($diff === 0) return '<span class="text-warning">Hari ini</span>';
    if ($diff === 1) return 'Besok';
    return $diff . ' hari lagi';
}

$pengumuman = mysqli_query(
    $conn,
    "SELECT pengumuman FROM pengumuman ORDER BY created_at DESC LIMIT 1"
);

$row = mysqli_fetch_assoc($pengumuman);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Dashboard</title>
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
                    <a href="" class="logo">
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
                        <li class="nav-item">
                            <a href="dashboardSiswa.php" class="collapsed" aria-expanded="false">
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
                    </ul>
                </div>
            </div>
        </div>
        <!-- End Sidebar -->
        <!-- End Sidebar -->

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <!-- Logo Header -->
                    <div class="logo-header" data-background-color="dark">
                        <a href="" class="logo">
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

                        <!-- Total Tugas -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                <i class="fas fa-tasks"></i>
                                            </div>
                                        </div>

                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Total Tugas</p>
                                                <h4 class="card-title">
                                                    <?= $total_tugas ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Belum Dikerjakan -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                                <i class="fas fa-clipboard-list"></i>
                                            </div>
                                        </div>

                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Belum Dikerjakan</p>
                                                <h4 class="card-title">
                                                    <?= $belum_dikerjakan ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sudah Dikerjakan -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>

                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Sudah Dikerjakan</p>
                                                <h4 class="card-title">
                                                    <?= $sudah_dikerjakan ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Tugas Terbaru -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-book me-2"></i>Tugas Terbaru
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($daftar_tugas)): ?>
                                    <p class="text-muted mb-0">Belum ada tugas.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Judul Tugas</th>
                                                    <th>Mapel</th>
                                                    <th>Batas Waktu</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($daftar_tugas as $t): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($t['judul']) ?></td>
                                                    <td><?= htmlspecialchars($t['nama_mapel']) ?></td>
                                                    <td><?= formatDeadline($t['deadline']) ?></td>
                                                    <td>
                                                        <?php if ($t['sudah_kumpul']): ?>
                                                        <span class="badge bg-success">Sudah Dikumpulkan</span>
                                                        <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Belum
                                                            Dikumpulkan</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-2">
                                        <a href="tugas/index.php" class="btn btn-sm btn-primary">Lihat Semua Tugas</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Hari Ini & Pengumuman -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-calendar-day me-2"></i>Jadwal Hari Ini (<?= $hari_ini ?>)
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($jadwal_hari_ini)): ?>
                                    <p class="text-muted mb-0">Tidak ada jadwal pelajaran hari ini.</p>
                                    <?php else: ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($jadwal_hari_ini as $j): ?>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span>
                                                <i class="fas fa-clock text-muted me-2"></i>
                                                <?= substr($j['jam_masuk'], 0, 5) ?> -
                                                <?= substr($j['jam_keluar'], 0, 5) ?>
                                            </span>
                                            <span class="fw-bold"><?= htmlspecialchars($j['nama_mapel']) ?></span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Pengumuman</h4>
                                </div>
                                <div class="card-body">
                                    <p>
                                        <?= htmlspecialchars($row['pengumuman'] ?? 'Belum ada pengumuman.') ?>
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
    <div class="modal fade" id="calendarModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kalender</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mapsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lokasi ICT Boarding School Pakenjeng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.0490064280293!2d107.63090179999999!3d-7.459830900000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e66210076a60459%3A0x6a5343201d23b2c1!2sICT%20BOARDING%20SCHOOL!5e0!3m2!1sid!2sid!4v1767444462801!5m2!1sid!2sid"
                        width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy">
                    </iframe>

                </div>
            </div>
        </div>
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