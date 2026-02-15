<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$pageTitle = "Dashboard";
include __DIR__ . '/../includes/header.php';

$id_mahasiswa = $_SESSION['user_id'];

// ---------------------
// Total pemasukan, pengeluaran, saldo
// ---------------------
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN jenis='Pemasukan' THEN jumlah ELSE 0 END) AS total_pemasukan,
    SUM(CASE WHEN jenis='Pengeluaran' THEN jumlah ELSE 0 END) AS total_pengeluaran
    FROM transaksi WHERE id_mahasiswa = ?");
$stmt->execute([$id_mahasiswa]);
$totals = $stmt->fetch(PDO::FETCH_ASSOC);

$total_pemasukan = $totals['total_pemasukan'] ?? 0;
$total_pengeluaran = $totals['total_pengeluaran'] ?? 0;
$saldo = $total_pemasukan - $total_pengeluaran;

// ---------------------
// Ambil semua anggaran user
// ---------------------
$stmt = $pdo->prepare("SELECT * FROM anggaran WHERE id_mahasiswa = ?");
$stmt->execute([$id_mahasiswa]);
$anggarans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------
// Hitung pengeluaran per periode
// ---------------------
function ambilTotal($pdo, $id, $periode) {
    $sql = "SELECT COALESCE(SUM(jumlah),0) FROM transaksi 
            WHERE id_mahasiswa = ? AND jenis='Pengeluaran' ";
    if ($periode == "hari") $sql .= "AND DATE(tanggal) = CURDATE()";
    if ($periode == "minggu") $sql .= "AND YEARWEEK(tanggal,1) = YEARWEEK(CURDATE(),1)";
    if ($periode == "bulan") $sql .= "AND DATE_FORMAT(tanggal,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')";
    if ($periode == "tahun") $sql .= "AND DATE_FORMAT(tanggal,'%Y') = DATE_FORMAT(CURDATE(),'%Y')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchColumn();
}

$total_harian = ambilTotal($pdo, $id_mahasiswa, "hari");
$total_mingguan = ambilTotal($pdo, $id_mahasiswa, "minggu");
$total_bulanan = ambilTotal($pdo, $id_mahasiswa, "bulan");
$total_tahunan = ambilTotal($pdo, $id_mahasiswa, "tahun");

// ---------------------
// Ambil jumlah anggaran sesuai tipe
// ---------------------
$anggaran_harian = $anggaran_mingguan = $anggaran_bulanan = $anggaran_tahunan = 0;
foreach ($anggarans as $a) {
    if ($a['tipe'] == 'Harian') $anggaran_harian = $a['jumlah'];
    if ($a['tipe'] == 'Mingguan') $anggaran_mingguan = $a['jumlah'];
    if ($a['tipe'] == 'Bulanan') $anggaran_bulanan = $a['jumlah'];
    if ($a['tipe'] == 'Tahunan') $anggaran_tahunan = $a['jumlah'];
}

