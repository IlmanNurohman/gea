<?php
session_start();
include '../../../backend/koneksi.php';

$id = $_GET['id'] ?? '';
$user_id = $_GET['user_id'] ?? '';

if (!empty($id) && !empty($user_id)) {
    // Hapus data siswa
    mysqli_query($conn, "DELETE FROM siswa WHERE id = '$id'");
    // Hapus relasi akun di tabel users
    mysqli_query($conn, "DELETE FROM users WHERE id = '$user_id'");

    $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Data siswa berhasil dihapus', 'type' => 'success'];
} else {
    $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Data tidak ditemukan', 'type' => 'error'];
}

header("Location: index.php");
exit;
?>