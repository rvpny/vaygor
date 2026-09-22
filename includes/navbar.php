<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$navLoggedIn = !empty($_SESSION['id']);
$navRole    = $_SESSION['role'] ?? '';
$navName    = $_SESSION['name'] ?? '';
$navSolid   = !empty($navForceSolid); // halaman tanpa hero gelap → navbar solid sejak awal

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
?>

<nav id="siteNav" data-force="<?= $navSolid ? '1' : '0' ?>" class="fixed inset-x-0 top-0 z-50 <?= $navSolid ? 'scrolled' : '' ?>">
  <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-10">

    <!-- Logo -->
    <a href="index.php" class="flex items-center">
      <img id="navLogo" src="assets/images/logo(1).svg" class="h-5 w-auto sm:h-6" alt="VAYGOR">
    </a>

    <!-- Desktop links -->
    <div class="hidden items-center gap-9 lg:flex">
      <a href="index.php" class="nav-link">Beranda</a>
      <a href="index.php?p=browse" class="nav-link">Lapangan</a>
      <a href="index.php#venue" class="nav-link">Venue</a>
      <a href="index.php#cara" class="nav-link">Cara Booking</a>
    </div>

    <!-- Desktop auth area -->
    <div class="hidden items-center gap-5 lg:flex">

      <?php if (!$navLoggedIn): ?>

        <a href="login.php" class="nav-link">Masuk</a>
        <a href="index.php?p=browse" class="rounded-xl bg-neon px-6 py-3 text-sm font-bold text-vaygor-950 shadow-lg shadow-black/20 transition hover:scale-[1.03] hover:bg-white">Book Now</a>

      <?php else: ?>

        <?php if ($navIsAdmin): ?>
          <a href="#" class="admin-pill">Dashboard</a>
        <?php endif; ?>

        <div class="flex items-center gap-3">
          <div class="nav-avatar flex h-10 w-10 items-center justify-center rounded-full bg-neon text-sm font-extrabold text-vaygor-950 ring-2 ring-white/40"><?= $navInitial; ?></div>
          <div class="leading-tight">
            <p class="nav-name max-w-[10rem] truncate text-sm font-bold"><?= htmlspecialchars($navName); ?></p>
            <a href="logout.php" class="nav-logout text-xs font-medium">Keluar</a>
          </div>
        </div>

      <?php endif; ?>

    </div>

    <!-- Mobile burger -->
    <button id="menuBtn" type="button" aria-label="Menu" class="nav-burger flex h-11 w-11 items-center justify-center rounded-xl transition hover:bg-white/20 lg:hidden">
      <svg id="iconBars" class="nav-icon h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M4 6h16M4 12h16M4 18h16" />
      </svg>
      <svg id="iconClose" class="nav-icon hidden h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
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
            <span class="text-xs font-medium text-vaygor-600"><?= $navIsAdmin ? 'Administrator' : 'Member'; ?></span>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <?php if ($navIsAdmin): ?>
            <a href="#" class="rounded-xl border border-vaygor-200 px-4 py-3 text-center text-sm font-bold text-vaygor-700 transition hover:bg-vaygor-50">Dashboard</a>
            <a href="logout.php" class="rounded-xl bg-gray-900 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-gray-700">Keluar</a>
          <?php else: ?>
            <a href="index.php?p=browse" class="rounded-xl bg-neon px-4 py-3 text-center text-sm font-bold text-vaygor-950 transition hover:bg-vaygor-600 hover:text-white">Book Now</a>
            <a href="logout.php" class="rounded-xl border border-gray-300 px-4 py-3 text-center text-sm font-bold text-gray-700 transition hover:bg-gray-100">Keluar</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<style>
  #siteNav {
    background: transparent;
    transition: background-color .3s ease, box-shadow .3s ease;
  }
  #siteNav .nav-link {
    color: rgba(255, 255, 255, .85);
    transition: color .25s ease;
  }
  #siteNav .nav-link:hover { color: #b6f500; }
  #siteNav .nav-burger {
    color: #fff;
    background: rgba(255, 255, 255, .1);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .25);
  }
  #siteNav .nav-burger:hover { background: rgba(255, 255, 255, .2); }
  #siteNav .nav-name { color: #fff; }
  #siteNav .nav-logout { color: rgba(255, 255, 255, .7); transition: color .25s ease; }
  #siteNav .nav-logout:hover { color: #b6f500; }
  #siteNav .admin-pill {
    color: #fff;
    background: rgba(255, 255, 255, .1);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .3);
  }
  #siteNav .admin-pill:hover { background: rgba(255, 255, 255, .2); }

  #siteNav.scrolled {
    background: rgba(255, 255, 255, .92);
    box-shadow: 0 6px 24px rgba(6, 34, 17, .08);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
  }
  #siteNav.scrolled .nav-link { color: #124726; }
  #siteNav.scrolled .nav-link:hover { color: #1b703a; }
  #siteNav.scrolled #navLogo { filter: brightness(0) opacity(.85); }
  #siteNav.scrolled .nav-burger {
    color: #124726;
    background: rgba(18, 71, 38, .06);
    box-shadow: inset 0 0 0 1px rgba(18, 71, 38, .12);
  }
  #siteNav.scrolled .nav-burger:hover { background: rgba(18, 71, 38, .12); }
  #siteNav.scrolled .nav-name { color: #0e3a1f; }
  #siteNav.scrolled .nav-logout { color: #124726; }
  #siteNav.scrolled .nav-logout:hover { color: #1b703a; }
  #siteNav.scrolled .admin-pill {
    color: #124726;
    background: rgba(18, 71, 38, .06);
    box-shadow: inset 0 0 0 1px rgba(18, 71, 38, .15);
  }
  #siteNav.scrolled .admin-pill:hover { background: rgba(18, 71, 38, .12); }
</style>

<script>
  (function () {
    var nav = document.getElementById('siteNav');
    var btn = document.getElementById('menuBtn');
    var menu = document.getElementById('mobileMenu');
    var bars = document.getElementById('iconBars');
    var close = document.getElementById('iconClose');

    function onScroll() {
      if (!nav) return;
      if (nav.getAttribute('data-force') === '1') return;
      if (window.scrollY > 24) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (btn && menu) {
      btn.addEventListener('click', function () {
        var isOpen = !menu.classList.contains('hidden');
        menu.classList.toggle('hidden', isOpen);
        if (bars) bars.classList.toggle('hidden', !isOpen);
        if (close) close.classList.toggle('hidden', isOpen);
      });
    }
  })();
</script>