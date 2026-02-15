<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();



$id_mahasiswa = $_SESSION['user_id'];

/**
 * Hitung saldo utama (pemasukan - pengeluaran)
 */
function get_saldo_utama(PDO $pdo, int $id_mahasiswa): float {
    $stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN jenis='pemasukan' THEN jumlah ELSE 0 END),0) AS pemasukan,
        COALESCE(SUM(CASE WHEN jenis='pengeluaran' THEN jumlah ELSE 0 END),0) AS pengeluaran
    FROM transaksi WHERE id_mahasiswa = ?");
    $stmt->execute([$id_mahasiswa]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float)($row['pemasukan'] - $row['pengeluaran']);
}

// Ambil saldo utama saat menampilkan form (untuk info user)
$saldo_utama_info = get_saldo_utama($pdo, $id_mahasiswa);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $initial = (float) ($_POST['initial'] ?? 0);
    $target_amount = $_POST['target_amount'] ? (float) $_POST['target_amount'] : null;
    $target_date = $_POST['target_date'] ?: null;
    $token = $_POST['_csrf'] ?? '';

    if (!csrf_check($token)) {
        flash('error','Token CSRF tidak valid.');
        redirect('tabungan/create.php');
    }
    if ($nama === '') {
        flash('error','Nama rekening wajib diisi.');
        redirect('tabungan/create.php');
    }
    if ($initial < 0) {
        flash('error','Initial deposit tidak boleh negatif.');
        redirect('tabungan/create.php');
    }

    try {
        // mulai transaksi DB untuk seluruh proses pembuatan rekening + initial deposit
        $pdo->beginTransaction();

        // Kunci baris transaksi user supaya perhitungan saldo konsisten (hindari race)
        // Catatan: SELECT id ... FOR UPDATE mengunci baris transaksi user sampai commit/rollback.
        $lockTrans = $pdo->prepare("SELECT id FROM transaksi WHERE id_mahasiswa = ? FOR UPDATE");
        $lockTrans->execute([$id_mahasiswa]);

        // hitung ulang saldo utama setelah lock
        $saldo_utama = get_saldo_utama($pdo, $id_mahasiswa);

        // jika initial > 0, pastikan saldo utama mencukupi
        if ($initial > 0 && $initial > $saldo_utama) {
            $pdo->rollBack();
            flash('error','Initial deposit melebihi saldo utama saat ini (Rp ' . number_format($saldo_utama,0,',','.') . ').');
            redirect('tabungan/create.php');
        }

        // Insert rekening (saldo awal 0)
        $stmt = $pdo->prepare("INSERT INTO tabungan (id_mahasiswa, nama, saldo, target_amount, target_date) VALUES (?, ?, 0, ?, ?)");
        $stmt->execute([$id_mahasiswa, $nama, $target_amount, $target_date]);
        $tabungan_id = (int)$pdo->lastInsertId();

        // Jika ada initial deposit, proses sebagai transfer atomik dari saldo utama ke tabungan
        if ($initial > 0) {
            // 1) insert tabungan_transaksi (deposit)
            $stmt1 = $pdo->prepare(
                "INSERT INTO tabungan_transaksi (tabungan_id, id_mahasiswa, tipe, jumlah, keterangan) 
                 VALUES (?, ?, 'deposit', ?, ?)"
            );
            $stmt1->execute([$tabungan_id, $id_mahasiswa, $initial, 'Initial deposit']);

            // 2) update tabungan saldo
            $stmt2 = $pdo->prepare(
                "UPDATE tabungan SET saldo = saldo + ?, updated_at = NOW() WHERE id = ? AND id_mahasiswa = ?"
            );
            $stmt2->execute([$initial, $tabungan_id, $id_mahasiswa]);

            // 3) insert transaksi utama sebagai pengeluaran (mengurangi saldo utama)
            $stmt3 = $pdo->prepare(
                "INSERT INTO transaksi (id_mahasiswa, jenis, jumlah, keterangan) VALUES (?, 'pengeluaran', ?, ?)"
            );
            $stmt3->execute([$id_mahasiswa, $initial, 'Transfer ke tabungan: ' . $nama]);
        }

        // commit semua perubahan
        $pdo->commit();

        flash('success','Rekening tabungan berhasil dibuat.' . ($initial > 0 ? ' Initial deposit Rp ' . number_format($initial,0,',','.') . ' diproses.' : ''));
        redirect('tabungan/index.php');

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        // jawab dengan pesan yang ramah — tambahkan $e->getMessage() untuk debugging (hati-hati di production)
        flash('error','Gagal membuat rekening: ' . $e->getMessage());
        redirect('tabungan/create.php');
    }
}
$pageTitle = "Buat Rekening Tabungan";
include __DIR__ . '/../../includes/header.php';
?>

<div class="card shadow-sm p-4">
  <h4>Buat Rekening Tabungan</h4>

  <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Nama Rekening</label>
      <input name="nama" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Initial Deposit (opsional)</label>
      <input name="initial" type="number" step="0.01" class="form-control" value="0">
      <div class="form-text">
        Jika diisi, dana akan dipindahkan dari saldo utama (membuat transaksi pengeluaran). 
        Saldo utama saat ini: <strong>Rp <?= number_format($saldo_utama_info,0,',','.') ?></strong>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Target (opsional)</label>
      <input name="target_amount" type="number" step="0.01" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Target Date (opsional)</label>
      <input name="target_date" type="date" class="form-control">
    </div>
    <button class="btn btn-primary">Simpan</button>
    <a href="index.php" class="btn btn-secondary">Batal</a>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
