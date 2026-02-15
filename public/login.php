<?php
// public/login.php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $token = $_POST['_csrf'] ?? '';

  if (!csrf_check($token)) {
    flash('error', 'Token CSRF tidak valid.');
    redirect('login.php');
  }

  if ($email === '' || $password === '') {
    flash('error', 'Email dan password wajib diisi.');
    redirect('login.php');
  }

  $user = find_user_by_email($pdo, $email);
  if (!$user || !password_verify($password, $user['kata_sandi'])) {
    flash('error', 'Email atau password salah.');
    redirect('login.php');
  }

  // Berhasil login
  session_regenerate_id(true);
  $_SESSION['user_id'] = $user['id_mahasiswa'];
  $_SESSION['user_name'] = $user['nama'];
  redirect('index.php');
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Keuangan Mahasiswa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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

    .card-login {
      border-radius: 1rem;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      background: #fff;
      padding: 2rem;
      width: 100%;
      max-width: 400px;
      transition: transform 0.3s;
    }

    .card-login:hover {
      transform: translateY(-5px);
    }

    .card-login h4 {
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

    .btn-login {
      background: #6a11cb;
      border: none;
      width: 100%;
      font-weight: 600;
      transition: background 0.3s;
    }

    .btn-login:hover {
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

  <div class="card-login">
    <h4 class="mb-4">Login Keuangan Mahasiswa</h4>

    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('success')): ?>
      <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

      <div class="mb-3 input-group">
        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
        <input name="email" type="email" class="form-control" placeholder="Email" required>
      </div>

      <div class="mb-3 input-group">
        <span class="input-group-text"><i class="fa fa-lock"></i></span>
        <input name="password" type="password" class="form-control" placeholder="Password" required>
      </div>

      <button type="submit" class="btn btn-login mb-3">Login</button>
      <div class="text-center">
        <small>Belum punya akun? <a href="register.php">Daftar baru</a></small>
      </div>
    </form>
  </div>

</body>

</html>