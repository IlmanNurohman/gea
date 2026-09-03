<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

// Fetch Master Data
$data_kelas = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
$kelas_options = mysqli_fetch_all($data_kelas, MYSQLI_ASSOC);

$data_mapel = mysqli_query($conn, "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel ASC");
$mapel_options = mysqli_fetch_all($data_mapel, MYSQLI_ASSOC);

$data_guru = mysqli_query($conn, "SELECT id, nama_guru FROM guru ORDER BY nama_guru ASC");
$guru_options = mysqli_fetch_all($data_guru, MYSQLI_ASSOC);

// Fetch All Jadwal
$query_jadwal = "SELECT jadwal.*, kelas.nama_kelas, mapel.nama_mapel, guru.nama_guru 
                 FROM jadwal 
                 JOIN kelas ON jadwal.kelas_id = kelas.id 
                 JOIN mapel ON jadwal.mapel_id = mapel.id 
                 JOIN guru ON jadwal.guru_id = guru.id 
                 ORDER BY FIELD(jadwal.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jadwal.jam_masuk ASC";
$res_jadwal = mysqli_query($conn, $query_jadwal);
$jadwal_list = mysqli_fetch_all($res_jadwal, MYSQLI_ASSOC);
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

            <div class=" container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Jadwal</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Manajemen Jadwal</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Data Jadwal</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalTambahJadwal">
                                    <i class="fa fa-plus"></i> Tambah Jadwal
                                </button>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <!-- Nav Tabs untuk Pisah Tampilan Kelas & Guru -->
                                    <ul class="nav nav-tabs card-header-tabs" id="jadwalTab" role="tablist">
                                        <li class="nav-item">
                                            <button class="nav-link active" id="kelas-tab" data-bs-toggle="tab"
                                                data-bs-target="#tab-kelas" type="button">Jadwal Per Kelas</button>
                                        </li>
                                        <li class="nav-item">
                                            <button class="nav-link" id="guru-tab" data-bs-toggle="tab"
                                                data-bs-target="#tab-guru" type="button">Jadwal Per Guru</button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content" id="jadwalTabContent">

                                        <!-- TAB JADWAL PER KELAS -->
                                        <div class="tab-pane fade show active" id="tab-kelas" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="display table table-striped table-hover basic-datatables">
                                                    <thead>
                                                        <tr>
                                                            <th>Hari</th>
                                                            <th>Jam</th>
                                                            <th>Kelas</th>
                                                            <th>Mata Pelajaran</th>
                                                            <th>Guru Pengajar</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($jadwal_list as $j) : ?>
                                                        <tr>
                                                            <td><span class="badge bg-info"><?= $j['hari'] ?></span>
                                                            </td>
                                                            <td><?= date('H:i', strtotime($j['jam_masuk'])) ?> -
                                                                <?= date('H:i', strtotime($j['jam_keluar'])) ?></td>
                                                            <td><strong><?= htmlspecialchars($j['nama_kelas']) ?></strong>
                                                            </td>
                                                            <td><?= htmlspecialchars($j['nama_mapel']) ?></td>
                                                            <td><?= htmlspecialchars($j['nama_guru']) ?></td>
                                                            <td>
                                                                <button class="btn btn-warning btn-sm"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditJadwal<?= $j['id'] ?>">Edit</button>
                                                                <a href="hapus_jadwal.php?id=<?= $j['id'] ?>"
                                                                    class="btn btn-danger btn-sm"
                                                                    onclick="return confirm('Hapus jadwal ini?')">Hapus</a>
                                                            </td>
                                                        </tr>

                                                        <!-- Modal Edit Jadwal -->
                                                        <div class="modal fade" id="modalEditJadwal<?= $j['id'] ?>"
                                                            tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <form action="proses_jadwal.php?aksi=edit"
                                                                        method="POST">
                                                                        <input type="hidden" name="id"
                                                                            value="<?= $j['id'] ?>">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Edit Jadwal</h5>
                                                                            <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="mb-3">
                                                                                <label>Pilih Kelas</label>
                                                                                <select name="kelas_id"
                                                                                    class="form-select" required>
                                                                                    <?php foreach ($kelas_options as $k) : ?>
                                                                                    <option value="<?= $k['id'] ?>"
                                                                                        <?= $j['kelas_id'] == $k['id'] ? 'selected' : '' ?>>
                                                                                        <?= htmlspecialchars($k['nama_kelas']) ?>
                                                                                    </option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label>Pilih Hari</label>
                                                                                <select name="hari" class="form-select"
                                                                                    required>
                                                                                    <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h) : ?>
                                                                                    <option value="<?= $h ?>"
                                                                                        <?= $j['hari'] == $h ? 'selected' : '' ?>>
                                                                                        <?= $h ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label>Pilih Mapel</label>
                                                                                <select name="mapel_id"
                                                                                    class="form-select" required>
                                                                                    <?php foreach ($mapel_options as $m) : ?>
                                                                                    <option value="<?= $m['id'] ?>"
                                                                                        <?= $j['mapel_id'] == $m['id'] ? 'selected' : '' ?>>
                                                                                        <?= htmlspecialchars($m['nama_mapel']) ?>
                                                                                    </option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label>Pilih Guru</label>
                                                                                <select name="guru_id"
                                                                                    class="form-select" required>
                                                                                    <?php foreach ($guru_options as $g) : ?>
                                                                                    <option value="<?= $g['id'] ?>"
                                                                                        <?= $j['guru_id'] == $g['id'] ? 'selected' : '' ?>>
                                                                                        <?= htmlspecialchars($g['nama_guru']) ?>
                                                                                    </option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-6 mb-3">
                                                                                    <label>Jam Masuk</label>
                                                                                    <input type="time" name="jam_masuk"
                                                                                        value="<?= $j['jam_masuk'] ?>"
                                                                                        class="form-control" required>
                                                                                </div>
                                                                                <div class="col-6 mb-3">
                                                                                    <label>Jam Keluar</label>
                                                                                    <input type="time" name="jam_keluar"
                                                                                        value="<?= $j['jam_keluar'] ?>"
                                                                                        class="form-control" required>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-danger"
                                                                                data-bs-dismiss="modal">Batal</button>
                                                                            <button type="submit" name="update"
                                                                                class="btn btn-primary">Simpan
                                                                                Ubah</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- TAB JADWAL PER GURU -->
                                        <div class="tab-pane fade" id="tab-guru" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="display table table-striped table-hover basic-datatables">
                                                    <thead>
                                                        <tr>
                                                            <th>Guru Pengajar</th>
                                                            <th>Hari</th>
                                                            <th>Jam</th>
                                                            <th>Mata Pelajaran</th>
                                                            <th>Mengajar Di Kelas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($jadwal_list as $j) : ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($j['nama_guru']) ?></strong>
                                                            </td>
                                                            <td><span class="badge bg-primary"><?= $j['hari'] ?></span>
                                                            </td>
                                                            <td><?= date('H:i', strtotime($j['jam_masuk'])) ?> -
                                                                <?= date('H:i', strtotime($j['jam_keluar'])) ?></td>
                                                            <td><?= htmlspecialchars($j['nama_mapel']) ?></td>
                                                            <td><span
                                                                    class="badge bg-success"><?= htmlspecialchars($j['nama_kelas']) ?></span>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
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
            <footer class="footer">
                <div class="container-fluid d-flex justify-content-center">

                    <div class="copyright ">
                        &copy; 2026 All rights reserved.
                    </div>

                </div>
            </footer>
        </div>

        <!-- Modal Tambah Jadwal -->
        <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="proses_jadwal.php?aksi=tambah" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Jadwal Pelajaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Pilih Kelas</label>
                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($kelas_options as $k) : ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Pilih Hari</label>
                                <select name="hari" class="form-select" required>
                                    <option value="">-- Pilih Hari --</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Pilih Mapel</label>
                                <select name="mapel_id" class="form-select" required>
                                    <option value="">-- Pilih Mapel --</option>
                                    <?php foreach ($mapel_options as $m) : ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Pilih Guru</label>
                                <select name="guru_id" class="form-select" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php foreach ($guru_options as $g) : ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_guru']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label>Jam Masuk</label>
                                    <input type="time" name="jam_masuk" class="form-control" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label>Jam Keluar</label>
                                    <input type="time" name="jam_keluar" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
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
    $(document).ready(function() {
        $('.basic-datatables').DataTable({});
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