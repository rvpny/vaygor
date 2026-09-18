<?php
require_once __DIR__ . '/../config/helpers.php';

if (is_logged_in()) {
    redirect(current_user()['role'] === 'admin' ? '../admin/index.php' : '../user/index.php');
}

$errors = [];
$name   = '';
$email  = '';
$phone  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name     = trim((string) ($_POST['name'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $phone    = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');

    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }
    if ($password === '' || strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }
    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar.';
        }
    }

    if (!$errors) {
        $insert = db()->prepare(
            "INSERT INTO users (name, email, phone, password, role, status)
             VALUES (?, ?, ?, ?, 'user', 'active')"
        );
        $insert->execute([
            $name,
            $email,
            $phone !== '' ? $phone : null,
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        login([
            'id'    => (int) db()->lastInsertId(),
            'name'  => $name,
            'email' => $email,
            'role'  => 'user',
        ]);
        redirect('../user/index.php');
    }
}

$base      = '..';
$pageTitle = 'Daftar - VAYGOR';
require __DIR__ . '/../includes/head.php';
?>
<div class="auth-wrap">
  <a class="auth-brand" href="../index.php">
    <span>V</span><span>A</span><span>Y</span><span>G</span>
    <span class="logo-ball"><svg viewBox="0 0 512 512"><path d="M256 25C128.3 25 25 128.3 25 256s103.3 231 231 231 231-103.3 231-231S383.7 25 256 25zm0 30c20.7 0 40.8 3.1 59.7 8.9l-26.4 45.7-33.3-19.2-33.3 19.2-26.4-45.7C215.2 58.1 235.3 55 256 55zm-80.6 19.8l28.2 48.9-38.5 22.2L121 130.6c14.8-22 34-40.5 54.4-55.8zm161.2 0c20.4 15.3 39.6 33.8 54.4 55.8l-44.1 15.3-38.5-22.2 28.2-48.9z"/></svg></span>
    <span>R</span>
  </a>
  <div class="auth-tagline">RENT. PLAY. WIN.</div>

  <div class="auth-card">
    <h1 class="auth-title">Daftar</h1>
    <p class="auth-sub">Buat akun untuk mulai booking</p>

    <?php if ($errors): ?>
      <div class="auth-alert error">
        <?php foreach ($errors as $i => $msg): ?>
          <?= $i > 0 ? '<br>' : '' ?><?= e($msg) ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="auth-field">
        <label for="name">Nama Lengkap</label>
        <input id="name" type="text" name="name" value="<?= e($name) ?>" autocomplete="name" required>
      </div>
      <div class="auth-field">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
      </div>
      <div class="auth-field">
        <label for="phone">No. HP (opsional)</label>
        <input id="phone" type="tel" name="phone" value="<?= e($phone) ?>" autocomplete="tel">
      </div>
      <div class="auth-field">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" autocomplete="new-password" required>
      </div>
      <div class="auth-field">
        <label for="password_confirm">Ulangi Password</label>
        <input id="password_confirm" type="password" name="password_confirm" autocomplete="new-password" required>
      </div>
      <button class="auth-btn" type="submit">Daftar</button>
    </form>
  </div>

  <p class="auth-alt">Sudah punya akun? <a href="login.php">Masuk</a></p>
</div>
</body>
</html>
