<?php
include '../../includes/config.php';
include '../../includes/functions.php';
require_login();

$id_mahasiswa = $_SESSION['user_id'];

// Ambil semua rekening tabungan milik user
$stmt = $pdo->prepare("SELECT * FROM tabungan WHERE id_mahasiswa = ?");
$stmt->execute([$id_mahasiswa]);
$tabungan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pilih rekening aktif
$tabungan_id = isset($_GET['tabungan_id']) ? (int)$_GET['tabungan_id'] : null;
$tabungan = null;
$transaksi = [];
$total_deposit = 0;
$total_withdraw = 0;
$saldo = 0;

if ($tabungan_id) {
    // Ambil data rekening
    $stmt = $pdo->prepare("SELECT * FROM tabungan WHERE id = ? AND id_mahasiswa = ?");
    $stmt->execute([$tabungan_id, $id_mahasiswa]);
    $tabungan = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tabungan) {
        // Ambil transaksi tabungan
        $stmt = $pdo->prepare("SELECT * FROM tabungan_transaksi WHERE tabungan_id = ? ORDER BY tanggal ASC");
        $stmt->execute([$tabungan_id]);
        $transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Hitung total deposit & withdraw
        $stmt = $pdo->prepare("SELECT SUM(jumlah) FROM tabungan_transaksi WHERE tabungan_id = ? AND tipe='deposit'");
        $stmt->execute([$tabungan_id]);
        $total_deposit = (float) $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT SUM(jumlah) FROM tabungan_transaksi WHERE tabungan_id = ? AND tipe='withdraw'");
        $stmt->execute([$tabungan_id]);
        $total_withdraw = (float) $stmt->fetchColumn();

        $saldo = $tabungan['saldo'];
    }
}
?>

<?php include '../../includes/header.php'; ?>
<div class="container mt-4">
    <h3 class="mb-4">Laporan Tabungan</h3>

    <!-- Pilih rekening -->
    <form method="GET" class="mb-3">
        <label>Pilih Rekening Tabungan:</label>
        <select name="tabungan_id" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
            <option value="">-- Pilih --</option>
            <?php foreach ($tabungan_list as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $tabungan_id == $t['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($tabungan): ?>
        <!-- Ringkasan -->
        <div class="row text-center mb-4 g-3">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Total Deposit</h6>
                        <h4>Rp <?= number_format($total_deposit, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6>Total Withdraw</h6>
                        <h4>Rp <?= number_format($total_withdraw, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Saldo Saat Ini</h6>
                        <h4>Rp <?= number_format($saldo, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <?php if ($tabungan['target_amount']):
                $progress = min(100, ($saldo / $tabungan['target_amount']) * 100);
            ?>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h6>Progress Target</h6>
                            <h4><?= round($progress, 1) ?>%</h4>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Grafik -->
        <div class="mb-5">
            <canvas id="tabunganChart"></canvas>
        </div>

        <!-- Detail Transaksi -->
        <!-- Detail Transaksi -->
        <h4 class="mb-3">Detail Transaksi (<?= htmlspecialchars($tabungan['nama']) ?>)</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="min-width:110px; white-space:nowrap;">Tanggal</th>
                        <th style="min-width:80px;">Tipe</th>
                        <th style="min-width:120px; white-space:nowrap;">Jumlah</th>
                        <th style="min-width:200px; width:50%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transaksi) > 0): ?>
                        <?php foreach ($transaksi as $row): ?>
                            <tr>
                                <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['tipe'] == 'deposit' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($row['tipe']) ?>
                                    </span>
                                </td>
                                <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Belum ada transaksi untuk tabungan ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($tabungan_id): ?>
        <div class="alert alert-danger">Rekening tidak ditemukan.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($tabungan): ?>
    <script>
        const ctx = document.getElementById('tabunganChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Deposit', 'Withdraw', 'Saldo'],
                datasets: [{
                    label: 'Jumlah (Rp)',
                    data: [<?= $total_deposit ?>, <?= $total_withdraw ?>, <?= $saldo ?>],
                    backgroundColor: ['green', 'red', 'blue']
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
                        text: 'Ringkasan Tabungan: <?= addslashes($tabungan['nama']) ?>'
                    }
                }
            }
        });
    </script>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>