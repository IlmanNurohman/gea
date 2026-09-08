<?php

session_start();
include '../../backend/koneksi.php';

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'orang_tua'
) {
    die('Akses ditolak');
}

$orang_tua_id = (int) ($_SESSION['user_id'] ?? 0);

if ($orang_tua_id <= 0) {
    die('Session orang tua tidak valid.');
}

/*
|--------------------------------------------------------------------------
| Ambil anak yang terhubung dengan akun orang tua
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        s.id,
        s.nama_lengkap,
        s.nisn
     FROM orang_tua_siswa ots
     INNER JOIN siswa s
        ON s.id = ots.siswa_id
     WHERE ots.orang_tua_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $orang_tua_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$anak = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$anak) {
    die('Anak belum terhubung dengan akun orang tua.');
}

$siswa_id = (int) $anak['id'];

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

$mode = $_GET['mode'] ?? 'hari_ini';

if (!in_array($mode, ['hari_ini', 'mingguan', 'bulanan'], true)) {
    $mode = 'hari_ini';
}

$bulan = (int) ($_GET['bulan'] ?? date('n'));
$tahun = (int) ($_GET['tahun'] ?? date('Y'));

if ($bulan < 1 || $bulan > 12) {
    $bulan = (int) date('n');
}

if ($tahun < 2020 || $tahun > 2100) {
    $tahun = (int) date('Y');
}

/*
|--------------------------------------------------------------------------
| Nama bulan (dipakai di beberapa tempat)
|--------------------------------------------------------------------------
*/

$nama_bulan = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

/*
|--------------------------------------------------------------------------
| Tentukan periode
|--------------------------------------------------------------------------
*/

$hari_ini = date('Y-m-d');

if ($mode === 'mingguan') {

    $tanggal_mulai = date(
        'Y-m-d',
        strtotime('monday this week')
    );

    $tanggal_selesai = date(
        'Y-m-d',
        strtotime('sunday this week')
    );
} elseif ($mode === 'bulanan') {

    $tanggal_mulai = date(
        'Y-m-01',
        strtotime("$tahun-$bulan-01")
    );

    $tanggal_selesai = date(
        'Y-m-t',
        strtotime("$tahun-$bulan-01")
    );
} else {

    $tanggal_mulai = $hari_ini;
    $tanggal_selesai = $hari_ini;
}

/*
|--------------------------------------------------------------------------
| Statistik periode
|--------------------------------------------------------------------------
*/

$statistik = [
    'Hadir' => 0,
    'Izin'  => 0,
    'Sakit' => 0,
    'Alpa'  => 0
];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        status,
        COUNT(*) AS jumlah
     FROM absensi
     WHERE siswa_id = ?
     AND tanggal BETWEEN ? AND ?
     GROUP BY status"
);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $siswa_id,
    $tanggal_mulai,
    $tanggal_selesai
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {

    $status = $row['status'];

    if (isset($statistik[$status])) {
        $statistik[$status] = (int) $row['jumlah'];
    }
}

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| Total absensi
|--------------------------------------------------------------------------
*/

$total_absensi = array_sum($statistik);

/*
|--------------------------------------------------------------------------
| Persentase kehadiran
|--------------------------------------------------------------------------
*/

$persentase_hadir = 0;

if ($total_absensi > 0) {
    $persentase_hadir = round(
        ($statistik['Hadir'] / $total_absensi) * 100
    );
}

/*
|--------------------------------------------------------------------------
| Absensi hari ini
|--------------------------------------------------------------------------
*/

$absensi_hari_ini = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        a.id,
        a.tanggal,
        a.status,
        a.keterangan,
        m.nama_mapel
     FROM absensi a
     INNER JOIN mapel m
        ON m.id = a.mapel_id
     WHERE a.siswa_id = ?
     AND a.tanggal = ?
     ORDER BY m.nama_mapel ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "is",
    $siswa_id,
    $hari_ini
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $absensi_hari_ini[] = $row;
}

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| Rekap per mata pelajaran
|--------------------------------------------------------------------------
*/

