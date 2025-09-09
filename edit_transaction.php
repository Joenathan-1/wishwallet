<?php
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

if ($transaction_id <= 0) { header("Location: budgeting.php"); exit(); }

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$transaction_id, $user_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) { header("Location: budgeting.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $date = $_POST['date'];

    if (!empty($type) && !empty($amount) && !empty($date)) {
        $update_stmt = $pdo->prepare("UPDATE transactions SET type = ?, amount = ?, description = ?, category_id = ?, transaction_date = ? WHERE id = ? AND user_id = ?");
        if ($update_stmt->execute([$type, $amount, $description, $category_id, $date, $transaction_id, $user_id])) {
            header("Location: budgeting.php?status=updated");
            exit();
        } else {
            $message = '<div class="notification is-danger">Gagal memperbarui transaksi.</div>';
        }
    }
}

$stmt_cat = $pdo->prepare("SELECT id, name, type FROM categories WHERE user_id = ? ORDER BY name");
$stmt_cat->execute([$user_id]);
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
$income_cats = array_filter($categories, fn($cat) => $cat['type'] == 'income');
$expense_cats = array_filter($categories, fn($cat) => $cat['type'] == 'expense');
?>

<section class="section">
    <h1 class="title">Edit Transaksi</h1>
    <?= $message ?>
    <div class="box">
        <form method="POST">
             <div class="field"><label class="label">Tipe</label><div class="control"><div class="select is-fullwidth"><select name="type" required><option value="income" <?= ($transaction['type'] == 'income') ? 'selected' : '' ?>>Pemasukan</option><option value="expense" <?= ($transaction['type'] == 'expense') ? 'selected' : '' ?>>Pengeluaran</option></select></div></div></div>
             <div class="field"><label class="label">Jumlah (Rp)</label><div class="control"><input class="input" type="number" step="0.01" name="amount" value="<?= htmlspecialchars($transaction['amount']) ?>" required></div></div>
             <div class="field"><label class="label">Tanggal</label><div class="control"><input class="input" type="date" name="date" value="<?= htmlspecialchars($transaction['transaction_date']) ?>" required></div></div>
             <div class="field"><label class="label">Deskripsi</label><div class="control"><textarea class="textarea" name="description"><?= htmlspecialchars($transaction['description']) ?></textarea></div></div>
             <div class="field"><label class="label">Kategori</label><div class="control"><div class="select is-fullwidth">
                 <select name="category_id">
                     <option value="">-- Pilih Kategori --</option>
                     <optgroup label="Pemasukan"><?php foreach($income_cats as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($transaction['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></optgroup>
                     <optgroup label="Pengeluaran"><?php foreach($expense_cats as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($transaction['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></optgroup>
                 </select>
             </div></div></div>
            <div class="field is-grouped"><div class="control"><button type="submit" class="button is-primary">Simpan</button></div><div class="control"><a href="budgeting.php" class="button is-light">Batal</a></div></div>
        </form>
    </div>
</section>

<?php // Penutup dari sidebar.php ?>
        </div>
    </div>
</div>
</body>
</html>