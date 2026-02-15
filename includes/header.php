<?php
// includes/header.php
require_once __DIR__ . '/functions.php';
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' - Keuangan Mahasiswa' : 'Keuangan Mahasiswa' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    html,
    body {
      height: 100%;
      margin: 0;
    }

    body {
      display: flex;
      flex-direction: column;
      font-family: 'Segoe UI', sans-serif;
      background: #f5f6fa;
    }

    #app {
      flex: 1 0 auto;
      padding: 20px 0;
      padding-left: 10px;
      padding-right: 10px;
    }

    /* Navbar */
    .navbar-custom {
      background: linear-gradient(90deg, #6a11cb, #2575fc);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .navbar-custom .navbar-brand {
      color: #fff;
      font-weight: 700;
      font-size: 1.5rem;
    }

    .navbar-custom .nav-link {
      color: #fff;
      transition: color 0.3s;
    }

    .navbar-custom .nav-link:hover {
      color: #ffd700;
    }

    /* Supaya hamburger terlihat jelas */
    .navbar-toggler {
      border: none;
    }

    /* .navbar-toggler-icon {
      background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30'
      xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255,255,255,1%29'
      stroke-width='2' stroke-linecap='round' stroke-miterlimit='10'
      d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    } */

    /* Footer */
    footer {
      background: #2575fc;
      color: #fff;
      padding: 1rem 0;
      font-size: 0.9rem;
      flex-shrink: 0;
    }

    footer a {
      color: #ffd700;
      text-decoration: none;
      margin: 0 5px;
    }

    footer a:hover {
      text-decoration: underline;
    }

    /* Responsive tweak */
    @media (max-width: 576px) {
      .navbar-custom .navbar-brand {
        font-size: 1.2rem;
      }

      footer {
        font-size: 0.8rem;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-custom navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">Budget.in</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <?php if (!empty($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/transaksi/index.php">Transaksi</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/anggaran/index.php">Anggaran</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/tabungan/index.php">Tabungan</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown1" role="button" data-bs-toggle="dropdown">
                Laporan
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown1">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/laporan/laporan_keuangan.php">Laporan Keuangan</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/laporan/laporan_tabungan.php">Laporan Tabungan</a></li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-bs-toggle="dropdown">
                <i class="fa fa-user-circle"></i> <?= e($_SESSION['user_name']) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown2">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile/index.php"><i class="fa fa-id-card"></i> Profil</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile/edit.php"><i class="fa fa-edit"></i> Edit Profil</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li>
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fa fa-sign-out-alt"></i> Logout
                  </a>
                </li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php"><i class="fa fa-sign-in-alt"></i> Login</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/register.php"><i class="fa fa-user-plus"></i> Register</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Konfirmasi Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin keluar dari aplikasi?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-danger">Ya, Logout</a>
      </div>
    </div>
  </div>
</div>

  <div id="app" class="container">