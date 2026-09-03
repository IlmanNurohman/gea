<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') { die('Akses ditolak'); }

$user_id = $_SESSION['user_id'] ?? '';
$q_siswa = mysqli_query($conn, "SELECT id, kelas_id FROM siswa WHERE user_id = '$user_id'");
$d_siswa = mysqli_fetch_assoc($q_siswa);
$siswa_id = $d_siswa['id'] ?? 0;
$kelas_id = $d_siswa['kelas_id'] ?? 0;

$query_tugas = "SELECT tugas.*, mapel.nama_mapel, guru.nama_guru,
               pengumpulan_tugas.id as id_kumpul, pengumpulan_tugas.nilai, pengumpulan_tugas.catatan_guru
               FROM tugas 
               JOIN mapel ON tugas.mapel_id = mapel.id 
               JOIN guru ON tugas.guru_id = guru.id 
               LEFT JOIN pengumpulan_tugas ON tugas.id = pengumpulan_tugas.tugas_id AND pengumpulan_tugas.siswa_id = '$siswa_id'
               WHERE tugas.kelas_id = '$kelas_id' 
               ORDER BY tugas.deadline DESC";
$data_tugas = mysqli_query($conn, $query_tugas);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Tugas Saya - Siswa</title>
    <link rel="stylesheet" href="../../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../../assets/css/kaiadmin.min.css" />
</head>

<body>
    <div class="container mt-4">
        <h3>Daftar Tugas Saya</h3>

        <div class="row">
            <?php while ($t = mysqli_fetch_assoc($data_tugas)) : ?>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <strong><?= $t['nama_mapel'] ?> (<?= $t['nama_guru'] ?>)</strong>
                        <small class="text-danger">Deadline: <?= date('d-m-Y H:i', strtotime($t['deadline'])) ?></small>
                    </div>
                    <div class="card-body">
                        <h5><?= htmlspecialchars($t['judul']) ?></h5>
                        <p><?= nl2br(htmlspecialchars($t['deskripsi'])) ?></p>

                        <?php if ($t['file_tugas']) : ?>
                        <a href="../../../uploads/tugas/<?= $t['file_tugas'] ?>" target="_blank"
                            class="btn btn-outline-info btn-sm mb-3">Download Soal / Lampiran</a>
                        <?php endif; ?>

                        <hr>
                        <?php if ($t['id_kumpul']) : ?>
                        <div class="alert alert-success">
                            <strong>Sudah Dikumpulkan</strong><br>
                            Nilai: <strong><?= $t['nilai'] !== NULL ? $t['nilai'] : 'Belum Dinilai' ?></strong><br>
                            Catatan Guru: <em><?= htmlspecialchars($t['catatan_guru'] ?? '-') ?></em>
                        </div>
                        <?php else : ?>
                        <!-- Form Kerjakan & Upload Jawaban -->
                        <form action="kumpul_tugas.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="siswa_id" value="<?= $siswa_id ?>">

                            <div class="mb-2">
                                <label class="form-label">Upload File Jawaban (PDF/DOCX/JPG/ZIP)</label>
                                <input type="file" name="file_jawaban" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <input type="text" name="catatan_siswa" class="form-control"
                                    placeholder="Catatan singkat (Opsional)">
                            </div>
                            <button type="submit" class="btn btn-success w-100">Kirim Jawaban</button>
                        </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>

</html>