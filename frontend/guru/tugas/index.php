<?php
session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') { die('Akses ditolak'); }

$user_id = $_SESSION['user_id'] ?? '';
$q_guru  = mysqli_query($conn, "SELECT id FROM guru WHERE user_id = '$user_id'");
$d_guru  = mysqli_fetch_assoc($q_guru);
$guru_id = $d_guru['id'] ?? 0;

// Fetch Data Master
$kelas_list = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$mapel_list = mysqli_query($conn, "SELECT * FROM mapel ORDER BY nama_mapel ASC");

// Fetch List Tugas buatan guru ini
$query_tugas = "SELECT tugas.*, kelas.nama_kelas, mapel.nama_mapel, 
               (SELECT COUNT(*) FROM pengumpulan_tugas WHERE tugas_id = tugas.id) as total_kumpul 
               FROM tugas 
               JOIN kelas ON tugas.kelas_id = kelas.id 
               JOIN mapel ON tugas.mapel_id = mapel.id 
               WHERE tugas.guru_id = '$guru_id' ORDER BY tugas.id DESC";
$data_tugas = mysqli_query($conn, $query_tugas);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Kelola Tugas - Guru</title>
    <link rel="stylesheet" href="../../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../../assets/css/kaiadmin.min.css" />
</head>

<body>
    <div class="container mt-4">
        <h3>Kelola Tugas Siswa</h3>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahTugas">+ Tambah
            Tugas</button>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Judul Tugas</th>
                            <th>Deadline</th>
                            <th>File</th>
                            <th>Dikumpulkan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($t = mysqli_fetch_assoc($data_tugas)) : ?>
                        <tr>
                            <td><?= $t['nama_kelas'] ?></td>
                            <td><?= $t['nama_mapel'] ?></td>
                            <td><strong><?= htmlspecialchars($t['judul']) ?></strong></td>
                            <td><?= date('d-m-Y H:i', strtotime($t['deadline'])) ?></td>
                            <td>
                                <?php if ($t['file_tugas']) : ?>
                                <a href="../../../uploads/tugas/<?= $t['file_tugas'] ?>" target="_blank"
                                    class="btn btn-info btn-sm">Download</a>
                                <?php else : ?> - <?php endif; ?>
                            </td>
                            <td><span class="badge bg-success"><?= $t['total_kumpul'] ?> Siswa</span></td>
                            <td>
                                <a href="detail_pengumpulan.php?tugas_id=<?= $t['id'] ?>"
                                    class="btn btn-warning btn-sm">Periksa & Nilai</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Tugas -->
    <div class="modal fade" id="modalTambahTugas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="proses_tugas.php?aksi=tambah" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="guru_id" value="<?= $guru_id ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Tugas Baru</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Pilih Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php while ($k = mysqli_fetch_assoc($kelas_list)) : ?>
                                <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Pilih Mapel</label>
                            <select name="mapel_id" class="form-select" required>
                                <option value="">-- Pilih Mapel --</option>
                                <?php while ($m = mysqli_fetch_assoc($mapel_list)) : ?>
                                <option value="<?= $m['id'] ?>"><?= $m['nama_mapel'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Judul Tugas</label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi / Perintah</label>
                            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Deadline Tanggal & Jam</label>
                            <input type="datetime-local" name="deadline" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Upload File Tugas (Opsional)</label>
                            <input type="file" name="file_tugas" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan Tugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../../assets/js/core/bootstrap.min.js"></script>
</body>

</html>