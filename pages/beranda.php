<div class="p-0 m-0">
    <div class="relative overflow-hidden bg-[linear-gradient(90deg,#16a34aee,rgba(0,0,0,0.62)),url('assets/images/3x12wclyJrsyLTLEdRiGAcsys.png')] bg-cover bg-center text-white pt-28 pb-24 sm:pb-28 px-6 sm:px-8 lg:px-12 xl:px-20 min-h-[520px] md:rounded-b-[32px] lg:rounded-b-[48px]">
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]" aria-hidden="true" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27120%27 height=%27120%27 viewBox=%270 0 120 120%27%3E%3Cpath fill=%27white%27 fill-opacity=%270.9%27 d=%27M60 18 L78 36 L78 60 L60 78 L42 60 L42 36 Z M60 78 L78 96 L60 114 L42 96 Z M18 36 L36 54 L18 72 L0 54 Z M102 36 L120 54 L102 72 L84 54 Z%27/%3E%3C/svg%3E'); background-size: 140px 140px; mask-image: radial-gradient(ellipse 70% 60% at 70% 30%, black 55%, transparent 85%);"></div>
        <div class="relative max-w-3xl">
            <span class="mb-3 inline-flex rounded-full bg-white/15 px-3 py-1 font-inter text-[11px] font-bold uppercase tracking-widest text-white ring-1 ring-white/20">
                Sports Court Booking - Magelang
            </span>
            <h1 class="font-spartan text-5xl font-extrabold leading-[0.9] tracking-[-0.04em] sm:text-6xl lg:text-7xl">
                FIND YOUR PLAY.
            </h1>
            <p class="mt-4 max-w-lg font-inter text-[15px] leading-relaxed text-white/95">
                Temukan lapangan olahraga favoritmu, pilih waktu, dan langsung booking. Harga jelas, jadwal 08:00 sampai 23:00.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="index.php?p=browse" class="inline-flex items-center justify-center rounded-xl bg-[#B6F500] px-6 py-3 text-sm font-bold text-zinc-900 hover:bg-[#c8ff1a]">Cari Lapangan</a>
                <a href="index.php?p=produk" class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-zinc-900 hover:bg-zinc-50">Lihat Pancuranmas</a>
            </div>
        </div>
    </div>

    <div class="relative z-10 mx-auto -mt-10 sm:-mt-12 w-[92%] max-w-5xl rounded-2xl sm:rounded-3xl border border-zinc-200 bg-white p-4 sm:p-5 shadow-[0_12px_32px_rgba(0,0,0,0.08)]">
        <form action="index.php" method="get" class="grid gap-3 sm:grid-cols-[1.4fr_1fr_1fr_auto] sm:items-end">
            <input type="hidden" name="p" value="browse">
            <div>
                <label for="q" class="block text-xs font-semibold text-zinc-700">Cari venue</label>
                <input id="q" type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari nama venue" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
            </div>
            <div>
                <label for="type" class="block text-xs font-semibold text-zinc-700">Tipe</label>
                <select id="type" name="type" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
                    <option value="">Semua tipe</option>
                    <option value="futsal" <?php echo (($_GET['type'] ?? '')==='futsal'?'selected':''); ?>>Futsal</option>
                    <option value="indoor" <?php echo (($_GET['type'] ?? '')==='indoor'?'selected':''); ?>>Indoor</option>
                    <option value="outdoor" <?php echo (($_GET['type'] ?? '')==='outdoor'?'selected':''); ?>>Outdoor</option>
                </select>
            </div>
            <div>
                <label for="date" class="block text-xs font-semibold text-zinc-700">Tanggal</label>
                <input id="date" type="date" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
            </div>
            <button type="submit" class="inline-flex h-[46px] items-center justify-center rounded-xl bg-vaygor-600 px-6 text-sm font-bold text-white hover:bg-vaygor-700">Cari Lapangan</button>
        </form>
        <p class="mt-3 text-center text-xs text-zinc-500 sm:text-left">Contoh: ketik Pancuranmas lalu tekan Cari Lapangan.</p>
    </div>

</div>