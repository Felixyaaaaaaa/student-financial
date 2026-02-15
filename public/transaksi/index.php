<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pageTitle = "Transaksi";
include __DIR__ . '/../../includes/header.php';

// Ambil data transaksi user
$id_mahasiswa = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id_mahasiswa = ? ORDER BY tanggal DESC");
$stmt->execute([$id_mahasiswa]);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash message
if ($msg = flash('success')) {
    echo '<div class="alert alert-success">' . e($msg) . '</div>';
}
if ($msg = flash('error')) {
    echo '<div class="alert alert-danger">' . e($msg) . '</div>';
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Daftar Transaksi</h3>
    <a href="tambah.php" class="btn btn-primary d-flex align-items-center justify-content-center">
        <i class="fa fa-plus"></i>
        <span class="d-none d-sm-inline ms-2">Tambah Transaksi</span>
    </a>
</div>



<div class="table-responsive">      
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th style="min-width:50px">No</th>
                <th style="min-width:80px">Jenis</th>
                <th style="min-width:120px">Jumlah (Rp)</th>
                <th style="min-width:180px">Keterangan</th>
                <th style="min-width:150px">Tanggal</th>
                <th style="min-width:100px" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transaksi)): ?>
                <tr>
                    <td colspan="6" class="text-center">Belum ada transaksi</td>
                </tr>
            <?php else: ?>
                <?php foreach ($transaksi as $i => $t): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= e($t['jenis']) ?></td>
                    <td><?= number_format($t['jumlah'],0,',','.') ?></td>
                    <td class="text-wrap" style="white-space: normal; word-break: break-word;">
                        <?= e($t['keterangan']) ?>
                    </td>
                    <td class="text-wrap" style="white-space: normal; word-break: break-word;">
                        <?= date('d-m-Y H:i', strtotime($t['tanggal'])) ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="hapus.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus transaksi ini?')">
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