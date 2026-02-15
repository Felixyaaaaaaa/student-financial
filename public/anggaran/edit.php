<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();



$id = $_GET['id'] ?? null;
if (!$id) {
    flash('error', 'ID anggaran tidak ditemukan.');
    redirect('index.php');
}

// Ambil data anggaran
$stmt = $pdo->prepare("SELECT * FROM anggaran WHERE id = ? AND id_mahasiswa = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$anggaran = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anggaran) {
    flash('error', 'Anggaran tidak ditemukan.');
    redirect('index.php');
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipe = $_POST['tipe'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $token = $_POST['_csrf'] ?? '';

    if (!csrf_check($token)) {
        flash('error', 'Token CSRF tidak valid.');
        redirect("edit.php?id=$id");
    }

    if ($tipe === '' || $jumlah === '') {
        flash('error', 'Tipe dan jumlah wajib diisi.');
        redirect("edit.php?id=$id");
    }

    $stmt = $pdo->prepare("UPDATE anggaran SET tipe = ?, jumlah = ?, keterangan = ? WHERE id = ? AND id_mahasiswa = ?");
    $stmt->execute([$tipe, $jumlah, $keterangan, $id, $_SESSION['user_id']]);
    flash('success', 'Anggaran berhasil diperbarui.');
    redirect('anggaran/index.php');
}
$pageTitle = "Edit Anggaran";
include __DIR__ . '/../../includes/header.php';
?>

<div class="card shadow-sm p-4">
    <h4 class="mb-3">Edit Anggaran</h4>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <div class="mb-3">
            <label class="form-label">Tipe Anggaran</label>
            <select name="tipe" class="form-select" required>
                <option value="Harian" <?= $anggaran['tipe']=='Harian'?'selected':'' ?>>Harian</option>
                <option value="Mingguan" <?= $anggaran['tipe']=='Mingguan'?'selected':'' ?>>Mingguan</option>
                <option value="Bulanan" <?= $anggaran['tipe']=='Bulanan'?'selected':'' ?>>Bulanan</option>
                <option value="Tahunan" <?= $anggaran['tipe']=='Tahunan'?'selected':'' ?>>Tahunan</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah (Rp)</label>
            <input type="number" name="jumlah" class="form-control" value="<?= e($anggaran['jumlah']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control"><?= e($anggaran['keterangan']) ?></textarea>
        </div>
        <button class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
