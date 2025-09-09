<?php
require_once 'config/database.php';
check_login();

$user_id = $_SESSION['user_id'];
$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($transaction_id > 0) {
    $stmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transaction_id, $user_id]);
    if ($stmt->fetch()) {
        $delete_stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
        $delete_stmt->execute([$transaction_id]);
    }
}
header("Location: budgeting.php?status=deleted");
exit();
?>