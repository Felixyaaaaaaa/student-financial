<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();



$id_mahasiswa = $_SESSION['user_id'];

// Ambil tipe anggaran yang sudah ada agar tidak bisa dipilih lagi
$stmt = $pdo->prepare("SELECT tipe FROM anggaran WHERE id_mahasiswa = ?");
$stmt->execute([$id_mahasiswa]);
$tipe_existing = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipe = $_POST['tipe'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $token = $_POST['_csrf'] ?? '';

    if (!csrf_check($token)) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('tambah.php');
    }

    if ($tipe === '' || $jumlah === '') {
        flash('error', 'Tipe dan jumlah wajib diisi.');
        redirect('tambah.php');
    }

    // Cek apakah anggaran dengan tipe ini sudah ada untuk user
    if (in_array($tipe, $tipe_existing)) {
        flash('error', "Anggaran tipe $tipe sudah dibuat. Hanya bisa 1 per tipe.");
        redirect('tambah.php');
    }

    // Insert anggaran baru
    $stmt = $pdo->prepare("INSERT INTO anggaran (id_mahasiswa, tipe, jumlah, keterangan) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_mahasiswa, $tipe, $jumlah, $keterangan]);
    flash('success', 'Anggaran berhasil ditambahkan.');
    redirect('anggaran/index.php');
}

$pageTitle = "Tambah Anggaran";
include __DIR__ . '/../../includes/header.php';
?>

<div class="card shadow-sm p-4">
    <h4 class="mb-3">Tambah Anggaran</h4>

    <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

        <div class="mb-3">
            <label class="form-label">Tipe Anggaran</label>
            <select name="tipe" class="form-select" required>
                <option value="">Pilih tipe</option>
                <option value="Harian" <?= in_array('Harian', $tipe_existing) ? 'disabled' : '' ?>>Harian</option>
                <option value="Mingguan" <?= in_array('Mingguan', $tipe_existing) ? 'disabled' : '' ?>>Mingguan</option>
                <option value="Bulanan" <?= in_array('Bulanan', $tipe_existing) ? 'disabled' : '' ?>>Bulanan</option>
                <option value="Tahunan" <?= in_array('Tahunan', $tipe_existing) ? 'disabled' : '' ?>>Tahunan</option>
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
