<?php
include __DIR__ . '/database/konfig.php';
session_start();

if (!empty($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}

$successMsg = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);
$redirect = $_GET['redirect'] ?? '';
$errorMsg = '';
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if ($email === '' || $pass === '') {
        $errorMsg = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ? AND status = 'active' LIMIT 1");
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

                // Balik ke halaman asal setelah login (anti open-redirect: hanya path lokal).
                if ($redirect !== '' && parse_url($redirect, PHP_URL_SCHEME) === null && $redirect[0] !== '//') {
                    header('Location: ' . $redirect);
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $errorMsg = 'Email atau kata sandi salah.';
            }
        } else {
            $errorMsg = 'Email atau kata sandi salah.';
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
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style type="text/tailwindcss">
    @theme {
      --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
      --font-spartan: "League Spartan", sans-serif;

      --color-vaygor-50: #eef7f0;
      --color-vaygor-100: #d6eedd;
      --color-vaygor-200: #b0dcc0;
      --color-vaygor-300: #82c49c;
      --color-vaygor-400: #4fa673;
      --color-vaygor-500: #2f8c58;
      --color-vaygor-600: #1b703a;
      --color-vaygor-700: #16582f;
      --color-vaygor-800: #124726;
      --color-vaygor-900: #0e3a1f;
      --color-vaygor-950: #062211;
      --color-neon: #b6f500;
      --color-cream: #fff4c7;
    }

    @layer base {
      html {
        scroll-behavior: smooth;
      }

      body {
        @apply bg-[#F6F8F5] text-gray-900 antialiased;
      }
    }
  </style>
  <style>
    :focus-visible { outline: 2px solid #1B703A; outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition: none !important; } }
  </style>
</head>
<body class="min-h-screen bg-[#f6f8f5] font-inter text-zinc-900 antialiased">
  <div class="min-h-screen grid lg:grid-cols-2">
    <div class="hidden lg:flex flex-col justify-between text-white p-10 relative overflow-hidden bg-vaygor-950">
      <img src="assets/images/beranda.png" alt="Pemain bermain bola di lapangan saat matahari terbenam" class="absolute inset-0 h-full w-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-t from-vaygor-950/85 via-vaygor-700/45 to-vaygor-600/20"></div>
      <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 20%, white 1px, transparent 1px); background-size: 22px 22px;"></div>
      <div class="relative">
        <a href="index.php" class="inline-flex items-center gap-2 font-spartan text-2xl font-extrabold tracking-tight">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white text-vaygor-600 text-sm">VG</span>
          VAYGOR
        </a>
        <p class="mt-6 max-w-md text-sm leading-relaxed text-white/90">Lapangan hangat di ujung hari. Cari nama venue, pilih jam, dan booking tanpa ribet. Satu aksen lime untuk harga yang penting.</p>
      </div>
      <div class="relative">
        <div class="rounded-2xl bg-white/10 backdrop-blur p-5 border border-white/20">
          <p class="font-spartan text-lg font-bold leading-tight">FIND YOUR PLAY.</p>
          <p class="mt-2 text-sm text-white/80">Harga mulai Rp 15.000 per jam di Pancuranmas. Jadwal 08:00 sampai 23:00.</p>
        </div>
        <p class="mt-4 text-xs text-white/70">Khusus akun user. Admin masuk lewat jalur admin. Foto ilustrasi suasana bermain, bukan venue spesifik.</p>
      </div>
    </div>

    <div class="flex items-center justify-center px-6 py-10 lg:px-12">
      <div class="w-full max-w-md">
        <a href="index.php" class="lg:hidden inline-flex items-center gap-2 font-spartan text-xl font-extrabold tracking-tight text-vaygor-600">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-vaygor-600 text-white text-xs">VG</span>
          VAYGOR
        </a>
        <h1 class="mt-6 font-spartan text-3xl font-extrabold tracking-tight text-zinc-900">Masuk untuk booking</h1>
        <p class="mt-2 text-sm leading-relaxed text-zinc-600">Pakai email terdaftar. Setelah masuk kamu bisa lanjut booking lapangan.</p>

        <?php if ($successMsg !== ''): ?>
          <div role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            <?php echo htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>
        <?php if ($errorMsg !== ''): ?>
          <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <form action="" method="post" class="mt-8 space-y-5" novalidate>
          <div>
            <label for="email" class="block text-sm font-semibold text-zinc-800">Email</label>
            <input id="email" type="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="rava@example.com" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
          </div>
          <div>
            <label for="pass" class="block text-sm font-semibold text-zinc-800">Kata sandi</label>
            <input id="pass" type="password" name="pass" required autocomplete="current-password" placeholder="Masukkan kata sandi" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
          </div>
          <button type="submit" name="login" class="w-full rounded-xl bg-vaygor-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-vaygor-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-vaygor-600">Masuk</button>
          <p class="text-center text-xs text-zinc-500">Belum punya akun? <a href="register.php" class="font-semibold text-vaygor-600 underline-offset-4 hover:underline">Daftar sekarang</a></p>
        </form>

        <div class="mt-8 rounded-xl bg-[#B6F500]/20 border border-[#B6F500]/40 px-4 py-3">
          <p class="text-xs font-semibold text-zinc-800">Akun tes user</p>
          <p class="mt-1 text-xs leading-relaxed text-zinc-600">rava@example.com / user123</p>
          <p class="mt-1 text-xs text-zinc-500">Admin tidak lewat halaman ini.</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
