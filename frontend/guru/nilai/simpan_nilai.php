<?php
session_start();
include '../../../backend/koneksi.php';

if (isset($_POST['simpan'])) {
    $guru_id     = $_POST['guru_id'];
    $kelas_id    = $_POST['kelas_id'];
    $mapel_id    = $_POST['mapel_id'];
    $jenis_ujian = $_POST['jenis_ujian'];
    $nilai_data  = $_POST['nilai'] ?? [];

    foreach ($nilai_data as $siswa_id => $val_nilai) {
        $val_nilai = (int) $val_nilai;

        // Cek apakah nilai siswa sudah ada di database
        $cek = mysqli_query($conn, "SELECT id FROM nilai_ujian 
                                    WHERE kelas_id = '$kelas_id' 
                                    AND mapel_id = '$mapel_id' 
                                    AND siswa_id = '$siswa_id' 
                                    AND jenis_ujian = '$jenis_ujian'");

        if (mysqli_num_rows($cek) > 0) {
            // Update nilai yang sudah ada
            mysqli_query($conn, "UPDATE nilai_ujian 
                                 SET nilai = '$val_nilai', guru_id = '$guru_id' 
                                 WHERE kelas_id = '$kelas_id' 
                                 AND mapel_id = '$mapel_id' 
                                 AND siswa_id = '$siswa_id' 
                                 AND jenis_ujian = '$jenis_ujian'");
        } else {
            // Insert nilai baru
            mysqli_query($conn, "INSERT INTO nilai_ujian (guru_id, kelas_id, mapel_id, siswa_id, jenis_ujian, nilai) 
                                 VALUES ('$guru_id', '$kelas_id', '$mapel_id', '$siswa_id', '$jenis_ujian', '$val_nilai')");
        }
    }

    $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Nilai ujian berhasil disimpan', 'type' => 'success'];
    header("Location: index.php?jenis_ujian=$jenis_ujian&kelas_id=$kelas_id&mapel_id=$mapel_id");
    exit;
}
?>