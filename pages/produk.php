<?php
$vid = (int)($_GET['id'] ?? 1);
$venue = null;
try {
    $stmt = $conn->prepare("SELECT id, name, location, description, field_type, price_per_hour, image FROM fields WHERE id = ? AND status='available' LIMIT 1");
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $res = $stmt->get_result();
    $venue = $res->fetch_assoc();
    $stmt->close();
} catch (Throwable $e) { $venue = null; }
if (!$venue) {
    http_response_code(404);
    echo '<div class="pt-28 pb-12 text-center"><p class="font-spartan text-xl font-bold">Venue tidak ditemukan</p><a href="index.php?p=browse" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white">Kembali ke browse</a></div>';
    return;
}
function p_img($row) {
    $c = $row['image'] ?? '';
    $base = __DIR__ . '/../assets/images/';
    if ($c !== '' && file_exists($base . $c)) return 'assets/images/' . $c;
    if (file_exists($base . 'lapangan-pancuran.png')) return 'assets/images/lapangan-pancuran.png';
    return 'assets/images/wGrqDff8QHtt4PXzMsos8WVXJI_1.png';
}
function p_price($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
$scheds = [];
try {
    $stmt = $conn->prepare("SELECT day_of_week, open_time, close_time FROM field_schedules WHERE field_id=? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $r = $stmt->get_result();
    while($row=$r->fetch_assoc()) $scheds[]=$row;
    $stmt->close();
} catch(Throwable $e) {}
$other = [];
try {
    $stmt = $conn->prepare("SELECT id, name, field_type, price_per_hour, image FROM fields WHERE id != ? AND status='available' ORDER BY id ASC LIMIT 3");
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) $other[] = $row;
    $stmt->close();
} catch (Throwable $e) {}
?>
<div class="pt-20">
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 py-6">
    <a href="index.php?p=browse" class="inline-flex items-center gap-1 text-sm text-zinc-600 hover:text-vaygor-600">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
      Kembali ke browse
    </a>
  </div>

  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 pb-12">
    <div class="grid gap-6 lg:grid-cols-[1.7fr_1fr]">
      <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">
        <img src="<?php echo htmlspecialchars(p_img($venue), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-64 sm:h-80 w-full object-cover">
        <div class="grid grid-cols-2 gap-2 p-2 bg-zinc-50">
          <img src="assets/images/BI1q9FfyMAeVA3VVQYv2goJnig_2.png" alt="" class="h-28 w-full object-cover rounded-xl">
          <img src="assets/images/vKBS9C3RJNkcYZLBU3IHvfLjv0_1.png" alt="" class="h-28 w-full object-cover rounded-xl">
        </div>
      </div>

      <div class="rounded-2xl border border-zinc-200 bg-white p-6">
        <div class="flex flex-wrap gap-2">
          <span class="rounded-full bg-vaygor-50 px-3 py-1 text-xs font-semibold text-vaygor-700"><?php echo htmlspecialchars(ucfirst($venue['field_type']), ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-600">Football</span>
          <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-600">24 Hour</span>
        </div>
        <h1 class="mt-3 font-spartan text-2xl sm:text-3xl font-extrabold tracking-tight"><?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="mt-1 text-sm text-zinc-600"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="mt-3 flex items-center gap-2">
          <span class="font-spartan text-2xl font-extrabold text-vaygor-600"><?php echo htmlspecialchars(p_price($venue['price_per_hour']), ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="text-sm text-zinc-500">/ jam</span>
          <span class="ml-auto text-xs text-zinc-500">25 Reviews • 4.4</span>
        </div>
        <a href="index.php?p=booking&id=<?php echo (int)$venue['id']; ?>" class="mt-6 flex w-full items-center justify-center rounded-xl bg-vaygor-600 px-6 py-3.5 text-sm font-bold text-white hover:bg-vaygor-700">Booking Sekarang</a>
        <p class="mt-2 text-center text-xs text-zinc-500">Cek jadwal di bawah sebelum booking.</p>
      </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Deskripsi</h2>
          <p class="mt-2 text-sm leading-relaxed text-zinc-600"><?php echo htmlspecialchars($venue['description'] ?? 'Lapangan futsal di Magelang.', ENT_QUOTES, 'UTF-8'); ?></p>
          <img src="assets/images/Ayt7dcCT9fAjGZaPk6wuKGJslMU_1.png" alt="Denah" class="mt-4 w-full rounded-xl border border-zinc-100">
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Detail venue</h2>
          <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl bg-zinc-50 p-3"><dt class="text-xs text-zinc-500">Luas</dt><dd class="font-semibold">25 x 11</dd></div>
            <div class="rounded-xl bg-zinc-50 p-3"><dt class="text-xs text-zinc-500">Harga</dt><dd class="font-semibold"><?php echo htmlspecialchars(p_price($venue['price_per_hour']), ENT_QUOTES, 'UTF-8'); ?>/jam</dd></div>
            <div class="rounded-xl bg-zinc-50 p-3"><dt class="text-xs text-zinc-500">Tipe</dt><dd class="font-semibold"><?php echo htmlspecialchars(ucfirst($venue['field_type']), ENT_QUOTES, 'UTF-8'); ?></dd></div>
            <div class="rounded-xl bg-zinc-50 p-3"><dt class="text-xs text-zinc-500">Lokasi</dt><dd class="font-semibold"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
          </dl>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Jam operasional</h2>
          <?php if(!empty($scheds)): ?>
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
              <?php foreach($scheds as $s): ?>
                <div class="rounded-xl border border-zinc-100 bg-zinc-50 px-3 py-2 flex justify-between"><span class="text-zinc-600"><?php echo htmlspecialchars($s['day_of_week'], ENT_QUOTES, 'UTF-8'); ?></span><span class="font-semibold"><?php echo htmlspecialchars(substr($s['open_time'],0,5), ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars(substr($s['close_time'],0,5), ENT_QUOTES, 'UTF-8'); ?></span></div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-sm text-zinc-600">Jadwal belum tersedia.</p>
          <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Lokasi</h2>
          <img src="assets/images/PhExImUwbpxAs1tNEBY2rU2rO5k_2.png" alt="Peta" class="mt-3 h-40 w-full object-cover rounded-xl">
          <p class="mt-3 text-sm text-zinc-600"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></p>
          <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($venue['name'].' Magelang'); ?>" target="_blank" rel="noopener" class="mt-3 inline-flex rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold hover:border-vaygor-600 hover:text-vaygor-600">Buka Peta →</a>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Aturan Pak Agus</h2>
          <p class="text-xs text-zinc-500">Aturan Pemakaian (Denda jika tidak mematuhi):</p>
          <ol class="mt-2 list-decimal pl-5 space-y-1 text-sm text-zinc-700">
            <li>Dilarang membawa sajam dan alkohol</li>
            <li>Dilarang melakukan kekerasan</li>
            <li>Konfirmasi Booking min 3 jam</li>
            <li>Dilarang merusak dan mengambil dari lapangan</li>
            <li>Kehilangan barang bukan tanggung jawab kami</li>
          </ol>
        </div>

        <div id="ulasan" class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Ulasan Penyewa</h2>
          <div class="mt-3 flex items-center gap-3">
            <span class="font-spartan text-3xl font-extrabold">4.4</span>
            <span class="text-sm text-zinc-600">25 Reviews</span>
          </div>
          <div class="mt-4 space-y-3">
            <div class="flex justify-between text-sm"><span>Kondisi Lapangan</span><b>4.00</b></div>
            <div class="flex justify-between text-sm"><span>Kenyamanan &amp; Kebersihan</span><b>3.70</b></div>
            <div class="flex justify-between text-sm"><span>Komunikasi</span><b>4.50</b></div>
          </div>
          <div class="mt-6 rounded-xl bg-zinc-50 p-4">
            <div class="flex gap-3">
              <img src="assets/images/Kq6VosSy8jdclArIT8XO0NKR3nI.png" alt="" class="h-9 w-9 rounded-full">
              <div><p class="text-sm font-semibold">@Basis nama tengahku</p><p class="text-xs text-zinc-500">07 Agustus 2026</p></div>
            </div>
            <p class="mt-3 text-sm">WOYY itu danis suruh pulang cok... ganggu ae</p>
            <div class="mt-3 rounded-xl bg-white p-3 border border-zinc-100">
              <p class="text-xs font-semibold">Balasan dari Lapangan Pancuranmas (P.Agus):</p>
              <p class="text-sm">Bayar harus dikejar dulu sampai masjid lu, untung tertangkap kau SUKI!!!</p>
            </div>
          </div>
          <a href="#ulasan" class="mt-4 inline-flex text-sm font-semibold text-vaygor-600">Lihat semua ulasan</a>
        </div>
      </div>

      <div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h3 class="font-spartan font-bold">Venue Lain di Magelang</h3>
          <?php if(empty($other)): ?>
            <p class="text-sm text-zinc-500 mt-2">Tidak ada venue lain.</p>
          <?php else: foreach($other as $o): ?>
            <a href="index.php?p=produk&id=<?php echo (int)$o['id']; ?>" class="mt-4 flex gap-3 rounded-xl border border-zinc-100 p-3 hover:border-vaygor-600">
              <img src="<?php echo htmlspecialchars(p_img($o), ENT_QUOTES, 'UTF-8'); ?>" alt="" class="h-16 w-16 rounded-xl object-cover">
              <div>
                <p class="text-sm font-bold"><?php echo htmlspecialchars($o['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="text-xs text-zinc-500"><?php echo htmlspecialchars(ucfirst($o['field_type']), ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars(p_price($o['price_per_hour']), ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </a>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
