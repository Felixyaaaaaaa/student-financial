<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pageTitle = "Anggaran";
include __DIR__ . '/../../includes/header.php';

// Ambil data anggaran user
$id_mahasiswa = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM anggaran WHERE id_mahasiswa = ? ORDER BY tanggal_buat DESC");
$stmt->execute([$id_mahasiswa]);
$anggarans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash message
if ($msg = flash('success')) echo '<div class="alert alert-success">' . e($msg) . '</div>';
if ($msg = flash('error')) echo '<div class="alert alert-danger">' . e($msg) . '</div>';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Daftar Anggaran</h3>
    <a href="tambah.php" class="btn btn-primary d-flex align-items-center justify-content-center">
        <i class="fa fa-plus"></i>
        <span class="d-none d-sm-inline ms-2">Tambah Anggaran</span>
    </a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th style="min-width:50px">No</th>
                <th style="min-width:100px">Tipe</th>
                <th style="min-width:120px">Jumlah (Rp)</th>
                <th style="min-width:200px">Keterangan</th>
                <th style="min-width:150px">Tanggal Buat</th>
                <th style="min-width:100px" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($anggarans)): ?>
                <tr>
                    <td colspan="6" class="text-center">Belum ada anggaran</td>
                </tr>
            <?php else: ?>
                <?php foreach ($anggarans as $i => $a): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= e($a['tipe']) ?></td>
                    <td><?= number_format($a['jumlah'],0,',','.') ?></td>
                    <td class="text-wrap" style="white-space: normal; word-break: break-word;">
                        <?= e($a['keterangan']) ?>
                    </td>
                    <td class="text-wrap" style="white-space: normal; word-break: break-word;">
                        <?= date('d-m-Y', strtotime($a['tanggal_buat'])) ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="hapus.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus anggaran ini?')">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
