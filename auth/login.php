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

$statLapangan = (int) db()->query('SELECT COUNT(*) FROM fields')->fetchColumn();
$statBooking  = (int) db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$statKategori = (int) db()->query('SELECT COUNT(*) FROM kategori')->fetchColumn();

$base      = '..';
$pageTitle = 'Masuk - VAYGOR';
require __DIR__ . '/../includes/head.php';
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<div class="auth-wrap">
  <div class="auth-hero">
    <img class="auth-hero-img" src="<?= $base ?>/assets/images/hero.png" alt="Pemain bermain bola di lapangan saat matahari terbenam">
    <div class="auth-paths" aria-hidden="true">
      <svg viewBox="-200 -250 950 1150" preserveAspectRatio="xMidYMid slice" fill="none">
        <?php foreach ([1, -1] as $pos): ?>
          <g data-pos="<?= (int) $pos ?>">
            <?php for ($i = 0; $i < 24; $i++): $step = $i * 5 * $pos; ?>
              <path d="M-<?= 380 - $step ?> -<?= 189 + $i * 6 ?>C-<?= 380 - $step ?> -<?= 189 + $i * 6 ?> -<?= 312 - $step ?> <?= 216 - $i * 6 ?> <?= 152 - $step ?> <?= 343 - $i * 6 ?>C<?= 616 - $step ?> <?= 470 - $i * 6 ?> <?= 684 - $step ?> <?= 875 - $i * 6 ?> <?= 684 - $step ?> <?= 875 - $i * 6 ?>"
                stroke-width="<?= round(1.7 + $i * 0.085, 2) ?>"
                stroke-opacity="<?= round(0.14 + $i * 0.032, 2) ?>"/>
            <?php endfor; ?>
          </g>
        <?php endforeach; ?>
      </svg>
    </div>
    <div class="auth-hero-veil" aria-hidden="true"></div>
    <a class="auth-hero-brand" href="../index.php">
      <img class="auth-hero-logo" src="<?= $base ?>/assets/images/logo(1).svg" alt="VAYGOR">
    </a>
    <div class="auth-hero-bottom">
      <p class="auth-hero-title">KELOLA<br>LAPANGANMU.</p>
      <p class="auth-hero-sub">Jadwal, booking, pembayaran — satu dashboard.</p>
      <div class="auth-hero-stats">
        <div><p class="num"><?= $statLapangan ?></p><p class="lbl">Venue</p></div>
        <div><p class="num"><?= $statBooking ?></p><p class="lbl">Booking</p></div>
        <div><p class="num"><?= $statKategori ?></p><p class="lbl">Kategori</p></div>
      </div>
    </div>
  </div>

  <div class="auth-panel">
    <div class="auth-card">
      <p class="auth-eyebrow">Panel Admin</p>
      <h1 class="auth-title">Masuk pengelola.</h1>
      <p class="auth-sub">Masuk untuk mengelola lapangan dan booking.</p>

      <?php if ($error !== ''): ?>
        <div class="auth-alert" id="login-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="auth-field">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required<?php if ($error !== ''): ?> aria-invalid="true" aria-describedby="login-error"<?php endif; ?>>
        </div>
        <div class="auth-field">
          <label for="password">Password</label>
          <input id="password" type="password" name="password" autocomplete="current-password" required<?php if ($error !== ''): ?> aria-invalid="true" aria-describedby="login-error"<?php endif; ?>>
        </div>
        <button class="auth-btn" type="submit">Masuk ke Dashboard</button>
      </form>

      <p class="auth-alt">Belum punya akun? <a href="../register.php">Daftar</a></p>

      <details class="auth-demo">
        <summary>Akun seed (dev)</summary>
        <p>admin@futsalmagelang.com / admin123 (admin)<br>rava@example.com / user123 (user)</p>
      </details>
    </div>
  </div>
</div>
</body>
</html>
