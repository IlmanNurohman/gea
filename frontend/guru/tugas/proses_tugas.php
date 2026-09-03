<?php
session_start();
include '../../../backend/koneksi.php';

$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'tambah') {
    $guru_id  = $_POST['guru_id'];
    $kelas_id = $_POST['kelas_id'];
    $mapel_id = $_POST['mapel_id'];
    $judul    = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi= mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $deadline = $_POST['deadline'];

    $file_tugas = NULL;
    if (!empty($_FILES['file_tugas']['name'])) {
        $filename   = time() . '_' . $_FILES['file_tugas']['name'];
        $target_dir = "../../../uploads/tugas/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        move_uploaded_file($_FILES['file_tugas']['tmp_name'], $target_dir . $filename);
        $file_tugas = $filename;
    }

    $q_file = $file_tugas ? "'$file_tugas'" : "NULL";
    $query  = "INSERT INTO tugas (guru_id, kelas_id, mapel_id, judul, deskripsi, deadline, file_tugas) 
               VALUES ('$guru_id', '$kelas_id', '$mapel_id', '$judul', '$deskripsi', '$deadline', $q_file)";

    mysqli_query($conn, $query);
    header("Location: index.php");
    exit;
}

if ($aksi === 'nilai') {
    $id_kumpul = $_POST['id_kumpul'];
    $tugas_id  = $_POST['tugas_id'];
    $nilai     = $_POST['nilai'];
    $catatan   = mysqli_real_escape_string($conn, $_POST['catatan_guru']);

    mysqli_query($conn, "UPDATE pengumpulan_tugas SET nilai = '$nilai', catatan_guru = '$catatan' WHERE id = '$id_kumpul'");
    header("Location: detail_pengumpulan.php?tugas_id=" . $tugas_id);
    exit;
}
?>