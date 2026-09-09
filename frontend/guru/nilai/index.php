<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') { die('Akses ditolak'); }

$user_id = $_SESSION['user_id'] ?? '';
$q_guru  = mysqli_query($conn, "SELECT id FROM guru WHERE user_id = '$user_id'");
$d_guru  = mysqli_fetch_assoc($q_guru);
$guru_id = $d_guru['id'] ?? 0;

// Filter
$kelas_id = $_GET['kelas_id'] ?? '';
$mapel_id = $_GET['mapel_id'] ?? '';

// Master Data Filter
$kelas_list = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$mapel_list = mysqli_query($conn, "SELECT * FROM mapel ORDER BY nama_mapel ASC");

$siswa_list = [];
if (!empty($kelas_id) && !empty($mapel_id)) {
    // Query mengambil siswa & nilai UTS/UAS
    $q_siswa = "SELECT siswa.id AS siswa_id, siswa.nisn, siswa.nama_lengkap,
                (SELECT nilai FROM nilai_ujian WHERE siswa_id = siswa.id AND kelas_id = '$kelas_id' AND mapel_id = '$mapel_id' AND jenis_ujian = 'UTS') as nilai_uts,
                (SELECT nilai FROM nilai_ujian WHERE siswa_id = siswa.id AND kelas_id = '$kelas_id' AND mapel_id = '$mapel_id' AND jenis_ujian = 'UAS') as nilai_uas
                FROM siswa 
                WHERE siswa.kelas_id = '$kelas_id' 
                ORDER BY siswa.nama_lengkap ASC";
    $siswa_list = mysqli_query($conn, $q_siswa);
}
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Data Materi</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="../../../assets/img/kaiadmin/favicon.ico" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

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
                            <a href="../jadwal/index.php">
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
                            <a href="../absensi/index.php">
                                <i class="fas fa-user-check"></i>
                                <p>Absensi Siswa</p>
                            </a>
                        </li>

                        <!-- Absensi Mandiri -->
                        <li class="nav-item">
                            <a href="../absensi_mandiri/index.php">
                                <i class="fas fa-user-clock"></i>
                                <p>Absensi Mandiri</p>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <!-- Logo Header -->
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
                                                    <img src="../../../assets/img/cs admin.png" alt="..."
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
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Nilai</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="fas fa-star"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Manajemen Nilai</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Data Nilai Siswa</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class=" mb-3">
                                <?php if (!empty($kelas_id) && !empty($mapel_id)) : ?>
                                <a href="tambah.php?kelas_id=<?= $kelas_id ?>&mapel_id=<?= $mapel_id ?>"
                                    class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Input / Edit Nilai
                                </a>
                                <?php endif; ?>
                            </div>

                            <!-- Form Filter -->
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold">Pilih Kelas</label>
                                            <select name="kelas_id" class="form-select" required>
                                                <option value="">-- Pilih Kelas --</option>
                                                <?php while ($k = mysqli_fetch_assoc($kelas_list)) : ?>
                                                <option value="<?= $k['id'] ?>"
                                                    <?= $kelas_id == $k['id'] ? 'selected' : '' ?>>
                                                    <?= $k['nama_kelas'] ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold">Pilih Mata Pelajaran</label>
                                            <select name="mapel_id" class="form-select" required>
                                                <option value="">-- Pilih Mapel --</option>
                                                <?php while ($m = mysqli_fetch_assoc($mapel_list)) : ?>
                                                <option value="<?= $m['id'] ?>"
                                                    <?= $mapel_id == $m['id'] ? 'selected' : '' ?>>
                                                    <?= $m['nama_mapel'] ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-info w-100 text-white">
                                                <i class="fa fa-search"></i> Tampilkan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Tabel Rekapitulasi Nilai -->
                            <?php if (!empty($kelas_id) && !empty($mapel_id)) : ?>
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Daftar Nilai Siswa</h4>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%;" class="text-center">No</th>
                                                    <th style="width: 15%;">NISN</th>
                                                    <th>Nama Siswa</th>
                                                    <th class="text-center" style="width: 15%;">Nilai UTS</th>
                                                    <th class="text-center" style="width: 15%;">Nilai UAS</th>
                                                    <th class="text-center" style="width: 15%;">Rata-Rata</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (mysqli_num_rows($siswa_list) > 0) : ?>
                                                <?php 
                                    $no = 1;
                                    while ($s = mysqli_fetch_assoc($siswa_list)) : 
                                        $uts = $s['nilai_uts'];
                                        $uas = $s['nilai_uas'];
                                        $rata = ($uts !== NULL && $uas !== NULL) ? number_format(($uts + $uas) / 2, 1) : '-';
                                    ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars($s['nisn']) ?></td>
                                                    <td><strong><?= htmlspecialchars($s['nama_lengkap']) ?></strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <?= $uts !== NULL ? '<span class="badge bg-info fs-6">' . $uts . '</span>' : '<span class="text-muted">-</span>' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?= $uas !== NULL ? '<span class="badge bg-warning text-dark fs-6">' . $uas . '</span>' : '<span class="text-muted">-</span>' ?>
                                                    </td>
                                                    <td class="text-center fw-bold"><?= $rata ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                                <?php else : ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Belum ada data siswa
                                                        pada
                                                        kelas
                                                        ini.
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php else : ?>
                            <div class="alert alert-info text-center">
                                Silakan pilih <strong>Kelas</strong> dan <strong>Mata Pelajaran</strong> terlebih dahulu
                                untuk
                                melihat
                                daftar nilai.
                            </div>
                            <?php endif; ?>
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
    <script src="../../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../../assets/js/core/popper.min.js"></script>
    <script src="../../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../../assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="../../../assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="../../../assets/js/kaiadmin.min.js"></script>
</body>

</html>