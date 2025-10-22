<?php
// install.php - run once to create DB and tables
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = 'root';
$DB_NAME = 'adept_play';

try{
    $pdo = new PDO("mysql:host={$DB_HOST};charset=utf8mb4", $DB_USER, $DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    // create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$DB_NAME}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$DB_NAME}`");

    // users table (with optional is_blocked field)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE,
        email VARCHAR(150) UNIQUE,
        password VARCHAR(255),
        wallet_balance DECIMAL(10,2) DEFAULT 0,
        is_blocked TINYINT(1) DEFAULT 0,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // admin table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE,
        password VARCHAR(255)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // tournaments
    $pdo->exec("CREATE TABLE IF NOT EXISTS tournaments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        game_name VARCHAR(150),
        entry_fee DECIMAL(10,2) DEFAULT 0,
        prize_pool DECIMAL(12,2) DEFAULT 0,
        match_time DATETIME,
        commission_percentage DECIMAL(5,2) DEFAULT 0,
        room_id VARCHAR(100),
        room_password VARCHAR(100),
        status VARCHAR(50) DEFAULT 'Upcoming',
        winner_user_id INT NULL,
        created_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // participants
    $pdo->exec("CREATE TABLE IF NOT EXISTS participants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        tournament_id INT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // transactions
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        amount DECIMAL(12,2),
        type VARCHAR(10),
        description VARCHAR(255),
        created_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insert default admin if not exists
    $stmt = $pdo->prepare('SELECT id FROM admin WHERE username = ?');
    $stmt->execute(['admin']);
    if(!$stmt->fetch()){
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admin (username,password) VALUES (?,?)');
        $stmt->execute(['admin',$hash]);
    }

    // Mark success and redirect to login
    header('Location: login.php');
    exit;

} catch(Exception $e){
    echo '<pre>Installation failed: '.htmlspecialchars($e->getMessage())."\n\n";
    echo 'Make sure DB credentials in install.php match your local MySQL.\n</pre>';
}
