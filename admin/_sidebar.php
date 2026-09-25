<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$navLoggedIn = !empty($_SESSION['id']);
$navBase     = $base ?? '';

$navUser = null;
$navUid  = (int) ($_SESSION['id'] ?? 0);
if ($navUid > 0) {
    if (!empty($GLOBALS['conn'])) {
        $navRes = mysqli_query($GLOBALS['conn'], 'SELECT name, email FROM users WHERE id = ' . $navUid . ' LIMIT 1');
        $navUser = $navRes ? mysqli_fetch_assoc($navRes) : null;
    } elseif (function_exists('db')) {
        try {
            $navStmt = db()->prepare('SELECT name, email FROM users WHERE id = ? LIMIT 1');
            $navStmt->execute([$navUid]);
            $navUser = $navStmt->fetch() ?: null;
        } catch (Throwable $e) {
            $navUser = null;
        }
    }
}

$navName     = $navUser['name'] ?? ($_SESSION['name'] ?? 'Admin');
$navEmail    = $navUser['email'] ?? '';
$navInitial  = mb_strtoupper(mb_substr(trim((string) $navName), 0, 1));
$navActive   = $adminNav ?? '';

$navItems = [
    ['dashboard',  'index.php',     'Dashboard',   'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
    ['booking',    'bookings.php',  'Booking',     'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z'],
    ['lapangan',   'fields.php',    'Lapangan',    'M12 3 2 8v2h20V8L12 3zM4 12v7h3v-7H4zm6 0v7h4v-7h-4zm7 0v7h3v-7h-3zM2 21h20v2H2v-2z'],
    ['kategori',   'categories.php','Kategori',    'M2 7h8V3H2v4zm0 14h8v-4H2v4zm10 0h8v-4h-8v4zM12 3v4h8V3h-8z'],
    ['pengguna',   'users.php',     'Pengguna',    'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'],
    ['pembayaran', 'payments.php',  'Pembayaran',  'M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z'],
];
?>
<button id="adminMenuBtn" class="admin-menu-btn" type="button" aria-label="Buka menu">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
    <path d="M4 6h16M4 12h16M4 18h16" />
  </svg>
  Menu
</button>

<div id="adminScrim" class="admin-scrim" aria-hidden="true"></div>

<aside id="adminSide" class="admin-side" aria-label="Menu admin">
  <a class="admin-brand" href="<?= e($navBase) ?>/index.php">
    <img src="<?= e($navBase) ?>/assets/images/logo(1).svg" alt="VAYGOR">
    <button id="adminSideClose" class="admin-side-close" type="button" aria-label="Tutup menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <path d="M6 6l12 12M18 6L6 18" />
      </svg>
    </button>
  </a>

  <nav class="admin-nav">
    <?php foreach ($navItems as [$key, $href, $label, $icon]): ?>
      <a class="admin-nav-item my-1<?= $navActive === $key ? ' active' : '' ?>" href="<?= e($href) ?>"<?= $navActive === $key ? ' aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $icon ?>"/></svg>
        <span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="admin-side-foot">
    <div class="admin-user">
      <span class="admin-avatar" aria-hidden="true"><?= e($navInitial) ?></span>
      <span class="admin-user-text">
        <?= e($navName) ?>
        <small><?= e($navEmail) ?></small>
      </span>
    </div>
    <a class="admin-logout" href="<?= e($navBase) ?>/logout.php">Keluar</a>
  </div>
</aside>

<script>
(function () {
  var side    = document.getElementById('adminSide');
  var scrim   = document.getElementById('adminScrim');
  var openBtn = document.getElementById('adminMenuBtn');
  var closeBtn = document.getElementById('adminSideClose');

  function open() {
    if (!side) return;
    side.classList.add('open');
    if (scrim) scrim.classList.add('is-visible');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    if (side) side.classList.remove('open');
    if (scrim) scrim.classList.remove('is-visible');
    document.body.style.overflow = '';
  }

  if (openBtn) openBtn.addEventListener('click', open);
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (scrim) scrim.addEventListener('click', close);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  window.addEventListener('resize', function () { if (window.innerWidth >= 1024) close(); });
})();
</script>