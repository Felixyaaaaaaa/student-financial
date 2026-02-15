<?php
// public/tabungan/transaction.php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

/**
 * Hitung saldo utama (pemasukan - pengeluaran)
 */
function get_saldo_utama(PDO $pdo, int $id_mahasiswa): float {
    $stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN jenis='Pemasukan' THEN jumlah ELSE 0 END),0) AS pemasukan,
        COALESCE(SUM(CASE WHEN jenis='Pengeluaran' THEN jumlah ELSE 0 END),0) AS pengeluaran
    FROM transaksi WHERE id_mahasiswa = ?");
    $stmt->execute([$id_mahasiswa]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float)($row['pemasukan'] - $row['pengeluaran']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('tabungan/index.php');
}

$id_mahasiswa = $_SESSION['user_id'];
$tabungan_id = (int) ($_POST['tabungan_id'] ?? 0);
$action = $_POST['action'] ?? '';
$jumlah = (float) ($_POST['jumlah'] ?? 0);
$keterangan = trim($_POST['keterangan'] ?? '');
$token = $_POST['_csrf'] ?? '';

$back = "tabungan/show.php?id=" . urlencode($tabungan_id);

// validasi dasar
if (!csrf_check($token)) {
    flash('error', 'Token CSRF tidak valid.');
    redirect($back);
}
if ($tabungan_id <= 0 || $jumlah <= 0) {
    flash('error', 'Data tidak valid (ID atau jumlah).');
    redirect($back);
}
if (!in_array($action, ['deposit','withdraw'])) {
    flash('error', 'Aksi tidak valid.');
    redirect($back);
}

// ambil rekening (cek kepemilikan)
$stmt = $pdo->prepare("SELECT * FROM tabungan WHERE id = ? AND id_mahasiswa = ?");
$stmt->execute([$tabungan_id, $id_mahasiswa]);
$tab = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tab) {
    flash('error', 'Rekening tidak ditemukan.');
    redirect('tabungan/index.php');
}

try {
    // mulai transaksi DB
    $pdo->beginTransaction();

    // kunci baris tabungan untuk menghindari race
    $lockTabungan = $pdo->prepare("SELECT saldo FROM tabungan WHERE id = ? AND id_mahasiswa = ? FOR UPDATE");
    $lockTabungan->execute([$tabungan_id, $id_mahasiswa]);
    $lockedTab = $lockTabungan->fetch(PDO::FETCH_ASSOC);
    if (!$lockedTab) {
        throw new Exception('Rekening tidak tersedia (mungkin sudah dihapus).');
    }

    // kunci baris transaksi user agar pembacaan saldo utama konsisten
    // Note: ini mengunci semua baris transaksi user — aman tapi heavy di dataset besar.
    // Jika beban jadi masalah, bisa dipertimbangkan strategi lain (mis. summary table atau optimistic locking).
    $lockTrans = $pdo->prepare("SELECT id FROM transaksi WHERE id_mahasiswa = ? FOR UPDATE");
    $lockTrans->execute([$id_mahasiswa]);

    // hitung saldo utama setelah lock
    $saldo_utama = get_saldo_utama($pdo, $id_mahasiswa);
    $current_saldo_tabungan = (float)$lockedTab['saldo'];

    if ($action === 'deposit') {
        // deposit = pindah dari saldo utama -> tabungan (mengurangi saldo utama)
        if ($jumlah > $saldo_utama) {
            $pdo->rollBack();
            flash('error', 'Saldo utama tidak mencukupi. Saldo utama: Rp ' . number_format($saldo_utama,0,',','.'));
            redirect($back);
        }

        // 1) insert tabungan_transaksi (deposit)
        $stmt1 = $pdo->prepare("INSERT INTO tabungan_transaksi (tabungan_id, id_mahasiswa, tipe, jumlah, keterangan) VALUES (?, ?, 'deposit', ?, ?)");
        $stmt1->execute([$tabungan_id, $id_mahasiswa, $jumlah, $keterangan]);

        // 2) update tabungan saldo
        $stmt2 = $pdo->prepare("UPDATE tabungan SET saldo = saldo + ?, updated_at = NOW() WHERE id = ? AND id_mahasiswa = ?");
        $stmt2->execute([$jumlah, $tabungan_id, $id_mahasiswa]);

        // 3) insert transaksi utama sebagai pengeluaran (mengurangi saldo utama)
        $stmt3 = $pdo->prepare("INSERT INTO transaksi (id_mahasiswa, jenis, jumlah, keterangan) VALUES (?, 'pengeluaran', ?, ?)");
        $stmt3->execute([$id_mahasiswa, $jumlah, 'Transfer ke tabungan: ' . $tab['nama'] . ($keterangan ? ' - ' . $keterangan : '')]);

        $pdo->commit();
        flash('success', 'Deposit ke tabungan berhasil. Rp ' . number_format($jumlah,0,',','.'));
        redirect($back);

    } else { // withdraw
        // withdraw = ambil dari tabungan -> saldo utama (menambah saldo utama)
        if ($current_saldo_tabungan < $jumlah) {
            $pdo->rollBack();
            flash('error', 'Saldo tabungan tidak mencukupi. Saldo tabungan: Rp ' . number_format($current_saldo_tabungan,0,',','.'));
            redirect($back);
        }

        // 1) insert tabungan_transaksi (withdraw)
        $stmt1 = $pdo->prepare("INSERT INTO tabungan_transaksi (tabungan_id, id_mahasiswa, tipe, jumlah, keterangan) VALUES (?, ?, 'withdraw', ?, ?)");
        $stmt1->execute([$tabungan_id, $id_mahasiswa, $jumlah, $keterangan]);

        // 2) update tabungan saldo
        $stmt2 = $pdo->prepare("UPDATE tabungan SET saldo = saldo - ?, updated_at = NOW() WHERE id = ? AND id_mahasiswa = ?");
        $stmt2->execute([$jumlah, $tabungan_id, $id_mahasiswa]);

        // 3) insert transaksi utama sebagai pemasukan (menambah saldo utama)
        $stmt3 = $pdo->prepare("INSERT INTO transaksi (id_mahasiswa, jenis, jumlah, keterangan) VALUES (?, 'pemasukan', ?, ?)");
        $stmt3->execute([$id_mahasiswa, $jumlah, 'Tarik dari tabungan: ' . $tab['nama'] . ($keterangan ? ' - ' . $keterangan : '')]);

        $pdo->commit();
        flash('success', 'Withdraw dari tabungan berhasil. Rp ' . number_format($jumlah,0,',','.'));
        redirect($back);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // menampilkan pesan yang ramah; tambahkan $e->getMessage() untuk debugging jika perlu
    flash('error', 'Gagal memproses transaksi: ' . $e->getMessage());
    redirect($back);
}
