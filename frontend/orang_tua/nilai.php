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
        s.nisn,
        s.kelas_id
     FROM orang_tua_siswa ots
     INNER JOIN siswa s
        ON s.id = ots.siswa_id
     WHERE ots.orang_tua_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $orang_tua_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$anak = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$anak) {
    die('Anak belum terhubung dengan akun orang tua.');
}

$siswa_id = (int) $anak['id'];
$kelas_id = (int) $anak['kelas_id'];

/*
|--------------------------------------------------------------------------
| Tab aktif
|--------------------------------------------------------------------------
*/

$tab = $_GET['tab'] ?? 'tugas';

if (!in_array($tab, ['tugas', 'ujian'], true)) {
    $tab = 'tugas';
}

/*
|--------------------------------------------------------------------------
| Nilai tugas per mata pelajaran
|--------------------------------------------------------------------------
*/

$tugas_per_mapel = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        t.id AS tugas_id,
        t.judul,
        t.deadline,
        m.id AS mapel_id,
        m.nama_mapel,
        p.id AS id_kumpul,
        p.nilai,
        p.catatan_guru
     FROM tugas t
     INNER JOIN mapel m
        ON m.id = t.mapel_id
     LEFT JOIN pengumpulan_tugas p
        ON p.tugas_id = t.id
        AND p.siswa_id = ?
     WHERE t.kelas_id = ?
     ORDER BY m.nama_mapel ASC, t.deadline ASC"
);

mysqli_stmt_bind_param($stmt, "ii", $siswa_id, $kelas_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {

    $mapel_id = $row['mapel_id'];

    if (!isset($tugas_per_mapel[$mapel_id])) {
        $tugas_per_mapel[$mapel_id] = [
            'nama_mapel' => $row['nama_mapel'],
            'daftar'     => [],
            'total_nilai'=> 0,
            'jumlah_dinilai' => 0
        ];
    }

    $tugas_per_mapel[$mapel_id]['daftar'][] = $row;

    if ($row['nilai'] !== null) {
        $tugas_per_mapel[$mapel_id]['total_nilai'] += (int) $row['nilai'];
        $tugas_per_mapel[$mapel_id]['jumlah_dinilai']++;
    }
}

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| Rata-rata nilai tugas keseluruhan
|--------------------------------------------------------------------------
*/

$total_nilai_tugas    = 0;
$jumlah_nilai_tugas   = 0;

foreach ($tugas_per_mapel as $mp) {
    $total_nilai_tugas  += $mp['total_nilai'];
    $jumlah_nilai_tugas += $mp['jumlah_dinilai'];
}

$rata_rata_tugas = $jumlah_nilai_tugas > 0
    ? round($total_nilai_tugas / $jumlah_nilai_tugas, 1)
    : null;

/*
|--------------------------------------------------------------------------
| Nilai UTS & UAS per mata pelajaran
|--------------------------------------------------------------------------
*/

$ujian_per_mapel = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        m.id AS mapel_id,
        m.nama_mapel,
        n.jenis_ujian,
        n.nilai
     FROM nilai_ujian n
     INNER JOIN mapel m
        ON m.id = n.mapel_id
     WHERE n.siswa_id = ?
     ORDER BY m.nama_mapel ASC"
);

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {

    $mapel_id = $row['mapel_id'];

    if (!isset($ujian_per_mapel[$mapel_id])) {
        $ujian_per_mapel[$mapel_id] = [
            'nama_mapel' => $row['nama_mapel'],
            'UTS' => null,
            'UAS' => null
        ];
    }

    $jenis = strtoupper($row['jenis_ujian']);

    if (in_array($jenis, ['UTS', 'UAS'], true)) {
        $ujian_per_mapel[$mapel_id][$jenis] = (int) $row['nilai'];
    }
}

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| Rata-rata nilai ujian keseluruhan
|--------------------------------------------------------------------------
*/

$total_nilai_ujian  = 0;
$jumlah_nilai_ujian = 0;

foreach ($ujian_per_mapel as $mp) {
    foreach (['UTS', 'UAS'] as $jenis) {
        if ($mp[$jenis] !== null) {
            $total_nilai_ujian += $mp[$jenis];
            $jumlah_nilai_ujian++;
        }
    }
}

$rata_rata_ujian = $jumlah_nilai_ujian > 0
    ? round($total_nilai_ujian / $jumlah_nilai_ujian, 1)
    : null;

/*
|--------------------------------------------------------------------------
| Helper badge status tugas
|--------------------------------------------------------------------------
*/

