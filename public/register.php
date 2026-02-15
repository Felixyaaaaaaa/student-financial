<?php
// public/register.php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $token = $_POST['_csrf'] ?? '';

    if (!csrf_check($token)) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('register.php');
    }

    // Basic validation
    if ($nama === '' || $email === '' || $password === '') {
        flash('error', 'Semua field wajib diisi.');
        redirect('register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Format email tidak valid.');
        redirect('register.php');
    }

    if ($password !== $password2) {
        flash('error', 'Konfirmasi password tidak cocok.');
        redirect('register.php');
    }

    // Check existing user
    if (find_user_by_email($pdo, $email)) {
        flash('error', 'Email sudah terdaftar. Silakan login atau gunakan email lain.');
        redirect('register.php');
    }

    // Create user
    $userId = create_user($pdo, $nama, $email, $password);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $nama;
    redirect('index.php');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - Keuangan Mahasiswa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #6a11cb, #2575fc);
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .card-register {
      border-radius: 1rem;
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
      overflow: hidden;
      background: #fff;
      padding: 2rem;
      width: 100%;
      max-width: 450px;
      transition: transform 0.3s;
    }
    .card-register:hover {
      transform: translateY(-5px);
    }
    .card-register h4 {
      font-weight: 700;
      text-align: center;
      color: #333;
    }
    .input-group-text {
      background: #2575fc;
      color: #fff;
      border: none;
    }
    .form-control:focus {
      box-shadow: none;
      border-color: #2575fc;
    }
    .btn-register {
      background: #6a11cb;
      border: none;
      width: 100%;
      font-weight: 600;
      transition: background 0.3s;
    }
    .btn-register:hover {
      background: #2575fc;
    }
    .text-center a {
      color: #6a11cb;
      text-decoration: none;
    }
    .text-center a:hover {
      text-decoration: underline;
    }
    @media (max-width: 767px) {
      body {
        margin: 20px;
      }
    }
  </style>
</head>
<body>

<div class="card-register">
  <h4 class="mb-4">Register Mahasiswa</h4>

  <?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger"><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    
    <div class="mb-3 input-group">
      <span class="input-group-text"><i class="fa fa-user"></i></span>
      <input name="nama" type="text" class="form-control" placeholder="Nama Lengkap" required>
    </div>

    <div class="mb-3 input-group">
      <span class="input-group-text"><i class="fa fa-envelope"></i></span>
      <input name="email" type="email" class="form-control" placeholder="Email" required>
    </div>

    <div class="mb-3 input-group">
      <span class="input-group-text"><i class="fa fa-lock"></i></span>
      <input name="password" type="password" class="form-control" placeholder="Password" required>
    </div>

    <div class="mb-3 input-group">
      <span class="input-group-text"><i class="fa fa-lock"></i></span>
      <input name="password2" type="password" class="form-control" placeholder="Konfirmasi Password" required>
    </div>

    <button type="submit" class="btn btn-register mb-3">Daftar</button>
    <div class="text-center">
      <small>Sudah punya akun? <a href="login.php">Login</a></small>
    </div>
  </form>
</div>

</body>
</html>
