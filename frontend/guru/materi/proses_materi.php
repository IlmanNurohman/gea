<?php
session_start();
include '../../../backend/koneksi.php';

$aksi = $_GET['aksi'] ?? '';

// 1. TAMBAH MATERI
if ($aksi === 'tambah') {
    $guru_id   = $_POST['guru_id'];
    $kelas_id  = $_POST['kelas_id'];
    $mapel_id  = $_POST['mapel_id'];
    $pertemuan = mysqli_real_escape_string($conn, $_POST['pertemuan']);
    $tanggal   = $_POST['tanggal'];
    $judul     = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    if (!empty($_FILES['file_materi']['name'])) {
        $filename   = time() . '_materi_' . $_FILES['file_materi']['name'];
        $target_dir = "../../../uploads/materi/";

        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $target_dir . $filename)) {
            $query = "INSERT INTO materi (guru_id, kelas_id, mapel_id, pertemuan, tanggal, judul, deskripsi, file_materi) 
                      VALUES ('$guru_id', '$kelas_id', '$mapel_id', '$pertemuan', '$tanggal', '$judul', '$deskripsi', '$filename')";
            mysqli_query($conn, $query);
        }
    }

    header("Location: index.php");
    exit;
}

// 2. HAPUS MATERI
if ($aksi === 'hapus') {
    $id = $_GET['id'] ?? 0;
    
    // Hapus file fisik dari folder
    $q = mysqli_query($conn, "SELECT file_materi FROM materi WHERE id = '$id'");
    $d = mysqli_fetch_assoc($q);
    if ($d && $d['file_materi']) {
        @unlink("../../../uploads/materi/" . $d['file_materi']);
    }

    mysqli_query($conn, "DELETE FROM materi WHERE id = '$id'");
    header("Location: index.php");
    exit;
}
?>