<?php
// 1. Panggil header terlebih dahulu (ini akan memuat koneksi DB dan HTML)
include __DIR__ . '/../includes/header.php';

// 2. Lakukan pengecekan login dan role
check_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect jika bukan admin
    echo "<script>window.location.href = '../dashboard.php';</script>";
    exit();
}

// --- LOGIKA PENCARIAN ---
// 3. Siapkan query dasar dan keyword
$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
$sql = "SELECT id, uuid, username, email, created_at FROM users WHERE role = 'user'";
$params = [];

// Jika ada keyword pencarian, tambahkan kondisi WHERE
if (!empty($keyword)) {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $params[] = '%' . $keyword . '%';
    $params[] = '%' . $keyword . '%';
}

$sql .= " ORDER BY created_at DESC";

// Eksekusi query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dapatkan nama halaman saat ini untuk menandai menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="columns" style="min-height: 100vh; margin-bottom: 0;">

    <aside class="column is-2 is-narrow-mobile is-fullheight is-hidden-mobile is-flex is-flex-direction-column app-sidebar">
        <div id="app-brand-logo" class="app-brand title is-4 has-text-white has-text-centered mb-6 is-flex-wrap-nowrap">
            <span class="icon"><i class="fas fa-wallet"></i></span>
            <span>Wish Wallet</span>
        </div>
        
        <ul class="menu-list" style="flex-grow: 1;">
            <li><a href="../dashboard.php" class="has-text-white"><span class="icon"><i class="fas fa-tachometer-alt"></i></span> Dashboard</a></li>
            <li><a href="../reports.php" class="has-text-white"><span class="icon"><i class="fas fa-chart-pie"></i></span> Laporan</a></li>
            <li><a href="../budgeting.php" class="has-text-white"><span class="icon"><i class="fas fa-money-bill-wave"></i></span> Budgeting</a></li>
            <li><a href="../categories.php" class="has-text-white"><span class="icon"><i class="fas fa-tags"></i></span> Kategori</a></li>
            <li><a href="../goals.php" class="has-text-white"><span class="icon"><i class="fas fa-bullseye"></i></span> Savings Goals</a></li>
            <li><a href="../profile.php" class="has-text-white"><span class="icon"><i class="fas fa-user-circle"></i></span> User Profile</a></li>
            
            <hr style="background-color: #8c7fe0; margin: 1rem 0;">
            <li><a href="master_data.php" class="<?= $current_page == 'master_data.php' ? 'is-active' : '' ?> has-text-white"><span class="icon"><i class="fas fa-database"></i></span> Master Data</a></li>
        </ul>

        <div class="logout-link">
            <a href="../logout.php" class="has-text-white">
                <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <div class="column main-content">
        <div class="level">
            <div class="level-left">
                <div>
                    <h1 class="title">Master Data Pengguna</h1>
                    <h2 class="subtitle">Daftar semua pengguna yang terdaftar di sistem.</h2>
                </div>
            </div>
        </div>
        <hr>

        <div class="box">
            <form action="master_data.php" method="POST">
                <div class="field has-addons">
                    <div class="control is-expanded">
                        <input class="input" type="text" name="keyword" placeholder="Cari berdasarkan username atau email..." value="<?= htmlspecialchars($keyword) ?>" autocomplete="off">
                    </div>
                    <div class="control">
                        <button type="submit" name="cari" class="button is-primary">
                            <span class="icon"><i class="fas fa-search"></i></span>
                            <span>Cari</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="box">
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tanggal Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="3" class="has-text-centered">
                                <?php if (!empty($keyword)): ?>
                                    Pengguna dengan kata kunci "<?= htmlspecialchars($keyword) ?>" tidak ditemukan.
                                <?php else: ?>
                                    Belum ada pengguna yang terdaftar.
                                <?php endif; ?>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('d F Y, H:i', strtotime($user['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> </div> <?php // Kode Javascript & Penutup HTML ?>
<!-- <script>
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
</script> -->

</body>
</html>