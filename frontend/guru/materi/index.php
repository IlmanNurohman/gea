<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') { die('Akses ditolak'); }

$user_id = $_SESSION['user_id'] ?? '';
$q_guru  = mysqli_query($conn, "SELECT id FROM guru WHERE user_id = '$user_id'");
$d_guru  = mysqli_fetch_assoc($q_guru);
$guru_id = $d_guru['id'] ?? 0;

// Master Data Kelas & Mapel
$kelas_list = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$mapel_list = mysqli_query($conn, "SELECT * FROM mapel ORDER BY nama_mapel ASC");

// Fetch Data Materi
$query_materi = "SELECT materi.*, kelas.nama_kelas, mapel.nama_mapel 
                FROM materi 
                JOIN kelas ON materi.kelas_id = kelas.id 
                JOIN mapel ON materi.mapel_id = mapel.id 
                WHERE materi.guru_id = '$guru_id' 
                ORDER BY materi.id DESC";
$data_materi = mysqli_query($conn, $query_materi);
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
                <!-- End Navbar -->
            </div>
            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Materi</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="fas fa-book-open"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Manajemen Materi</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Data Materi</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <button class="btn btn-primary mb-3" data-bs-toggle="modal"
                                    data-bs-target="#modalTambahMateri">+
                                    Tambah
                                    Materi</button>
                            </div>



                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Kelola Materi Pembelajaran</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Kelas</th>
                                                    <th>Mapel</th>
                                                    <th>Pertemuan</th>
                                                    <th>Judul Materi</th>
                                                    <th>File</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($m = mysqli_fetch_assoc($data_materi)) : ?>
                                                <tr>
                                                    <td><?= date('d-m-Y', strtotime($m['tanggal'])) ?></td>
                                                    <td><?= $m['nama_kelas'] ?></td>
                                                    <td><?= $m['nama_mapel'] ?></td>
                                                    <td><span
                                                            class="badge bg-secondary"><?= htmlspecialchars($m['pertemuan']) ?></span>
                                                    </td>
                                                    <td><strong><?= htmlspecialchars($m['judul']) ?></strong></td>
                                                    <td>
                                                        <a href="../../../uploads/materi/<?= $m['file_materi'] ?>"
                                                            target="_blank" class="btn btn-info btn-sm">Download
                                                            File</a>
                                                    </td>
                                                    <td>
                                                        <a href="proses_materi.php?aksi=hapus&id=<?= $m['id'] ?>"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Hapus materi ini?')">Hapus</a>
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

    <!-- Modal Tambah Materi -->
    <div class="modal fade" id="modalTambahMateri" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="proses_materi.php?aksi=tambah" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="guru_id" value="<?= $guru_id ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Materi Pembelajaran</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Pilih Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php while ($k = mysqli_fetch_assoc($kelas_list)) : ?>
                                <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Pilih Mapel</label>
                            <select name="mapel_id" class="form-select" required>
                                <option value="">-- Pilih Mapel --</option>
                                <?php while ($mp = mysqli_fetch_assoc($mapel_list)) : ?>
                                <option value="<?= $mp['id'] ?>"><?= $mp['nama_mapel'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Pertemuan Ke-</label>
                            <input type="text" name="pertemuan" class="form-control"
                                placeholder="Contoh: Pertemuan 1 / Bab 2" required>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal Upload / Pelaksanaan</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>"
                                required>
                        </div>
                        <div class="mb-3">
                            <label>Judul Materi</label>
                            <input type="text" name="judul" class="form-control" placeholder="Judul Modul/Materi"
                                required>
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi Singkat (Opsional)</label>
                            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Upload File Materi (PDF/PPT/DOCX/ZIP)</label>
                            <input type="file" name="file_materi" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan Materi</button>
                    </div>
                </form>
            </div>
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