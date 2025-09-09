<?php
// WAJIB: Tiga baris ini akan menampilkan error yang tersembunyi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db_name = 'db_budgeting';
$username = 'root';
$password = 'root'; // Sesuaikan dengan password database Anda

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Fungsi untuk memeriksa apakah user sudah login
function check_login() {
    // PERUBAHAN: Sekarang memeriksa 'user_uuid' bukan 'user_id'
    if (!isset($_SESSION['user_uuid'])) {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk membuat UUID baru
function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
?>