<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();



$id_mahasiswa = $_SESSION['user_id'];
$tabungan_id = (int) ($_GET['id'] ?? 0);
if ($tabungan_id <= 0) { flash('error','ID tidak ditemukan.'); redirect('index.php'); }

// Ambil rekening
$stmt = $pdo->prepare("SELECT * FROM tabungan WHERE id = ? AND id_mahasiswa = ?");
$stmt->execute([$tabungan_id, $id_mahasiswa]);
$tab = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tab) { flash('error','Rekening tidak ditemukan.'); redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $target_amount = $_POST['target_amount'] ? (float) $_POST['target_amount'] : null;
    $target_date = $_POST['target_date'] ?: null;
    $token = $_POST['_csrf'] ?? '';

    if (!csrf_check($token)) {
        flash('error','Token CSRF tidak valid.');
        redirect("edit.php?id={$tabungan_id}");
    }
    if ($nama === '') {
        flash('error','Nama rekening wajib diisi.');
        redirect("edit.php?id={$tabungan_id}");
    }

    $stmt = $pdo->prepare("UPDATE tabungan SET nama = ?, target_amount = ?, target_date = ?, updated_at = NOW() WHERE id = ? AND id_mahasiswa = ?");
    $stmt->execute([$nama, $target_amount, $target_date, $tabungan_id, $id_mahasiswa]);

    flash('success','Rekening berhasil diperbarui.');
    redirect('tabungan/index.php');
}

$pageTitle = "Edit Rekening Tabungan";
include __DIR__ . '/../../includes/header.php';
?>

<div class="card shadow-sm p-4">
  <h4>Edit Rekening Tabungan</h4>

  <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

    <div class="mb-3">
      <label class="form-label">Nama Rekening</label>
      <input name="nama" class="form-control" required value="<?= e($tab['nama']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Target (opsional)</label>
      <input name="target_amount" type="number" step="0.01" class="form-control" value="<?= $tab['target_amount'] ? e($tab['target_amount']) : '' ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Target Date (opsional)</label>
      <input name="target_date" type="date" class="form-control" value="<?= $tab['target_date'] ? e(date('Y-m-d', strtotime($tab['target_date']))) : '' ?>">
    </div>

    <div class="mb-3">
      <small class="text-muted">Saldo saat ini: <strong>Rp <?= number_format($tab['saldo'],0,',','.') ?></strong>. Untuk menambah/mengurangi saldo, gunakan fitur Deposit / Withdraw pada halaman detail.</small>
    </div>

    <button class="btn btn-primary">Simpan Perubahan</button>
    <a href="index.php" class="btn btn-secondary">Batal</a>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
