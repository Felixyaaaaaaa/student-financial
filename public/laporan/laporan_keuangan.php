<?php
include '../../includes/config.php';
include '../../includes/functions.php';
require_login();

$id = $_SESSION['user_id'];

// filter periode
$filter = $_GET['filter'] ?? 'bulan';
$conds = ["id_mahasiswa = :id"]; // selalu filter user

switch ($filter) {
  case 'hari':
    $conds[] = "DATE(tanggal) = CURDATE()";
    $periode_label = "Hari ini";
    break;
  case 'minggu':
    $conds[] = "YEARWEEK(tanggal, 1) = YEARWEEK(CURDATE(), 1)";
    $periode_label = "Minggu ini";
    break;
  case 'tahun':
    $conds[] = "YEAR(tanggal) = YEAR(CURDATE())";
    $periode_label = "Tahun ini";
    break;
  default: // 'bulan'
    $conds[] = "MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
    $filter = 'bulan';
    $periode_label = "Bulan ini";
}

$where = implode(' AND ', $conds);

// ambil data transaksi (list)
$stmt = $pdo->prepare("SELECT tanggal, jenis, jumlah, keterangan
                       FROM transaksi
                       WHERE $where
                       ORDER BY tanggal ASC");
$stmt->execute([':id' => $id]);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ringkasan
$sum = $pdo->prepare("
  SELECT
    COALESCE(SUM(CASE WHEN LOWER(jenis)='pemasukan'  THEN jumlah END),0) AS pemasukan,
    COALESCE(SUM(CASE WHEN LOWER(jenis)='pengeluaran' THEN jumlah END),0) AS pengeluaran
  FROM transaksi
  WHERE $where
");
$sum->execute([':id' => $id]);
$tot = $sum->fetch(PDO::FETCH_ASSOC);

$total_pemasukan  = (float)$tot['pemasukan'];
$total_pengeluaran = (float)$tot['pengeluaran'];

$pageTitle = "Laporan Keuangan";
include '../../includes/header.php';
?>
<div class="container mt-4">
  <h3 class="mb-4">Laporan Keuangan</h3>

  <form method="GET" class="mb-3 d-flex flex-wrap align-items-center gap-2">
    <label class="me-2">Filter Periode:</label>
    <select name="filter" onchange="this.form.submit()" class="form-select w-auto">
      <option value="hari" <?= $filter == 'hari' ? 'selected' : '' ?>>Harian</option>
      <option value="minggu" <?= $filter == 'minggu' ? 'selected' : '' ?>>Mingguan</option>
      <option value="bulan" <?= $filter == 'bulan' ? 'selected' : '' ?>>Bulanan</option>
      <option value="tahun" <?= $filter == 'tahun' ? 'selected' : '' ?>>Tahunan</option>
    </select>
  </form>

  <div class="row text-center mb-4 g-3">
    <div class="col-md-6">
      <div class="card bg-success text-white h-100">
        <div class="card-body">
          <h6>Total Pemasukan (<?= $periode_label ?>)</h6>
          <h4>Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h4>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card bg-danger text-white h-100">
        <div class="card-body">
          <h6>Total Pengeluaran (<?= $periode_label ?>)</h6>
          <h4>Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h4>
        </div>
      </div>
    </div>
  </div>

  <div class="mb-5">
    <canvas id="laporanChart"></canvas>
  </div>

  <h4 class="mb-3">Detail Transaksi</h4>
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="min-width:110px; white-space:nowrap;">Tanggal</th>
          <th style="min-width:80px;">Jenis</th>
          <th style="min-width:120px; white-space:nowrap;">Jumlah</th>
          <th style="min-width:200px; width:50%;">Keterangan</th>
        </tr>
      </thead>


      <tbody>
        <?php if ($transaksi): foreach ($transaksi as $row): ?>
            <tr>
              <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
              <td>
                <span class="badge bg-<?= (strtolower($row['jenis']) == 'pemasukan' ? 'success' : 'danger') ?>">
                  <?= htmlspecialchars($row['jenis']) ?>
                </span>
              </td>
              <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
              <td><?= e($row['keterangan']) ?></td>
            </tr>
          <?php endforeach;
        else: ?>
          <tr>
            <td colspan="4" class="text-center">Tidak ada transaksi pada periode ini.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('laporanChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Pemasukan', 'Pengeluaran'],
      datasets: [{
        label: 'Jumlah (Rp)',
        data: [<?= $total_pemasukan ?>, <?= $total_pengeluaran ?>],
        backgroundColor: ['#198754', '#dc3545']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false
        },
        title: {
          display: true,
          text: 'Ringkasan Keuangan (<?= $periode_label ?>)'
        }
      }
    }
  });
</script>
<?php include '../../includes/footer.php'; ?>