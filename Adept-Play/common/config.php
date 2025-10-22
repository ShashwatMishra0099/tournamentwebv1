<?php
// common/config.php
// DB config and app-wide helpers
session_start();

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = 'root';
$DB_NAME = 'adept_play';

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    // If DB doesn't exist, we'll handle in install.php. Show friendly error for other pages.
    $pdo = null;
}

function rupee($n){
    return '₹'.number_format((float)$n,2);
}

function is_logged(){
    return isset($_SESSION['user_id']);
}

function current_user($pdo){
    if(!is_logged()) return null;
    $stmt = $pdo->prepare("SELECT id, username, email, wallet_balance, is_blocked FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function is_admin_logged(){
    return isset($_SESSION['admin_id']);
}

?>
