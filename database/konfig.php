<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_futsal";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        throw new mysqli_sql_exception($conn->connect_error, $conn->connect_errno);
    }
} catch (Throwable $e) {
    error_log("DB connection failed: " . $e->getMessage());
    http_response_code(500);
    echo '<!doctype html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Maaf - VAYGOR</title><style>body{font-family:Inter,system-ui,sans-serif;background:#f6f8f5;color:#18181b;display:grid;place-items:center;min-height:100vh;margin:0;padding:24px} .card{background:#fff;border:1px solid #e4e4e7;border-radius:16px;padding:32px;max-width:480px;text-align:center} h1{font-family:League Spartan,sans-serif;font-size:22px;margin:0 0 8px} p{font-size:14px;color:#52525b;line-height:1.6} a{display:inline-flex;margin-top:16px;background:#1B703A;color:#fff;padding:10px 18px;border-radius:10px;text-decoration:none;font-weight:700;font-size:14px}</style></head><body><div class="card"><h1>Maaf, ada kendala koneksi</h1><p>Sistem sedang tidak dapat terhubung ke database. Coba lagi beberapa saat. Jika berlanjut, hubungi pengelola.</p><a href="index.php">Kembali ke beranda</a></div></body></html>';
    exit;
}
