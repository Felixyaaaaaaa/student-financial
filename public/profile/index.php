<?php
include '../../includes/config.php';
include '../../includes/functions.php';
require_login();

$id_mahasiswa = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt->execute([$id_mahasiswa]);
$mhs = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = "Profil Saya";
include '../../includes/header.php';
if ($msg = flash('success')) echo '<div class="alert alert-success">' . e($msg) . '</div>';
if ($msg = flash('error')) echo '<div class="alert alert-danger">' . e($msg) . '</div>';
?>
<div class="container mt-4">
    <h3>Profil Saya</h3>
    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <p><strong>Nama:</strong> <?= e($mhs['nama']) ?></p>
            <p><strong>Email:</strong> <?= e($mhs['email']) ?></p>
            <p><strong>Dibuat pada:</strong> <?= date('d-m-Y H:i', strtotime($mhs['created_at'])) ?></p>
            <a href="edit.php" class="btn btn-primary">Edit Profil</a>
            <a href="delete.php" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus akun beserta semua data?')">
            Hapus Akun
            </a>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>