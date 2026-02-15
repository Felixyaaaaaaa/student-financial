<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$id = $_GET['id'] ?? null;
$id_mahasiswa = $_SESSION['user_id'];

// Ambil data transaksi
$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = ? AND id_mahasiswa = ?");
$stmt->execute([$id, $id_mahasiswa]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    flash('error', 'Transaksi tidak ditemukan.');
    redirect('transaksi/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = $_POST['jenis'] ?? '';
    $jumlah = (float) ($_POST['jumlah'] ?? 0);
    $keterangan = $_POST['keterangan'] ?? '';
    $token = $_POST['_csrf'] ?? '';

    if (!csrf_check($token)) {
        flash('error', 'Token CSRF tidak valid.');
        redirect("transaksi/edit.php?id=$id");
    }

    if ($jenis === '' || $jumlah <= 0) {
        flash('error', 'Jenis dan jumlah wajib diisi.');
        redirect("transaksi/edit.php?id=$id");
    }

    // Hitung saldo utama tanpa transaksi yang sedang diedit
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN jumlah ELSE 0 END),0) -
            COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN jumlah ELSE 0 END),0)
        AS saldo
        FROM transaksi
        WHERE id_mahasiswa = ? AND id != ?
    ");
    $stmt->execute([$id_mahasiswa, $id]);
    $saldo = (float) $stmt->fetchColumn();

    // Validasi pengeluaran
    if ($jenis === 'Pengeluaran' && $jumlah > $saldo) {
        flash('error', 'Pengeluaran melebihi saldo utama. Saldo saat ini: Rp ' . number_format($saldo, 0, ',', '.'));
        redirect("transaksi/edit.php?id=$id");
    }

    // Update transaksi
    $stmt = $pdo->prepare("UPDATE transaksi SET jenis = ?, jumlah = ?, keterangan = ? WHERE id = ? AND id_mahasiswa = ?");
    $stmt->execute([$jenis, $jumlah, $keterangan, $id, $id_mahasiswa]);

    flash('success', 'Transaksi berhasil diperbarui.');
    redirect('transaksi/index.php');
}

$pageTitle = "Edit Transaksi";
include __DIR__ . '/../../includes/header.php';
?>

<div class="card shadow-sm p-4">
    <h4 class="mb-3">Edit Transaksi</h4>

    <!-- Tampilkan flash message -->
    <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php elseif ($msg = flash('success')): ?>
        <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <div class="mb-3">
            <label class="form-label">Jenis</label>
            <select name="jenis" class="form-select" required>
                <option value="">Pilih jenis</option>
                <option value="Pemasukan" <?= $transaksi['jenis'] === 'Pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                <option value="Pengeluaran" <?= $transaksi['jenis'] === 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah (Rp)</label>
            <input type="number" name="jumlah" class="form-control" value="<?= e($transaksi['jumlah']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control"><?= e($transaksi['keterangan']) ?></textarea>
        </div>
        <button class="btn btn-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
