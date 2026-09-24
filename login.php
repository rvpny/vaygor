<?php
include __DIR__ . '/database/konfig.php';
session_start();

if (!empty($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}

$successMsg = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);
$errorMsg = '';
$fieldErrors = [];
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if ($email === '' || $pass === '') {
        $errorMsg = 'Email dan kata sandi wajib diisi.';
        if ($email === '') $fieldErrors['email'] = 'Email wajib diisi.';
        if ($pass === '') $fieldErrors['pass'] = 'Kata sandi wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ? AND role = 'user' AND status = 'active' LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($pass, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['status'] = true;
                header('Location: index.php');
                exit;
            } else {
                $errorMsg = 'Email atau kata sandi salah.';
                $fieldErrors['pass'] = 'Cek lagi kata sandimu.';
            }
        } else {
            $errorMsg = 'Email atau kata sandi salah.';
            $fieldErrors['email'] = 'Email belum terdaftar atau akun tidak aktif.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Masuk ke VAYGOR untuk booking lapangan futsal di Magelang.">
  <title>Masuk - VAYGOR</title>
  <link rel="icon" href="assets/images/logo(1).svg">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style type="text/tailwindcss">
    @theme {
      --color-vaygor-50: #eef7f0;
      --color-vaygor-500: #16a34a;
      --color-vaygor-600: #1B703A;
      --color-vaygor-700: #14522a;
      --color-vaygor-950: #062211;
      --color-neon: #B6F500;
      --font-inter: "Inter", ui-sans-serif, system-ui, sans-serif;
      --font-spartan: "League Spartan", ui-sans-serif, system-ui, sans-serif;
    }
  </style>
  <style>
    :focus-visible { outline: 2px solid #1B703A; outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition: none !important; animation: none !important; } }
  </style>
</head>
<body class="min-h-screen bg-[#f6f8f5] font-inter text-zinc-900 antialiased">
  <div class="min-h-screen grid lg:grid-cols-2">
    <!-- Panel kiri: foto lapangan + gradien hijau (sesuai DESIGN.md) -->
    <div class="hidden lg:flex flex-col justify-between text-white p-10 relative overflow-hidden bg-vaygor-950">
      <img src="assets/images/hero.png" alt="Pemain bermain bola di lapangan saat matahari terbenam" class="absolute inset-0 h-full w-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-t from-vaygor-950/90 via-vaygor-700/50 to-vaygor-600/25"></div>

      <a href="index.php" class="relative inline-flex items-center gap-2.5">
        <img src="assets/images/logo(1).svg" alt="" class="h-7 w-auto brightness-0" aria-hidden="true">
        <span class="font-spartan text-2xl font-extrabold tracking-tight">VAYGOR</span>
      </a>

      <div class="relative">
        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1 text-[11px] font-bold uppercase tracking-widest text-neon ring-1 ring-white/20">
          <span class="h-1.5 w-1.5 rounded-full bg-neon"></span>
          Sports Court Booking
        </span>
        <h1 class="mt-5 font-spartan text-6xl font-black leading-[0.92] tracking-[-0.03em]">
          FIND YOUR<br><span class="text-neon">PLAY.</span>
        </h1>
        <p class="mt-4 max-w-md text-sm leading-relaxed text-white/85">
          Cari lapangan, pilih jam, langsung main. Tanpa telepon, tanpa antre.
        </p>
      </div>

      <div class="relative">
        <div class="grid grid-cols-3 gap-4 border-t border-white/15 pt-5">
          <div>
            <p class="font-spartan text-2xl font-extrabold">120<span class="text-neon">+</span></p>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-widest text-white/60">Venue</p>
          </div>
          <div>
            <p class="font-spartan text-2xl font-extrabold text-neon">15rb</p>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-widest text-white/60">Mulai /jam</p>
          </div>
          <div>
            <p class="font-spartan text-2xl font-extrabold">08-23</p>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-widest text-white/60">Setiap hari</p>
          </div>
        </div>
        <p class="mt-4 text-[11px] text-white/55">Akun khusus user. Foto suasana bermain, bukan venue tertentu.</p>
      </div>
    </div>

    <!-- Panel kanan: form -->
    <div class="flex items-center justify-center px-6 py-10 lg:px-12">
      <div class="w-full max-w-md">
        <a href="index.php" class="lg:hidden inline-flex items-center gap-2 mb-2">
          <img src="assets/images/logo(1).svg" alt="" class="h-6 w-auto" aria-hidden="true">
          <span class="font-spartan text-xl font-extrabold tracking-tight text-vaygor-600">VAYGOR</span>
        </a>

        <p class="text-xs font-bold uppercase tracking-[0.2em] text-vaygor-600">Masuk</p>
        <h1 class="mt-2 font-spartan text-3xl sm:text-4xl font-black tracking-tight text-zinc-900">Main lagi, tanpa antre.</h1>
        <p class="mt-2 text-sm leading-relaxed text-zinc-600">Email terdaftar, langsung lanjut booking lapangan.</p>

        <?php if ($successMsg !== ''): ?>
          <div role="status" class="mt-6 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            <svg class="mt-0.5 h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            <?php echo htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>
        <?php if ($errorMsg !== '' && empty($fieldErrors)): ?>
          <div role="alert" class="mt-6 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <svg class="mt-0.5 h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <form action="" method="post" class="mt-7 space-y-4" novalidate>
          <div>
            <label for="email" class="block text-sm font-semibold text-zinc-800">Email</label>
            <div class="mt-1.5 relative">
              <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>
              </svg>
              <input id="email" type="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="nama@email.com" aria-invalid="<?php echo isset($fieldErrors['email']) ? 'true' : 'false'; ?>" <?php echo isset($fieldErrors['email']) ? 'aria-describedby="email-err"' : ''; ?> class="mt-0 w-full rounded-xl border <?php echo isset($fieldErrors['email']) ? 'border-red-300 bg-red-50/50' : 'border-zinc-200 bg-white'; ?> pl-10 pr-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
              <?php if (isset($fieldErrors['email'])): ?>
                <p id="email-err" class="mt-1.5 text-xs font-medium text-red-600"><?php echo htmlspecialchars($fieldErrors['email'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
            </div>
          </div>

          <div>
            <div class="flex items-baseline justify-between">
              <label for="pass" class="block text-sm font-semibold text-zinc-800">Kata sandi</label>
            </div>
            <div class="mt-1.5 relative">
              <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/>
              </svg>
              <input id="pass" type="password" name="pass" required autocomplete="current-password" placeholder="Masukkan kata sandi" aria-invalid="<?php echo isset($fieldErrors['pass']) ? 'true' : 'false'; ?>" <?php echo isset($fieldErrors['pass']) ? 'aria-describedby="pass-err"' : ''; ?> class="w-full rounded-xl border <?php echo isset($fieldErrors['pass']) ? 'border-red-300 bg-red-50/50' : 'border-zinc-200 bg-white'; ?> pl-10 pr-12 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
              <button type="button" id="togglePass" aria-label="Tampilkan kata sandi" aria-controls="pass" aria-pressed="false" class="absolute right-1.5 top-1/2 -translate-y-1/2 flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600">
                <svg id="eyeOpen" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg id="eyeOff" class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 5.1A10.5 10.5 0 0112 5c6.5 0 10 7 10 7a17.9 17.9 0 01-3.4 4.4M6.6 6.6C3.9 8.4 2 12 2 12s3.5 7 10 7c1.4 0 2.7-.3 3.9-.8"/></svg>
              </button>
              <?php if (isset($fieldErrors['pass'])): ?>
                <p id="pass-err" class="mt-1.5 text-xs font-medium text-red-600"><?php echo htmlspecialchars($fieldErrors['pass'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
            </div>
          </div>

          <button type="submit" name="login" class="w-full flex items-center justify-center gap-2 rounded-2xl bg-vaygor-600 px-5 py-4 text-sm font-bold text-white shadow-lg shadow-vaygor-600/25 transition hover:bg-vaygor-700 hover:shadow-xl hover:shadow-vaygor-700/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-vaygor-600">
            Masuk dan Booking
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <p class="text-center text-xs text-zinc-500">Belum punya akun? <a href="register.php" class="font-semibold text-vaygor-600 underline-offset-4 hover:underline">Daftar sekarang</a></p>
        </form>

        <details class="mt-7 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-zinc-500 open:pb-3">
          <summary class="cursor-pointer select-none text-[11px] font-semibold uppercase tracking-wider text-zinc-400 hover:text-zinc-600">Akun demo (dev)</summary>
          <p class="mt-2 text-xs leading-relaxed">demo@vaygor.id / user123<br>rava@example.com / user123</p>
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
    document.getElementById('eyeOpen').classList.toggle('hidden', show);
    document.getElementById('eyeOff').classList.toggle('hidden', !show);
  });
})();
</script>
</body>
</html>
