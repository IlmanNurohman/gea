<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepsek') {
    die('Akses ditolak');
}

// ===================================================== // AMBIL DAFTAR KELAS UNTUK DROPDOWN // =====================================================
$q_kelas = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");

// ===================================================== // AMBIL FILTER DARI GET // =====================================================
$kelas_id     = isset($_GET['kelas_id']) ? (int) $_GET['kelas_id'] : 0;
$filter_valid = $kelas_id > 0;
$data_siswa   = null;

if ($filter_valid) {
    // Fetch data siswa dengan join kelas, difilter berdasarkan kelas yang dipilih
    $stmt = mysqli_prepare($conn, "
        SELECT siswa.*, kelas.nama_kelas
        FROM siswa
        LEFT JOIN kelas ON siswa.kelas_id = kelas.id
        WHERE siswa.kelas_id = ?
        ORDER BY siswa.id DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
    mysqli_stmt_execute($stmt);
    $data_siswa = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Data Siswa</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="../../../assets/img/kaiadmin/favicon.ico" type="image/x-icon" />

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
                        <li class="nav-item active">
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
                        <h3 class="fw-bold mb-3">Data Siswa</h3>
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
                                <a href="#">Manajemen Siswa</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Data Siswa</a>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h3 class="fw-bold mb-3">Data Siswa</h3>
                            <h6 class="op-7 mb-2">Pilih kelas, untuk melihat daftar nama siswa per kelas
                            </h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Filter Siswa</h4>
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

                    <?php if (!$filter_valid): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Silakan pilih kelas terlebih dahulu untuk menampilkan daftar siswa.
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 card-title>Daftar Siswa</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (mysqli_num_rows($data_siswa) === 0): ?>
                                    <p class="text-muted mb-0">Belum ada siswa pada kelas ini.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>NISN</th>
                                                    <th>Nama Lengkap</th>
                                                    <th>L/P</th>
                                                    <th>TTL</th>
                                                    <th>Kelas</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; while ($s = mysqli_fetch_assoc($data_siswa)) : ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars($s['nisn']) ?></td>
                                                    <td><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                                                    <td><?= $s['jenis_kelamin'] ?></td>
                                                    <td><?= htmlspecialchars($s['tempat_lahir']) ?>,
                                                        <?= $s['tanggal_lahir'] ?></td>
                                                    <td><?= htmlspecialchars($s['nama_kelas'] ?? '-') ?></td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                            data-bs-target="#modalEditSiswa<?= $s['id'] ?>">
                                                            Edit
                                                        </button>
                                                        <a href="hapus_siswa.php?id=<?= $s['id'] ?>&user_id=<?= $s['user_id'] ?>"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Hapus data siswa ini?')">Hapus</a>
                                                    </td>
                                                </tr>

                                                <?php endwhile; ?>
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
    <script>
    $(document).ready(function() {
        $("#basic-datatables").DataTable({});
    });
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