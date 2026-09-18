<?php
// expects $conn from index.php, GET q/type/date via index.php router
$q = trim($_GET['q'] ?? '');
$type = trim($_GET['type'] ?? '');
$date = trim($_GET['date'] ?? '');

$venues = [];
$dbError = '';
try {
    // hanya field yang available; date diteruskan tapi belum memfilter (komentar eksplisit: butuh logika ketersediaan fase booking)
    $sql = "SELECT id, name, location, field_type, price_per_hour, image FROM fields WHERE status='available'";
    $params = [];
    $types = '';

    if ($q !== '') {
        $sql .= " AND (name LIKE ? OR location LIKE ?)";
        $like = "%$q%";
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }
    if (in_array($type, ['indoor','outdoor'], true)) {
        $sql .= " AND field_type = ?";
        $params[] = $type;
        $types .= 's';
    } elseif ($type !== '' && $type !== 'futsal') {
        // futsal + tipe lain diperlakukan sebagai keyword jika bukan indoor/outdoor, tidak difilter ketat
    }
    $sql .= " ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $venues[] = $row;
    }
    $stmt->close();
} catch (Throwable $e) {
    $dbError = 'Gagal memuat venue. Coba lagi.';
}

function vaygor_browse_img($row) {
    $cand = $row['image'] ?? '';
    $base = __DIR__ . '/../assets/images/';
    if ($cand !== '' && file_exists($base . $cand)) {
        return 'assets/images/' . $cand;
    }
    // DB seed pakai pancuranmas.jpg yang tidak ada file-nya, fallback ke foto asli indoor
    if (file_exists($base . 'lapangan-pancuran.png')) return 'assets/images/lapangan-pancuran.png';
    return 'assets/images/wGrqDff8QHtt4PXzMsos8WVXJI_1.png';
}
function vaygor_price($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }

