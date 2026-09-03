```php
<?php

session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    die('Akses ditolak');
}

if (!isset($_POST['simpan'])) {
    header('Location: index.php');
    exit;
}

$guru_id  = (int) ($_POST['guru_id'] ?? 0);
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$mapel_id = (int) ($_POST['mapel_id'] ?? 0);
$tanggal  = $_POST['tanggal'] ?? date('Y-m-d');

$absensi    = $_POST['absensi'] ?? [];
$keterangan = $_POST['keterangan'] ?? [];

if ($guru_id <= 0 || $kelas_id <= 0) {
    die('Data guru atau kelas tidak valid.');
}

foreach ($absensi as $siswa_id => $status) {

    $siswa_id = (int) $siswa_id;

    $status = mysqli_real_escape_string(
        $conn,
        $status
    );

    $ket = mysqli_real_escape_string(
        $conn,
        $keterangan[$siswa_id] ?? ''
    );

    // Cek absensi siswa pada tanggal tersebut
    $cek = mysqli_query(
        $conn,
        "SELECT id
         FROM absensi
         WHERE siswa_id = '$siswa_id'
         AND tanggal = '$tanggal'
             AND mapel_id = '$mapel_id'
         LIMIT 1"
    );

    if (!$cek) {
        die('Gagal mengecek absensi: ' . mysqli_error($conn));
    }

    if (mysqli_num_rows($cek) > 0) {

        // Jika sudah ada → update
        $query = mysqli_query(
            $conn,
            "UPDATE absensi
             SET
                status = '$status',
                keterangan = '$ket',
                guru_id = '$guru_id'
             WHERE siswa_id = '$siswa_id'
             AND tanggal = '$tanggal'
                 AND mapel_id = '$mapel_id'"
        );

    } else {

        // Jika belum ada → insert
        $query = mysqli_query(
            $conn,
            "INSERT INTO absensi
                (siswa_id, guru_id, mapel_id, tanggal, status, keterangan)
             VALUES
                ('$siswa_id', '$guru_id', '$mapel_id', '$tanggal', '$status', '$ket')"
        );
    }

    if (!$query) {
        die('Gagal menyimpan absensi: ' . mysqli_error($conn));
    }
}

$_SESSION['swal'] = [
    'title' => 'Berhasil',
    'text'  => 'Data absensi siswa berhasil disimpan.',
    'type'  => 'success'
];

header(
    "Location: index.php?kelas_id=$kelas_id&mapel_id=$mapel_id"
);
exit;