<?php
session_start();
include '../../../backend/koneksi.php';

$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'tambah') {
    $pengumuman   = mysqli_real_escape_string($conn, $_POST['pengumuman']);
    $judul        = mysqli_real_escape_string($conn, $_POST['judul']);

    $query = "INSERT INTO pengumuman (judul, pengumuman) VALUES ('$judul', '$pengumuman')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Pengumuman berhasil ditambahkan', 'type' => 'success'];
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal menyimpan data pengumuman', 'type' => 'error'];
    }
    header("Location: index.php");
    exit;
}

if ($aksi === 'edit') {
    $id         = $_POST['id'];
    $pengumuman = mysqli_real_escape_string($conn, $_POST['pengumuman']);
    $judul      = mysqli_real_escape_string($conn, $_POST['judul']);
    $q_update = "UPDATE pengumuman SET 
                    judul = '$judul',
                    pengumuman = '$pengumuman'
                 WHERE id = '$id'";

    if (mysqli_query($conn, $q_update)) {
        $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Pengumuman berhasil diperbarui', 'type' => 'success'];
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal mengupdate pengumuman', 'type' => 'error'];
    }
    header("Location: index.php");
    exit;
}
?>