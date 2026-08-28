<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

if (isset($_POST['simpan'])) {
    $namaKelas = $_POST['nama_kelas'];
    $waliKelas = $_POST['wali_kelas'];

    mysqli_query($conn, "
        INSERT INTO kelas (nama_kelas, wali_kelas)
        VALUES ('$namaKelas', '$waliKelas')
    ");

    header("Location: index.php");
}
?>