<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$pdo  = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name  = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $pass  = (string) ($_POST['pass'] ?? '');
    $role  = (string) ($_POST['role'] ?? '');
    $errs  = [];

    if ($name === '') $errs[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'Format email tidak valid.';
    if (strlen($pass) < 8) $errs[] = 'Kata sandi minimal 8 karakter.';
    if (!in_array($role, ['admin', 'user'], true)) $errs[] = 'Role tidak valid.';

    if (!$errs) {
        $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $dup->execute([$email]);
        if ($dup->fetch()) $errs[] = 'Email sudah terdaftar.';
    }

    if ($errs) {
        flash_set('err', implode(' ', $errs));
    } else {
        $ins = $pdo->prepare(
            'INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $ins->execute([
            $name,
            $email,
            $phone !== '' ? $phone : null,
            password_hash($pass, PASSWORD_DEFAULT),
            $role,
            'active',
        ]);
        flash_set('ok', 'Pengguna "' . $name . '" ditambahkan sebagai ' . ($role === 'admin' ? 'Admin' : 'Member') . '.');
        redirect('users.php');
    }
}

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

    <?php if ($ok = flash_get('ok')): ?>
      <div class="admin-flash is-ok"><?= e($ok) ?></div>
    <?php endif; ?>
    <?php if ($err = flash_get('err')): ?>
      <div class="admin-flash is-err"><?= e($err) ?></div>
    <?php endif; ?>

    <section class="admin-panel">
      <form class="admin-form" method="post">
        <?= csrf_field() ?>
        <div class="admin-form-grid">
          <div class="admin-field">
            <label for="u-name">Nama lengkap</label>
            <input class="admin-input" id="u-name" type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
          </div>
          <div class="admin-field">
            <label for="u-email">Email</label>
            <input class="admin-input" id="u-email" type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="admin-field">
            <label for="u-phone">Telepon</label>
            <input class="admin-input" id="u-phone" type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx">
          </div>
          <div class="admin-field">
            <label for="u-pass">Kata sandi (min 8 karakter)</label>
            <input class="admin-input" id="u-pass" type="password" name="pass" minlength="8" autocomplete="new-password" required>
          </div>
          <div class="admin-field">
            <label for="u-role">Role</label>
            <select class="admin-select" id="u-role" name="role">
              <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>Member</option>
              <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>
        </div>
        <div class="admin-form-actions">
          <button class="admin-btn" type="submit">Tambahkan</button>
        </div>
      </form>
    </section>

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