// ---------------------
// Ambil data 7 hari terakhir untuk grafik line
// ---------------------
$stmt = $pdo->prepare("SELECT DATE(tanggal) as tgl, 
    SUM(CASE WHEN jenis='Pemasukan' THEN jumlah ELSE 0 END) as pemasukan,
    SUM(CASE WHEN jenis='Pengeluaran' THEN jumlah ELSE 0 END) as pengeluaran
    FROM transaksi 
    WHERE id_mahasiswa = ? AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(tanggal) ORDER BY tgl ASC");
$stmt->execute([$id_mahasiswa]);
$data7hari = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels7 = [];
$pemasukan7 = [];
$pengeluaran7 = [];
foreach ($data7hari as $d) {
    $labels7[] = date('d M', strtotime($d['tgl']));
    $pemasukan7[] = $d['pemasukan'];
    $pengeluaran7[] = $d['pengeluaran'];
}
?>

<!-- Header Welcome -->
<div class="card shadow-sm p-4 text-white mb-4" style="background: linear-gradient(135deg, #6a11cb, #2575fc); border-radius: 15px;">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4>Halo, <?= e($_SESSION['user_name']) ?> 👋</h4>
      <p>Semangat hari ini! Jangan lupa catat pengeluaranmu</p>
    </div>
    <div>
      <i class="fa fa-wallet fa-4x opacity-50"></i>
    </div>
  </div>
</div>


<!-- Info Cards Total -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card text-center shadow-sm h-100 border-start border-4 border-primary">
      <div class="card-body">
        <i class="fa fa-wallet fa-2x mb-2 text-primary"></i>
        <h6>Total Pemasukan</h6>
        <p class="fs-5 fw-bold">Rp <?= number_format($total_pemasukan,0,',','.') ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center shadow-sm h-100 border-start border-4 border-danger">
      <div class="card-body">
        <i class="fa fa-credit-card fa-2x mb-2 text-danger"></i>
        <h6>Total Pengeluaran</h6>
        <p class="fs-5 fw-bold">Rp <?= number_format($total_pengeluaran,0,',','.') ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center shadow-sm h-100 border-start border-4 border-success">
      <div class="card-body">
        <i class="fa fa-coins fa-2x mb-2 text-success"></i>
        <h6>Saldo Saat Ini</h6>
        <p class="fs-5 fw-bold">Rp <?= number_format($saldo,0,',','.') ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center shadow-sm h-100 border-start border-4 border-warning">
      <div class="card-body">
        <i class="fa fa-tasks fa-2x mb-2 text-warning"></i>
        <h6>Kelola Anggaran</h6>
        <a href="<?= BASE_URL ?>/anggaran/index.php" class="btn btn-sm btn-primary mt-2"><i class="fa fa-edit"></i> Anggaran</a>
      </div>
    </div>
  </div>
</div>

<!-- Grafik -->
<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="mb-3">Anggaran vs Pengeluaran</h6>
        <?php
        // Cek apakah ada anggaran yang diisi
        $ada_anggaran = ($anggaran_harian > 0) || ($anggaran_mingguan > 0) || ($anggaran_bulanan > 0) || ($anggaran_tahunan > 0);
        if (!$ada_anggaran): ?>
          <div class="alert alert-warning mb-0">
            <i class="fa fa-info-circle"></i> Belum ada anggaran yang diisi. Silakan isi anggaran terlebih dahulu untuk melihat grafik.
          </div>
        <?php else: ?>
          <canvas id="anggaranChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="mb-3">Tren 7 Hari Terakhir</h6>
        <?php
        // Cek apakah ada data 7 hari terakhir
        $ada_data7 = !empty($labels7) && array_sum($pemasukan7) + array_sum($pengeluaran7) > 0;
        if (!$ada_data7): ?>
          <div class="alert alert-warning mb-0">
            <i class="fa fa-info-circle"></i> Belum ada data transaksi dalam 7 hari terakhir.
          </div>
        <?php else: ?>
          <canvas id="lineChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Progress Anggaran -->
<?php if ($ada_anggaran): ?>
<h5 class="mb-3">Progress Anggaran</h5>
<div class="row g-3 mb-4">
<?php
$anggaran_data = [
    ['label'=>'Harian', 'anggaran'=>$anggaran_harian, 'pengeluaran'=>$total_harian],
    ['label'=>'Mingguan', 'anggaran'=>$anggaran_mingguan, 'pengeluaran'=>$total_mingguan],
    ['label'=>'Bulanan', 'anggaran'=>$anggaran_bulanan, 'pengeluaran'=>$total_bulanan],
    ['label'=>'Tahunan', 'anggaran'=>$anggaran_tahunan, 'pengeluaran'=>$total_tahunan],
];
foreach ($anggaran_data as $data):
    if ($data['anggaran'] > 0):
        $warna = ($data['pengeluaran'] > $data['anggaran']) ? 'danger' : 'success';
        $persen = min(100, ($data['pengeluaran']/$data['anggaran'])*100);
?>
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h6><?= $data['label'] ?></h6>
        <p>Anggaran: Rp <?= number_format($data['anggaran'],0,',','.') ?></p>
        <p>Pengeluaran: Rp <?= number_format($data['pengeluaran'],0,',','.') ?></p>
        <div class="progress">
          <div class="progress-bar bg-<?= $warna ?>" role="progressbar" style="width: <?= $persen ?>%"><?= round($persen) ?>%</div>
        </div>
      </div>
    </div>
  </div>
<?php
    endif;
endforeach;
?>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Bar Chart Anggaran vs Pengeluaran
<?php if ($ada_anggaran): ?>
new Chart(document.getElementById('anggaranChart'), {
    type: 'bar',
    data: {
        labels: ['Harian','Mingguan','Bulanan','Tahunan'],
        datasets: [{
            label: 'Anggaran',
            data: [<?= $anggaran_harian ?>, <?= $anggaran_mingguan ?>, <?= $anggaran_bulanan ?>, <?= $anggaran_tahunan ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
        },{
            label: 'Pengeluaran',
            data: [<?= $total_harian ?>, <?= $total_mingguan ?>, <?= $total_bulanan ?>, <?= $total_tahunan ?>],
            backgroundColor: 'rgba(255, 99, 132, 0.7)'
        }]
    }
});
<?php endif; ?>

// Line Chart 7 hari terakhir
<?php if ($ada_data7): ?>
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels7) ?>,
        datasets: [{
            label: 'Pemasukan',
            data: <?= json_encode($pemasukan7) ?>,
            borderColor: 'green',
            fill: false
        },{
            label: 'Pengeluaran',
            data: <?= json_encode($pengeluaran7) ?>,
            borderColor: 'red',
            fill: false
        }]
    }
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
