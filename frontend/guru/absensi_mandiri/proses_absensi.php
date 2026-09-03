<?php
session_start();
include '../../../backend/koneksi.php';

// Atur zona waktu ke WIB
date_default_timezone_set('Asia/Jakarta');

$aksi = $_GET['aksi'] ?? '';

// 1. PROSES ABSEN MASUK
if ($aksi === 'masuk') {
    $guru_id    = $_POST['guru_id'] ?? '';
    $status     = $_POST['status'] ?? 'Hadir';
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan'] ?? '');
    $tanggal    = date('Y-m-d');
    $jam_sekarang = date('H:i:s');

    if (!empty($guru_id)) {
        if ($status === 'Hadir') {
            $query = "INSERT INTO absensi_guru (guru_id, tanggal, jam_masuk, status) 
                      VALUES ('$guru_id', '$tanggal', '$jam_sekarang', '$status')";
        } else {
            $query = "INSERT INTO absensi_guru (guru_id, tanggal, status, keterangan) 
                      VALUES ('$guru_id', '$tanggal', '$status', '$keterangan')";
        }

        if (mysqli_query($conn, $query)) {
            $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Absen masuk berhasil dicatat', 'type' => 'success'];
        } else {
            $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal mencatat absensi', 'type' => 'error'];
        }
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Data guru tidak ditemukan', 'type' => 'error'];
    }

    header("Location: index.php");
    exit;
}

// 2. PROSES ABSEN PULANG
if ($aksi === 'keluar') {
    $id = $_POST['id'] ?? '';
    $jam_sekarang = date('H:i:s');

    if (!empty($id)) {
        $query = "UPDATE absensi_guru SET jam_keluar = '$jam_sekarang' WHERE id = '$id'";

        if (mysqli_query($conn, $query)) {
            $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Absen pulang berhasil dicatat', 'type' => 'success'];
        } else {
            $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal mengupdate jam pulang', 'type' => 'error'];
        }
    }

    header("Location: index.php");
    exit;
}

// Jika akses tanpa parameter yang sesuai
header("Location: index.php");
exit;
?>