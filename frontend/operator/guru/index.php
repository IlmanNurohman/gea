<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

// Fetch data guru dengan join mapel
$query_guru = "SELECT guru.*, mapel.nama_mapel 
               FROM guru 
               LEFT JOIN mapel ON guru.mapel_id = mapel.id 
               ORDER BY guru.id DESC";
$data_guru = mysqli_query($conn, $query_guru);

// Fetch data mapel untuk dropdown
$data_mapel = mysqli_query($conn, "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel ASC");
$mapel_options = [];
while ($row = mysqli_fetch_assoc($data_mapel)) {
    $mapel_options[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Data Guru</title>
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
                            <a href="../dashboard_superadmin.php" class="collapsed" aria-expanded="false">
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
                            <a href="../jadwal_siswa/index.php">
                                <i class="fas fa-calendar-alt"></i>
                                <p>Jadwal Siswa</p>
                            </a>
                        </li>

                        <!-- Jadwal Guru -->
                        <li class="nav-item">
                            <a href="../jadwal_guru/index.php">
                                <i class="fas fa-calendar-check"></i>
                                <p>Jadwal Guru</p>
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
                        <h3 class="fw-bold mb-3">Guru</h3>
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
                                <a href="#">Manajemen Guru</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Data Guru</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalTambahGuru">
                                    <i class="fa fa-plus"></i> Tambah Guru
                                </button>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Daftar Guru</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>NIP/NUPTK</th>
                                                    <th>Nama Guru</th>
                                                    <th>L/P</th>
                                                    <th>Mata Pelajaran</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; while ($g = mysqli_fetch_assoc($data_guru)) : ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars($g['nip']) ?></td>
                                                    <td><?= htmlspecialchars($g['nama_guru']) ?></td>
                                                    <td><?= $g['jenis_kelamin'] ?></td>
                                                    <td><?= htmlspecialchars($g['nama_mapel'] ?? '-') ?></td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                            data-bs-target="#modalEditGuru<?= $g['id'] ?>">
                                                            Edit
                                                        </button>
                                                        <a href="hapus_guru.php?id=<?= $g['id'] ?>&user_id=<?= $g['user_id'] ?>"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Hapus data guru ini?')">Hapus</a>
                                                    </td>
                                                </tr>

                                                <!-- Modal Edit Guru -->
                                                <div class="modal fade" id="modalEditGuru<?= $g['id'] ?>" tabindex="-1"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="proses_guru.php?aksi=edit" method="POST">
                                                                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Data Guru</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label>NIP/NUPTK</label>
                                                                        <input type="text" name="nip"
                                                                            value="<?= $g['nip'] ?>"
                                                                            class="form-control" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Nama Guru</label>
                                                                        <input type="text" name="nama_guru"
                                                                            value="<?= $g['nama_guru'] ?>"
                                                                            class="form-control" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Jenis Kelamin</label>
                                                                        <select name="jenis_kelamin" class="form-select"
                                                                            required>
                                                                            <option value="L"
                                                                                <?= $g['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>
                                                                                Laki-laki</option>
                                                                            <option value="P"
                                                                                <?= $g['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>
                                                                                Perempuan</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Mata Pelajaran</label>
                                                                        <select name="mapel_id"
                                                                            class="form-select select2-mapel-edit"
                                                                            style="width: 100%;">
                                                                            <option value=""></option>

                                                                            <?php foreach ($mapel_options as $m) : ?>
                                                                            <option value="<?= $m['id'] ?>"
                                                                                <?= $g['mapel_id'] == $m['id'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($m['nama_mapel']) ?>
                                                                            </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-danger"
                                                                        data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" name="update"
                                                                        class="btn btn-primary">Simpan Ubah</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
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

        <!-- Modal Tambah Guru -->
        <div class="modal fade" id="modalTambahGuru" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="proses_guru.php?aksi=tambah" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Guru Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>NIP/NUPTK (Sekaligus Username Login)</label>
                                <input type="text" name="nip" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password Akun</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Nama Guru</label>
                                <input type="text" name="nama_guru" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Mata Pelajaran</label>

                                <select name="mapel_id" class="form-select select2-mapel" style="width: 100%;">
                                    <option value=""></option>

                                    <?php foreach ($mapel_options as $m) : ?>
                                    <option value="<?= $m['id'] ?>">
                                        <?= htmlspecialchars($m['nama_mapel']) ?>
                                    </option>
                                    <?php endforeach; ?>

                                </select>
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

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {

        $('.select2-mapel').select2({
            tags: true,
            allowClear: true,
            placeholder: 'Pilih atau ketik mata pelajaran...',
            width: '100%',
            dropdownParent: $('#modalTambahGuru')
        });

        $('.select2-mapel-edit').each(function() {

            const modalId = $(this)
                .closest('.modal')
                .attr('id');

            $(this).select2({
                tags: true,
                allowClear: true,
                placeholder: 'Pilih atau ketik mata pelajaran...',
                width: '100%',
                dropdownParent: $('#' + modalId)
            });

        });

    });
    </script>
</body>

</html>