$rekap_mapel = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        m.id AS mapel_id,
        m.nama_mapel,

        SUM(CASE
            WHEN a.status = 'Hadir'
            THEN 1 ELSE 0
        END) AS hadir,

        SUM(CASE
            WHEN a.status = 'Izin'
            THEN 1 ELSE 0
        END) AS izin,

        SUM(CASE
            WHEN a.status = 'Sakit'
            THEN 1 ELSE 0
        END) AS sakit,

        SUM(CASE
            WHEN a.status = 'Alpa'
            THEN 1 ELSE 0
        END) AS alpa

     FROM absensi a

     INNER JOIN mapel m
        ON m.id = a.mapel_id

     WHERE a.siswa_id = ?
     AND a.tanggal BETWEEN ? AND ?

     GROUP BY
        m.id,
        m.nama_mapel

     ORDER BY
        m.nama_mapel ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $siswa_id,
    $tanggal_mulai,
    $tanggal_selesai
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $rekap_mapel[] = $row;
}

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| Helper status
|--------------------------------------------------------------------------
*/

function badgeStatus(string $status): string
{
    switch (strtolower($status)) {

        case 'hadir':
            return '<span class="badge bg-success">Hadir</span>';

        case 'izin':
            return '<span class="badge bg-warning text-dark">Izin</span>';

        case 'sakit':
            return '<span class="badge bg-info">Sakit</span>';

        case 'alpa':
            return '<span class="badge bg-danger">Alpa</span>';

        default:
            return '<span class="badge bg-secondary">'
                . htmlspecialchars($status)
                . '</span>';
    }
}

