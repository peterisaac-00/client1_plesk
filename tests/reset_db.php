<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=document_verify;charset=utf8mb4', 'root', 'root123', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach (['documents', 'clients', 'verification_logs', 'login_attempts'] as $t) {
    $pdo->exec("TRUNCATE TABLE `$t`");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
$adminHash = '$2y$10$kDpld1j6e.MQvoTcjzqCJug38JNIpFP1BWvaVnuhXTrhckgPpvY5y';
$stmt = $pdo->prepare('UPDATE users SET password = ?, must_change_password = 1 WHERE id = 1');
$stmt->execute([$adminHash]);
echo "DB reset OK\n";