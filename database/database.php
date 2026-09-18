<?php
/**
 * Konfigurasi & Koneksi Database MySQL
 * Database: db_futsal
 */

// Pengaturan Konfigurasi Database (Default Laragon / XAMPP)
$host     = 'localhost';
$username = 'root';
$password = '';
$database = 'db_futsal';

// ==========================================
// 1. Koneksi menggunakan MySQLi (Obyek & Prosedural)
// ==========================================
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi mysqli
if (!$conn) {
    die("Koneksi database gagal (MySQLi): " . mysqli_connect_error());
}

// Set karakter encoding ke utf8mb4
mysqli_set_charset($conn, "utf8mb4");


// ==========================================
// 2. Koneksi menggunakan PDO (Opsional / Modern)
// ==========================================
try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Koneksi database gagal (PDO): " . $e->getMessage());
}
