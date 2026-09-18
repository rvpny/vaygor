<?php
/**
 * Isi password seed untuk user contoh (kolom password di dump masih placeholder).
 * Jalankan: php database/seed_passwords.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../config/database.php';

$accounts = [
    'admin@futsalmagelang.com' => 'admin123',
    'rava@example.com'         => 'user123',
];

$pdo  = db();
$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');

foreach ($accounts as $email => $plain) {
    $stmt->execute([password_hash($plain, PASSWORD_DEFAULT), $email]);
    echo $stmt->rowCount() . " user diupdate: {$email}\n";
}

echo "Selesai.\n";
