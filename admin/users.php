<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$pdo  = db();
$users = $pdo->query(
    'SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at,
            COUNT(b.id) AS booking_count
     FROM users u
     LEFT JOIN bookings b ON b.id_user = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC, u.id DESC'
)->fetchAll();

$roleLabel = ['admin' => 'Admin', 'user' => 'Member'];
$statusBadge = [
    'active'   => ['Aktif', 'badge-available'],
    'inactive' => ['Nonaktif', 'badge-inactive'],
];

$base      = '..';
$pageTitle = 'Kelola Pengguna - Admin VAYGOR';
require __DIR__ . '/../includes/head.php';
?>
<div class="admin-shell">

  <?php $adminNav = 'pengguna'; require __DIR__ . '/_sidebar.php'; ?>

  <main class="admin-main">
    <header class="admin-topbar">
      <div>
        <h1 class="admin-page-title">Kelola Pengguna</h1>
        <p class="admin-page-sub"><?= count($users) ?> pengguna terdaftar</p>
      </div>
      <div class="admin-top-date"><?= count($users) ?> akun</div>
    </header>

    <section class="admin-panel">
      <?php if (!$users): ?>
        <div class="admin-empty">Belum ada pengguna.</div>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Kontak</th>
                <th>Role</th>
                <th>Booking</th>
                <th>Status</th>
                <th>Terdaftar</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <?php [$stLabel, $stClass] = $statusBadge[$u['status']] ?? [$u['status'], 'badge-inactive']; ?>
                <tr>
                  <td>
                    <?= e($u['name']) ?>
                    <div class="admin-sub"><?= e($u['email']) ?></div>
                  </td>
                  <td class="admin-muted"><?= e($u['phone'] ?: '—') ?></td>
                  <td>
                    <span class="badge badge-<?= $u['role'] === 'admin' ? 'available' : 'pending' ?>">
                      <?= e($roleLabel[$u['role']] ?? $u['role']) ?>
                    </span>
                  </td>
                  <td class="admin-muted"><?= (int) $u['booking_count'] ?></td>
                  <td><span class="badge <?= e($stClass) ?>"><?= e($stLabel) ?></span></td>
                  <td class="admin-muted"><?= $u['created_at'] ? e(date('d M Y', strtotime($u['created_at']))) : '—' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

</div>
</body>
</html>