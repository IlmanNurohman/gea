<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') { die('Akses ditolak'); }

// Handle Buka Sesi Absensi
if (isset($_POST['buka_sesi'])) {
    $kelas_id = $_POST['kelas_id'];
    $tanggal  = date('Y-m-d');

    // Cek apakah hari ini sesi kelas tersebut sudah ada
    $cek = mysqli_query($conn, "SELECT id FROM sesi_absensi WHERE kelas_id = '$kelas_id' AND tanggal = '$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        // Jika sudah ada, ubah status jadi buka
        mysqli_query($conn, "UPDATE sesi_absensi SET status = 'buka' WHERE kelas_id = '$kelas_id' AND tanggal = '$tanggal'");
    } else {
        // Jika belum ada, buat sesi baru
        mysqli_query($conn, "INSERT INTO sesi_absensi (kelas_id, tanggal, status) VALUES ('$kelas_id', '$tanggal', 'buka')");
    }
    $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Absensi kelas berhasil dibuka', 'type' => 'success'];
    header("Location: index.php"); exit;
}

// Handle Tutup Sesi Absensi
if (isset($_GET['tutup_id'])) {
    $id = $_GET['tutup_id'];
    mysqli_query($conn, "UPDATE sesi_absensi SET status = 'tutup' WHERE id = '$id'");
    $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Absensi kelas berhasil ditutup', 'type' => 'success'];
    header("Location: index.php"); exit;
}

$today = date('Y-m-d');
$data_kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$sesi_aktif = mysqli_query($conn, "SELECT sesi_absensi.*, kelas.nama_kelas 
                                  FROM sesi_absensi 
                                  JOIN kelas ON sesi_absensi.kelas_id = kelas.id 
                                  WHERE sesi_absensi.tanggal = '$today' ORDER BY sesi_absensi.id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Kontrol Absensi - Operator</title>
    <link rel="stylesheet" href="../../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../../assets/css/kaiadmin.min.css" />
</head>

<body>
    <div class="container mt-4">
        <h2>Buka / Tutup Absensi Hari Ini (<?= date('d-m-Y') ?>)</h2>

        <!-- Form Buka Absensi -->
        <div class="card my-3">
            <div class="card-body">
                <form method="POST" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="col-form-label">Pilih Kelas:</label>
                    </div>
                    <div class="col-auto">
                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>

                            <?php while ($k = mysqli_fetch_assoc($data_kelas)) : ?>
                            <option value="<?= $k['id'] ?>">
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="buka_sesi" class="btn btn-primary">Buka Akses Absensi</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Status Absensi Hari Ini -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Status Absensi Kelas Hari Ini</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Status Access Guru</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($s = mysqli_fetch_assoc($sesi_aktif)) : ?>
                        <tr>
                            <td><?= $s['nama_kelas'] ?></td>
                            <td><?= $s['tanggal'] ?></td>
                            <td>
                                <?php if ($s['status'] == 'buka') : ?>
                                <span class="badge bg-success">DIBUKA (Guru Bisa Mengabsen)</span>
                                <?php else : ?>
                                <span class="badge bg-danger">DITUTUP</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['status'] == 'buka') : ?>
                                <a href="index.php?tutup_id=<?= $s['id'] ?>" class="btn btn-warning btn-sm">Tutup
                                    Absensi</a>
                                <?php else : ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="kelas_id" value="<?= $s['kelas_id'] ?>">
                                    <button type="submit" name="buka_sesi" class="btn btn-success btn-sm">Buka
                                        Kembali</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>