<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

$id = (int)$_POST['id'];

if (isset($_POST['update'])) {

    $namaKelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $waliKelas     = mysqli_real_escape_string($conn, $_POST['guru_id']);

        $query = mysqli_query($conn, "
            UPDATE kelas
            SET nama_kelas='$namaKelas',
                guru_id='$waliKelas'
                
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