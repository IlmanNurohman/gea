<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

if (isset($_POST['simpan'])) {
    $namaKelas = $_POST['nama_kelas'];
    $waliKelas = $_POST['guru_id'];

    mysqli_query($conn, "
        INSERT INTO kelas (nama_kelas, guru_id)
        VALUES ('$namaKelas', '$waliKelas')
    ");

    header("Location: index.php");
}
?>