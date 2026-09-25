<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$pdo = db();

$showAddForm = false;
$addErrors   = [];
$oldForm     = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $op = (string) ($_POST['op'] ?? '');

    if ($op === 'add_admin') {
        $oldForm = [
            'name'  => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
        ];
        $pass = (string) ($_POST['password'] ?? '');
        $showAddForm = true;

        if ($oldForm['name'] === '') {
            $addErrors[] = 'Nama admin wajib diisi.';
        }
        if ($oldForm['email'] === '') {
            $addErrors[] = 'Email wajib diisi.';
        } elseif (!filter_var($oldForm['email'], FILTER_VALIDATE_EMAIL)) {
            $addErrors[] = 'Format email tidak valid.';
        }
        if (strlen($pass) < 8) {
            $addErrors[] = 'Kata sandi minimal 8 karakter.';
        }

        if (!$addErrors) {
            $exists = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $exists->execute([$oldForm['email']]);
            if ($exists->fetchColumn() !== false) {
                $addErrors[] = 'Email sudah terdaftar. Gunakan email lain.';
            }
        }

        if (!$addErrors) {
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $oldForm['name'],
                $oldForm['email'],
                $oldForm['phone'] !== '' ? $oldForm['phone'] : null,
                password_hash($pass, PASSWORD_DEFAULT),
                'admin',
                'active',
            ]);
            flash_set('ok', 'Admin baru berhasil ditambahkan.');
            redirect('users.php');
        }
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

$base       = '..';
$pageTitle  = 'Kelola Pengguna - Admin VAYGOR';
$adminShell = true;
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
      <div class="admin-topbar-side">
        <button id="addAdminBtn" class="admin-btn" type="button">+ Tambah Admin</button>
      </div>
    </header>

    <?php if ($msg = flash_get('ok')): ?>
      <div class="admin-flash is-ok"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash_get('err')): ?>
      <div class="admin-flash is-err"><?= e($msg) ?></div>
    <?php endif; ?>

    <section id="addAdminPanel" class="admin-panel<?= $showAddForm ? '' : ' hidden' ?>">
      <div class="admin-panel-head">
        <h2 class="admin-panel-title">Tambah Admin</h2>
        <button id="addAdminHide" class="admin-btn-ghost" type="button">Tutup</button>
      </div>

      <?php if ($addErrors): ?>
        <div class="admin-flash is-err">
          <?php foreach ($addErrors as $i => $err): ?>
            <?= $i > 0 ? '<br>' : '' ?><?= e($err) ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="admin-form" method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="op" value="add_admin">

        <div class="admin-form-grid">
          <div class="admin-field">
            <label for="add-name">Nama Lengkap</label>
            <input class="admin-input" id="add-name" type="text" name="name"
                   value="<?= e($oldForm['name']) ?>" required>
          </div>
          <div class="admin-field">
            <label for="add-email">Email</label>
            <input class="admin-input" id="add-email" type="email" name="email"
                   value="<?= e($oldForm['email']) ?>" required>
          </div>
          <div class="admin-field">
            <label for="add-phone">Telepon <span class="admin-help">(opsional)</span></label>
            <input class="admin-input" id="add-phone" type="tel" name="phone"
                   value="<?= e($oldForm['phone']) ?>">
          </div>
          <div class="admin-field">
            <label for="add-password">Kata Sandi</label>
            <input class="admin-input" id="add-password" type="password" name="password"
                   minlength="8" placeholder="Minimal 8 karakter" required>
          </div>
        </div>

        <div class="admin-form-actions">
          <button class="admin-btn" type="submit">Simpan Admin</button>
          <button id="addAdminCancel" class="admin-btn-ghost" type="button">Batal</button>
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
<script>
(function () {
  var panel  = document.getElementById('addAdminPanel');
  var toggle = document.getElementById('addAdminBtn');
  if (!panel || !toggle) return;

  function show() {
    panel.classList.remove('hidden');
    panel.querySelector('input') && panel.querySelector('input').focus();
  }
  function hide() {
    panel.classList.add('hidden');
  }

  toggle.addEventListener('click', function () {
    if (panel.classList.contains('hidden')) show();
    else hide();
  });

  var hides = document.querySelectorAll('#addAdminHide, #addAdminCancel');
  hides.forEach(function (b) { b.addEventListener('click', hide); });
})();
</script>
</body>
</html>