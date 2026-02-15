<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pageTitle = "Detail Tabungan";
include __DIR__ . '/../../includes/header.php';

$id_mahasiswa = $_SESSION['user_id'];
$tabungan_id = $_GET['id'] ?? null;
if (!$tabungan_id) {
  flash('error', 'ID tidak ditemukan.');
  redirect('tabungan/index.php');
}

// ambil rekening
$stmt = $pdo->prepare("SELECT * FROM tabungan WHERE id = ? AND id_mahasiswa = ?");
$stmt->execute([$tabungan_id, $id_mahasiswa]);
$tab = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tab) {
  flash('error', 'Rekening tidak ditemukan.');
  redirect('tabungan/index.php');
}

// ambil riwayat tabungan
$stmt2 = $pdo->prepare("SELECT * FROM tabungan_transaksi WHERE tabungan_id = ? ORDER BY tanggal DESC");
$stmt2->execute([$tabungan_id]);
$riwayat = $stmt2->fetchAll(PDO::FETCH_ASSOC);

if ($msg = flash('error')) echo '<div class="alert alert-danger">' . e($msg) . '</div>';
if ($msg = flash('success')) echo '<div class="alert alert-success">' . e($msg) . '</div>';
?>

<div class="row">
  <div class="col-md-6">
    <div class="card shadow-sm p-3 mb-3">
      <h4><?= e($tab['nama']) ?></h4>
      <p>Saldo: <strong>Rp <?= number_format($tab['saldo'], 0, ',', '.') ?></strong></p>
      <?php if ($tab['target_amount']):
        $progress = min(100, ($tab['saldo'] / $tab['target_amount']) * 100);
      ?>
        <p>Target: Rp <?= number_format($tab['target_amount'], 0, ',', '.') ?> (<?= round($progress) ?>%)</p>
        <div class="progress mb-3">
          <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%"><?= round($progress) ?>%</div>
        </div>
      <?php endif; ?>

      <hr>
      <h6>Operasi</h6>
      <form method="post" action="transaction.php" class="mb-3">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="tabungan_id" value="<?= e($tab['id']) ?>">
        <div class="mb-2">
          <label class="form-label">Tipe</label>
          <select name="action" class="form-select">
            <option value="deposit">Deposit (dari saldo utama)</option>
            <option value="withdraw">Withdraw (ke saldo utama)</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Jumlah</label>
          <input name="jumlah" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="mb-4">
          <label class="form-label">Keterangan</label>
          <input name="keterangan" class="form-control">
        </div>
        <button class="btn btn-primary">Proses</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card shadow-sm p-3">
      <h5>Riwayat Transaksi Tabungan</h5>
      <div class="table-responsive">
      <table class="table table-sm">
        <thead class="table-light"> 
          <tr>
            <th style="min-width:30px">No</th>
            <th style="min-width:100px">Tanggal</th>
            <th style="min-width:90px">Tipe</th>
            <th style="min-width:180px">Keterangan</th>
            <th style="min-width:120px">Jumlah</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($riwayat)): ?>
            <tr>
              <td colspan="5" class="text-center">Belum ada riwayat</td>
            </tr>
            <?php else: foreach ($riwayat as $i => $r): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= date('d-m-Y H:i', strtotime($r['tanggal'])) ?></td>
                <td><?= ucfirst($r['tipe']) ?></td>
                <td><?= e($r['keterangan']) ?></td>
                <td>Rp <?= number_format($r['jumlah'], 0, ',', '.') ?></td>
              </tr>
          <?php endforeach;
          endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>