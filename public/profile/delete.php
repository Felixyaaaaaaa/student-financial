<?php
include '../../includes/config.php';
include '../../includes/functions.php';
require_login();

$id_mahasiswa = $_SESSION['user_id'];

// Hapus semua data terkait mahasiswa ini
try {
    $pdo->beginTransaction();

    // Hapus tabungan transaksi
    $stmt = $pdo->prepare("DELETE FROM tabungan_transaksi WHERE id_mahasiswa = ?");
    $stmt->execute([$id_mahasiswa]);

    // Hapus tabungan
    $stmt = $pdo->prepare("DELETE FROM tabungan WHERE id_mahasiswa = ?");
    $stmt->execute([$id_mahasiswa]);

    // Hapus transaksi
    $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id_mahasiswa = ?");
    $stmt->execute([$id_mahasiswa]);

    // Hapus anggaran
    $stmt = $pdo->prepare("DELETE FROM anggaran WHERE id_mahasiswa = ?");
    $stmt->execute([$id_mahasiswa]);

    // Terakhir, hapus akun mahasiswa
    $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = ?");
    $stmt->execute([$id_mahasiswa]);

    $pdo->commit();

    // Hapus session
    session_destroy();

    flash('success', 'Akun dan semua data terkait berhasil dihapus.');
    header("Location: ../../login.php");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    flash('error', 'Terjadi kesalahan saat menghapus akun.');
    header("Location: index.php");
    exit;
}
