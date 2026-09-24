<div class="pb-10">

  <!-- ═══════════ HERO ═══════════ -->
  <section class="relative overflow-hidden bg-[linear-gradient(105deg,rgba(6,34,17,0.94)_0%,rgba(18,71,38,0.82)_48%,rgba(27,112,58,0.55)_100%),url('assets/images/beranda.png')] bg-cover bg-center rounded-b-[20px] md:rounded-b-[30px] lg:rounded-b-[80px]">
    <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-vaygor-400/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-20 top-40 h-64 w-64 rounded-full bg-neon/20 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-5 pb-40 pt-32 sm:px-8 sm:pt-36 lg:px-10 lg:pb-44 lg:pt-44">
      <div class="max-w-3xl">
        <h1 class="mt-6 font-spartan text-6xl font-black leading-[0.9] tracking-[-0.03em] text-white sm:text-7xl lg:text-8xl">
          FIND YOUR<br>
          <span class="text-neon">PLAY.</span>
        </h1>

        <p class="mt-6 max-w-xl text-base leading-relaxed text-white/85 sm:text-lg">
          Temukan lapangan olahraga favoritmu, pilih waktu, dan langsung booking.
          Cepat, mudah, dan tanpa ribet.
        </p>

        <div class="mt-12 flex flex-wrap items-center gap-x-10 gap-y-5">
          <div>
            <p class="font-spartan text-3xl font-extrabold text-white">120<span class="text-neon">+</span></p>
            <p class="mt-1 text-xs font-medium uppercase tracking-widest text-white/60">Venue Olahraga</p>
          </div>
          <div class="h-10 w-px bg-white/15"></div>
          <div>
            <p class="font-spartan text-3xl font-extrabold text-white">36<span class="text-neon">+</span></p>
            <p class="mt-1 text-xs font-medium uppercase tracking-widest text-white/60">Pengguna Aktif</p>
          </div>
          <div class="h-10 w-px bg-white/15"></div>
          <div>
            <p class="font-spartan text-3xl font-extrabold text-white">4.9<span class="text-neon">/5</span></p>
            <p class="mt-1 text-xs font-medium uppercase tracking-widest text-white/60">Rating Pelayanan</p>
          </div>
        </div>
      </div>
    </div>

  </section>

  <!-- ═══════════ SEARCH CARD ═══════════ -->
  <section class="relative z-10 mx-auto -mt-16 max-w-5xl px-5 sm:px-8">
    <form action="index.php" method="get" class="grid gap-4 rounded-3xl bg-white p-5 shadow-2xl shadow-vaygor-900/10 ring-1 ring-black/5 sm:p-6 md:grid-cols-[1.4fr_1fr_auto] md:items-end">
      <input type="hidden" name="p" value="browse">

      <div>
        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cari Venue</label>
        <div class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 transition focus-within:border-vaygor-400 focus-within:bg-white focus-within:ring-2 focus-within:ring-vaygor-100">
          <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <circle cx="11" cy="11" r="7" />
            <path d="M21 21l-4.35-4.35" />
          </svg>
          <input type="text" name="nama" placeholder="Nama venue / lapangan..." class="w-full bg-transparent text-sm text-gray-900 outline-none placeholder:text-gray-400">
        </div>
      </div>

      <div>
        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Olahraga</label>
        <select name="jenis" class="w-full cursor-pointer appearance-none rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 outline-none transition focus:border-vaygor-400 focus:bg-white focus:ring-2 focus:ring-vaygor-100">
          <option value="">Semua Olahraga</option>
          <?php
          $qjenis = mysqli_query($conn, "SELECT id_kat, name_kat FROM kategori ORDER BY id_kat");
          while ($jr = mysqli_fetch_assoc($qjenis)): ?>
            <option value="<?= (int) $jr['id_kat']; ?>"><?= htmlspecialchars($jr['name_kat'], ENT_QUOTES, 'UTF-8'); ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <button type="submit" class="flex items-center justify-center gap-2 rounded-2xl bg-vaygor-600 px-7 py-4 text-sm font-bold text-white shadow-lg shadow-vaygor-600/30 transition hover:bg-vaygor-700 hover:shadow-xl hover:shadow-vaygor-700/30">
        Cari Sekarang
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
      </button>
    </form>
  </section>

  <!-- ═══════════ KATEGORI ═══════════ -->
  <section id="kategori" class="mx-auto mt-24 max-w-7xl scroll-mt-24 px-5 sm:px-8 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-vaygor-600">Kategori</p>
        <h2 class="mt-2 font-spartan text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Pilih Olahraga Favoritmu</h2>
      </div>
      <a href="index.php?p=browse" class="inline-flex items-center gap-1 text-sm font-semibold text-vaygor-600 transition hover:text-vaygor-800">
        Lihat Semua
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
      </a>
    </div>

    <div class="container mx-auto">

      <div class="flex flex-row gap-5 overflow-x-auto p-4">
        <?php
        $qkat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY name_kat DESC");

        while ($k = mysqli_fetch_assoc($qkat)): ?>
          <a href="index.php?p=browse&kat=<?= $k['id_kat']; ?>" class="group relative shrink-0 h-52 w-64 overflow-hidden rounded-3xl ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-vaygor-900/20 sm:w-72">
            <img src="assets/images/<?= $k['logo_kat']; ?>" alt="<?= $k['name_kat']; ?>" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-110">
            <div class="absolute inset-0 bg-gradient-to-t from-vaygor-950/90 via-vaygor-950/10 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
              <p class="font-spartan text-lg font-bold text-white sm:text-xl"><?= $k['name_kat'] ?></p>
            </div>
          </a>
        <?php endwhile; ?>
      </div>
    </div>

  </section>

  <!-- ═══════════ VENUE POPULER ═══════════ -->
  <section id="venue" class="mx-auto mt-24 max-w-7xl scroll-mt-24 px-5 sm:px-8 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-vaygor-600">Top Rated</p>
        <h2 class="mt-2 font-spartan text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Venue Populer</h2>
      </div>
      <a href="index.php?p=browse" class="inline-flex items-center gap-1 text-sm font-semibold text-vaygor-600 transition hover:text-vaygor-800">
        Jelajahi Semua
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
      </a>
    </div>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php
      $dir = 'assets/uploads/';
      $qv = mysqli_query($conn, "
    SELECT 
        f.*,
        (
            SELECT k.name_kat
            FROM fields_kat fk
            JOIN kategori k ON k.id_Kat = fk.id_kat
            WHERE fk.id_field = f.id
            LIMIT 1
        ) AS category,

        ROUND(
            AVG(
                (b.rate_service + b.rate_comfort + b.rate_place) / 3
            ), 1
        ) AS rating,

        (
            SELECT COUNT(DISTINCT b.id) FROM bookings b
            WHERE b.id_field = f.id 
                AND b.rate_service > 0
                AND b.rate_comfort > 0
                AND b.rate_place > 0
        ) AS total_rating

        

    FROM fields f

    LEFT JOIN fields_kat fk 
        ON fk.id_field = f.id

    LEFT JOIN kategori k 
        ON k.id_kat = fk.id_kat

    LEFT JOIN bookings b 
        ON b.id_field = f.id
        AND b.rate_service > 0
        AND b.rate_comfort > 0
        AND b.rate_place > 0

    GROUP BY f.id 
    ORDER BY total_rating DESC
    LIMIT 4
");
      while ($v = mysqli_fetch_assoc($qv)): ?>
        <a href="index.php?p=produk&id=<?= $v['id']; ?>" class="group overflow-hidden rounded-3xl bg-white ring-1 ring-black/5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-vaygor-900/15">
          <div class="relative h-48 overflow-hidden">
            <img src="<?= $dir . $v['image']; ?>" alt="<?= $v['name']; ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">

            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-vaygor-700 shadow backdrop-blur <?= $v['category'] !== null ? '' : 'hidden'; ?>">
              <?= htmlspecialchars($v['category']); ?>
            </span>
          </div>
          <div class="p-5">
            <div class="flex items-center justify-between">
              <p class="font-spartan text-lg font-bold text-gray-900"><?= $v['name']; ?></p>
              <span class="flex items-center gap-1 text-xs font-semibold text-gray-500">
                <svg class="h-3.5 w-3.5 fill-amber-400" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                </svg>

                <?= $v['total_rating'] > 0 ? $v['rating'] : '0'; ?>
                (<?= $v['total_rating']; ?>)
              </span>
            </div>
            <p class="mt-1 flex items-center gap-1 text-xs text-gray-400">
              <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              <?= $v['location']; ?>
            </p>
            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
              <p class="font-spartan text-lg font-extrabold text-vaygor-600">Rp <?= number_format((int) $v['price'], 0, ',', '.'); ?><span class="text-xs font-medium text-gray-400">/jam</span></p>
              <button type="button" class="flex items-center gap-1 rounded-full bg-vaygor-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-vaygor-700">Details</button>
            </div>
          </div>
        </a>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- ═══════════ KEUNGGULAN ═══════════ -->
  <section id="keunggulan" class="mx-auto mt-24 max-w-7xl scroll-mt-24 px-5 sm:px-8 lg:px-10">
    <div class="text-center">
      <p class="text-xs font-bold uppercase tracking-[0.2em] text-vaygor-600">Kenapa VAYGOR?</p>
      <h2 class="mt-2 font-spartan text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Main Lebih Mudah,<br class="sm:hidden"> Langsung Jadi</h2>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
      <div class="group rounded-3xl bg-white p-8 ring-1 ring-black/5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-vaygor-900/15">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-vaygor-50 text-vaygor-600 transition duration-300 group-hover:bg-vaygor-600 group-hover:text-white">
          <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 3" />
          </svg>
        </div>
        <h3 class="mt-5 font-spartan text-xl font-bold text-gray-900">Booking Instan</h3>
        <p class="mt-2 text-sm leading-relaxed text-gray-500">Pilih lapangan, tentukan jam main, dan konfirmasi dalam hitungan menit. Tanpa antre, tanpa ribet.</p>
      </div>

      <div class="group rounded-3xl bg-white p-8 ring-1 ring-black/5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-vaygor-900/15">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-vaygor-50 text-vaygor-600 transition duration-300 group-hover:bg-vaygor-600 group-hover:text-white">
          <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
          </svg>
        </div>
        <h3 class="mt-5 font-spartan text-xl font-bold text-gray-900">Harga Bersahabat</h3>
        <p class="mt-2 text-sm leading-relaxed text-gray-500">Bandingkan harga antar lapangan, pilih yang paling pas di kantongmu, dan nikmati promo terbaru tiap minggu.</p>
      </div>

      <div class="group rounded-3xl bg-white p-8 ring-1 ring-black/5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-vaygor-900/15">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-vaygor-50 text-vaygor-600 transition duration-300 group-hover:bg-vaygor-600 group-hover:text-white">
          <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z" />
            <path d="M9 12l2 2 4-4" />
          </svg>
        </div>
        <h3 class="mt-5 font-spartan text-xl font-bold text-gray-900">Aman & Terpercaya</h3>
        <p class="mt-2 text-sm leading-relaxed text-gray-500">Setiap venue sudah diverifikasi. Booking aman, aturan jelas, dan bantuan siap 24 jam untuk para pemain.</p>
      </div>
    </div>
  </section>

  <!-- ═══════════ CARA BOOKING ═══════════ -->
  <section id="cara" class="mx-auto mt-24 scroll-mt-24">
    <div class="grid items-center gap-10 overflow-hidden bg-gradient-to-br from-vaygor-700 via-vaygor-800 to-vaygor-950 p-8 shadow-2xl shadow-vaygor-900/30 sm:p-12 lg:grid-cols-2 lg:p-16">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-neon">Cara Booking</p>
        <h2 class="mt-3 font-spartan text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">Main Bareng Teman,<br>Gampang Banget</h2>
        <p class="mt-4 max-w-md text-sm leading-relaxed text-white/70 sm:text-base">Empat langkah simpel dari mencari lapangan sampai konfirmasi booking. Tidak perlu telepon atau datang langsung.</p>
        <a href="index.php?p=browse" class="mt-8 inline-flex items-center gap-2 rounded-2xl bg-neon px-7 py-4 text-sm font-bold text-vaygor-950 shadow-xl shadow-black/30 transition hover:scale-[1.03] hover:bg-white">
          Mulai Booking
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </a>
      </div>

      <ol class="space-y-5">
        <?php
        $langkah = [
          ['1', 'Cari Lapangan', 'Gunakan pencarian atau filter berdasarkan jenis olahraga, lokasi, dan harga.'],
          ['2', 'Pilih Waktu', 'Tentukan tanggal dan jam sesuai jadwal tim kamu.'],
          ['3', 'Konfirmasi Booking', 'Verifikasi data dan selesaikan booking dalam sekali klik.'],
          ['4', 'Tinggal Main!', 'Datang ke lapangan, tunjukkan bukti booking, dan mulai pertandingan.'],
        ];
        foreach ($langkah as $i => $l): ?>
          <li class="flex items-start gap-4 rounded-2xl bg-white/5 p-4 ring-1 ring-white/10 backdrop-blur transition hover:bg-white/10 sm:items-center">
            <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl font-spartan text-lg font-extrabold text-vaygor-950 <?= $i === 0 ? 'bg-neon' : 'bg-white/90'; ?>"><?= $l[0]; ?></span>
            <div>
              <p class="text-sm font-bold text-white"><?= $l[1]; ?></p>
              <p class="mt-0.5 text-xs leading-relaxed text-white/60"><?= $l[2]; ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>

  <!-- ═══════════ CTA ═══════════ -->
  <section class="mx-auto mt-24 max-w-7xl px-5 sm:px-8 lg:px-10">
    <div class="relative overflow-hidden rounded-[2.5rem] bg-black px-8 py-16 text-center shadow-2xl sm:py-20">
      <img src="assets/images/mini.webp" alt="" class="absolute inset-0 h-full w-full object-cover opacity-40">
      <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-vaygor-950/60"></div>

      <div class="relative">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-neon">Ready Team Player?</p>
        <h2 class="mx-auto mt-4 max-w-2xl font-spartan text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">SAATNYA KAMU KELUAR DAN BERTANDING</h2>
        <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-white/70 sm:text-base">Jangan tunda lagi. Lapangan terbaik, harga pas, dan tim kamu sudah menunggu di dalam.</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
          <a href="index.php?p=browse" class="rounded-2xl bg-neon px-8 py-4 text-sm font-bold text-vaygor-950 shadow-xl shadow-black/30 transition hover:scale-[1.03] hover:bg-white">Book Sekarang</a>
          <a href="#venue" class="rounded-2xl bg-white/10 px-8 py-4 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white/20">Lihat Venue</a>
        </div>
      </div>
    </div>
  </section>



</div>

<!-- ═══════════ FOOTER ═══════════ -->
<?php include 'includes/footer.php'; ?>