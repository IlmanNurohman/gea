<?php
session_start();
include '../../../backend/koneksi.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    if (mysqli_query($conn, "DELETE FROM jadwal WHERE id = '$id'")) {
        $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Jadwal berhasil dihapus', 'type' => 'success'];
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal menghapus jadwal', 'type' => 'error'];
    }
}

header("Location: index.php");
exit;
?>