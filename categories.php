<?php
include 'includes/header.php';
include 'includes/sidebar.php';

// PERUBAHAN 1: Gunakan 'user_uuid' dari session
$user_uuid = $_SESSION['user_uuid'];
$message = '';
$editing_category = null;

// PERUBAHAN 2: Logika INSERT dan UPDATE diperbarui untuk UUID
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $category_uuid = isset($_POST['category_uuid']) ? $_POST['category_uuid'] : '';

    if (!empty($name) && !empty($type)) {
        if (!empty($category_uuid)) { // Proses Update
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, type = ? WHERE uuid = ? AND user_uuid = ?");
            if ($stmt->execute([$name, $type, $category_uuid, $user_uuid])) {
                $message = '<div class="notification is-success is-light">Kategori berhasil diperbarui!</div>';
            }
        } else { // Proses Insert
            $new_category_uuid = generate_uuid(); // Buat UUID baru
            $stmt = $pdo->prepare("INSERT INTO categories (uuid, user_uuid, name, type) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$new_category_uuid, $user_uuid, $name, $type])) {
                $message = '<div class="notification is-success is-light">Kategori berhasil ditambahkan!</div>';
            }
        }
    }
}

// PERUBAHAN 3: Logika DELETE dan EDIT diperbarui untuk UUID
if (isset($_GET['action']) && isset($_GET['uuid'])) {
    $uuid = $_GET['uuid'];

    if ($_GET['action'] == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE uuid = ? AND user_uuid = ?");
        if ($stmt->execute([$uuid, $user_uuid])) {
            $message = '<div class="notification is-info is-light">Kategori berhasil dihapus.</div>';
        }
    }

    if ($_GET['action'] == 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE uuid = ? AND user_uuid = ?");
        $stmt->execute([$uuid, $user_uuid]);
        $editing_category = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// PERUBAHAN 4: Perbarui query SELECT untuk menampilkan daftar
$stmt_income = $pdo->prepare("SELECT * FROM categories WHERE user_uuid = ? AND type = 'income' ORDER BY name");
$stmt_income->execute([$user_uuid]);
$income_categories = $stmt_income->fetchAll(PDO::FETCH_ASSOC);

$stmt_expense = $pdo->prepare("SELECT * FROM categories WHERE user_uuid = ? AND type = 'expense' ORDER BY name");
$stmt_expense->execute([$user_uuid]);
$expense_categories = $stmt_expense->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="level">
    <div class="level-left">
        <div>
            <h1 class="title">Manajemen Kategori</h1>
            <h2 class="subtitle">Kelola kategori pemasukan dan pengeluaran Anda.</h2>
        </div>
    </div>
</div>
<hr>
<?= $message ?>

<div class="columns">
    <div class="column is-half">
        <div class="box">
            <h2 class="subtitle"><?= $editing_category ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h2>
            <form method="POST" action="categories.php">
                <input type="hidden" name="category_uuid" value="<?= $editing_category['uuid'] ?? '' ?>">
                <div class="field"><label class="label">Nama Kategori</label><div class="control"><input class="input" type="text" name="name" placeholder="Contoh: Transportasi" value="<?= htmlspecialchars($editing_category['name'] ?? '') ?>" required></div></div>
                <div class="field"><label class="label">Tipe Kategori</label><div class="control"><div class="select is-fullwidth"><select name="type" required><option value="expense" <?= (isset($editing_category['type']) && $editing_category['type'] == 'expense') ? 'selected' : '' ?>>Pengeluaran</option><option value="income" <?= (isset($editing_category['type']) && $editing_category['type'] == 'income') ? 'selected' : '' ?>>Pemasukan</option></select></div></div></div>
                <div class="field is-grouped">
                    <div class="control"><button type="submit" class="button is-primary"><?= $editing_category ? 'Simpan Perubahan' : 'Tambah Kategori' ?></button></div>
                    <?php if ($editing_category): ?>
                    <div class="control"><a href="categories.php" class="button is-light">Batal Edit</a></div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="column is-half">
        <div class="box">
            <h2 class="subtitle">Daftar Kategori Anda</h2>
            <h3 class="title is-6 has-text-success">Pemasukan</h3>
            <table class="table is-fullwidth is-striped is-narrow"><tbody>
                <?php foreach($income_categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td class="has-text-right">
                        <a href="categories.php?action=edit&uuid=<?= $cat['uuid'] ?>" class="button is-small is-warning">Edit</a>
                        <a href="categories.php?action=delete&uuid=<?= $cat['uuid'] ?>" class="button is-small is-danger" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody></table>
            <hr>
            <h3 class="title is-6 has-text-danger">Pengeluaran</h3>
            <table class="table is-fullwidth is-striped is-narrow"><tbody>
                <?php foreach($expense_categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td class="has-text-right">
                        <a href="categories.php?action=edit&uuid=<?= $cat['uuid'] ?>" class="button is-small is-warning">Edit</a>
                        <a href="categories.php?action=delete&uuid=<?= $cat['uuid'] ?>" class="button is-small is-danger" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
</div>

