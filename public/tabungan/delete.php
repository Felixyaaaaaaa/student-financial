<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$id_mahasiswa = $_SESSION['user_id'];
$tabungan_id = (int) ($_GET['id'] ?? 0);
if ($tabungan_id <= 0) { flash('error','ID tidak ditemukan.'); redirect('tabungan/index.php'); }

// ambil rekening
$stmt = $pdo->prepare("SELECT * FROM tabungan WHERE id = ? AND id_mahasiswa = ?");
$stmt->execute([$tabungan_id, $id_mahasiswa]);
$tab = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tab) { flash('error','Rekening tidak ditemukan.'); redirect('tabungan/index.php'); }

// hanya izinkan hapus bila saldo = 0
if ((float)$tab['saldo'] > 0) {
    flash('error','Rekening tidak bisa dihapus karena saldo masih ada (Rp ' . number_format($tab['saldo'],0,',','.') . '). Silakan withdraw dulu atau pindahkan saldonya.');
    redirect('tabungan/index.php');
}

// hapus rekening (riwayat tabungan juga akan dihapus karena FK ON DELETE CASCADE)
$stmtDel = $pdo->prepare("DELETE FROM tabungan WHERE id = ? AND id_mahasiswa = ?");
$stmtDel->execute([$tabungan_id, $id_mahasiswa]);

flash('success','Rekening tabungan berhasil dihapus.');
redirect('tabungan/index.php');
