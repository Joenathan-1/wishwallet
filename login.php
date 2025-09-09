<?php
require_once 'config/database.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        // Ambil data user, termasuk kolom uuid
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // --- PERBAIKAN DI SINI ---
            // Simpan 'uuid' pengguna ke session, bukan lagi 'id'
            $_SESSION['user_uuid'] = $user['uuid']; 
            
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
include 'includes/header.php';
?>
<style>
    /* Aturan ini mencegah scroll di seluruh halaman */
    html, body {
        overflow: hidden;
    }
    .hero.is-fullheight {
        background: url('assets/images/money-background.jpg') no-repeat center center;
        background-size: cover;
        position: relative;
    }
    .hero.is-fullheight::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.55);
        z-index: 1;
    }
    .hero-body {
        display: flex;
        position: relative;
        z-index: 2;
    }
    .login-form-card {
        background-color: rgba(255, 255, 255, 0.95);
        padding: 2.5rem;
        border-radius: 8px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        max-width: 450px;
        width: 100%;
    }
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-15px); }
        60% { transform: translateY(-8px); }
    }
    .animated-money-icon {
        animation: bounce 2s infinite;
        display: inline-block;
    }
</style>

<section class="hero is-fullheight">
    <div class="hero-body is-justify-content-center is-align-items-center">
        <div class="column is-4-desktop is-6-tablet is-11-mobile login-form-card">
            <div class="has-text-centered mb-5">
                <span class="icon is-large has-text-success animated-money-icon">
                    <i class="fas fa-dollar-sign fa-3x"></i>
                </span>
                <h1 class="title is-3 has-text-black">
                    Selamat Datang
                </h1>
                <h2 class="subtitle is-6 has-text-grey">
                    Login untuk mengelola keuangan Anda
                </h2>
            </div>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
                <div class="notification is-success is-light">Registrasi berhasil! Silakan login.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="notification is-danger is-light"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label class="label">Email</label>
                    <div class="control has-icons-left">
                        <input class="input" type="email" name="email" placeholder="contoh@email.com" required>
                        <span class="icon is-small is-left"><i class="fas fa-envelope"></i></span>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Password</label>
                    <div class="control has-icons-left">
                        <input class="input" type="password" name="password" placeholder="********" required>
                        <span class="icon is-small is-left"><i class="fas fa-lock"></i></span>
                    </div>
                </div>
                <div class="field mt-5">
                    <button type="submit" class="button is-success is-fullwidth is-large">
                        <span class="icon"><i class="fas fa-sign-in-alt"></i></span>
                        <span>Login</span>
                    </button>
                </div>
                <p class="has-text-centered mt-4">Belum punya akun? <a href="register.php" class="has-text-info">Daftar di sini</a></p>
            </form>
        </div>
    </div>
</section>
</body>
</html>