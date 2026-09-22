<?php
$vid = (int)($_GET['id'] ?? 1);
$venue = null;
try {
  $stmt = $conn->prepare("
    SELECT * FROM fields WHERE id = ? LIMIT 1
    
    ");
  $stmt->bind_param("i", $vid);
  $stmt->execute();
  $res = $stmt->get_result();
  $venue = $res->fetch_assoc();
  $stmt->close();
} catch (Throwable $e) {
  $venue = null;
}
if (!$venue) {
  http_response_code(404);
  echo '<div class="pt-28 pb-12 text-center"><p class="font-spartan text-xl font-bold">Venue tidak ditemukan</p><a href="index.php?p=browse" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white">Kembali ke browse</a></div>';
  return;
}
function p_img($row)
{
  $c = $row['image'] ?? '';
  if ($c !== '') {
    if (file_exists(__DIR__ . '/../assets/uploads/' . $c)) return 'assets/uploads/' . $c;
    if (file_exists(__DIR__ . '/../assets/images/' . $c)) return 'assets/images/' . $c;
  }
  return 'assets/images/lapangan.png';
}
function p_price($n)
{
  return 'Rp ' . number_format((float)$n, 0, ',', '.');
}
$scheds = [];
try {
  $stmt = $conn->prepare("SELECT day_of_week, open_time, close_time FROM field_schedules WHERE field_id=? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
  $stmt->bind_param("i", $vid);
  $stmt->execute();
  $r = $stmt->get_result();
  while ($row = $r->fetch_assoc()) $scheds[] = $row;
  $stmt->close();
} catch (Throwable $e) {
}
$other = [];
try {
  $stmt = $conn->prepare("SELECT 
        f.*,
      
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

    LEFT JOIN bookings b 
        ON b.id_field = f.id
        AND b.rate_service > 0
        AND b.rate_comfort > 0
        AND b.rate_place > 0

    WHERE f.id != ?
    GROUP BY f.id
    ORDER BY f.id ASC");
  $stmt->bind_param("i", $vid);
  $stmt->execute();
  $r = $stmt->get_result();
  while ($row = $r->fetch_assoc()) $other[] = $row;
  $stmt->close();
} catch (Throwable $e) {
}

// Rating dari tabel bookings
$ratingData = ['avg' => 0, 'count' => 0, 'service' => 0, 'comfort' => 0, 'place' => 0];
// Kategori milik venue ini
$cats = [];
// Peta kategori untuk semua field (dipakai di "Venue Lain")
$catMap = [];
try {
  $rq = $conn->prepare("SELECT COUNT(*) AS c,
            ROUND(AVG((rate_service + rate_comfort + rate_place) / 3), 1) AS a,
            ROUND(AVG(rate_service), 1) AS s,
            ROUND(AVG(rate_comfort), 1) AS co,
            ROUND(AVG(rate_place), 1) AS p
        FROM bookings
        WHERE id_field = ? AND rate_service > 0 AND rate_comfort > 0 AND rate_place > 0");
  $rq->bind_param("i", $vid);
  $rq->execute();
  $rr = $rq->get_result()->fetch_assoc();
  $rq->close();
  if ($rr && (int) $rr['c'] > 0) {
    $ratingData = [
      'avg'     => (float) $rr['a'],
      'count'   => (int) $rr['c'],
      'service' => (float) $rr['s'],
      'comfort' => (float) $rr['co'],
      'place'   => (float) $rr['p'],
    ];
  }

  $qc = $conn->prepare("SELECT k.name_kat FROM fields_kat fk JOIN kategori k ON k.id_kat = fk.id_kat WHERE fk.id_field = ? ORDER BY k.name_kat");
  $qc->bind_param("i", $vid);
  $qc->execute();
  $rc = $qc->get_result();
  while ($c = $rc->fetch_assoc()) $cats[] = $c['name_kat'];
  $qc->close();

  $qm = mysqli_query($conn, "SELECT fk.id_field, k.name_kat FROM fields_kat fk JOIN kategori k ON k.id_kat = fk.id_kat ORDER BY k.name_kat");
  while ($m = mysqli_fetch_assoc($qm)) $catMap[$m['id_field']][] = $m['name_kat'];
} catch (Throwable $e) {
}

$userLoggedIn = !empty($_SESSION['id']);
$bookUrl = $userLoggedIn
  ? 'index.php?p=booking&id=' . (int) $venue['id']
  : 'login.php?redirect=' . urlencode('index.php?p=produk&id=' . (int) $venue['id']);
$bookLabel = $userLoggedIn ? 'Booking Sekarang' : 'Masuk untuk Booking';
?>
<div class="pt-20">
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 py-6">
    <a href="index.php?p=browse" class="inline-flex items-center gap-1 text-sm text-zinc-600 hover:text-vaygor-600">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
        <path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
      </svg>
      Kembali ke browse
    </a>
  </div>

  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 pb-12">
    <div class="grid gap-6 lg:grid-cols-[1.7fr_1fr]">
      <div class="rounded-2xl border border-zinc-200 bg-white p-6">

        <!-- Image -->
        <img src="<?php echo htmlspecialchars(p_img($venue), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?>" class="rounded-2xl h-64 sm:h-80 w-full object-cover">

        <!-- header -->
        <div class="flex flex-wrap gap-2">
          <?php if (!empty($cats)): ?>
            <?php foreach ($cats as $cn): ?>
              <span class="inline-block rounded-full bg-vaygor-50 px-3 py-1 text-xs font-semibold text-vaygor-700 mt-4">
                <?php echo htmlspecialchars(ucfirst($cn), ENT_QUOTES, 'UTF-8'); ?>
              </span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <h1 class="mt-3 font-spartan text-2xl sm:text-3xl font-extrabold tracking-tight"><?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="mt-1 text-sm text-zinc-600"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="mt-3 flex items-center gap-2">
          <span class="font-spartan text-2xl font-extrabold text-vaygor-600"><?php echo htmlspecialchars(p_price($venue['price']), ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="text-sm text-zinc-500">/ jam</span>
          <span class="ml-auto text-xs text-zinc-500"><?php echo (int) $ratingData['count']; ?> Reviews<?php echo $ratingData['count'] > 0 ? ' • ' . number_format($ratingData['avg'], 1) : ''; ?></span>
        </div>
        <a href="<?php echo htmlspecialchars($bookUrl, ENT_QUOTES, 'UTF-8'); ?>" class="mt-6 flex w-full items-center justify-center rounded-xl bg-vaygor-600 px-6 py-3.5 text-sm font-bold text-white hover:bg-vaygor-700"><?php echo htmlspecialchars($bookLabel, ENT_QUOTES, 'UTF-8'); ?></a>
        <?php if (!$userLoggedIn): ?>
          <p class="mt-2 text-center text-xs text-zinc-500">Perlu masuk akun dulu untuk lanjut booking.</p>
        <?php else: ?>
          <p class="mt-2 text-center text-xs text-zinc-500">Cek jadwal di bawah sebelum booking.</p>
        <?php endif; ?>
      </div>


      <!-- detail -->
      <div class="rounded-2xl border border-zinc-200 bg-white p-6">
        <h2 class="font-spartan text-lg font-bold">Detail venue</h2>
        <dl class="mt-3 grid gap-3 text-sm">
          <div class="rounded-xl bg-zinc-50 p-3">
            <dt class="text-xs text-zinc-500">Luas</dt>
            <dd class="font-semibold">25 x 11</dd>
          </div>
          <div class="rounded-xl bg-zinc-50 p-3">
            <dt class="text-xs text-zinc-500">Harga</dt>
            <dd class="font-semibold"><?php echo htmlspecialchars(p_price($venue['price']), ENT_QUOTES, 'UTF-8'); ?>/jam</dd>
          </div>
          <div class="rounded-xl bg-zinc-50 p-3">
            <dt class="text-xs text-zinc-500">Lokasi</dt>
            <dd class="font-semibold"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></dd>
          </div>
        </dl>
      </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Deskripsi</h2>
          <p class="mt-2 text-sm leading-relaxed text-zinc-600"><?php echo htmlspecialchars($venue['description'] ?? 'Lapangan futsal di Magelang.', ENT_QUOTES, 'UTF-8'); ?></p>
          <img src="assets/images/lapangan.png" alt="Denah" class="mt-4 w-full rounded-xl border border-zinc-100">
        </div>



        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Jam operasional</h2>
          <?php if (!empty($scheds)): ?>
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
              <?php foreach ($scheds as $s): ?>
                <div class="rounded-xl border border-zinc-100 bg-zinc-50 px-3 py-2 flex justify-between"><span class="text-zinc-600"><?php echo htmlspecialchars($s['day_of_week'], ENT_QUOTES, 'UTF-8'); ?></span><span class="font-semibold"><?php echo htmlspecialchars(substr($s['open_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars(substr($s['close_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></span></div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-sm text-zinc-600">Jadwal belum tersedia.</p>
          <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 bg-[linear-gradient(105deg,rgba(18,71,38,0.82)_48%,rgba(232,233,232,0.55)_100%),url('assets/images/maps.png')] bg-cover bg-center text-white flex flex-col">
          <h2 class="font-spartan text-lg font-bold">Lokasi</h2>
          <p class="mt-3 text-sm"><?= htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></p>
          <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($venue['name'] . ' Magelang'); ?>" target="_blank" rel="noopener" class="mt-3 ml-auto inline-flex rounded-xl border border-vaygor-300 bg-vaygor-500 px-4 py-2 text-sm font-semibold hover:text-vaygor-600 hover:bg-vaygor-100">Buka Peta →</a>
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
          <?php if ($ratingData['count'] > 0): ?>
            <div class="mt-3 flex items-center gap-3">
              <span class="font-spartan text-3xl font-extrabold"><?php echo number_format($ratingData['avg'], 1); ?></span>
              <span class="text-sm text-zinc-600"><?php echo (int) $ratingData['count']; ?> Reviews</span>
            </div>
            <div class="mt-4 space-y-3">
              <div class="flex justify-between text-sm"><span>Kondisi Lapangan</span><b><?php echo number_format($ratingData['place'], 2); ?></b></div>
              <div class="flex justify-between text-sm"><span>Kenyamanan &amp; Kebersihan</span><b><?php echo number_format($ratingData['comfort'], 2); ?></b></div>
              <div class="flex justify-between text-sm"><span>Komunikasi</span><b><?php echo number_format($ratingData['service'], 2); ?></b></div>
            </div>
          <?php else: ?>
            <p class="mt-3 text-sm text-zinc-500">Belum ada ulasan untuk lapangan ini. Jadilah yang pertama setelah main!</p>
          <?php endif; ?>
          <div class="mt-6 rounded-xl bg-zinc-50 p-4">
            <div class="flex gap-3">
              <span class="flex h-9 w-9 items-center justify-center rounded-full bg-vaygor-600 text-xs font-extrabold text-white">B</span>
              <div>
                <p class="text-sm font-semibold">@Basis nama tengahku</p>
                <p class="text-xs text-zinc-500">07 Agustus 2026</p>
              </div>
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
          <?php if (empty($other)): ?>
            <p class="text-sm text-zinc-500 mt-2">Tidak ada venue lain.</p>
            <?php else: foreach ($other as $o):
              $ocats = $catMap[$o['id']] ?? [];
              $olabel = !empty($ocats) ? implode(', ', array_slice($ocats, 0, 2)) : 'Lapangan';
              ?>
              <a href="index.php?p=produk&id=<?php echo (int) $o['id']; ?>" class="mt-4 flex gap-3 rounded-xl border border-zinc-100 p-3 hover:border-vaygor-600">
                <img src="<?php echo htmlspecialchars(p_img($o), ENT_QUOTES, 'UTF-8'); ?>" alt="" class="h-16 w-16 rounded-xl object-cover">
                <div>
                  <p class="text-sm font-bold"><?php echo htmlspecialchars($o['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                  <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($olabel, ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars(p_price($o['price']), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
              </a>
          <?php endforeach;
          endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>