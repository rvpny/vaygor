<?php
$vid = (int)($_GET['id'] ?? 1);

function p_price($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function p_price_of($row) { return $row['price_per_hour'] ?? $row['price'] ?? 0; }
function p_img($row)
{
    $c = $row['image'] ?? '';
    if ($c !== '') {
        if (file_exists(__DIR__ . '/../assets/uploads/' . $c)) return 'assets/uploads/' . $c;
        if (file_exists(__DIR__ . '/../assets/images/' . $c)) return 'assets/images/' . $c;
    }
    if (file_exists(__DIR__ . '/../assets/images/lapangan.png')) return 'assets/images/lapangan.png';
    if (file_exists(__DIR__ . '/../assets/images/lapangan-pancuran.png')) return 'assets/images/lapangan-pancuran.png';
    return 'assets/images/wGrqDff8QHtt4PXzMsos8WVXJI_1.png';
}

$venue = null;
$isOldSchema = false;
try {
    $stmt = $conn->prepare("SELECT f.* FROM fields f WHERE f.id = ? AND f.status='available' LIMIT 1");
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $venue = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Throwable $e) {
    $venue = null;
    $msg = $e->getMessage();
    if (str_contains($msg, 'Unknown column') || str_contains($msg, "doesn't exist")) {
        $isOldSchema = true;
        try {
            $stmt = $conn->prepare("SELECT f.* FROM fields f WHERE f.id = ? LIMIT 1");
            $stmt->bind_param("i", $vid);
            $stmt->execute();
            $venue = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } catch (Throwable $e2) { $venue = null; }
    }
}
if (!$venue) {
    http_response_code(404);
    echo '<div class="pt-28 pb-12 text-center"><p class="font-spartan text-xl font-bold">Venue tidak ditemukan</p><a href="index.php?p=browse" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white">Kembali ke browse</a></div>';
    return;
}

// tipe venue: field_type (skema baru) atau kategori pertama dari fields_kat (skema lama)
$venueType = trim((string)($venue['field_type'] ?? ''));
if ($venueType === '') {
    try {
        $stmt = $conn->prepare("SELECT k.name_kat FROM fields_kat fk JOIN kategori k ON k.id_kat = fk.id_kat WHERE fk.id_field = ? LIMIT 1");
        $stmt->bind_param("i", $vid);
        $stmt->execute();
        $kr = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $venueType = $kr['name_kat'] ?? '';
    } catch (Throwable $e) {}
}
if ($venueType === '') $venueType = 'Lapangan';

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
    $sql = $isOldSchema
        ? "SELECT f.* FROM fields f WHERE f.id != ? ORDER BY f.id ASC LIMIT 3"
        : "SELECT f.* FROM fields f WHERE f.id != ? AND f.status='available' ORDER BY f.id ASC LIMIT 3";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) $other[] = $row;
    $stmt->close();
} catch (Throwable $e) {}

// Galeri: hanya foto asli venue ini (bukan stok/lain venue).
$gallery = [];
$mainImg = p_img($venue);
$gallery[] = ['src' => $mainImg, 'alt' => 'Foto venue ' . $venue['name']];

// Review dinamis dari bookings (status selesai/confirmed + nilai > 0)
$reviews = [];
$reviewCount = 0;
$avgOverall = 0.0;
$avgPlace = 0.0;
$avgComfort = 0.0;
$avgService = 0.0;
try {
    $stmt = $conn->prepare("SELECT b.id_user, b.booking_date, b.rate_service, b.rate_comfort, b.rate_place, b.review, u.name FROM bookings b JOIN users u ON u.id = b.id_user WHERE b.id_field = ? AND b.status IN ('completed','confirmed') AND b.rate_service > 0 AND b.review <> '' ORDER BY b.booking_date DESC, b.id DESC");
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) $reviews[] = $row;
    $stmt->close();
} catch (Throwable $e) {}

$reviewCount = count($reviews);
if ($reviewCount > 0) {
    $sumP = $sumC = $sumS = 0;
    $bulanId = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $tmp = [];
    foreach ($reviews as $rv) {
        $sumP += (int)$rv['rate_place'];
        $sumC += (int)$rv['rate_comfort'];
        $sumS += (int)$rv['rate_service'];
        $ts = strtotime($rv['booking_date']);
        $rv['dateLabel'] = sprintf('%02d %s %d', (int)date('j', $ts), $bulanId[(int)date('n', $ts)] ?? date('M', $ts), (int)date('Y', $ts));
        $rv['avg'] = ((int)$rv['rate_place'] + (int)$rv['rate_comfort'] + (int)$rv['rate_service']) / 3;
        $rv['hasReply'] = ((int)$rv['id_user'] === 2 && (int)$venue['id'] === 1);
        $tmp[] = $rv;
    }
    $reviews = $tmp;
    $avgPlace = $sumP / $reviewCount;
    $avgComfort = $sumC / $reviewCount;
    $avgService = $sumS / $reviewCount;
    $avgOverall = ($avgPlace + $avgComfort + $avgService) / 3;
}
$ratingBadge = $reviewCount > 0
    ? number_format($avgOverall, 1, ',', '.') . ' dari ' . $reviewCount . ' ulasan'
    : 'Belum ada ulasan';

