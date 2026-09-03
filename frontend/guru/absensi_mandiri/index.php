<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    die('Akses ditolak');
}

$user_id = $_SESSION['user_id'] ?? '';
$q_guru  = mysqli_query($conn, "SELECT id, nama_guru, nip FROM guru WHERE user_id = '$user_id'");
$d_guru  = mysqli_fetch_assoc($q_guru);
$guru_id = $d_guru['id'] ?? 0;

$today = date('Y-m-d');

// 1. Cek Apakah Operator Sudah Membuka Sesi Absensi Guru Hari Ini
$q_sesi = mysqli_query($conn, "SELECT status FROM sesi_absensi_guru WHERE tanggal = '$today' AND status = 'buka'");
$sesi_terbuka = (mysqli_num_rows($q_sesi) > 0);

// 2. Cek Absensi Mandiri Guru Hari Ini
$q_cek = mysqli_query($conn, "SELECT * FROM absensi_guru WHERE guru_id = '$guru_id' AND tanggal = '$today'");
$absen_today = mysqli_fetch_assoc($q_cek);

// 3. Fetch Riwayat Absensi
$q_riwayat = mysqli_query($conn, "SELECT * FROM absensi_guru WHERE guru_id = '$guru_id' ORDER BY tanggal DESC LIMIT 30");

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
                        <!-- Panel Absen -->
                        <div class="col-md-5">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-title">Absen Hari Ini (<?= date('d-m-Y') ?>)</div>
                                </div>
                                <div class="card-body text-center">
                                    <h4><strong><?= htmlspecialchars($d_guru['nama_guru']) ?></strong></h4>
                                    <p class="text-muted">NIP: <?= htmlspecialchars($d_guru['nip']) ?></p>
                                    <hr>

                                    <!-- JIKA SESI BELUM DIBUKA OPERATOR -->
                                    <?php if (!$sesi_terbuka) : ?>
                                    <div class="alert alert-danger" role="alert">
                                        <h5 class="alert-heading"><i class="fa fa-lock"></i> Absensi Belum Dibuka!</h5>
                                        <p class="mb-0">Operator belum membuka sesi absensi kehadiran guru hari ini.
                                            Silakan hubungi Operator Sekolah.</p>
                                    </div>

                                    <!-- JIKA SESI SUDAH DIBUKA OPERATOR -->
                                    <?php else : ?>

                                    <?php if (!$absen_today) : ?>
                                    <!-- BELUM ABSEN MASUK -->
                                    <form action="proses_absensi.php?aksi=masuk" method="POST">
                                        <input type="hidden" name="guru_id" value="<?= $guru_id ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Status Kehadiran</label>
                                            <select name="status" class="form-select" id="selectStatus"
                                                onchange="toggleKeterangan()">
                                                <option value="Hadir">Hadir</option>
                                                <option value="Izin">Izin</option>
                                                <option value="Sakit">Sakit</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 d-none" id="boxKeterangan">
                                            <label class="form-label">Keterangan (Alasan)</label>
                                            <input type="text" name="keterangan" class="form-control"
                                                placeholder="Tuliskan alasan...">
                                        </div>
                                        <button type="submit" class="btn btn-success btn-lg w-100">
                                            <i class="fa fa-check-circle"></i> Absen Masuk Sekarang
                                        </button>
                                    </form>

                                    <?php elseif ($absen_today['status'] !== 'Hadir') : ?>
                                    <div class="alert alert-info">
                                        Status Anda Hari Ini: <strong><?= $absen_today['status'] ?></strong><br>
                                        <em><?= htmlspecialchars($absen_today['keterangan'] ?? '-') ?></em>
                                    </div>

                                    <?php elseif ($absen_today['jam_masuk'] && !$absen_today['jam_keluar']) : ?>
                                    <div class="alert alert-success mb-3">
                                        Jam Masuk: <strong><?= $absen_today['jam_masuk'] ?> WIB</strong>
                                    </div>
                                    <form action="proses_absensi.php?aksi=keluar" method="POST">
                                        <input type="hidden" name="id" value="<?= $absen_today['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-lg w-100">
                                            <i class="fa fa-sign-out-alt"></i> Absen Pulang Sekarang
                                        </button>
                                    </form>

                                    <?php else : ?>
                                    <div class="alert alert-secondary">
                                        <p class="mb-1">Jam Masuk: <strong><?= $absen_today['jam_masuk'] ?> WIB</strong>
                                        </p>
                                        <p class="mb-0">Jam Pulang: <strong><?= $absen_today['jam_keluar'] ?>
                                                WIB</strong></p>
                                    </div>
                                    <button class="btn btn-secondary w-100" disabled>Absensi Hari Ini Selesai</button>
                                    <?php endif; ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Absensi Guru -->
                        <div class="col-md-7">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-title">Riwayat Absensi Kehadiran</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Masuk</th>
                                                    <th>Pulang</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($r = mysqli_fetch_assoc($q_riwayat)) : ?>
                                                <tr>
                                                    <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                                                    <td><?= $r['jam_masuk'] ?? '-' ?></td>
                                                    <td><?= $r['jam_keluar'] ?? '-' ?></td>
                                                    <td>
                                                        <?php $bg = $r['status'] === 'Hadir' ? 'bg-success' : ($r['status'] === 'Izin' ? 'bg-warning' : 'bg-danger'); ?>
                                                        <span class="badge <?= $bg ?>"><?= $r['status'] ?></span>
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
    <script>
    function toggleKeterangan() {
        var status = document.getElementById('selectStatus').value;
        var box = document.getElementById('boxKeterangan');
        if (status === 'Izin' || status === 'Sakit') {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }
    </script>
    <?php if (isset($_SESSION['swal'])): ?>
    <script>
    swal({
        title: "<?= $_SESSION['swal']['title'] ?>",
        text: "<?= $_SESSION['swal']['text'] ?>",
        icon: "<?= $_SESSION['swal']['type'] ?>",
        button: "OK"
    });
    </script>
    <?php unset($_SESSION['swal']); endif; ?>
</body>

</html>