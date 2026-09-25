<?php
include __DIR__ . '/database/konfig.php';
session_start();
$id = $_SESSION['id'] ?? '';
$_SESSION['status'] = !empty($id);

$role = $_SESSION['role'] ?? '';

// Admin: langsung ke panel admin (sidebar). User: front-end (navbar).
// Ini pembagian bersih kedua layout yang disatukan lewat satu entry point.
if ($role === 'admin') {
    header('Location: admin/index.php');
    exit;
}

$p = $_GET['p'] ?? 'beranda';
$dir = 'pages/' . $p . '.php';

// Halaman yang butuh login: cek sebelum ada output supaya redirect berfungsi.
$protectedPages = ['booking', 'pesanan', 'review'];
if (in_array($p, $protectedPages, true) && empty($id)) {
    $req = $_SERVER['REQUEST_URI'] ?? 'index.php';
    header('Location: login.php?redirect=' . urlencode($req));
    exit;
}

// Navbar: tembus pandang hanya di halaman yang punya hero gelap di atas (beranda & browse).
$navForceSolid = !in_array($p, ['beranda', 'browse'], true);
if (!file_exists($dir)) {
    $p = '404';
}

$base      = '';
$pageTitle = 'VAYGOR - Rent. Play. Win';
require __DIR__ . '/includes/head.php';
?>

  <?php include 'includes/navbar.php'; ?>

  <?php if ($p === '404'): ?>
    <div class="pt-28 pb-12 text-center">
      <h1 class="font-spartan text-4xl font-extrabold text-vaygor-700">404</h1>
      <p class="mt-2 text-sm text-gray-500">Halaman tidak ditemukan.</p>
      <a href="index.php" class="mt-6 inline-flex rounded-xl bg-vaygor-600 px-6 py-3 text-sm font-bold text-white hover:bg-vaygor-700">Kembali ke Beranda</a>
    </div>
  <?php elseif (file_exists($dir)): ?>
    <?php include $dir; ?>
  <?php else: ?>
    <?php include 'pages/beranda.php'; ?>
  <?php endif; ?>

  <?php include 'includes/footer.php'; ?>

</body>

</html>