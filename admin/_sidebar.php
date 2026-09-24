<?php
$navUser = current_user();
$active  = $adminNav ?? '';
$navInitial = mb_strtoupper(mb_substr(trim((string) ($navUser['name'] ?? 'A')), 0, 1));
$navItems = [
    ['dashboard', 'index.php',      'Dashboard',   'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
    ['booking',   'bookings.php',   'Booking',     'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z'],
    ['lapangan',  'fields.php',     'Lapangan',    'M12 3 2 8v2h20V8L12 3zM4 12v7h3v-7H4zm6 0v7h4v-7h-4zm7 0v7h3v-7h-3zM2 21h20v2H2v-2z'],
    ['kategori',  'categories.php', 'Kategori',    'M2 7h8V3H2v4zm0 14h8v-4H2v4zm10 0h8v-4h-8v4zM12 3v4h8V3h-8z'],
    ['pengguna',  'users.php',      'Pengguna',    'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'],
    ['pembayaran','payments.php',   'Pembayaran',  'M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z'],
];
?>
<aside class="admin-side">
  <a class="admin-brand" href="index.php">
    <img src="../assets/images/logo(1).svg" alt="VAYGOR">
  </a>

  <nav class="admin-nav" aria-label="Menu admin">
    <?php foreach ($navItems as [$key, $href, $label, $icon]): ?>
      <a class="admin-nav-item<?= $active === $key ? ' active' : '' ?>" href="<?= e($href) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $icon ?>"/></svg>
        <span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="admin-side-foot">
    <div class="admin-user">
      <span class="admin-avatar" aria-hidden="true"><?= e($navInitial) ?></span>
      <span class="admin-user-text">
        <?= e($navUser['name']) ?>
        <small><?= e($navUser['email']) ?></small>
      </span>
    </div>
    <form method="post" action="../auth/logout.php">
      <?= csrf_field() ?>
      <button class="admin-logout" type="submit">Logout</button>
    </form>
  </div>
</aside>
