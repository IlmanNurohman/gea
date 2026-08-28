<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

$id = (int)$_POST['id'];

if (isset($_POST['update'])) {

    $namaKelas = mysqli_real_escape_string($conn, $_POST['nam_kelas']);
    $waliKelas     = mysqli_real_escape_string($conn, $_POST['wali_kelas']);

        $query = mysqli_query($conn, "
            UPDATE users
            SET nama_kelas='$namaKelas',
                password='$waliKelas',
                
            WHERE id=$id
        ");
    
    if ($query) {
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Data kelas berhasil diupdate.'
        ];
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Gagal Database!',
            'text' => mysqli_error($conn)
        ];
    }

    header("Location: index.php");
    exit();
}