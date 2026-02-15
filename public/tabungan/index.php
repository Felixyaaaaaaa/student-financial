<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pageTitle = "Tabungan";
include __DIR__ . '/../../includes/header.php';

$id_mahasiswa = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM tabungan WHERE id_mahasiswa = ? ORDER BY created_at DESC");
$stmt->execute([$id_mahasiswa]);
$tabungans = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($msg = flash('success')) echo '<div class="alert alert-success">' . e($msg) . '</div>';
if ($msg = flash('error')) echo '<div class="alert alert-danger">' . e($msg) . '</div>';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Tabungan</h3>
  <a href="create.php" class="btn btn-primary d-flex align-items-center justify-content-center">
      <i class="fa fa-plus"></i>
      <span class="d-none d-sm-inline ms-2">Buat Rekening</span>
  </a>
</div>

<div class="row g-3">
<?php if (empty($tabungans)): ?>
  <div class="col-12">
    <div class="alert alert-info">Belum ada rekening tabungan. Buat rekening untuk mulai menabung.</div>
  </div>
<?php else: ?>
  <?php foreach($tabungans as $t): 
    $progress = ($t['target_amount'] && $t['target_amount'] > 0) ? min(100, ($t['saldo'] / $t['target_amount']) * 100) : 0;
  ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= e($t['nama']) ?></h5>
          <p class="mb-1">Saldo: <strong>Rp <?= number_format($t['saldo'],0,',','.') ?></strong></p>
          
          <?php if($t['target_amount']): ?>
            <p class="mb-2 small text-muted">
              Target: Rp <?= number_format($t['target_amount'],0,',','.') ?> (<?= round($progress) ?>%)
            </p>
            <div class="progress mb-2">
              <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%">
                <?= round($progress) ?>%
              </div>
            </div>
          <?php endif; ?>

          <div class="mt-auto d-flex justify-content-start gap-2">
            <a href="show.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">
              <i class="fa fa-eye"></i><span> Detail</span>
            </a>
            <a href="edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-warning">
              <i class="fa fa-edit"></i><span> Edit</span>
            </a>
            <a href="delete.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus rekening?')">
              <i class="fa fa-trash"></i><span> Hapus</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
