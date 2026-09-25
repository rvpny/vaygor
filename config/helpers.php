<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=db_futsal;charset=utf8mb4',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    $token = (string) ($_POST['_csrf'] ?? '');
    if ($token === '' || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Sesi tidak valid. Silakan muat ulang halaman.');
    }
}

function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'][$type] = $msg;
}

function flash_get(string $type): string
{
    $msg = $_SESSION['flash'][$type] ?? '';
    unset($_SESSION['flash'][$type]);
    return $msg;
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function current_user(): array
{
    static $user = null;
    $uid = (int) ($_SESSION['id'] ?? 0);
    if ($uid < 1) {
        return [];
    }
    if ($user === null) {
        try {
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$uid]);
            $user = $stmt->fetch() ?: [];
        } catch (Throwable $e) {
            $user = [];
        }
    }
    return $user;
}

function require_login(?string $redirect = null): void
{
    if (!empty($_SESSION['id'])) {
        return;
    }
    $to = $redirect;
    if ($to === null || $to === '') {
        $to = $_SERVER['REQUEST_URI'] ?? 'index.php';
    }
    header('Location: login.php?redirect=' . urlencode($to));
    exit;
}

function require_role(string $role): void
{
    $uid = (int) ($_SESSION['id'] ?? 0);
    $r   = (string) ($_SESSION['role'] ?? '');

    if ($uid < 1) {
        header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
        exit;
    }
    if ($r !== $role) {
        header('Location: ' . ($role === 'admin' ? '../index.php' : '/'));
        exit;
    }
}