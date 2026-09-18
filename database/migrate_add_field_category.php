<?php
/**
 * Migrasi: tambah kolom `category` (cabang olahraga) di tabel `fields`.
 * Jalankan: php database/migrate_add_field_category.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../config/database.php';

$pdo = db();

$exists = (int) $pdo->query(
    "SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fields' AND COLUMN_NAME = 'category'"
)->fetchColumn();

if ($exists === 0) {
    $pdo->exec("ALTER TABLE fields ADD COLUMN category VARCHAR(50) NULL AFTER field_type, ADD KEY category (category)");
    echo "Kolom `category` ditambahkan.\n";
} else {
    echo "Kolom `category` sudah ada, dilewati.\n";
}

$stmt = $pdo->prepare("UPDATE fields SET category = ? WHERE name LIKE ? AND (category IS NULL OR category = '')");
$stmt->execute(['futsal', '%Pancuranmas%']);
echo $stmt->rowCount() . " lapangan diset kategori futsal.\n";

foreach ($pdo->query('SELECT id, name, field_type, category FROM fields')->fetchAll() as $f) {
    echo "- #{$f['id']} {$f['name']} [{$f['field_type']}] kategori: " . ($f['category'] ?? '-') . "\n";
}
