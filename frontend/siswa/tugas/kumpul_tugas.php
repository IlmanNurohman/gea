<?php
session_start();
include '../../../backend/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tugas_id      = $_POST['tugas_id'];
    $siswa_id      = $_POST['siswa_id'];
    $catatan_siswa = mysqli_real_escape_string($conn, $_POST['catatan_siswa'] ?? '');

    if (!empty($_FILES['file_jawaban']['name'])) {
        $filename   = time() . '_siswa_' . $siswa_id . '_' . $_FILES['file_jawaban']['name'];
        $target_dir = "../../../uploads/jawaban/";

        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        if (move_uploaded_file($_FILES['file_jawaban']['tmp_name'], $target_dir . $filename)) {
            $query = "INSERT INTO pengumpulan_tugas (tugas_id, siswa_id, file_jawaban, catatan_siswa) 
                      VALUES ('$tugas_id', '$siswa_id', '$filename', '$catatan_siswa')";
            mysqli_query($conn, $query);
        }
    }
}

header("Location: index.php");
exit;
?>