function badgeStatusTugas(?int $id_kumpul, ?int $nilai): string
{
    if ($id_kumpul === null) {
        return '<span class="badge bg-secondary">Belum Dikumpulkan</span>';
    }

    if ($nilai === null) {
        return '<span class="badge bg-warning text-dark">Menunggu Penilaian</span>';
    }

    return '<span class="badge bg-success">Nilai: ' . $nilai . '</span>';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Nilai</title>
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
                        <li class="nav-item active">
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
                        <a href="../dashboard_superadmin.php" class="logo">
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
                                <a href="#">Rekap Nilai</a>
                            </li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">

                                <div class="card-body">

                                    <div class="row align-items-center">

                                        <div class="col-md-7">

                                            <h4 class="mb-1">
                                                Nilai Anak
                                            </h4>

                                            <h5 class="mb-1">
                                                <?= htmlspecialchars($anak['nama_lengkap']) ?>
                                            </h5>

                                            <p class="text-muted mb-0">
                                                NISN: <?= htmlspecialchars($anak['nisn']) ?>
                                            </p>

                                        </div>

                                        <div class="col-md-5 text-md-end mt-3 mt-md-0">

                                            <span class="badge bg-primary fs-6 me-2">
                                                Rata-rata Tugas:
                                                <?= $rata_rata_tugas ?? '-' ?>
                                            </span>

                                            <span class="badge bg-info fs-6">
                                                Rata-rata UTS/UAS:
                                                <?= $rata_rata_ujian ?? '-' ?>
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">

                        <div class="card-header bg-white">

                            <ul class="nav nav-tabs card-header-tabs" role="tablist">

                                <li class="nav-item" role="presentation">
                                    <a href="?tab=tugas" class="nav-link <?= $tab === 'tugas' ? 'active' : '' ?>">
                                        Nilai Tugas
                                    </a>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <a href="?tab=ujian" class="nav-link <?= $tab === 'ujian' ? 'active' : '' ?>">
                                        Nilai UTS & UAS
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </div>

                    <?php if ($tab === 'tugas'): ?>

                    <?php if (count($tugas_per_mapel) === 0): ?>

                    <div class="card shadow-sm">
                        <div class="card-body text-center p-5">
                            <div class="fs-1">📋</div>
                            <h5>Belum ada tugas</h5>
                            <p class="text-muted mb-0">
                                Belum ada tugas yang diberikan untuk kelas ini.
                            </p>
                        </div>
                    </div>

                    <?php else: ?>

                    <?php foreach ($tugas_per_mapel as $mapel_id => $mp): ?>

                    <div class="card shadow-sm mb-4">

                        <div class="card-header bg-white d-flex justify-content-between align-items-center">

                            <h5 class="mb-0">
                                <?= htmlspecialchars($mp['nama_mapel']) ?>
                            </h5>

                            <span class="text-muted small">
                                Rata-rata:
                                <?= $mp['jumlah_dinilai'] > 0
                                ? round($mp['total_nilai'] / $mp['jumlah_dinilai'], 1)
                                : '-' ?>
                            </span>

                        </div>

                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover mb-0">

                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>Judul Tugas</th>
                                            <th>Deadline</th>
                                            <th>Status / Nilai</th>
                                            <th>Catatan Guru</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php foreach ($mp['daftar'] as $index => $row): ?>

                                        <tr>
                                            <td><?= $index + 1 ?></td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($row['judul']) ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                date('d/m/Y', strtotime($row['deadline']))
                                            ) ?>
                                            </td>

                                            <td>
                                                <?= badgeStatusTugas(
                                                $row['id_kumpul'] !== null ? (int) $row['id_kumpul'] : null,
                                                $row['nilai'] !== null ? (int) $row['nilai'] : null
                                            ) ?>
                                            </td>

                                            <td>
                                                <?php if (!empty($row['catatan_guru'])): ?>
                                                <?= htmlspecialchars($row['catatan_guru']) ?>
                                                <?php else: ?>
                                                <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    <?php endforeach; ?>

                    <?php endif; ?>

                    <?php endif; ?>

                    <?php if ($tab === 'ujian'): ?>

                    <div class="card shadow-sm">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">Rekap Nilai UTS & UAS</h5>
                        </div>

                        <div class="card-body p-0">

                            <?php if (count($ujian_per_mapel) === 0): ?>

                            <div class="text-center p-5">
                                <div class="fs-1">📊</div>
                                <h5>Belum ada nilai ujian</h5>
                                <p class="text-muted mb-0">
                                    Belum ada nilai UTS/UAS yang diinput guru.
                                </p>
                            </div>

                            <?php else: ?>

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover mb-0">

                                    <thead class="table-light">
                                        <tr>
                                            <th>Mata Pelajaran</th>
                                            <th class="text-center">UTS</th>
                                            <th class="text-center">UAS</th>
                                            <th class="text-center">Rata-rata</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php foreach ($ujian_per_mapel as $mp): ?>

                                        <?php

                            $nilai_ada = array_filter(
                                [$mp['UTS'], $mp['UAS']],
                                fn($v) => $v !== null
                            );

                            $rata = count($nilai_ada) > 0
                                ? round(array_sum($nilai_ada) / count($nilai_ada), 1)
                                : null;

                            ?>

                                        <tr>
                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($mp['nama_mapel']) ?>
                                                </strong>
                                            </td>

                                            <td class="text-center">
                                                <?= $mp['UTS'] ?? '-' ?>
                                            </td>

                                            <td class="text-center">
                                                <?= $mp['UAS'] ?? '-' ?>
                                            </td>

                                            <td class="text-center">
                                                <strong><?= $rata ?? '-' ?></strong>
                                            </td>
                                        </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                            <?php endif; ?>

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