$chips = [
    '' => 'Semua',
    'indoor' => 'Indoor',
    'outdoor' => 'Outdoor',
];
$countText = count($venues) . ' venue';
if ($q !== '' || $type !== '') $countText .= ' ditemukan';
?>
<div class="pt-20">
  <!-- Hero strip -->
  <div class="relative overflow-hidden bg-vaygor-600 text-white">
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 25% 20%, white 1px, transparent 1px); background-size: 20px 20px;"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-vaygor-700/60 to-transparent"></div>
    <div class="relative mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 py-8 sm:py-10">
      <p class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold tracking-widest uppercase ring-1 ring-white/20">Jelajahi Lapangan</p>
      <h1 class="mt-3 font-spartan text-3xl sm:text-4xl font-extrabold tracking-tight">Cari yang pas buat timmu</h1>
      <p class="mt-2 text-sm text-white/90">Hasil untuk <span class="font-semibold"><?php echo $q !== '' ? htmlspecialchars($q, ENT_QUOTES, 'UTF-8') : 'semua venue'; ?></span> - <?php echo htmlspecialchars($countText, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
  </div>

  <!-- Search -->
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 -mt-6 relative z-10">
    <form action="index.php" method="get" class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5 shadow-[0_12px_32px_rgba(0,0,0,0.08)]">
      <input type="hidden" name="p" value="browse">
      <div class="grid gap-3 sm:grid-cols-[1.5fr_1fr_1fr_auto] sm:items-end">
        <div>
          <label for="bq" class="block text-xs font-semibold text-zinc-700">Cari</label>
          <div class="mt-1.5 relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            <input id="bq" type="text" name="q" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari nama atau lokasi" class="w-full rounded-xl border border-zinc-200 bg-white pl-9 pr-4 py-3 text-sm focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
          </div>
        </div>
        <div>
          <label for="btype" class="block text-xs font-semibold text-zinc-700">Tipe</label>
          <select id="btype" name="type" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
            <option value="">Semua tipe</option>
            <option value="indoor" <?php echo $type==='indoor'?'selected':''; ?>>Indoor</option>
            <option value="outdoor" <?php echo $type==='outdoor'?'selected':''; ?>>Outdoor</option>
          </select>
        </div>
        <div>
          <label for="bdate" class="block text-xs font-semibold text-zinc-700">Tanggal</label>
          <input id="bdate" type="date" name="date" value="<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
        </div>
        <button type="submit" class="h-[46px] rounded-xl bg-vaygor-600 px-6 text-sm font-bold text-white hover:bg-vaygor-700">Cari Lapangan</button>
      </div>
      <p class="mt-2 text-xs text-zinc-500">Tanggal diteruskan ke booking (belum memfilter ketersediaan sampai fase booking).</p>
    </form>
  </div>

  <!-- Chips -->
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 mt-5">
    <div class="flex flex-wrap gap-2">
      <?php foreach ($chips as $val => $label): 
        $active = $type === $val;
        $href = 'index.php?p=browse' . ($q !== '' ? '&q=' . urlencode($q) : '') . ($val !== '' ? '&type=' . urlencode($val) : '') . ($date !== '' ? '&date=' . urlencode($date) : '');
      ?>
        <a href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-full border px-4 py-2 text-sm font-semibold <?php echo $active ? 'border-vaygor-600 bg-vaygor-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-vaygor-600 hover:text-vaygor-600'; ?>"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Event banner (label jujur) -->
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 mt-6">
    <div class="rounded-2xl bg-gradient-to-br from-vaygor-600 to-vaygor-700 text-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold tracking-widest uppercase ring-1 ring-white/20">Segera hadir</p>
        <h2 class="mt-2 font-spartan text-xl sm:text-2xl font-bold">HAORNAS 2026</h2>
        <p class="mt-1 text-sm text-white/80">Turnamen komunitas - info jadwal menyusul.</p>
      </div>
      <span class="inline-flex rounded-xl bg-[#B6F500] px-5 py-2.5 text-sm font-bold text-zinc-900">Segera hadir</span>
    </div>
  </div>

  <!-- Results -->
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 mt-8 pb-12">
    <?php if ($dbError !== ''): ?>
      <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif (empty($venues)): ?>
      <div class="rounded-2xl border border-zinc-200 bg-white p-8 text-center">
        <p class="font-spartan text-lg font-bold text-zinc-900">Tidak ada venue yang cocok</p>
        <p class="mt-1 text-sm text-zinc-600">Coba ubah kata kunci atau tipe. Contoh: kosongkan pencarian untuk lihat semua.</p>
        <a href="index.php?p=browse" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-vaygor-700">Lihat semua venue</a>
      </div>
    <?php else: ?>
      <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($venues as $v): ?>
          <a href="index.php?p=produk&id=<?php echo (int)$v['id']; ?>" class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm hover:shadow-md transition">
            <div class="h-36 sm:h-40 overflow-hidden bg-zinc-100">
              <img src="<?php echo htmlspecialchars(vaygor_browse_img($v), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($v['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover group-hover:scale-[1.02] transition" loading="lazy">
            </div>
            <div class="p-4">
              <div class="flex gap-2 text-[11px] text-zinc-500">
                <span class="rounded-full bg-zinc-100 px-2 py-1"><?php echo htmlspecialchars(ucfirst($v['field_type']), ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="rounded-full bg-vaygor-50 text-vaygor-700 px-2 py-1">Magelang</span>
              </div>
              <h3 class="mt-2 font-spartan text-base font-bold text-zinc-900"><?php echo htmlspecialchars($v['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($v['location'], ENT_QUOTES, 'UTF-8'); ?></p>
              <div class="mt-3 flex items-center justify-between">
                <span class="text-sm font-bold text-vaygor-600"><?php echo htmlspecialchars(vaygor_price($v['price_per_hour']), ENT_QUOTES, 'UTF-8'); ?>/jam</span>
                <span class="text-xs font-semibold text-zinc-900 group-hover:text-vaygor-600">Lihat →</span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
