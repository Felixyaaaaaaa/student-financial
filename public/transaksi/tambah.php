<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = $_POST['jenis'] ?? '';
    $jumlah = (int) ($_POST['jumlah'] ?? 0);
    $keterangan = $_POST['keterangan'] ?? '';
    $token = $_POST['_csrf'] ?? '';

    if (!csrf_check($token)) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('tambah.php');
    }

    if ($jenis === '' || $jumlah <= 0) {
        flash('error', 'Jenis dan jumlah wajib diisi.');
        redirect('tambah.php');
    }

    // Hitung saldo total user
    $stmt = $pdo->prepare("SELECT 
            COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN jumlah ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN jumlah ELSE 0 END), 0) 
        AS saldo 
        FROM transaksi 
        WHERE id_mahasiswa = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $saldo = (int) $stmt->fetchColumn();

    // Validasi jika pengeluaran > saldo
    if ($jenis === 'Pengeluaran' && $jumlah > $saldo) {
        flash('error', 'Pengeluaran melebihi saldo yang tersedia (Saldo: Rp ' . number_format($saldo, 0, ',', '.') . ').');
        redirect('transaksi/tambah.php');
    }

    // Simpan transaksi
    $stmt = $pdo->prepare("INSERT INTO transaksi (id_mahasiswa, jenis, jumlah, keterangan) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $jenis, $jumlah, $keterangan]);

    flash('success', 'Transaksi berhasil ditambahkan.');
    redirect('transaksi/index.php');
}

$pageTitle = "Tambah Transaksi";
include __DIR__ . '/../../includes/header.php';
?>

<div class="card shadow-sm p-4">
    <h4 class="mb-3">Tambah Transaksi</h4>

    <!-- Tampilkan pesan flash -->
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
                <option value="Pemasukan">Pemasukan</option>
                <option value="Pengeluaran">Pengeluaran</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah (Rp)</label>
            <input type="number" name="jumlah" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control"></textarea>
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>