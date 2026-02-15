<?php
include '../../includes/config.php';
include '../../includes/functions.php';
require_login();

$id_mahasiswa = $_SESSION['user_id'];

// Ambil data lama
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt->execute([$id_mahasiswa]);
$mhs = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];

    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $password_konfirmasi = $_POST['password_konfirmasi'] ?? '';

    $password_update = $mhs['kata_sandi']; // default tidak berubah

    // Jika user ingin ubah password
    if (!empty($password_lama) || !empty($password_baru) || !empty($password_konfirmasi)) {
        // 1. Cek password lama benar?
        if (!password_verify($password_lama, $mhs['kata_sandi'])) {
            flash('error', 'Password lama salah!');
            redirect('profile/edit.php');
            exit;
        }

        // 2. Cek konfirmasi password baru
        if ($password_baru !== $password_konfirmasi) {
            flash('error', 'Konfirmasi password tidak cocok!');
            redirect('profile/edit.php');
            exit;
        }

        // 3. Update password jika semua valid
        if (!empty($password_baru)) {
            $password_update = password_hash($password_baru, PASSWORD_DEFAULT);
        }
    }

    // Update data
    $stmt = $pdo->prepare("UPDATE mahasiswa SET nama=?, email=?, kata_sandi=?, updated_at=NOW() WHERE id_mahasiswa=?");
    $stmt->execute([$nama, $email, $password_update, $id_mahasiswa]);

    $_SESSION['user_name'] = $nama; // update session
    flash('success', 'Profil berhasil diperbarui.');
    redirect('profile/index.php');
    exit;
}

$pageTitle = "Edit Profil";
include '../../includes/header.php';
?>
<div class="container mt-4">
  <h3>Edit Profil</h3>
  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <form method="post">
        <div class="mb-3">
          <label>Nama</label>
          <input type="text" name="nama" class="form-control" value="<?= e($mhs['nama']) ?>" required>
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="<?= e($mhs['email']) ?>" required>
        </div>

        <hr>
        <h6>Ganti Password (Opsional)</h6>
        <div class="mb-3">
          <label>Password Lama</label>
          <input type="password" name="password_lama" class="form-control">
        </div>
        <div class="mb-3">
          <label>Password Baru</label>
          <input type="password" name="password_baru" class="form-control">
        </div>
        <div class="mb-3">
          <label>Konfirmasi Password Baru</label>
          <input type="password" name="password_konfirmasi" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
