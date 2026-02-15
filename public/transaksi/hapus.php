<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$id = $_GET['id'] ?? null;
$id_mahasiswa = $_SESSION['user_id'];

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id = ? AND id_mahasiswa = ?");
    $stmt->execute([$id, $id_mahasiswa]);
    flash('success', 'Transaksi berhasil dihapus.');
}

redirect('transaksi/index.php');
