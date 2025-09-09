<?php
// 1. Panggil header dan sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// 2. Gunakan 'user_uuid' dari session
$user_uuid = $_SESSION['user_uuid'];

// Default: bulan ini
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// 3. Perbarui query untuk ringkasan
$stmt_summary = $pdo->prepare(
    "SELECT 
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
     FROM transactions 
     WHERE user_uuid = ? AND transaction_date BETWEEN ? AND ?"
);
$stmt_summary->execute([$user_uuid, $start_date, $end_date]);
$summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);

$total_income = $summary['total_income'] ?? 0;
$total_expense = $summary['total_expense'] ?? 0;
$net_profit = $total_income - $total_expense;

// 4. Perbarui query untuk pie chart
$stmt_pie = $pdo->prepare(
    "SELECT c.name as category_name, SUM(t.amount) as total_amount
     FROM transactions t
     JOIN categories c ON t.category_id = c.id
     WHERE t.user_uuid = ? AND t.type = 'expense' AND t.transaction_date BETWEEN ? AND ?
     GROUP BY c.name
     ORDER BY total_amount DESC"
);
$stmt_pie->execute([$user_uuid, $start_date, $end_date]);
$pie_data = $stmt_pie->fetchAll(PDO::FETCH_ASSOC);

$pie_labels = [];
$pie_values = [];
foreach($pie_data as $data) {
    $pie_labels[] = $data['category_name'];
    $pie_values[] = $data['total_amount'];
}
?>

<div class="level">
    <div class="level-left">
        <div>
            <h1 class="title">Laporan Keuangan</h1>
            <h2 class="subtitle">Analisis pemasukan dan pengeluaran Anda.</h2>
        </div>
    </div>
</div>
<hr>

<div class="box">
    <form method="GET">
        <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">Pilih Periode</label></div>
            <div class="field-body">
                <div class="field"><div class="control"><input class="input" type="date" name="start_date" value="<?= $start_date ?>"></div></div>
                <div class="field"><div class="control"><input class="input" type="date" name="end_date" value="<?= $end_date ?>"></div></div>
                <div class="field"><div class="control"><button class="button is-primary">Tampilkan</button></div></div>
            </div>
        </div>
    </form>
</div>

<div class="columns is-multiline">
    <div class="column is-one-third">
        <div class="box has-background-success-light">
            <p class="heading">Total Pemasukan</p>
            <p class="title is-4">Rp <?= number_format($total_income, 0, ',', '.') ?></p>
        </div>
    </div>
    <div class="column is-one-third">
        <div class="box has-background-danger-light">
            <p class="heading">Total Pengeluaran</p>
            <p class="title is-4">Rp <?= number_format($total_expense, 0, ',', '.') ?></p>
        </div>
    </div>
    <div class="column is-one-third">
        <div class="box has-background-info-light">
            <p class="heading">Selisih (Profit/Rugi)</p>
            <p class="title is-4 <?= $net_profit >= 0 ? 'has-text-success' : 'has-text-danger' ?>">
                Rp <?= number_format($net_profit, 0, ',', '.') ?>
            </p>
        </div>
    </div>
</div>

<div class="box">
    <h2 class="subtitle">Komposisi Pengeluaran per Kategori</h2>
    <?php if(empty($pie_data)): ?>
        <div class="notification is-warning is-light">
            Tidak ada data pengeluaran berkategori pada periode ini untuk ditampilkan di grafik.
        </div>
    <?php else: ?>
        <div style="max-width: 450px; margin: auto;">
            <canvas id="expensePieChart"></canvas>
        </div>
    <?php endif; ?>
</div>

    </div> </div> <script>
// Skrip untuk Chart.js (pie chart)
document.addEventListener('DOMContentLoaded', () => {
    const pieLabels = <?= json_encode($pie_labels) ?>;
    if (pieLabels && pieLabels.length > 0) {
        const pieValues = <?= json_encode($pie_values) ?>;
        new Chart(document.getElementById('expensePieChart'), {
            type: 'pie',
            data: {
                labels: pieLabels,
                datasets: [{
                    label: 'Pengeluaran (Rp)',
                    data: pieValues,
                    backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#FFCD56'],
                }]
            }
        });
    }
});

// Skrip untuk animasi klik logo
document.addEventListener('DOMContentLoaded', () => {
    const logo = document.getElementById('app-brand-logo');
    if (logo && !logo.hasAttribute('data-listener-attached')) {
        logo.setAttribute('data-listener-attached', 'true');
        logo.addEventListener('click', () => {
            const rect = logo.getBoundingClientRect();
            const originX = rect.left + rect.width / 2;
            const originY = rect.top + rect.height / 2;
            for (let i = 0; i < 10; i++) {
                createParticle(originX, originY);
            }
        });
    }
});

function createParticle(x, y) {
    const particle = document.createElement('div');
    particle.className = 'money-particle';
    const icons = ['fas fa-coins', 'fas fa-money-bill-wave'];
    const randomIcon = icons[Math.floor(Math.random() * icons.length)];
    const color = randomIcon.includes('coins') ? '#FFD700' : '#4CAF50';
    particle.innerHTML = `<i class="${randomIcon}" style="color: ${color};"></i>`;
    document.body.appendChild(particle);
    particle.style.left = x + 'px';
    particle.style.top = y + 'px';
    const destinationX = (Math.random() - 0.5) * 300;
    const destinationY = (Math.random() - 0.5) * 300;
    const rotation = Math.random() * 540 - 270;
    const animationDuration = Math.random() * 800 + 500;
    particle.style.setProperty('--x', destinationX + 'px');
    particle.style.setProperty('--y', destinationY + 'px');
    particle.style.setProperty('--r', rotation + 'deg');
    particle.style.animation = `fly-out ${animationDuration}ms ease-out forwards`;
    setTimeout(() => { particle.remove(); }, animationDuration);
}
</script>

</body>
</html>