<?php
require_once __DIR__ . '/config/helpers.php';

$back = (string) ($_GET['redirect'] ?? '');
$backOk = preg_match('#^/?[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*\.php(?:\?[A-Za-z0-9_=&%.\-]*)?$#', $back) === 1 || $back === '/';

if (is_logged_in() || !empty($_SESSION['id'])) {
    if (is_logged_in() && empty($_SESSION['id'])) {
        $_SESSION['id']   = (int) current_user()['id'];
        $_SESSION['role'] = current_user()['role'];
    }
    $role = $_SESSION['user']['role'] ?? ($_SESSION['role'] ?? '');
    redirect($role === 'admin' ? 'admin/index.php' : ($backOk && $back !== '' ? $back : 'index.php'));
}

$successMsg = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);
$error = '';
$fieldErrors = [];
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim((string) ($_POST['email'] ?? ''));
    $pass  = (string) ($_POST['pass'] ?? '');

    if ($email === '' || $pass === '') {
        if ($email === '') $fieldErrors['email'] = 'Email wajib diisi.';
        if ($pass === '')  $fieldErrors['pass']  = 'Kata sandi wajib diisi.';
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = 'Akun tidak aktif. Hubungi administrator.';
            } else {
                login($user);
                $_SESSION['id']     = (int) $user['id'];
                $_SESSION['role']   = $user['role'];
                $_SESSION['status'] = true;

                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                }
                redirect($backOk && $back !== '' ? $back : 'index.php');
            }
        } else {
            $error = 'Email atau kata sandi salah.';
        }
    }
}

$statVenue   = (int) db()->query('SELECT COUNT(*) FROM fields')->fetchColumn();
$statBooking = (int) db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$statMin     = (int) db()->query('SELECT COALESCE(MIN(price), 0) FROM fields')->fetchColumn();
$statMinLabel = $statMin > 0 ? ($statMin >= 1000 ? (int) round($statMin / 1000) . 'rb' : (string) $statMin) : '-';

$base      = '.';
$pageTitle = 'Masuk - VAYGOR';
require __DIR__ . '/includes/head.php';
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<div class="auth-wrap">
  <div class="auth-hero">
    <img class="auth-hero-img" src="assets/images/hero.png" alt="Pemain bermain bola di lapangan saat matahari terbenam">
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
    <a class="auth-hero-brand" href="index.php">
      <img class="auth-hero-logo" src="assets/images/logo(1).svg" alt="VAYGOR">
    </a>
    <div class="auth-hero-bottom">
      <p class="auth-hero-title">FIND YOUR<br><em>PLAY.</em></p>
      <p class="auth-hero-sub">Cari lapangan, pilih jam, langsung main. Tanpa telepon, tanpa antre.</p>
      <div class="auth-hero-stats">
        <div><p class="num"><?= $statVenue ?></p><p class="lbl">Venue</p></div>
        <div><p class="num"><?= $statBooking ?></p><p class="lbl">Booking</p></div>
        <div><p class="num"><?= e($statMinLabel) ?></p><p class="lbl">Mulai /jam</p></div>
      </div>
    </div>
  </div>

  <div class="auth-panel">
    <div class="auth-card">
      <p class="auth-eyebrow">Masuk</p>
      <h1 class="auth-title">Main lagi, tanpa antre.</h1>
      <p class="auth-sub">Email terdaftar, langsung lanjut booking lapangan.</p>

      <?php if ($successMsg !== ''): ?>
        <div class="auth-alert is-ok" id="login-flash" role="status"><?= e($successMsg) ?></div>
      <?php endif; ?>
      <?php if ($error !== ''): ?>
        <div class="auth-alert" id="login-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <form action="" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="auth-field">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required<?php if (isset($fieldErrors['email'])): ?> aria-invalid="true" aria-describedby="email-err"<?php elseif ($error !== ''): ?> aria-invalid="true" aria-describedby="login-error"<?php endif; ?>>
          <?php if (isset($fieldErrors['email'])): ?>
            <p class="auth-err" id="email-err"><?= e($fieldErrors['email']) ?></p>
          <?php endif; ?>
        </div>
        <div class="auth-field">
          <label for="pass">Kata sandi</label>
          <div class="auth-pass">
            <input id="pass" type="password" name="pass" autocomplete="current-password" required<?php if (isset($fieldErrors['pass'])): ?> aria-invalid="true" aria-describedby="pass-err"<?php elseif ($error !== ''): ?> aria-invalid="true" aria-describedby="login-error"<?php endif; ?>>
            <button class="auth-eye" type="button" id="togglePass" aria-label="Tampilkan kata sandi" aria-controls="pass" aria-pressed="false">
              <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="eyeOff" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none"><path d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 5.1A10.5 10.5 0 0112 5c6.5 0 10 7 10 7a17.9 17.9 0 01-3.4 4.4M6.6 6.6C3.9 8.4 2 12 2 12s3.5 7 10 7c1.4 0 2.7-.3 3.9-.8"/></svg>
            </button>
          </div>
          <?php if (isset($fieldErrors['pass'])): ?>
            <p class="auth-err" id="pass-err"><?= e($fieldErrors['pass']) ?></p>
          <?php endif; ?>
        </div>
        <button class="auth-btn" type="submit">Masuk dan Booking</button>
      </form>

      <p class="auth-alt">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>

      <details class="auth-demo">
        <summary>Akun seed (dev)</summary>
        <p>admin@futsalmagelang.com / admin123 (admin)<br>demo@vaygor.id / user123 (user)</p>
      </details>
    </div>
  </div>
</div>
<script>
(function () {
  var btn = document.getElementById('togglePass');
  var input = document.getElementById('pass');
  if (!btn || !input) return;
  btn.addEventListener('click', function () {
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    document.getElementById('eyeOpen').style.display = show ? 'none' : '';
    document.getElementById('eyeOff').style.display = show ? '' : 'none';
  });
})();
</script>
</body>
</html>
