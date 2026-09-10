<?php
session_start();
include '../../backend/koneksi.php';

if ($_SESSION['role'] != 'operator') {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'] ?? null;

if (!$id) {
    die('User belum login');
}
$today = date('Y-m-d');

// Nama hari dalam bahasa Indonesia
$hari_inggris = date('l');
$hari_map = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];

$hari_ini = $hari_map[$hari_inggris];

// 1. Total guru yang memiliki jadwal mengajar hari ini
$q_total_guru = mysqli_query($conn, "
    SELECT COUNT(DISTINCT guru_id) AS total
    FROM jadwal
    WHERE hari = '$hari_ini'
");

$d_total_guru = mysqli_fetch_assoc($q_total_guru);
$total_guru = (int) ($d_total_guru['total'] ?? 0);


// 2. Jumlah guru yang sudah absensi hari ini
$q_sudah_absen = mysqli_query($conn, "
    SELECT COUNT(DISTINCT guru_id) AS total
    FROM absensi_guru
    WHERE tanggal = '$today'
");

$d_sudah_absen = mysqli_fetch_assoc($q_sudah_absen);
$sudah_absen = (int) ($d_sudah_absen['total'] ?? 0);


// 3. Jumlah guru yang belum absensi
$belum_absen = max(0, $total_guru - $sudah_absen);

$q_belum_absen = mysqli_query($conn, "
    SELECT 
        guru.id AS guru_id,
        guru.nama_guru,
        guru.nip,
        mapel.nama_mapel,
        kelas.nama_kelas,
        jadwal.jam_masuk,
        jadwal.jam_keluar
    FROM jadwal
    INNER JOIN guru 
        ON guru.id = jadwal.guru_id
    INNER JOIN mapel 
        ON mapel.id = jadwal.mapel_id
    INNER JOIN kelas 
        ON kelas.id = jadwal.kelas_id
    LEFT JOIN absensi_guru 
        ON absensi_guru.guru_id = guru.id
        AND absensi_guru.tanggal = '$today'
    WHERE jadwal.hari = '$hari_ini'
      AND absensi_guru.id IS NULL
    ORDER BY jadwal.jam_masuk ASC, guru.nama_guru ASC
");

$q_users = mysqli_query($conn, "
    SELECT COUNT(id) AS total
    FROM users
");

$d_users = mysqli_fetch_assoc($q_users);
$users = (int) ($d_users['total'] ?? 0);

$q_siswa = mysqli_query($conn, "
    SELECT COUNT(id) AS total
    FROM siswa
");

$d_siswa = mysqli_fetch_assoc($q_siswa);
$siswa = (int) ($d_siswa['total'] ?? 0);
$q_siswa_per_kelas = mysqli_query($conn, "
    SELECT kelas.nama_kelas, COUNT(siswa.id) AS total
    FROM kelas
    LEFT JOIN siswa ON siswa.kelas_id = kelas.id
    GROUP BY kelas.id, kelas.nama_kelas
    ORDER BY kelas.nama_kelas ASC
");

$kelas_labels = [];
$kelas_data   = [];
while ($r = mysqli_fetch_assoc($q_siswa_per_kelas)) {
    $kelas_labels[] = $r['nama_kelas'];
    $kelas_data[]   = (int) $r['total'];
}
$q_jk = mysqli_query($conn, "
    SELECT jenis_kelamin, COUNT(id) AS total
    FROM siswa
    GROUP BY jenis_kelamin
");

$jk_laki      = 0;
$jk_perempuan = 0;
while ($r = mysqli_fetch_assoc($q_jk)) {
    if ($r['jenis_kelamin'] === 'L') {
        $jk_laki = (int) $r['total'];
    } elseif ($r['jenis_kelamin'] === 'P') {
        $jk_perempuan = (int) $r['total'];
    }
}

$q_absensi_siswa = mysqli_query($conn, "
    SELECT
        jadwal.id AS jadwal_id,
        mapel.nama_mapel,
        kelas.nama_kelas,
        jadwal.jam_masuk,
        jadwal.jam_keluar,
        SUM(CASE WHEN absensi.status = 'H' THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN absensi.status IS NOT NULL AND absensi.status != 'H' THEN 1 ELSE 0 END) AS tidak_hadir
    FROM jadwal
    INNER JOIN mapel 
        ON mapel.id = jadwal.mapel_id
    INNER JOIN kelas 
        ON kelas.id = jadwal.kelas_id
    LEFT JOIN siswa 
        ON siswa.kelas_id = jadwal.kelas_id
    LEFT JOIN absensi 
        ON absensi.siswa_id = siswa.id
        AND absensi.mapel_id = jadwal.mapel_id
        AND absensi.tanggal = '$today'
    WHERE jadwal.hari = '$hari_ini'
    GROUP BY jadwal.id, mapel.nama_mapel, kelas.nama_kelas, jadwal.jam_masuk, jadwal.jam_keluar
    ORDER BY jadwal.jam_masuk ASC
");

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Admin Dashboard</title>
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
        <!-- Sidebar -->
        <div class="sidebar" data-background-color="dark">
            <div class="sidebar-logo">
                <!-- Logo Header -->
                <div class="logo-header" data-background-color="dark">
                    <a href="" class=" logo">
                        <img src="../../assets/img/logo.png" alt="navbar brand"
                            style="height: 30px; margin-right: 10px;" />
                    </a>
                    <div class=" nav-toggle">
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
                        <li class="nav-item active">
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
                            <a href="absensi_guru/index.php">
                                <i class="fas fa-user-check"></i>
                                <p>Absensi Guru</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="guru/index.php">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <p>Data Guru</p>
                            </a>
                        </li>

                        <!-- Data Siswa -->
                        <li class="nav-item">
                            <a href="siswa/index.php">
                                <i class="fas fa-user-graduate"></i>
                                <p>Data Siswa</p>
                            </a>
                        </li>

                        <!-- Data Kelas -->
                        <li class="nav-item">
                            <a href="kelas/index.php">
                                <i class="fas fa-chalkboard"></i>
                                <p>Data Kelas</p>
                            </a>
                        </li>

                        <!-- Users -->
                        <li class="nav-item">
                            <a href="users/index.php">
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
                            <a href="jadwal/index.php">
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
                            <a href="pengumuman/index.php">
                                <i class="fas fa-bullhorn"></i>
                                <p>Pengumuman</p>
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
                                                    <img src="../../assets/img/cs admin.png" alt="image profile"
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

                                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="#modalGantiPassword"><i class="fas fa-lock"></i>
                                                Ganti Password
                                            </a>
                                        </li>


                                        <li>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="../../logout.php"><i
                                                    class="fas fa-sign-out-alt"></i> Logout</a>
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
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Users</p>
                                                <h4 class="card-title">
                                                    <?= $users ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Siswa</p>
                                                <h4 class="card-title">
                                                    <?= $siswa ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                                <p class="card-category">Absensi Guru</p>
                                                <h4 class="card-title">
                                                    <?= $sudah_absen ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                                <p class="card-category">Belum Absensi</p>
                                                <h4 class="card-title">
                                                    <?= $belum_absen ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Jumlah Siswa per Kelas</div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="barChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Siswa Berdasarkan Jenis Kelamin</div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="pieChart" style="width: 50%; height: 50%"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 card-title> Guru Belum Absensi Hari Ini</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive table-hover table-sales">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Guru</th>
                                                        <th>NIP</th>
                                                        <th>Mata Pelajaran</th>
                                                        <th>Kelas</th>
                                                        <th>Jam Mengajar</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php if (mysqli_num_rows($q_belum_absen) > 0): ?>

                                                    <?php $no = 1; ?>

                                                    <?php while ($row = mysqli_fetch_assoc($q_belum_absen)): ?>

                                                    <tr>
                                                        <td><?= $no++ ?></td>

                                                        <td>
                                                            <?= htmlspecialchars($row['nama_guru']) ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($row['nip']) ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($row['nama_mapel']) ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($row['nama_kelas']) ?>
                                                        </td>

                                                        <td>
                                                            <?= date('H:i', strtotime($row['jam_masuk'])) ?>
                                                            -
                                                            <?= date('H:i', strtotime($row['jam_keluar'])) ?>
                                                        </td>
                                                    </tr>

                                                    <?php endwhile; ?>

                                                    <?php else: ?>

                                                    <tr>
                                                        <td colspan="6" class="text-center">
                                                            <i class="fas fa-check-circle text-success"></i>
                                                            Semua guru sudah melakukan absensi.
                                                        </td>
                                                    </tr>

                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 card-title> Absensi Siswa Hari Ini</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive table-hover table-sales">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Mata Pelajaran</th>
                                                        <th>Kelas</th>
                                                        <th>Hadir</th>
                                                        <th>Tidak Hadir</th>
                                                        <th>Jam Mapel</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php if (mysqli_num_rows($q_absensi_siswa) > 0): ?>

                                                    <?php $no = 1; ?>

                                                    <?php while ($row = mysqli_fetch_assoc($q_absensi_siswa)): ?>

                                                    <tr>
                                                        <td><?= $no++ ?></td>

                                                        <td>
                                                            <?= htmlspecialchars($row['nama_mapel']) ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($row['nama_kelas']) ?>
                                                        </td>

                                                        <td class="text-success fw-bold">
                                                            <?= (int) $row['hadir'] ?>
                                                        </td>

                                                        <td class="text-danger fw-bold">
                                                            <?= (int) $row['tidak_hadir'] ?>
                                                        </td>

                                                        <td>
                                                            <?= date('H:i', strtotime($row['jam_masuk'])) ?>
                                                            -
                                                            <?= date('H:i', strtotime($row['jam_keluar'])) ?>
                                                        </td>
                                                    </tr>

                                                    <?php endwhile; ?>

                                                    <?php else: ?>

                                                    <tr>
                                                        <td colspan="6" class="text-center">
                                                            Tidak ada jadwal pelajaran hari ini.
                                                        </td>
                                                    </tr>

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

                        <div class="copyright ">
                            &copy; 2026 All rights reserved.
                        </div>

                    </div>
                </footer>
            </div>
        </div>


        <!-- Modal Ganti Password -->
        <div class="modal fade" id="modalGantiPassword" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Ganti Password</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>

                    <form method="POST" action="../../backend/ganti_password.php">

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">
                                    Password Baru
                                </label>

                                <input type="password" name="password_baru" class="form-control"
                                    placeholder="Masukkan password baru" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Konfirmasi Password
                                </label>

                                <input type="password" name="konfirmasi_password" class="form-control"
                                    placeholder="Ulangi password baru" required>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                Batal
                            </button>

                            <button type="submit" name="ganti_password" class="btn btn-primary">
                                Simpan
                            </button>
                        </div>

                    </form>

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

        <script>
        const kelasLabels = <?= json_encode($kelas_labels) ?>;
        const kelasData = <?= json_encode($kelas_data) ?>;
        const jkLaki = <?= (int) $jk_laki ?>;
        const jkPerempuan = <?= (int) $jk_perempuan ?>;

        // Bar Chart: Jumlah Siswa per Kelas
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: kelasLabels,
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: kelasData,
                    backgroundColor: '#1572E8',
                    borderRadius: 6,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.parsed.y + ' siswa';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Pie Chart: Jumlah Siswa Berdasarkan Jenis Kelamin
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [jkLaki, jkPerempuan],
                    backgroundColor: ['#1572E8', '#e91e63']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = jkLaki + jkPerempuan;
                                const pct = total ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
        </script>

</body>

</html>