<?php
include 'includes/header.php';
include 'includes/sidebar.php';

// PERUBAHAN 1: Gunakan 'user_uuid' dari session
$user_uuid = $_SESSION['user_uuid'];
$message = '';

// PERUBAHAN 2: Logika INSERT diperbarui untuk UUID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_goal'])) {
    $goal_name = $_POST['goal_name'];
    $target_amount = $_POST['target_amount'];
    if (!empty($goal_name) && !empty($target_amount)) {
        $new_goal_uuid = generate_uuid(); // Buat UUID baru untuk goal
        $stmt = $pdo->prepare("INSERT INTO savings_goals (uuid, user_uuid, goal_name, target_amount) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$new_goal_uuid, $user_uuid, $goal_name, $target_amount])) {
            $message = '<div class="notification is-success is-light">Tujuan tabungan berhasil ditambahkan!</div>';
        }
    }
}

// PERUBAHAN 3: Logika DELETE diperbarui untuk UUID
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['uuid'])) {
    $goal_uuid_to_delete = $_GET['uuid'];
    $stmt = $pdo->prepare("DELETE FROM savings_goals WHERE uuid = ? AND user_uuid = ?");
    if ($stmt->execute([$goal_uuid_to_delete, $user_uuid])) {
        $message = '<div class="notification is-info is-light">Tujuan tabungan berhasil dihapus.</div>';
    }
}

// PERUBAHAN 4: Perbarui query SELECT untuk total tabungan
$stmt = $pdo->prepare("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as total_savings FROM transactions WHERE user_uuid = ?");
$stmt->execute([$user_uuid]);
$current_savings = $stmt->fetchColumn() ?? 0;

// PERUBAHAN 5: Perbarui query SELECT untuk daftar goals
$stmt = $pdo->prepare("SELECT * FROM savings_goals WHERE user_uuid = ?");
$stmt->execute([$user_uuid]);
$goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="level">
    <div class="level-left">
        <div>
            <h1 class="title">Tujuan Tabungan (Savings Goals)</h1>
            <h2 class="subtitle">Total tabungan Anda saat ini: <strong class="has-text-success">Rp <?= number_format($current_savings, 0, ',', '.') ?></strong></h2>
        </div>
    </div>
</div>
<hr>
<?= $message ?>

<div class="columns">
    <div class="column is-two-thirds">
         <div class="box">
            <h2 class="subtitle">Daftar Tujuan Anda</h2>
            <?php if (empty($goals)): ?>
                <p>Anda belum memiliki tujuan tabungan.</p>
            <?php else: ?>
                <?php foreach ($goals as $goal):
                    $progress_percentage = $goal['target_amount'] > 0 ? ($current_savings / $goal['target_amount']) * 100 : 0;
                    if ($progress_percentage > 100) $progress_percentage = 100;
                    $is_achieved = $current_savings >= $goal['target_amount'];
                ?>
                    <div class="mb-5 p-4" style="border: 1px solid #dbdbdb; border-radius: 6px;">
                        <div class="level">
                            <div class="level-left"><p class="is-size-5"><strong><?= htmlspecialchars($goal['goal_name']) ?></strong></p></div>
                            <div class="level-right">
                                <?php if ($is_achieved): ?>
                                    <span class="tag is-success is-large"><span class="icon mr-2"><i class="fas fa-check-circle"></i></span>Tercapai!</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <progress class="progress <?= $is_achieved ? 'is-success' : 'is-primary' ?>" value="<?= $current_savings ?>" max="<?= $goal['target_amount'] ?>"></progress>
                        <p>Terkumpul Rp <?= number_format($current_savings, 0, ',', '.') ?> dari Target Rp <?= number_format($goal['target_amount'], 0, ',', '.') ?></p>
                        <!-- PERUBAHAN 6: Link Hapus menggunakan UUID -->
                        <a href="goals.php?action=delete&uuid=<?= $goal['uuid'] ?>" class="is-size-7 has-text-danger" onclick="return confirm('Yakin hapus goal ini?')">Hapus Goal</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
         </div>
    </div>
    <div class="column">
        <div class="box">
            <h2 class="subtitle">Tambah Tujuan Baru</h2>
            <form method="POST">
                <div class="field"><label class="label">Nama Tujuan</label><div class="control"><input class="input" type="text" name="goal_name" required></div></div>
                <div class="field"><label class="label">Target (Rp)</label><div class="control"><input class="input" type="number" step="1000" name="target_amount" required></div></div>
                <div class="field"><button type="submit" name="add_goal" class="button is-primary is-fullwidth">Tambah</button></div>
            </form>
        </div>
    </div>
</div>

