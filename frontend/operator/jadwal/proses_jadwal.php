<?php
session_start();
include '../../../backend/koneksi.php';

$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'tambah') {
    $kelas_id   = mysqli_real_escape_string($conn, $_POST['kelas_id']);
    $hari       = mysqli_real_escape_string($conn, $_POST['hari']);
    $mapel_id   = mysqli_real_escape_string($conn, $_POST['mapel_id']);
    $guru_id    = mysqli_real_escape_string($conn, $_POST['guru_id']);
    $jam_masuk  = mysqli_real_escape_string($conn, $_POST['jam_masuk']);
    $jam_keluar = mysqli_real_escape_string($conn, $_POST['jam_keluar']);

    // Cek Bentrok Kelas pada Jam & Hari yang sama
    $cek_kelas = mysqli_query($conn, "SELECT id FROM jadwal WHERE kelas_id = '$kelas_id' AND hari = '$hari' 
                                      AND (('$jam_masuk' < jam_keluar AND '$jam_keluar' > jam_masuk))");
    
    // Cek Bentrok Guru pada Jam & Hari yang sama
    $cek_guru  = mysqli_query($conn, "SELECT id FROM jadwal WHERE guru_id = '$guru_id' AND hari = '$hari' 
                                      AND (('$jam_masuk' < jam_keluar AND '$jam_keluar' > jam_masuk))");

    if (mysqli_num_rows($cek_kelas) > 0) {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Kelas ini sudah ada jadwal pelajaran lain pada jam tersebut!', 'type' => 'error'];
    } else if (mysqli_num_rows($cek_guru) > 0) {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Guru tersebut sedang mengajar di kelas lain pada jam yang sama!', 'type' => 'error'];
    } else {
        $query = "INSERT INTO jadwal (kelas_id, mapel_id, guru_id, hari, jam_masuk, jam_keluar) 
                  VALUES ('$kelas_id', '$mapel_id', '$guru_id', '$hari', '$jam_masuk', '$jam_keluar')";

        if (mysqli_query($conn, $query)) {
            $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Jadwal pelajaran berhasil ditambahkan', 'type' => 'success'];
        } else {
            $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal menyimpan data jadwal', 'type' => 'error'];
        }
    }
    header("Location: index.php");
    exit;
}

if ($aksi === 'edit') {
    $id         = $_POST['id'];
    $kelas_id   = mysqli_real_escape_string($conn, $_POST['kelas_id']);
    $hari       = mysqli_real_escape_string($conn, $_POST['hari']);
    $mapel_id   = mysqli_real_escape_string($conn, $_POST['mapel_id']);
    $guru_id    = mysqli_real_escape_string($conn, $_POST['guru_id']);
    $jam_masuk  = mysqli_real_escape_string($conn, $_POST['jam_masuk']);
    $jam_keluar = mysqli_real_escape_string($conn, $_POST['jam_keluar']);

    $q_update = "UPDATE jadwal SET 
                    kelas_id = '$kelas_id',
                    mapel_id = '$mapel_id',
                    guru_id = '$guru_id',
                    hari = '$hari',
                    jam_masuk = '$jam_masuk',
                    jam_keluar = '$jam_keluar'
                 WHERE id = '$id'";

    if (mysqli_query($conn, $q_update)) {
        $_SESSION['swal'] = ['title' => 'Berhasil', 'text' => 'Jadwal pelajaran berhasil diperbarui', 'type' => 'success'];
    } else {
        $_SESSION['swal'] = ['title' => 'Gagal', 'text' => 'Gagal mengupdate jadwal', 'type' => 'error'];
    }
    header("Location: index.php");
    exit;
}
?>