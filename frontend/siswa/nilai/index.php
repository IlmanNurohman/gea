<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') { die('Akses ditolak'); }

$user_id = $_SESSION['user_id'] ?? '';
$q_siswa = mysqli_query($conn, "SELECT id, kelas_id, nama_lengkap, nisn FROM siswa WHERE user_id = '$user_id'");
$d_siswa = mysqli_fetch_assoc($q_siswa);
$siswa_id = $d_siswa['id'] ?? 0;
$kelas_id = $d_siswa['kelas_id'] ?? 0;

// Query mengambil semua mapel & nilai UTS/UAS siswa tersebut
$query_nilai = "SELECT mapel.nama_mapel,
                (SELECT nilai FROM nilai_ujian WHERE siswa_id = '$siswa_id' AND mapel_id = mapel.id AND jenis_ujian = 'UTS') as nilai_uts,
                (SELECT nilai FROM nilai_ujian WHERE siswa_id = '$siswa_id' AND mapel_id = mapel.id AND jenis_ujian = 'UAS') as nilai_uas
                FROM mapel 
                ORDER BY mapel.nama_mapel ASC";
$data_nilai = mysqli_query($conn, $query_nilai);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Nilai Pelajaran</title>
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
                        <li class="nav-item active">
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
                    </ul>
                </div>
            </div>
        </div>
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
                                        <span class="fw-bold"><?= $_SESSION ['username']  ?></span>
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
                        <h3 class="fw-bold mb-3">Nilai</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#"> <i class="fas fa-star"></i></a>
                            </li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Manajemen Nilai</a></li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Nilai UAS/UTS</a>
                            </li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Rekap Nilai UTS & UAS</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="display table table-striped table-hover basic-datatables">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th>Mata Pelajaran</th>
                                                    <th class="text-center" style="width: 20%;">Nilai UTS</th>
                                                    <th class="text-center" style="width: 20%;">Nilai UAS</th>
                                                    <th class="text-center" style="width: 20%;">Rata-Rata</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                            $no = 1;
                            while ($n = mysqli_fetch_assoc($data_nilai)) : 
                                $uts = $n['nilai_uts'];
                                $uas = $n['nilai_uas'];
                                
                                // Hitung Rata-Rata jika dua-duanya sudah ada
                                $rata = '-';
                                if ($uts !== NULL && $uas !== NULL) {
                                    $rata = number_format(($uts + $uas) / 2, 1);
                                }
                            ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><strong><?= htmlspecialchars($n['nama_mapel']) ?></strong></td>
                                                    <td class="text-center">
                                                        <?= $uts !== NULL ? '<span class="badge bg-info fs-6">' . $uts . '</span>' : '<span class="text-muted">-</span>' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?= $uas !== NULL ? '<span class="badge bg-warning fs-6 text-dark">' . $uas . '</span>' : '<span class="text-muted">-</span>' ?>
                                                    </td>
                                                    <td class="text-center fw-bold">
                                                        <?= $rata ?>
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