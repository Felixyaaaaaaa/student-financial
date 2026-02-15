<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$id = $_GET['id'] ?? null;
if (!$id) {
    flash('error', 'ID anggaran tidak ditemukan.');
    redirect('index.php');
}

$stmt = $pdo->prepare("DELETE FROM anggaran WHERE id = ? AND id_mahasiswa = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

flash('success', 'Anggaran berhasil dihapus.');
redirect('anggaran/index.php');
