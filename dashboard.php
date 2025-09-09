<?php 
include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// PERUBAHAN 1: Mengambil user_uuid dari session, bukan user_id
$user_uuid = $_SESSION['user_uuid'];

// --- SEMUA QUERY DI BAWAH INI DIPERBARUI ---

// Hitung total balance keseluruhan
$stmt_balance = $pdo->prepare("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance FROM transactions WHERE user_uuid = ?");
$stmt_balance->execute([$user_uuid]);
$current_balance = $stmt_balance->fetchColumn() ?? 0;

// Hitung total pemasukan dan pengeluaran bulan ini
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$stmt_monthly = $pdo->prepare(
    "SELECT 
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as monthly_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as monthly_expense
     FROM transactions WHERE user_uuid = ? AND transaction_date BETWEEN ? AND ?"
);
$stmt_monthly->execute([$user_uuid, $month_start, $month_end]);
$monthly_summary = $stmt_monthly->fetch(PDO::FETCH_ASSOC);
$monthly_income_val = $monthly_summary['monthly_income'] ?? 0;
$monthly_expense_val = $monthly_summary['monthly_expense'] ?? 0;

// Data untuk widget profil
$stmt_user = $pdo->prepare("SELECT profile_picture, email FROM users WHERE uuid = ?");
$stmt_user->execute([$user_uuid]);
$user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Data untuk riwayat transaksi
$stmt_history = $pdo->prepare("SELECT * FROM transactions WHERE user_uuid = ? ORDER BY transaction_date DESC, id DESC LIMIT 5");
$stmt_history->execute([$user_uuid]);
$history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

// Data untuk Grafik
$current_year_chart = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$monthly_income_chart = array_fill(0, 12, 0);
$monthly_expense_chart = array_fill(0, 12, 0);
$stmt_chart = $pdo->prepare("SELECT type, amount, MONTH(transaction_date) as month FROM transactions WHERE user_uuid = ? AND YEAR(transaction_date) = ?");
$stmt_chart->execute([$user_uuid, $current_year_chart]);
$chart_transactions = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

foreach ($chart_transactions as $transaction) {
    $month_index = $transaction['month'] - 1;
    if ($transaction['type'] == 'income') {
        $monthly_income_chart[$month_index] += $transaction['amount'];
    } else {
        $monthly_expense_chart[$month_index] += $transaction['amount'];
    }
}
?>

<div class="level">
    <div class="level-left">
        <div>
            <h1 class="title">Dashboard</h1>
            <h2 class="subtitle">Selamat datang kembali, <?= htmlspecialchars($_SESSION['username']) ?>! 👋</h2>
        </div>
    </div>
</div>
<hr>

<div class="columns is-multiline">
    <div class="column is-8">
        <div class="box p-5 mb-4">
            <p class="heading">Current Balance (Total)</p>
            <p class="title is-1" style="color: #6d5ed3;">Rp <?= number_format($current_balance, 0, ',', '.') ?></p>
        </div>

        <div class="columns">
            <div class="column">
                <div class="box">
                    <p class="heading">Pemasukan (Bulan Ini)</p>
                    <p class="title is-4 has-text-success">+ Rp <?= number_format($monthly_income_val, 0, ',', '.') ?></p>
                    <p class="is-size-7 has-text-grey">Data untuk bulan <?= date('F Y') ?></p>
                </div>
            </div>
            <div class="column">
                <div class="box">
                    <p class="heading">Pengeluaran (Bulan Ini)</p>
                    <p class="title is-4 has-text-danger">- Rp <?= number_format($monthly_expense_val, 0, ',', '.') ?></p>
                    <p class="is-size-7 has-text-grey">Data untuk bulan <?= date('F Y') ?></p>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="level">
                <div class="level-left">
                    <h3 class="title is-5">Aktivitas Keuangan</h3>
                </div>
                <div class="level-right">
                    <form method="GET">
                        <div class="select is-small is-rounded">
                            <select name="year" onchange="this.form.submit()">
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($y == $current_year_chart) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div style="position: relative; height:320px;">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>

    <div class="column is-4">
        <div class="box has-text-centered">
            <?php
            $profile_pic_path = 'assets/uploads/' . htmlspecialchars($user_info['profile_picture']);
            if (!file_exists($profile_pic_path) || empty($user_info['profile_picture'])) {
                $profile_pic_path = 'assets/uploads/default.png'; 
            }
            ?>
            <figure class="image is-128x128 is-inline-block mb-3">
                <img class="is-rounded" src="<?= $profile_pic_path ?>" alt="Profile">
            </figure>
            <p class="title is-4"><?= htmlspecialchars($_SESSION['username']) ?></p>
            <p class="subtitle is-6 has-text-grey"><?= htmlspecialchars($user_info['email']) ?></p>
            <a href="profile.php" class="button is-link is-light is-fullwidth">Edit Profile</a>
        </div>
        
        <div class="box">
            <h3 class="title is-5 mb-4">5 Transaksi Terakhir</h3>
            <?php if(empty($history)): ?>
                <p class="has-text-grey has-text-centered">Belum ada transaksi.</p>
            <?php else: ?>
                <?php foreach($history as $trans): 
                    $is_income = $trans['type'] == 'income';
                ?>
                <div class="media">
                    <div class="media-left"><span class="icon is-medium <?= $is_income ? 'has-text-success' : 'has-text-danger' ?>"><i class="fas fa-<?= $is_income ? 'arrow-down' : 'arrow-up' ?> fa-lg"></i></span></div>
                    <div class="media-content">
                        <p class="is-size-6"><?= htmlspecialchars($trans['description']) ?: ucfirst($trans['type']) ?></p>
                        <p class="is-size-7 has-text-grey"><?= date('d M Y', strtotime($trans['transaction_date'])) ?></p>
                    </div>
                    <div class="media-right has-text-right"><p class="is-size-6 has-text-weight-bold <?= $is_income ? 'has-text-success' : 'has-text-danger' ?>"><?= $is_income ? '+' : '-' ?> Rp <?= number_format($trans['amount'], 0, ',', '.') ?></p></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const incomeData = <?= json_encode(array_values($monthly_income_chart)) ?>;
    const expenseData = <?= json_encode(array_values($monthly_expense_chart)) ?>;

    new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: incomeData,
                    borderColor: 'hsl(153, 53%, 53%)',
                    backgroundColor: 'hsla(153, 53%, 53%, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 7
                },
                {
                    label: 'Pengeluaran',
                    data: expenseData,
                    borderColor: 'hsl(348, 86%, 61%)',
                    backgroundColor: 'hsla(348, 86%, 61%, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                x: { grid: { display: false } }
            },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, padding: 20 } }
            }
        }
    });
});
</script>

    </div> 
</div> 

<script>
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