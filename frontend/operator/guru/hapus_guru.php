<?php
session_start();
include '../../../backend/koneksi.php';

$id      = $_GET['id'] ?? '';
$user_id = $_GET['user_id'] ?? '';

if (!empty($id) && !empty($user_id)) {
    mysqli_query($conn, "DELETE FROM guru WHERE id = '$id'");
    mysqli_query($conn, "DELETE FROM users WHERE id = '$user_id'");

    $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Data guru berhasil dihapus', 'type' => 'success'];
} else {
    $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Data tidak terdeteksi', 'type' => 'error'];
}

header("Location: index.php");
exit;
?>