function formatTanggalIndonesia(string $tanggal): string
{
    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $timestamp = strtotime($tanggal);

    return date('d', $timestamp)
        . ' '
        . $bulan[(int) date('n', $timestamp)]
        . ' '
        . date('Y', $timestamp);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Rekap Absensi</title>
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
        <div class="sidebar" data-background-color="dark">
            <div class="sidebar-logo">
                <!-- Logo Header -->
                <div class="logo-header" data-background-color="dark">
                    <a href="" class="logo">
                        <img src="../../assets/img/logo.png" alt="navbar brand"
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
                        <li class="nav-item">
                            <a href="dashboardOrangtua.php" class="collapsed" aria-expanded="false">
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
                            <a href="absensi.php">
                                <i class="fas fa-user-check"></i>
                                <p>Absensi</p>
                            </a>
                        </li>

                        <!-- Nilai -->
                        <li class="nav-item">
                            <a href="nilai.php">
                                <i class="fas fa-star"></i>
                                <p>Nilai</p>
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

                                        <span class="fw-bold"><?= $_SESSION ['username'] ?></span>
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
                                                    <h4><?= $_SESSION ['username'] ?></h4>
                                                    <p class="text-muted"><?= $_SESSION ['email'] ?></p>

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
                        <h3 class="fw-bold mb-3">Absensi</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="fas fa-user-check"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Manajemen Absensi</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Data Abensi</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">

                            <div class="card">

                                <div class="card-body">

                                    <div class="row align-items-center">

                                        <div class="col-md-8">

                                            <h4 class="card-title">
                                                Absensi Anak
                                            </h4>
                                            <h5 class="card-title">
                                                <?= htmlspecialchars($anak['nama_lengkap']) ?>
                                            </h5>

                                            <p class="text-muted mb-0">

                                                NISN:
                                                <?= htmlspecialchars($anak['nisn']) ?>

                                            </p>

                                        </div>

                                        <div class="col-md-4 text-md-end mt-3 mt-md-0">

                                            <span class="badge bg-primary fs-6">

                                                Kehadiran
                                                <?= $persentase_hadir ?>%

                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">

                            <div class="card">

                                <div class="card-header">

                                    <ul class="nav nav-tabs card-header-tabs" role="tablist">

                                        <li class="nav-item" role="presentation">
                                            <a href="?mode=hari_ini"
                                                class="nav-link <?= $mode === 'hari_ini' ? 'active' : '' ?>">
                                                Hari Ini
                                            </a>
                                        </li>

                                        <li class="nav-item" role="presentation">
                                            <a href="?mode=mingguan"
                                                class="nav-link <?= $mode === 'mingguan' ? 'active' : '' ?>">
                                                Mingguan
                                            </a>
                                        </li>

                                        <li class="nav-item" role="presentation">
                                            <a href="?mode=bulanan&bulan=<?= date('n') ?>&tahun=<?= date('Y') ?>"
                                                class="nav-link <?= $mode === 'bulanan' ? 'active' : '' ?>">
                                                Bulanan
                                            </a>
                                        </li>

                                    </ul>

                                </div>

                                <?php if ($mode === 'bulanan'): ?>

                                <div class="card-body">

                                    <form method="GET" class="row g-2">

                                        <input type="hidden" name="mode" value="bulanan">

                                        <div class="col-md-4">

                                            <select name="bulan" class="form-select">

                                                <?php foreach ($nama_bulan as $nomor => $nama): ?>

                                                <option value="<?= $nomor ?>" <?= $bulan === $nomor
                                                                        ? 'selected'
                                                                        : '' ?>>
                                                    <?= $nama ?>
                                                </option>

                                                <?php endforeach; ?>

                                            </select>

                                        </div>

                                        <div class="col-md-3">

                                            <select name="tahun" class="form-select">

                                                <?php

                                $tahun_sekarang = (int) date('Y');

                                for (
                                    $i = $tahun_sekarang - 2;
                                    $i <= $tahun_sekarang;
                                    $i++
                                ):

                                ?>

                                                <option value="<?= $i ?>" <?= $tahun === $i
                                                                    ? 'selected'
                                                                    : '' ?>>
                                                    <?= $i ?>
                                                </option>

                                                <?php endfor; ?>

                                            </select>

                                        </div>

                                        <div class="col-md-2">

                                            <button type="submit" class="btn btn-primary w-100">
                                                Tampilkan
                                            </button>

                                        </div>

                                    </form>

                                </div>

                                <?php endif; ?>

                            </div>

                            <div class="mb-3">

                                <h4 class="card-title">

                                    <?php if ($mode === 'hari_ini'): ?>

                                    Absensi Hari Ini

                                    <?php elseif ($mode === 'mingguan'): ?>

                                    Rekap Minggu Ini

                                    <?php else: ?>

                                    Rekap Bulan
                                    <?= $nama_bulan[$bulan] ?? $bulan ?>
                                    <?= $tahun ?>

                                    <?php endif; ?>

                                </h4>

                                <small class="text-muted">

                                    <?= formatTanggalIndonesia($tanggal_mulai) ?>

                                    <?php if ($tanggal_mulai !== $tanggal_selesai): ?>

                                    -
                                    <?= formatTanggalIndonesia($tanggal_selesai) ?>

                                    <?php endif; ?>

                                </small>

                            </div>

                            <div class="row g-3 mb-4">

                                <!-- Hadir -->
                                <div class="col-6 col-md-3">

                                    <div class="card">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between">

                                                <div>

                                                    <small class="text-muted">
                                                        Hadir
                                                    </small>

                                                    <div class="stat-number text-success">
                                                        <?= $statistik['Hadir'] ?>
                                                    </div>

                                                </div>

                                                <div class="status-icon">
                                                    🟢
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <!-- Izin -->
                                <div class="col-6 col-md-3">

                                    <div class="card">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between">

                                                <div>

                                                    <small class="text-muted">
                                                        Izin
                                                    </small>

                                                    <div class="stat-number text-warning">
                                                        <?= $statistik['Izin'] ?>
                                                    </div>

                                                </div>

                                                <div class="status-icon">
                                                    🟡
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <!-- Sakit -->
                                <div class="col-6 col-md-3">

                                    <div class="card">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between">

                                                <div>

                                                    <small class="text-muted">
                                                        Sakit
                                                    </small>

                                                    <div class="stat-number text-info">
                                                        <?= $statistik['Sakit'] ?>
                                                    </div>

                                                </div>

                                                <div class="status-icon">
                                                    🔵
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <!-- Alpa -->
                                <div class="col-6 col-md-3">

                                    <div class="card">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between">

                                                <div>

                                                    <small class="text-muted">
                                                        Alpa
                                                    </small>

                                                    <div class="stat-number text-danger">
                                                        <?= $statistik['Alpa'] ?>
                                                    </div>

                                                </div>

                                                <div class="status-icon">
                                                    🔴
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <?php if ($mode === 'hari_ini'): ?>

                            <div class="card">

                                <div class="card-header">

                                    <h4 class="card-title">
                                        Absensi Setiap Mata Pelajaran
                                    </h4>

                                </div>

                                <div class="card-body">

                                    <?php if (count($absensi_hari_ini) > 0): ?>

                                    <div class="table-responsive">

                                        <table id="basic-datatables" class="display table table-striped table-hover">

                                            <thead>

                                                <tr>

                                                    <th width="50">
                                                        #
                                                    </th>

                                                    <th>
                                                        Mata Pelajaran
                                                    </th>

                                                    <th>
                                                        Status
                                                    </th>

                                                    <th>
                                                        Keterangan
                                                    </th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                <?php foreach (
                                        $absensi_hari_ini
                                        as $index => $row
                                    ): ?>

                                                <tr>

                                                    <td>
                                                        <?= $index + 1 ?>
                                                    </td>

                                                    <td>
                                                        <strong>
                                                            <?= htmlspecialchars(
                                                        $row['nama_mapel']
                                                    ) ?>
                                                        </strong>
                                                    </td>

                                                    <td>
                                                        <?= badgeStatus(
                                                    $row['status']
                                                ) ?>
                                                    </td>

                                                    <td>

                                                        <?php if (
                                                    !empty($row['keterangan'])
                                                ): ?>

                                                        <?= htmlspecialchars(
                                                        $row['keterangan']
                                                    ) ?>

                                                        <?php else: ?>

                                                        <span class="text-muted">
                                                            -
                                                        </span>

                                                        <?php endif; ?>

                                                    </td>

                                                </tr>

                                                <?php endforeach; ?>

                                            </tbody>

                                        </table>

                                    </div>

                                    <?php else: ?>

                                    <div class="alert alert-warning text-center">Belum ada absensi yang dilakukan hari
                                        ini
                                    </div>

                                    <?php endif; ?>

                                </div>

                            </div>
                        </div>
                    </div>

                    <?php endif; ?>

                    <div class="card">

                        <div class="card-header">

                            <h4 class="card-title">
                                Rekap Absensi Per Mata Pelajaran
                            </h4>

                        </div>

                        <div class="card-body">

                            <?php if (count($rekap_mapel) > 0): ?>

                            <div class="table-responsive">

                                <table id="basic-datatables" class="display table table-striped table-hover">

                                    <thead>

                                        <tr>

                                            <th>
                                                Mata Pelajaran
                                            </th>

                                            <th class="text-center">
                                                Hadir
                                            </th>

                                            <th class="text-center">
                                                Izin
                                            </th>

                                            <th class="text-center">
                                                Sakit
                                            </th>

                                            <th class="text-center">
                                                Alpa
                                            </th>

                                            <th class="text-center">
                                                Total
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php foreach ($rekap_mapel as $row): ?>

                                        <?php

                                    $total =
                                        (int) $row['hadir'] +
                                        (int) $row['izin'] +
                                        (int) $row['sakit'] +
                                        (int) $row['alpa'];

                                    ?>

                                        <tr>

                                            <td>

                                                <strong>
                                                    <?= htmlspecialchars(
                                                    $row['nama_mapel']
                                                ) ?>
                                                </strong>

                                            </td>

                                            <td class="text-center text-success">

                                                <?= (int) $row['hadir'] ?>

                                            </td>

                                            <td class="text-center text-warning">

                                                <?= (int) $row['izin'] ?>

                                            </td>

                                            <td class="text-center text-info">

                                                <?= (int) $row['sakit'] ?>

                                            </td>

                                            <td class="text-center text-danger">

                                                <?= (int) $row['alpa'] ?>

                                            </td>

                                            <td class="text-center">

                                                <strong>
                                                    <?= $total ?>
                                                </strong>

                                            </td>

                                        </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                            <?php else: ?>

                            <div class="alert alert-warning text-center">Belum ada rekap absensi
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


</body>

</html>