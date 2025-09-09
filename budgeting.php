<?php
include 'includes/header.php';
include 'includes/sidebar.php';

// PERUBAHAN 1: Gunakan 'user_uuid' dari session
$user_uuid = $_SESSION['user_uuid'];
$message = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'updated') $message = '<div class="notification is-success is-light">Transaksi berhasil diperbarui!</div>';
    if ($_GET['status'] == 'deleted') $message = '<div class="notification is-success is-light">Transaksi berhasil dihapus!</div>';
}

// PERUBAHAN 2: Logika INSERT diperbarui untuk UUID
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $date = $_POST['date'];

    if (!empty($type) && !empty($amount) && !empty($date)) {
        // Buat UUID baru untuk setiap transaksi
        $new_transaction_uuid = generate_uuid(); 

        // Query INSERT sekarang menggunakan uuid dan user_uuid
        $stmt = $pdo->prepare(
            "INSERT INTO transactions (uuid, user_uuid, type, amount, description, category_id, transaction_date) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        if ($stmt->execute([$new_transaction_uuid, $user_uuid, $type, $amount, $description, $category_id, $date])) {
            $message = '<div class="notification is-success is-light">Transaksi berhasil ditambahkan!</div>';
        } else {
            $message = '<div class="notification is-danger is-light">Gagal menambahkan transaksi.</div>';
        }
    } else {
        $message = '<div class="notification is-warning is-light">Mohon lengkapi semua field yang wajib.</div>';
    }
}

// PERUBAHAN 3: Perbarui query SELECT untuk kategori
$stmt_cat = $pdo->prepare("SELECT id, name, type FROM categories WHERE user_uuid = ? ORDER BY name");
$stmt_cat->execute([$user_uuid]);
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
$income_cats = array_filter($categories, fn($cat) => $cat['type'] == 'income');
$expense_cats = array_filter($categories, fn($cat) => $cat['type'] == 'expense');

// PERUBAHAN 4: Perbarui query SELECT untuk riwayat transaksi
$stmt_trans = $pdo->prepare(
    "SELECT t.*, c.name AS category_name FROM transactions t 
     LEFT JOIN categories c ON t.category_id = c.id 
     WHERE t.user_uuid = ? 
     ORDER BY t.transaction_date DESC, t.id DESC LIMIT 10"
);
$stmt_trans->execute([$user_uuid]);
$recent_transactions = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section">
    <h1 class="title">Manajemen Budgeting</h1>
    <?= $message ?>
    
    <div class="box">
        <h2 class="subtitle">Tambah Transaksi Baru</h2>
        <form method="POST" action="budgeting.php">
              <div class="field is-horizontal">
                  <div class="field-body">
                      <div class="field"><label class="label">Tipe</label><div class="control"><div class="select is-fullwidth"><select name="type" required><option value="expense">Pengeluaran</option><option value="income">Pemasukan</option></select></div></div></div>
                      <div class="field"><label class="label">Jumlah (Rp)</label><div class="control"><input class="input" type="number" step="0.01" name="amount" placeholder="50000" required></div></div>
                      <div class="field"><label class="label">Tanggal</label><div class="control"><input class="input" type="date" name="date" value="<?= date('Y-m-d') ?>" required></div></div>
                  </div>
              </div>
              <div class="field"><label class="label">Deskripsi</label><div class="control"><textarea class="textarea" name="description" placeholder="Contoh: Beli Kopi"></textarea></div></div>
              <div class="field">
                  <label class="label">Kategori (Opsional)</label>
                  <div class="control"><div class="select is-fullwidth">
                      <select name="category_id">
                          <option value="">-- Pilih Kategori --</option>
                          <optgroup label="Pemasukan"><?php foreach($income_cats as $cat): ?><option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></optgroup>
                          <optgroup label="Pengeluaran"><?php foreach($expense_cats as $cat): ?><option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></optgroup>
                      </select>
                  </div></div>
              </div>
              <div class="field"><button type="submit" class="button is-primary">Simpan Transaksi</button></div>
        </form>
    </div>

    <div class="box">
        <h2 class="subtitle">10 Transaksi Terakhir</h2>
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead><tr><th>Tanggal</th><th>Tipe</th><th>Deskripsi & Kategori</th><th class="has-text-right">Jumlah</th><th class="has-text-centered">Aksi</th></tr></thead>
                <tbody>
                    <?php if (empty($recent_transactions)): ?>
                        <tr><td colspan="5" class="has-text-centered">Belum ada transaksi.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_transactions as $trans): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($trans['transaction_date'])) ?></td>
                            <td><span class="tag <?= $trans['type'] == 'income' ? 'is-success' : 'is-danger' ?>"><?= ucfirst($trans['type']) ?></span></td>
                            <td><?= htmlspecialchars($trans['description']) ?><?php if ($trans['category_name']): ?><br><span class="tag is-info is-light mt-1"><?= htmlspecialchars($trans['category_name']) ?></span><?php endif; ?></td>
                            <td class="has-text-right">Rp <?= number_format($trans['amount'], 2, ',', '.') ?></td>
                            <td class="has-text-centered"><div class="buttons are-small is-centered">
                                <!-- PERUBAHAN 5: Link Edit & Hapus sekarang menggunakan UUID -->
                                <a href="edit_transaction.php?uuid=<?= $trans['uuid'] ?>" class="button is-warning"><span>Edit</span></a>
                                <a href="delete_transaction.php?uuid=<?= $trans['uuid'] ?>" class="button is-danger" onclick="return confirm('Anda yakin?');"><span>Hapus</span></a>
                            </div></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

