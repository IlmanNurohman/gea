<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepsek') {
    header("Location: ../../login.php");
    exit;
}

// ===================================================== // AMBIL FILTER DARI GET // =====================================================
$tanggal_dari   = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';

$filter_valid = $tanggal_dari !== '' && $tanggal_sampai !== '';
$rekap        = [];
$error_filter = '';

if ($filter_valid) {
    if ($tanggal_dari > $tanggal_sampai) {
        $error_filter = 'Tanggal "dari" tidak boleh lebih besar dari tanggal "sampai".';
    } else {
        // ===================================================== // QUERY REKAP ABSENSI GURU // =====================================================
        // Ringkasan total per guru: Hadir, Sakit, Izin, Alpa dalam rentang tanggal (semua guru sekaligus).
        $stmt = mysqli_prepare($conn, "
            SELECT
                guru.id,
                guru.nama_guru,
                guru.nip,
                SUM(CASE WHEN absensi_guru.status = 'H' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN absensi_guru.status = 'S' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN absensi_guru.status = 'I' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN absensi_guru.status = 'A' THEN 1 ELSE 0 END) AS alpa
            FROM guru
            LEFT JOIN absensi_guru
                ON absensi_guru.guru_id = guru.id
                AND absensi_guru.tanggal BETWEEN ? AND ?
            GROUP BY guru.id, guru.nama_guru, guru.nip
            ORDER BY guru.nama_guru ASC
        ");
        mysqli_stmt_bind_param($stmt, "ss", $tanggal_dari, $tanggal_sampai);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rekap  = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Rekap Absensi Guru</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="../../../assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
    WebFont.load({
        google: {
            families: ["Public Sans:300,400,500,600,700"]
        },
        custom: {
            families: ["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands",
                "simple-line-icons"
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
                    <a href="" class=" logo">
                        <img src="../../../assets/img/logo.png" alt="navbar brand"
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
                        <li class="nav-item">
                            <a href="../dashboardKepsek.php" class="collapsed" aria-expanded="false">
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

                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Rekap Absensi</h4>
                        </li>
                        <li class="nav-item">
                            <a href="../absensi_siswa/index.php">
                                <i class="fas fa-user-graduate"></i>
                                <p>Rekap Absensi Siswa</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="../absensi_guru/index.php">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <p>Rekap Absensi Guru</p>
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
                            <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                            <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                        </div>
                        <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
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
                                        <span class="fw-bold"><?= $_SESSION['username']; ?></span>
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
                                                    <h4><?= $_SESSION['username']; ?></h4>
                                                    <p class="text-muted"><?= $_SESSION['email']; ?></p>
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
                        <h3 class="fw-bold mb-3">Absensi Guru</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Rekap absensi Guru</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Data absensi</a>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h3 class="fw-bold mb-3">Rekap Absensi Guru</h3>
                            <h6 class="op-7 mb-2">Pilih rentang tanggal untuk melihat rekap kehadiran seluruh guru</h6>
                        </div>
                    </div>

                    <!-- Form Filter -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Filter Rekap</h4>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="index.php" class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label">Dari Tanggal</label>
                                            <input type="date" name="tanggal_dari" class="form-control"
                                                value="<?= htmlspecialchars($tanggal_dari) ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Sampai Tanggal</label>
                                            <input type="date" name="tanggal_sampai" class="form-control"
                                                value="<?= htmlspecialchars($tanggal_sampai) ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-search me-1"></i> Tampilkan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hasil Rekap -->
                    <?php if (!$filter_valid): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Silakan pilih periode tanggal terlebih dahulu untuk
                                menampilkan
                                rekap absensi guru.
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Rekap Kehadiran Guru</h4>
                                    <p class="text-muted mb-0">
                                        Periode: <?= date('d/m/Y', strtotime($tanggal_dari)) ?> s/d
                                        <?= date('d/m/Y', strtotime($tanggal_sampai)) ?>
                                    </p>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($rekap)): ?>
                                    <p class="text-muted mb-0">Data guru tidak ditemukan.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered align-middle">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>No</th>
                                                    <th class="text-start">Nama Guru</th>
                                                    <th>NIP</th>
                                                    <th>Hadir</th>
                                                    <th>Sakit</th>
                                                    <th>Izin</th>
                                                    <th>Alpa</th>
                                                    <th>Total Hari</th>
                                                    <th>% Kehadiran</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; ?>
                                                <?php foreach ($rekap as $g): ?>
                                                <?php
                                                    $hadir = (int) $g['hadir'];
                                                    $sakit = (int) $g['sakit'];
                                                    $izin  = (int) $g['izin'];
                                                    $alpa  = (int) $g['alpa'];
                                                    $total = $hadir + $sakit + $izin + $alpa;
                                                    $persen = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars($g['nama_guru']) ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($g['nip']) ?></td>
                                                    <td class="text-center text-success fw-bold"><?= $hadir ?></td>
                                                    <td class="text-center text-warning fw-bold"><?= $sakit ?></td>
                                                    <td class="text-center text-info fw-bold"><?= $izin ?></td>
                                                    <td class="text-center text-danger fw-bold"><?= $alpa ?></td>
                                                    <td class="text-center"><?= $total ?></td>
                                                    <td class="text-center">
                                                        <?php if ($total > 0): ?>
                                                        <?= $persen ?>%
                                                        <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                        <?php endif; ?>
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
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid d-flex justify-content-center">
                    <div class="copyright">&copy; 2026 All rights reserved.</div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="../../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../../assets/js/core/popper.min.js"></script>
    <script src="../../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="../../../assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="../../../assets/js/kaiadmin.min.js"></script>
</body>

</html>