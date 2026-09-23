<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Cegah akses halaman yang butuh login.
 * Kalau belum login, arahkan ke login.php beserta redirect balik.
 */
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