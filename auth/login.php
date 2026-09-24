<?php
require_once __DIR__ . '/../config/helpers.php';

if (is_logged_in()) {
    redirect(current_user()['role'] === 'admin' ? '../admin/index.php' : '../user/index.php');
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = 'Akun tidak aktif. Hubungi administrator.';
            } else {
                login($user);
                redirect($user['role'] === 'admin' ? '../admin/index.php' : '../user/index.php');
            }
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

$base      = '..';
$pageTitle = 'Masuk - VAYGOR';
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
    <h1 class="auth-title">Masuk</h1>
    <p class="auth-sub">Masuk untuk booking lapanganmu</p>

    <?php if ($error !== ''): ?>
      <div class="auth-alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="auth-field">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
      </div>
      <div class="auth-field">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" autocomplete="current-password" required>
      </div>
      <button class="auth-btn" type="submit">Masuk</button>
    </form>
  </div>

  <p class="auth-alt">Belum punya akun? <a href="../register.php">Daftar</a></p>

  <div class="auth-demo">
    Akun seed:<br>
    admin@futsalmagelang.com / admin123 (admin)<br>
    rava@example.com / user123 (user)
  </div>
</div>
</body>
</html>
