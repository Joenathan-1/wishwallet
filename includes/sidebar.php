<?php 
check_login(); 
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="columns" style="min-height: 100vh; margin-bottom: 0;">
    <aside class="column is-2 is-narrow-mobile is-fullheight is-hidden-mobile is-flex is-flex-direction-column app-sidebar">
        <div id="app-brand-logo" class="app-brand title is-4 has-text-white has-text-centered mb-6 is-flex-wrap-nowrap">
            <span class="icon"><i class="fas fa-wallet"></i></span>
            <span>Wish Wallet</span>
        </div>
        
        <ul class="menu-list" style="flex-grow: 1;">
            <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'is-active' : '' ?> has-text-white"><span class="icon"><i class="fas fa-tachometer-alt"></i></span> Dashboard</a></li>
            <li><a href="reports.php" class="<?= $current_page == 'reports.php' ? 'is-active' : '' ?> has-text-white"><span class="icon"><i class="fas fa-chart-pie"></i></span> Laporan</a></li>
            <li><a href="budgeting.php" class="<?= $current_page == 'budgeting.php' ? 'is-active' : '' ?> has-text-white"><span class="icon"><i class="fas fa-money-bill-wave"></i></span> Budgeting</a></li>
            <li><a href="categories.php" class="<?= $current_page == 'categories.php' ? 'is-active' : '' ?> has-text-white"><span class="icon"><i class="fas fa-tags"></i></span> Kategori</a></li>
            <li><a href="goals.php" class="<?= $current_page == 'goals.php' ? 'is-active' : '' ?> has-text-white"><span class="icon"><i class="fas fa-bullseye"></i></span> Savings Goals</a></li>
            <li><a href="profile.php" class="<?= $current_page == 'profile.php' ? 'is-active' : '' ?> has-text-white"><span class="icon"><i class="fas fa-user-circle"></i></span> User Profile</a></li>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <hr style="background-color: #8c7fe0; margin: 1rem 0;">
                <li><a href="admin/master_data.php" class="<?= $current_page == 'master_data.php' ? 'is-active' : '' ?> has-text-white"><span class="icon"><i class="fas fa-database"></i></span> Master Data</a></li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
            <div class="logout-link mb-2">
                <a href="logout.php" class="has-text-white">
                    <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span>Logout</span>
                </a>
            </div>
            <p class="is-size-7 has-text-centered" style="color: #d1caff;">
                &copy; <?= date('Y') ?> Jonathan Andrian G.
            </p>
        </div>
    </aside>

    <div class="column main-content">
<script>
    if (typeof createParticle !== 'function') {
        const cashSound = new Audio('assets/cashier-quotka-chingquot-sound-effect-129698.mp3');
        let lastClickTime = 0; // Variabel untuk menyimpan waktu klik terakhir

        function createParticle(x, y) {
            // ... (Fungsi createParticle Anda tetap sama, tidak perlu diubah) ...
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

        document.addEventListener('DOMContentLoaded', () => {
            const logo = document.getElementById('app-brand-logo');
            if (logo) {
                logo.addEventListener('click', (e) => {
                    const currentTime = new Date().getTime();
                    // Hitung jeda waktu antara klik sekarang dan klik terakhir (dalam milidetik)
                    const timeSinceLastClick = currentTime - lastClickTime;

                    // --- LOGIKA BARU UNTUK KECEPATAN AUDIO ---
                    if (timeSinceLastClick < 250) { // Jika jeda kurang dari 250md (klik cepat)
                        // Percepat audio, maksimal sampai 2.5x kecepatan normal
                        cashSound.playbackRate = Math.min(cashSound.playbackRate + 0.25, 2.5);
                    } else {
                        // Jika jeda lama (klik normal), kembalikan ke kecepatan normal
                        cashSound.playbackRate = 1;
                    }

                    cashSound.currentTime = 0;
                    cashSound.play();
                    
                    // Simpan waktu klik saat ini untuk perbandingan berikutnya
                    lastClickTime = currentTime;
                    // --- AKHIR LOGIKA BARU ---

                    // Bagian untuk membuat partikel tetap sama
                    const rect = logo.getBoundingClientRect();
                    const originX = rect.left + rect.width / 2;
                    const originY = rect.top + rect.height / 2;
                    for (let i = 0; i < 10; i++) {
                        createParticle(originX, originY);
                    }
                });
            }
        });
    }
</script>


