<?php
session_start();
include '../../../backend/koneksi.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    if (mysqli_query($conn, "DELETE FROM kelas WHERE id = '$id'")) {
        $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Kelas berhasil dihapus', 'type' => 'success'];
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal menghapus kelas', 'type' => 'error'];
    }
}

header("Location: index.php");
exit;
?>