// Fasilitas: hanya data terkonfirmasi dari DB (deskripsi + kapasitas + kategori).
$facilityTags = [];
$capacity = (int)($venue['capacity'] ?? 0);
if ($capacity > 0) $facilityTags[] = $capacity . ' pemain';
if ($venueType !== '') $facilityTags[] = ucfirst($venueType);
try {
    $stmt = $conn->prepare("SELECT k.name_kat FROM fields_kat fk JOIN kategori k ON k.id_kat = fk.id_kat WHERE fk.id_field = ?");
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $n = $row['name_kat'];
        if ($n && !in_array(ucfirst($n), $facilityTags, true) && !in_array($n, $facilityTags, true)) {
            $facilityTags[] = $n;
        }
    }
    $stmt->close();
} catch (Throwable $e) {}
?>
<div class="pt-20">
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 py-6">
    <a href="index.php?p=browse" class="inline-flex items-center gap-1 text-sm text-zinc-600 hover:text-vaygor-600">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
      Kembali ke browse
    </a>
    <p class="mt-3 text-xs font-bold uppercase tracking-[0.2em] text-vaygor-600">Detail venue</p>
  </div>

  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 pb-12">
    <div class="grid gap-6 lg:grid-cols-[1.7fr_1fr]">
      <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">
        <img src="<?php echo htmlspecialchars($gallery[0]['src'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($gallery[0]['alt'], ENT_QUOTES, 'UTF-8'); ?>" class="h-64 sm:h-80 w-full object-cover">
        <?php if (count($gallery) > 1): ?>
          <div class="grid grid-cols-2 gap-2 p-2 bg-zinc-50">
            <?php for ($gi = 1; $gi < count($gallery); $gi++): ?>
              <img src="<?php echo htmlspecialchars($gallery[$gi]['src'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($gallery[$gi]['alt'], ENT_QUOTES, 'UTF-8'); ?>" class="h-28 w-full object-cover rounded-xl">
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="rounded-2xl border border-zinc-200 bg-white p-6">
        <div class="flex flex-wrap gap-2">
          <span class="rounded-full bg-vaygor-50 px-3 py-1 text-xs font-semibold text-vaygor-700"><?php echo htmlspecialchars(ucfirst($venueType), ENT_QUOTES, 'UTF-8'); ?></span>
          <?php if (!empty($scheds)): ?>
            <?php
            $openMin = 24 * 60;
            $closeMin = 0;
            foreach ($scheds as $s) {
                $oh = (int)substr($s['open_time'], 0, 2) * 60 + (int)substr($s['open_time'], 3, 2);
                $ch = (int)substr($s['close_time'], 0, 2) * 60 + (int)substr($s['close_time'], 3, 2);
                if ($oh < $openMin) $openMin = $oh;
                if ($ch > $closeMin) $closeMin = $ch;
            }
            $isDay = ($openMin <= 0 && $closeMin >= 24 * 60);
            ?>
            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-600">Jam <?php echo htmlspecialchars(sprintf('%02d:%02d', intdiv($openMin, 60), $openMin % 60), ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars(sprintf('%02d:%02d', intdiv($closeMin, 60) % 24, $closeMin % 60), ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if ($isDay): ?>
              <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-600">Buka 24 jam</span>
            <?php endif; ?>
          <?php else: ?>
            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-600">Jadwal menyusul</span>
          <?php endif; ?>
        </div>
        <h1 class="mt-3 font-spartan text-2xl sm:text-3xl font-extrabold tracking-tight"><?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="mt-1 text-sm text-zinc-600"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="mt-3 flex items-center gap-2">
          <span class="font-spartan text-2xl font-extrabold text-vaygor-600"><?php echo htmlspecialchars(p_price(p_price_of($venue)), ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="text-sm text-zinc-500">/ jam</span>
          <span class="ml-auto text-xs text-zinc-500"><?php echo htmlspecialchars($ratingBadge, ENT_QUOTES, 'UTF-8'); ?></span>
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
          <h2 class="font-spartan text-lg font-bold">Fasilitas</h2>
          <?php if (!empty($facilityTags)): ?>
            <ul class="mt-3 flex flex-wrap gap-2 text-sm">
              <?php foreach ($facilityTags as $tag): ?>
                <li class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-zinc-700"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="mt-2 text-sm text-zinc-600">Info fasilitas menyusul dari pengelola venue.</p>
          <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Detail venue</h2>
          <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl bg-zinc-50 p-3"><dt class="text-xs text-zinc-500">Luas</dt><dd class="font-semibold">25 x 11</dd></div>
            <div class="rounded-xl bg-zinc-50 p-3"><dt class="text-xs text-zinc-500">Harga</dt><dd class="font-semibold"><?php echo htmlspecialchars(p_price(p_price_of($venue)), ENT_QUOTES, 'UTF-8'); ?>/jam</dd></div>
            <div class="rounded-xl bg-zinc-50 p-3"><dt class="text-xs text-zinc-500">Tipe</dt><dd class="font-semibold"><?php echo htmlspecialchars(ucfirst($venueType), ENT_QUOTES, 'UTF-8'); ?></dd></div>
            <div class="rounded-xl bg-zinc-50 p-3 col-span-2"><dt class="text-xs text-zinc-500">Kapasitas</dt><dd class="font-semibold"><?php echo $capacity > 0 ? htmlspecialchars((string)$capacity, ENT_QUOTES, 'UTF-8') . ' pemain' : 'Belum tersedia'; ?></dd></div>
            <div class="rounded-xl bg-zinc-50 p-3 col-span-2"><dt class="text-xs text-zinc-500">Lokasi</dt><dd class="font-semibold"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
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
          <?php if ($reviewCount === 0): ?>
            <div class="mt-3 rounded-xl border border-zinc-200 bg-zinc-50 p-5 text-center">
              <p class="text-sm font-semibold text-zinc-700">Belum ada ulasan untuk venue ini.</p>
              <p class="mt-1 text-xs text-zinc-500">Jadi yang pertama setelah main di sini.</p>
            </div>
          <?php else: ?>
            <div class="mt-3 flex items-center gap-3">
              <span class="font-spartan text-3xl font-extrabold"><?php echo htmlspecialchars(number_format($avgOverall, 1, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="text-sm text-zinc-600"><?php echo (int)$reviewCount; ?> Ulasan</span>
            </div>
            <div class="mt-4 space-y-3">
              <div class="flex justify-between text-sm"><span>Kondisi Lapangan</span><b><?php echo htmlspecialchars(number_format($avgPlace, 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></b></div>
              <div class="flex justify-between text-sm"><span>Kenyamanan &amp; Kebersihan</span><b><?php echo htmlspecialchars(number_format($avgComfort, 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></b></div>
              <div class="flex justify-between text-sm"><span>Komunikasi</span><b><?php echo htmlspecialchars(number_format($avgService, 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></b></div>
            </div>
            <div class="mt-6 flex gap-3 overflow-x-auto pb-2">
              <?php foreach ($reviews as $rv): ?>
                <div class="w-[320px] flex-shrink-0 rounded-xl bg-zinc-50 p-4">
                  <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-vaygor-600 text-xs font-extrabold text-white" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(mb_substr(trim((string)$rv['name']), 0, 1, 'UTF-8')), ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-semibold truncate"><?php echo htmlspecialchars($rv['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                      <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($rv['dateLabel'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <?php if (!empty($rv['avg']) && $rv['avg'] > 0): ?>
                      <span class="inline-flex flex-shrink-0 items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-xs font-bold text-amber-600">
                        <svg class="h-3 w-3 fill-amber-400" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>
                        <?php echo htmlspecialchars(number_format($rv['avg'], 1, ',', '.'), ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <p class="mt-3 text-sm leading-relaxed text-zinc-700 whitespace-pre-line"><?php echo htmlspecialchars(trim($rv['review']), ENT_QUOTES, 'UTF-8'); ?></p>
                  <?php if (!empty($rv['hasReply'])): ?>
                    <div class="mt-3 rounded-xl bg-white p-3 border border-zinc-100">
                      <p class="text-xs font-semibold">Balasan dari Lapangan Pancuranmas (P.Agus):</p>
                      <p class="text-sm">Bayar harus dikejar dulu sampai masjid lu, untung tertangkap kau SUKI!!!</p>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="mt-4 text-xs text-zinc-500">Menampilkan <?php echo (int)$reviewCount; ?> ulasan terakhir untuk venue ini.</p>
          <?php endif; ?>
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
                <p class="text-xs text-zinc-500"><?php echo htmlspecialchars(ucfirst($o['field_type'] ?? 'Lapangan'), ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars(p_price(p_price_of($o)), ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </a>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
