<?php
session_start();
include '../../../backend/koneksi.php';

$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'tambah') {
    $nisn          = mysqli_real_escape_string($conn, $_POST['nisn']);
    $password      = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tempat_lahir  = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $kelas_id      = !empty($_POST['kelas_id']) ? "'".mysqli_real_escape_string($conn, $_POST['kelas_id'])."'" : "NULL";

    // 1. Buat User akun terlebih dahulu
    $q_user = "INSERT INTO users (username, password, role) VALUES ('$nisn', '$password', 'siswa')";
    if (mysqli_query($conn, $q_user)) {
        $user_id = mysqli_insert_id($conn);
        
        // 2. Buat Data Siswa
        $q_siswa = "INSERT INTO siswa (user_id, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, nisn, kelas_id) 
                    VALUES ('$user_id', '$nama_lengkap', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$nisn', $kelas_id)";
        
        if (mysqli_query($conn, $q_siswa)) {
            $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Data siswa berhasil ditambahkan', 'type' => 'success'];
        } else {
            $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal membuat data siswa', 'type' => 'error'];
        }
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'NISN/Username sudah digunakan', 'type' => 'error'];
    }
    header("Location: index.php");
    exit;
}

if ($aksi === 'edit') {
    $id            = $_POST['id'];
    $nisn          = mysqli_real_escape_string($conn, $_POST['nisn']);
    $nama_lengkap  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tempat_lahir  = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $kelas_id      = !empty($_POST['kelas_id']) ? "'".mysqli_real_escape_string($conn, $_POST['kelas_id'])."'" : "NULL";

    $q_update = "UPDATE siswa SET 
                    nisn = '$nisn',
                    nama_lengkap = '$nama_lengkap',
                    jenis_kelamin = '$jenis_kelamin',
                    tempat_lahir = '$tempat_lahir',
                    tanggal_lahir = '$tanggal_lahir',
                    kelas_id = $kelas_id
                 WHERE id = '$id'";

    if (mysqli_query($conn, $q_update)) {
        $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Data siswa berhasil diperbarui', 'type' => 'success'];
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal mengupdate data', 'type' => 'error'];
    }
    header("Location: index.php");
    exit;
}
?>