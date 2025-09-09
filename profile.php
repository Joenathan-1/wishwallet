<?php
include 'includes/header.php';
include 'includes/sidebar.php';

// ... (Seluruh blok kode PHP Anda di bagian atas tetap sama, tidak perlu diubah) ...
$user_uuid = $_SESSION['user_uuid'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cropped_image'])) {
    $data = $_POST['cropped_image'];
    list($type, $data) = explode(';', $data);
    list(, $data)      = explode(',', $data);
    $data = base64_decode($data);
    $file_name = $user_uuid . '_' . uniqid() . '.png';
    $target_file = "assets/uploads/" . $file_name;

    if (file_put_contents($target_file, $data)) {
        $stmt_update = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE uuid = ?");
        if ($stmt_update->execute([$file_name, $user_uuid])) {
            $message = '<div class="notification is-success is-light">Foto profil berhasil diperbarui!</div>';
        }
    } else {
        $message = '<div class="notification is-danger is-light">Gagal menyimpan gambar.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $stmt_user = $pdo->prepare("SELECT username FROM users WHERE uuid = ?");
    $stmt_user->execute([$user_uuid]);
    $current_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $new_username = trim($_POST['username']);

    if ($new_username != $current_user['username']) {
        $stmt_check = $pdo->prepare("SELECT uuid FROM users WHERE username = ? AND uuid != ?");
        $stmt_check->execute([$new_username, $user_uuid]);
        if ($stmt_check->fetch()) {
            $message = '<div class="notification is-danger is-light">Username sudah digunakan!</div>';
        } else {
            $stmt_update = $pdo->prepare("UPDATE users SET username = ? WHERE uuid = ?");
            if ($stmt_update->execute([$new_username, $user_uuid])) {
                $_SESSION['username'] = $new_username;
                $message = '<div class="notification is-success is-light">Username berhasil diperbarui!</div>';
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT username, email, profile_picture FROM users WHERE uuid = ?");
$stmt->execute([$user_uuid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<style>
    /* CSS ini membuat kotak preview crop menjadi lingkaran */
    #crop-modal .cropper-view-box,
    #crop-modal .cropper-face {
        border-radius: 50%;
    }
    
    /* (Opsional) Menyembunyikan garis putus-putus default */
    #crop-modal .cropper-dashed {
        border: 0;
    }
</style>

<div class="level">
    <div class="level-left">
        <div>
            <h1 class="title">Profil Pengguna</h1>
            <h2 class="subtitle">Perbarui informasi dan foto profil Anda.</h2>
        </div>
    </div>
</div>
<hr>
<?= $message ?>

<div class="columns">
    <div class="column is-half">
        <div class="box">
            <h2 class="subtitle">Ganti Foto Profil</h2>
            <div class="has-text-centered mb-4">
                <figure class="image is-128x128 is-inline-block">
                    <?php
                    $profile_pic_path = 'assets/uploads/' . htmlspecialchars($user['profile_picture']);
                    if (!file_exists($profile_pic_path) || empty($user['profile_picture'])) {
                        $profile_pic_path = 'assets/uploads/default.png'; 
                    }
                    ?>
                    <img id="profile-image-display" class="is-rounded" src="<?= $profile_pic_path ?>" alt="Profile Picture">
                </figure>
            </div>
            <form id="crop-form" method="POST" action="profile.php">
                <input type="hidden" name="cropped_image" id="cropped_image_data">
            </form>
            <div class="file is-centered has-name">
                <label class="file-label">
                    <input class="file-input" type="file" name="profile_picture_input" id="profile_picture_input" accept="image/*">
                    <span class="file-cta">
                        <span class="file-icon"><i class="fas fa-upload"></i></span>
                        <span class="file-label">Pilih Gambar…</span>
                    </span>
                    <span class="file-name">Tidak ada file dipilih</span>
                </label>
            </div>
        </div>
    </div>

    <div class="column is-half">
        <div class="box">
            <h2 class="subtitle">Ganti Username</h2>
            <form method="POST" action="profile.php">
                <div class="field"><label class="label">Username</label><div class="control"><input class="input" type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"></div></div>
                <div class="field"><label class="label">Email</label><div class="control"><input class="input" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled></div></div>
                <button type="submit" class="button is-primary mt-4">Simpan Username</button>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="crop-modal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Potong Gambar Anda</p>
            <button class="delete" aria-label="close" id="close-modal-button"></button>
        </header>
        <section class="modal-card-body">
            <div style="max-height: 50vh;">
                <img id="image-to-crop" src="">
            </div>
        </section>
        <footer class="modal-card-foot is-justify-content-center">
            <button class="button is-success" id="crop-button">Potong & Simpan</button>
            <button class="button" id="cancel-crop-button">Batal</button>
        </footer>
    </div>
</div>

<script>
// --- FUNGSI BARU UNTUK MENGHASILKAN GAMBAR LINGKARAN ---
function getCroppedCircleCanvas(sourceCanvas) {
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    const width = sourceCanvas.width;
    const height = sourceCanvas.height;

    canvas.width = width;
    canvas.height = height;
    
    // Membuat path lingkaran
    context.beginPath();
    context.arc(width / 2, height / 2, Math.min(width, height) / 2, 0, 2 * Math.PI);
    context.closePath();
    
    // Menjadikan path lingkaran sebagai clipping mask
    context.clip();
    
    // Menggambar gambar hasil crop (yang berbentuk kotak) ke dalam canvas
    // Hanya bagian dalam lingkaran yang akan tergambar
    context.drawImage(sourceCanvas, 0, 0, width, height);

    return canvas;
}

document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('profile_picture_input');
    const fileNameSpan = document.querySelector('.file-name');
    const modal = document.getElementById('crop-modal');
    const imageToCrop = document.getElementById('image-to-crop');
    const cropButton = document.getElementById('crop-button');
    const closeModalButton = document.getElementById('close-modal-button');
    const cancelCropButton = document.getElementById('cancel-crop-button');
    const cropForm = document.getElementById('crop-form');
    const croppedImageDataInput = document.getElementById('cropped_image_data');

    let cropper;

    fileInput.onchange = (e) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            fileNameSpan.textContent = files[0].name;
            const reader = new FileReader();
            reader.onload = (event) => {
                imageToCrop.src = event.target.result;
                modal.classList.add('is-active');

                if (cropper) cropper.destroy();
                
                cropper = new Cropper(imageToCrop, {
                    aspectRatio: 1,
                    viewMode: 1,
                    background: false,
                    autoCropArea: 0.9,
                    // Tambahan: membuat preview crop lingkaran di dalam modal
                    preview: '.cropper-view-box' 
                });
            };
            reader.readAsDataURL(files[0]);
        }
    };

    const closeModal = () => {
        modal.classList.remove('is-active');
        fileInput.value = '';
        fileNameSpan.textContent = "Tidak ada file dipilih";
        if (cropper) cropper.destroy();
    };

    closeModalButton.addEventListener('click', closeModal);
    cancelCropButton.addEventListener('click', closeModal);

    cropButton.addEventListener('click', () => {
        if (cropper) {
            // Dapatkan hasil crop kotak dari Cropper.js
            const squareCanvas = cropper.getCroppedCanvas({
                width: 256,
                height: 256,
                imageSmoothingQuality: 'high',
            });

            // --- PERUBAHAN UTAMA: Gunakan fungsi baru kita ---
            // Ubah canvas kotak menjadi canvas lingkaran (dengan sudut transparan)
            const circleCanvas = getCroppedCircleCanvas(squareCanvas);
            
            // Ubah canvas lingkaran menjadi data URL base64
            const croppedImageDataURL = circleCanvas.toDataURL('image/png');
            
            croppedImageDataInput.value = croppedImageDataURL;
            cropForm.submit();
        }
    });
});
</script>

