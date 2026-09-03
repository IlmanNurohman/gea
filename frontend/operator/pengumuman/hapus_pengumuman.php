<?php
session_start();
include '../../../backend/koneksi.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    if (mysqli_query($conn, "DELETE FROM pengumuman WHERE id = '$id'")) {
        $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Pengumuman berhasil dihapus', 'type' => 'success'];
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal menghapus pengumuman', 'type' => 'error'];
    }
}

header("Location: index.php");
exit;
?>