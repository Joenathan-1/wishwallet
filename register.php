<?php
require_once 'config/database.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username atau Email sudah terdaftar!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Buat UUID baru untuk pengguna
            $new_user_uuid = generate_uuid(); 

            // Query INSERT sekarang menyertakan kolom `uuid`
            $stmt = $pdo->prepare("INSERT INTO users (uuid, username, email, password) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$new_user_uuid, $username, $email, $hashed_password])) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $error = 'Registrasi gagal, coba lagi.';
            }
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
    .register-form-card {
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
        <div class="column is-4-desktop is-6-tablet is-11-mobile register-form-card">
            <div class="has-text-centered mb-5">
                <span class="icon is-large has-text-info animated-money-icon">
                    <i class="fas fa-user-plus fa-3x"></i>
                </span>
                <h1 class="title is-3 has-text-black">
                    Buat Akun Baru
                </h1>
                <h2 class="subtitle is-6 has-text-grey">
                    Mulai perjalanan finansial Anda hari ini
                </h2>
            </div>

            <?php if ($error): ?>
                <div class="notification is-danger is-light"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label class="label">Username</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" name="username" placeholder="Contoh: userbaru" required>
                        <span class="icon is-small is-left"><i class="fas fa-user"></i></span>
                    </div>
                </div>
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
                        <input class="input" type="password" name="password" placeholder="Minimal 6 karakter" required>
                        <span class="icon is-small is-left"><i class="fas fa-lock"></i></span>
                    </div>
                </div>
                <div class="field mt-5">
                    <button type="submit" class="button is-info is-fullwidth is-large">
                        <span class="icon"><i class="fas fa-check-circle"></i></span>
                        <span>Register</span>
                    </button>
                </div>
                <p class="has-text-centered mt-4">Sudah punya akun? <a href="login.php" class="has-text-success">Login di sini</a></p>
            </form>
        </div>
    </div>
</section>
</body>
</html>