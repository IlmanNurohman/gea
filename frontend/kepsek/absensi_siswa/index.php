<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepsek') {
    header("Location: ../../login.php");
    exit;
}

// ===================================================== // AMBIL DAFTAR KELAS & MAPEL UNTUK DROPDOWN // =====================================================
$q_kelas = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
$q_mapel = mysqli_query($conn, "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel ASC");

// ===================================================== // AMBIL FILTER DARI GET // =====================================================
$kelas_id       = isset($_GET['kelas_id']) ? (int) $_GET['kelas_id'] : 0;
$mapel_id       = isset($_GET['mapel_id']) ? (int) $_GET['mapel_id'] : 0;
$tanggal_dari   = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';

$filter_valid = $kelas_id > 0 && $mapel_id > 0 && $tanggal_dari !== '' && $tanggal_sampai !== '';
$rekap        = [];
$nama_kelas_terpilih = '';
$nama_mapel_terpilih = '';
$error_filter = '';

if ($filter_valid) {
    if ($tanggal_dari > $tanggal_sampai) {
        $error_filter = 'Tanggal "dari" tidak boleh lebih besar dari tanggal "sampai".';
    } else {
        // Ambil nama kelas & mapel terpilih (untuk judul)
        $stmt = mysqli_prepare($conn, "SELECT nama_kelas FROM kelas WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);
        $r = mysqli_stmt_get_result($stmt);
        $d = mysqli_fetch_assoc($r);
        $nama_kelas_terpilih = $d['nama_kelas'] ?? '';
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conn, "SELECT nama_mapel FROM mapel WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $mapel_id);
        mysqli_stmt_execute($stmt);
        $r = mysqli_stmt_get_result($stmt);
        $d = mysqli_fetch_assoc($r);
        $nama_mapel_terpilih = $d['nama_mapel'] ?? '';
        mysqli_stmt_close($stmt);

        // ===================================================== // QUERY REKAP ABSENSI SISWA // =====================================================
        // Ringkasan total per siswa: Hadir, Sakit, Izin, Alpa dalam rentang tanggal untuk kelas & mapel terpilih.
        $stmt = mysqli_prepare($conn, "
            SELECT
                siswa.id,
                siswa.nama_lengkap,
                SUM(CASE WHEN absensi.status = 'H' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN absensi.status = 'S' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN absensi.status = 'I' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN absensi.status = 'A' THEN 1 ELSE 0 END) AS alpa
            FROM siswa
            LEFT JOIN absensi
                ON absensi.siswa_id = siswa.id
                AND absensi.mapel_id = ?
                AND absensi.tanggal BETWEEN ? AND ?
            WHERE siswa.kelas_id = ?
            GROUP BY siswa.id, siswa.nama_lengkap
            ORDER BY siswa.nama_lengkap ASC
        ");
        mysqli_stmt_bind_param($stmt, "issi", $mapel_id, $tanggal_dari, $tanggal_sampai, $kelas_id);
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
    <title>Rekap Absensi Siswa</title>
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
                        <li class="nav-item active">
                            <a href="../absensi_siswa/index.php">
                                <i class="fas fa-user-graduate"></i>
                                <p>Rekap Absensi Siswa</p>
                            </a>
                        </li>
                        <li class="nav-item">
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
                        <h3 class="fw-bold mb-3">Absensi Siswa</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="fas fa-user-graduate"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Rekap absensi Siswa</a>
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
                            <h3 class="fw-bold mb-3">Rekap Absensi Siswa</h3>
                            <h6 class="op-7 mb-2">Pilih kelas, mata pelajaran, dan rentang tanggal untuk melihat rekap
                            </h6>
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
                                            <label class="form-label">Kelas</label>
                                            <select name="kelas_id" class="form-control" required>
                                                <option value="">-- Pilih Kelas --</option>
                                                <?php mysqli_data_seek($q_kelas, 0); ?>
                                                <?php while ($k = mysqli_fetch_assoc($q_kelas)): ?>
                                                <option value="<?= $k['id'] ?>"
                                                    <?= $kelas_id == $k['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Mata Pelajaran</label>
                                            <select name="mapel_id" class="form-control" required>
                                                <option value="">-- Pilih Mapel --</option>
                                                <?php mysqli_data_seek($q_mapel, 0); ?>
                                                <?php while ($m = mysqli_fetch_assoc($q_mapel)): ?>
                                                <option value="<?= $m['id'] ?>"
                                                    <?= $mapel_id == $m['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($m['nama_mapel']) ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Dari Tanggal</label>
                                            <input type="date" name="tanggal_dari" class="form-control"
                                                value="<?= htmlspecialchars($tanggal_dari) ?>" required>
                                        </div>
                                        <div class="col-md-2">
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
                                Silakan pilih kelas, mata pelajaran dan periode tanggal terlebih dahulu untuk
                                menampilkan
                                rekap absensi siswa.
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        Rekap Kelas <?= htmlspecialchars($nama_kelas_terpilih) ?> -
                                        <?= htmlspecialchars($nama_mapel_terpilih) ?>
                                    </h4>
                                    <p class="text-muted mb-0">
                                        Periode: <?= date('d/m/Y', strtotime($tanggal_dari)) ?> s/d
                                        <?= date('d/m/Y', strtotime($tanggal_sampai)) ?>
                                    </p>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($rekap)): ?>
                                    <p class="text-muted mb-0">Tidak ada siswa pada kelas ini.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered align-middle">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>No</th>
                                                    <th class="text-start">Nama Siswa</th>
                                                    <th>Hadir</th>
                                                    <th>Sakit</th>
                                                    <th>Izin</th>
                                                    <th>Alpa</th>
                                                    <th>Total Pertemuan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; ?>
                                                <?php foreach ($rekap as $s): ?>
                                                <?php
                                                    $hadir = (int) $s['hadir'];
                                                    $sakit = (int) $s['sakit'];
                                                    $izin  = (int) $s['izin'];
                                                    $alpa  = (int) $s['alpa'];
                                                    $total = $hadir + $sakit + $izin + $alpa;
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                                                    <td class="text-center text-success fw-bold"><?= $hadir ?></td>
                                                    <td class="text-center text-warning fw-bold"><?= $sakit ?></td>
                                                    <td class="text-center text-info fw-bold"><?= $izin ?></td>
                                                    <td class="text-center text-danger fw-bold"><?= $alpa ?></td>
                                                    <td class="text-center"><?= $total ?></td>
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