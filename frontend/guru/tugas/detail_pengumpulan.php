<?php
session_start();
include '../../../backend/koneksi.php';

$tugas_id = $_GET['tugas_id'] ?? 0;
$q_tugas  = mysqli_query($conn, "SELECT tugas.*, mapel.nama_mapel, kelas.nama_kelas FROM tugas JOIN mapel ON tugas.mapel_id=mapel.id JOIN kelas ON tugas.kelas_id=kelas.id WHERE tugas.id='$tugas_id'");
$d_tugas  = mysqli_fetch_assoc($q_tugas);

$q_pengumpulan = mysqli_query($conn, "SELECT pengumpulan_tugas.*, siswa.nama_lengkap, siswa.nisn 
                                      FROM pengumpulan_tugas 
                                      JOIN siswa ON pengumpulan_tugas.siswa_id = siswa.id 
                                      WHERE pengumpulan_tugas.tugas_id = '$tugas_id' 
                                      ORDER BY pengumpulan_tugas.waktu_kumpul ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Penilaian Tugas</title>
    <link rel="stylesheet" href="../../../assets/css/bootstrap.min.css" />
</head>

<body>
    <div class="container mt-4">
        <a href="index.php" class="btn btn-secondary btn-sm mb-3">← Kembali</a>
        <h4>Pengumpulan Tugas: <?= htmlspecialchars($d_tugas['judul']) ?> (<?= $d_tugas['nama_kelas'] ?>)</h4>

        <div class="card my-3">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Waktu Kumpul</th>
                            <th>File Jawaban</th>
                            <th>Nilai</th>
                            <th>Aksi / Beri Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($q_pengumpulan)) : ?>
                        <tr>
                            <td><?= $p['nisn'] ?></td>
                            <td><?= $p['nama_lengkap'] ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($p['waktu_kumpul'])) ?></td>
                            <td><a href="../../../uploads/jawaban/<?= $p['file_jawaban'] ?>" target="_blank"
                                    class="btn btn-info btn-sm">Lihat File</a></td>
                            <td>
                                <strong><?= $p['nilai'] !== NULL ? $p['nilai'] : '<span class="text-danger">Belum Dinilai</span>' ?></strong>
                            </td>
                            <td>
                                <form action="proses_tugas.php?aksi=nilai" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="id_kumpul" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="tugas_id" value="<?= $tugas_id ?>">
                                    <input type="number" name="nilai" value="<?= $p['nilai'] ?>"
                                        class="form-control form-control-sm" style="width:70px;" placeholder="0-100"
                                        required>
                                    <input type="text" name="catatan_guru"
                                        value="<?= htmlspecialchars($p['catatan_guru'] ?? '') ?>"
                                        class="form-control form-control-sm" placeholder="Catatan">
                                    <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>