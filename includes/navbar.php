<?php $isLoggedIn = !empty($_SESSION['id']); ?>
<nav class="absolute top-0 z-50 flex h-16 sm:h-20 items-center w-full px-6 sm:px-8 lg:px-12 xl:px-20 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/70 border-b border-zinc-100">
    <a href="index.php" class="inline-flex items-center gap-2 font-spartan text-xl font-extrabold tracking-tight text-vaygor-600">
        <img src="assets/images/logo(1).svg" class="h-7 w-auto" alt="VAYGOR">
        <span class="hidden sm:inline">VAYGOR</span>
    </a>

    <div class="hidden sm:flex gap-6 ml-auto items-center">
        <a href="index.php?p=browse" class="font-inter text-sm font-medium text-zinc-700 hover:text-vaygor-600">Lapangan</a>
        <a href="index.php?p=produk" class="font-inter text-sm font-medium text-zinc-700 hover:text-vaygor-600">Venue</a>
        <a href="index.php?p=beranda" class="font-inter text-sm font-medium text-zinc-700 hover:text-vaygor-600">Cara Booking</a>
    </div>

    <div class="flex items-center gap-2 sm:gap-3 ml-auto sm:ml-6">
        <?php if ($isLoggedIn): ?>
          <a href="index.php?p=pesanan" class="hidden sm:inline-flex rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Pesanan</a>
          <a href="logout.php" class="inline-flex rounded-xl bg-zinc-900 px-4 sm:px-5 py-2.5 sm:py-3 text-sm font-bold text-white hover:bg-black">Keluar</a>
        <?php else: ?>
          <a href="login.php" class="hidden sm:inline-flex rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Masuk</a>
          <a href="index.php?p=browse" class="inline-flex rounded-xl bg-vaygor-600 px-4 sm:px-5 py-2.5 sm:py-3 text-sm font-bold text-white hover:bg-vaygor-700">Booking Lapangan</a>
        <?php endif; ?>
    </div>
</nav>