<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$navLoggedIn = !empty($_SESSION['id']);
$navRole    = $_SESSION['role'] ?? '';
$navName    = $_SESSION['name'] ?? '';

if ($navLoggedIn && !$navName) {
    $navConn = $GLOBALS['conn'] ?? null;
    if ($navConn) {
        $navRes = mysqli_query($navConn, 'SELECT name FROM users WHERE id = ' . (int) $_SESSION['id'] . ' LIMIT 1');
        $navRow = $navRes ? mysqli_fetch_assoc($navRes) : null;
        $navName = $navRow['name'] ?? 'Pengguna';
        $_SESSION['name'] = $navName;
    } else {
        $navName = 'Pengguna';
    }
}
$navInitial = $navName ? mb_strtoupper(mb_substr(trim($navName), 0, 1)) : 'V';
$navIsAdmin = $navLoggedIn && $navRole === 'admin';

// Halaman tanpa hero gelap (produk/booking/pesanan/404): nav solid agar teks terbaca.
// $navForceSolid dihitung di index.php; default transparan bila tidak diset.
$navSolid = !empty($navForceSolid);
$navLinkCls = $navSolid ? 'text-zinc-700 transition hover:text-vaygor-600' : 'text-white/85 transition hover:text-neon';
$navMasukCls = $navSolid ? 'text-sm font-semibold text-zinc-800 transition hover:text-vaygor-600' : 'text-sm font-semibold text-white transition hover:text-neon';
$navNameCls = $navSolid ? 'max-w-[10rem] truncate text-sm font-bold text-zinc-900' : 'max-w-[10rem] truncate text-sm font-bold text-white';
$navOutCls = $navSolid ? 'text-xs font-medium text-zinc-500 transition hover:text-vaygor-600' : 'text-xs font-medium text-white/70 transition hover:text-neon';
$navBurgerCls = $navSolid
  ? 'flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-700 transition hover:border-vaygor-600 hover:text-vaygor-600 lg:hidden'
  : 'flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white/20 lg:hidden';
$navAvatarRing = $navSolid ? 'ring-2 ring-vaygor-600/30' : 'ring-2 ring-white/40';
?>

<nav class="absolute inset-x-0 top-0 z-50">
  <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-10">

    <!-- Logo -->
    <a href="index.php" class="flex items-center">
      <img src="assets/images/logo(1).svg" class="h-5 w-auto sm:h-6<?php echo $navSolid ? ' brightness-0' : ''; ?>" alt="VAYGOR">
    </a>

    <!-- Desktop links -->
    <div class="hidden items-center gap-9 lg:flex">
      <a href="index.php" class="text-sm font-medium <?php echo $navLinkCls; ?>">Beranda</a>
      <a href="index.php?p=browse" class="text-sm font-medium <?php echo $navLinkCls; ?>">Lapangan</a>
      <a href="index.php#venue" class="text-sm font-medium <?php echo $navLinkCls; ?>">Venue</a>
      <a href="index.php#cara" class="text-sm font-medium <?php echo $navLinkCls; ?>">Cara Booking</a>
    </div>

    <!-- Desktop auth area -->
    <div class="hidden items-center gap-5 lg:flex">

      <?php if (!$navLoggedIn): ?>

        <a href="login.php" class="<?php echo $navMasukCls; ?>">Masuk</a>
        <a href="index.php?p=browse" class="rounded-xl bg-neon px-6 py-3 text-sm font-bold text-vaygor-950 shadow-lg shadow-black/20 transition hover:scale-[1.03] hover:bg-white">Book Now</a>

      <?php else: ?>

        <a href="index.php?p=browse" class="rounded-xl bg-neon px-6 py-3 text-sm font-bold text-vaygor-950 shadow-lg shadow-black/20 transition hover:scale-[1.03] hover:bg-white">Book Now</a>

        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-neon text-sm font-extrabold text-vaygor-950 <?php echo $navAvatarRing; ?>"><?= $navInitial; ?></div>
          <div class="leading-tight">
            <p class="<?php echo $navNameCls; ?>"><?= htmlspecialchars($navName); ?></p>
            <a href="logout.php" class="<?php echo $navOutCls; ?>">Keluar</a>
          </div>
        </div>

      <?php endif; ?>

    </div>

    <!-- Mobile burger -->
    <button id="menuBtn" type="button" aria-label="Menu" aria-expanded="false" aria-controls="mobileMenu" class="<?php echo $navBurgerCls; ?>">
      <svg id="iconBars" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M4 6h16M4 12h16M4 18h16" />
      </svg>
      <svg id="iconClose" class="hidden h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M6 6l12 12M18 6L6 18" />
      </svg>
    </button>
  </div>

  <!-- Mobile menu -->
  <div id="mobileMenu" class="hidden px-5 sm:px-8 lg:hidden">
    <div class="rounded-2xl bg-white/95 p-5 shadow-2xl ring-1 ring-black/5 backdrop-blur">
      <div class="flex flex-col gap-1">
        <a href="index.php" class="rounded-xl px-4 py-3 text-sm font-semibold text-gray-800 transition hover:bg-vaygor-50 hover:text-vaygor-600">Beranda</a>
        <a href="index.php?p=browse" class="rounded-xl px-4 py-3 text-sm font-semibold text-gray-800 transition hover:bg-vaygor-50 hover:text-vaygor-600">Lapangan</a>
        <a href="index.php#venue" class="rounded-xl px-4 py-3 text-sm font-semibold text-gray-800 transition hover:bg-vaygor-50 hover:text-vaygor-600">Venue</a>
        <a href="index.php#cara" class="rounded-xl px-4 py-3 text-sm font-semibold text-gray-800 transition hover:bg-vaygor-50 hover:text-vaygor-600">Cara Booking</a>
      </div>

      <div class="my-3 h-px bg-gray-200"></div>

      <?php if (!$navLoggedIn): ?>
        <div class="grid grid-cols-2 gap-3">
          <a href="login.php" class="rounded-xl border border-vaygor-200 px-4 py-3 text-center text-sm font-bold text-vaygor-700 transition hover:bg-vaygor-50">Masuk</a>
          <a href="index.php?p=browse" class="rounded-xl bg-neon px-4 py-3 text-center text-sm font-bold text-vaygor-950 transition hover:bg-vaygor-600 hover:text-white">Book Now</a>
        </div>

      <?php else: ?>
        <div class="mb-3 flex items-center gap-3 rounded-xl bg-vaygor-50 p-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-vaygor-600 text-sm font-extrabold text-white"><?= $navInitial; ?></div>
          <div class="leading-tight">
            <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($navName); ?></p>
            <span class="text-xs font-medium text-vaygor-600">Member</span>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <a href="index.php?p=browse" class="rounded-xl bg-neon px-4 py-3 text-center text-sm font-bold text-vaygor-950 transition hover:bg-vaygor-600 hover:text-white">Book Now</a>
          <a href="logout.php" class="rounded-xl border border-gray-300 px-4 py-3 text-center text-sm font-bold text-gray-700 transition hover:bg-gray-100">Keluar</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<script>
  (function () {
    var btn = document.getElementById('menuBtn');
    var menu = document.getElementById('mobileMenu');
    var bars = document.getElementById('iconBars');
    var close = document.getElementById('iconClose');
    if (!btn || !menu) return;
    btn.addEventListener('click', function () {
      var isOpen = !menu.classList.contains('hidden');
      menu.classList.toggle('hidden', isOpen);
      bars.classList.toggle('hidden', !isOpen);
      close.classList.toggle('hidden', isOpen);
      btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });
  })();
</script>