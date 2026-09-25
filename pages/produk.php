<?php
$vid = (int)($_GET['id'] ?? 1);
$uid = !empty($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
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

// Ulasan berisi teks dari tabel bookings
$reviews = [];
try {
  $stmt = $conn->prepare("SELECT b.id, b.booking_date, b.rate_service, b.rate_comfort, b.rate_place, b.review, u.name AS user_name
        FROM bookings b
        JOIN users u ON u.id = b.id_user
        WHERE b.id_field = ? AND b.review IS NOT NULL AND TRIM(b.review) <> ''
        ORDER BY b.id DESC");
  $stmt->bind_param("i", $vid);
  $stmt->execute();
  $rr = $stmt->get_result();
  while ($row = $rr->fetch_assoc()) $reviews[] = $row;
  $stmt->close();
} catch (Throwable $e) {
}

// Apakah user yang login punya booking "bisa direviews" untuk venue ini
$reviewAvailable = false;
$reviewBookingId = 0;
$alreadyReviewed = false;
if (!empty($_SESSION['id'])) {
  try {
    $rbq = $conn->prepare("SELECT b.id, b.rate_service, b.rate_comfort, b.rate_place, b.review
        FROM bookings b
        JOIN payments p ON p.booking_id = b.id
        WHERE b.id_user = ? AND b.id_field = ? AND b.status IN ('confirmed','completed')
          AND (b.status = 'completed' OR b.booking_date <= CURDATE()) AND p.payment_status = 'paid'
        ORDER BY b.booking_date DESC
        LIMIT 1");
    $rbq->bind_param("ii", $uid, $vid);
    $rbq->execute();
    $rb = $rbq->get_result()->fetch_assoc();
    $rbq->close();
    if ($rb) {
      $reviewAvailable = true;
      $reviewBookingId = (int) $rb['id'];
      $alreadyReviewed = (int) $rb['rate_service'] > 0 && (int) $rb['rate_comfort'] > 0 && (int) $rb['rate_place'] > 0 && trim((string) ($rb['review'] ?? '')) !== '';
    }
  } catch (Throwable $e) {
  }
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

  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 pb-28 md:pb-12">
    <!-- Image -->
    <img src="<?php echo htmlspecialchars(p_img($venue), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?>" class="rounded-2xl h-64 sm:h-80 w-full object-cover mb-6">

    <div class="grid gap-6 lg:grid-cols-[1.7fr_1fr] space-y-6">
      <!-- left col -->
      <div class="">
        <!-- badge kategori -->
        <div class="flex flex-wrap gap-2">
          <?php if (!empty($cats)): ?>
            <?php foreach ($cats as $cn): ?>
              <span class="inline-block rounded-full bg-vaygor-100 px-3 py-1 text-xs font-semibold text-vaygor-700">
                <?php echo htmlspecialchars(ucfirst($cn), ENT_QUOTES, 'UTF-8'); ?>
              </span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <!-- Header -->
        <h1 class="mt-3 font-spartan text-3xl font-extrabold tracking-tight"><?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?></h1>

        <div class="flex items-center gap-2 mb-5">

          <p class="text-sm text-zinc-500"><?= $ratingData['count'] > 0 ? number_format($ratingData['avg'], 1) : ''; ?></p>
          <p class="text-sm text-zinc-600"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <hr class="border-zinc-300 my-3">
        <!-- description -->
        <h2 class="font-spartan text-lg font-bold my-2">Deskripsi</h2>
        <p class="text-sm leading-relaxed text-zinc-600"><?php echo htmlspecialchars($venue['description'] ?? 'Lapangan futsal di Magelang.', ENT_QUOTES, 'UTF-8'); ?></p>
        <img src="assets/images/lapangan.png" alt="Denah" class="my-1 w-full rounded-xl border border-zinc-100">

        <!-- operasi -->
        <h2 class="font-spartan text-lg font-bold my-2">Jam operasional</h2>
        <?php if (!empty($scheds)): ?>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
            <?php foreach ($scheds as $s): ?>
              <div class="rounded-xl border shadow-sm border-zinc-100 bg-white px-3 py-2 flex justify-between"><span class="text-zinc-600"><?php echo htmlspecialchars($s['day_of_week'], ENT_QUOTES, 'UTF-8'); ?></span><span class="font-semibold"><?php echo htmlspecialchars(substr($s['open_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars(substr($s['close_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></span></div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-sm text-zinc-600">Jadwal belum tersedia.</p>
        <?php endif; ?>

        <!-- maps -->
        <div class="my-5 rounded-2xl border border-zinc-200 bg-white p-6 bg-[linear-gradient(105deg,rgba(18,71,38,0.82)_48%,rgba(232,233,232,0.55)_100%),url('assets/images/maps.png')] bg-cover bg-center text-white flex flex-col">
          <h2 class="font-spartan text-lg font-bold">Lokasi</h2>
          <p class="mt-3 text-sm"><?= htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></p>
          <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($venue['name'] . ' Magelang'); ?>" target="_blank" rel="noopener" class="mt-3 ml-auto inline-flex rounded-xl border border-vaygor-300 bg-vaygor-500 px-4 py-2 text-sm font-semibold hover:text-vaygor-600 hover:bg-vaygor-100">Buka Peta →</a>
        </div>

        <div id="ulasan" class="rounded-2xl border border-zinc-200 bg-white p-6">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-spartan text-lg font-bold">Ulasan Penyewa</h2>
            <?php if ($reviewAvailable): ?>
              <a href="index.php?p=review&id=<?php echo (int) $reviewBookingId; ?>" class="inline-flex items-center gap-1.5 rounded-xl bg-vaygor-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-vaygor-700">
                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>
                <?php echo $alreadyReviewed ? 'Perbarui Ulasan' : 'Tulis Ulasan'; ?>
              </a>
            <?php elseif (!$userLoggedIn): ?>
              <a href="login.php?redirect=<?php echo urlencode('index.php?p=produk&id=' . (int) $venue['id']); ?>" class="inline-flex items-center gap-1.5 rounded-xl border border-vaygor-200 bg-white px-4 py-2.5 text-xs font-bold text-vaygor-600 transition hover:bg-vaygor-50">
                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>
                Masuk untuk menulis ulasan
              </a>
            <?php endif; ?>
          </div>
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
            <p class="mt-3 text-sm text-zinc-500">Belum ada ulasan untuk lapangan ini. Jadilah yang pertama!</p>
          <?php endif; ?>
          <?php if (!empty($reviews)):
            function review_card($rv)
            {
              $initial = mb_strtoupper(mb_substr(trim($rv['user_name']), 0, 1));
              $avg = ($rv['rate_service'] > 0) ? round(($rv['rate_service'] + $rv['rate_comfort'] + $rv['rate_place']) / 3, 1) : 0;
              $date = date('d F Y', strtotime($rv['booking_date']));
              ?>
              <div class=" flex-shrink-0 rounded-xl bg-zinc-50 mt-5 p-4 border border-zinc-200">
                <div class="flex items-center gap-3">
                  <span class="flex h-9 w-9 items-center justify-center rounded-full bg-vaygor-600 text-xs font-extrabold text-white"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></span>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate"><?php echo htmlspecialchars($rv['user_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?></p>
                  </div>
                  <?php if ($avg > 0): ?>
                    <span class="inline-flex flex-shrink-0 items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-xs font-bold text-amber-600">
                      <svg class="h-3 w-3 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>
                      <?php echo number_format($avg, 1); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-zinc-700 whitespace-pre-line"><?php echo htmlspecialchars(trim($rv['review']), ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
              <?php
            }
          ?>
            <!-- Review pertama, tampil 1 default -->
            <?php review_card($reviews[0]); ?>

            <?php if (count($reviews) > 1): ?>
              <div class="mt-4">
                <button type="button" id="btnShowReviews" class="inline-flex items-center gap-2 rounded-xl border border-vaygor-200 bg-white px-4 py-2 text-sm font-bold text-vaygor-600 transition hover:bg-vaygor-50">
                  <span id="lblShowReviews">Lihat semua ulasan (<?php echo count($reviews); ?>)</span>
                  <svg id="iconShowReviews" class="h-4 w-4 transition duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6" /></svg>
                </button>
                <div id="allReviews" class="mt-4 hidden overflow-x-auto pb-2">
                  <div class="flex gap-4">
                    <?php foreach ($reviews as $rv): review_card($rv); endforeach; ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endif; ?>
          <script>
            (function () {
              var btn = document.getElementById('btnShowReviews');
              var box = document.getElementById('allReviews');
              if (!btn || !box) return;
              var icon = document.getElementById('iconShowReviews');
              var lbl = document.getElementById('lblShowReviews');
              btn.addEventListener('click', function () {
                var open = box.classList.toggle('hidden') === false;
                btn.classList.add(open ? 'bg-vaygor-50' : 'bg-white');
                if (icon) icon.classList.toggle('rotate-180', open);
                lbl.textContent = open ? 'Tutup ulasan' : 'Lihat semua ulasan (<?php echo count($reviews); ?>)';
              });
            })();
          </script>
        </div>

      </div>

      <!-- right col -->
      <div class="flex flex-col gap-5">
        <div class="hidden lg:block rounded-lg shadow-sm bg-white p-6">
          <h2 class="font-spartan font-black- text-lg font-bold mb-4">Harga Booking</h2>
          <span class="font-spartan text-2xl font-extrabold text-vaygor-600"><?php echo htmlspecialchars(p_price($venue['price']), ENT_QUOTES, 'UTF-8'); ?></span> <span class="text-sm text-zinc-500">/ Sesi</span>
          <a href="<?php echo htmlspecialchars($bookUrl, ENT_QUOTES, 'UTF-8'); ?>" class="mt-6 flex w-full items-center justify-center rounded-xl bg-vaygor-600 px-6 py-3.5 text-sm font-bold text-white hover:bg-vaygor-700"><?php echo htmlspecialchars($bookLabel, ENT_QUOTES, 'UTF-8'); ?></a>
          <?php if (!$userLoggedIn): ?>
            <p class="mt-2 text-center text-xs text-zinc-500">Perlu masuk akun dulu untuk lanjut booking.</p>
          <?php else: ?>
            <p class="mt-2 text-center text-xs text-zinc-500">Cek jadwal di bawah sebelum booking.</p>
          <?php endif; ?>
        </div>

        <div class="rounded-lg shadow-sm bg-white p-6">
          <h2 class="font-spartan text-lg font-bold">Aturan Booking</h2>
          <p class="text-xs text-zinc-500">beberapa tempat memiliki aturan lainnya</p>
          <ol class="mt-2 list-decimal pl-5 space-y-1 text-sm text-zinc-700">
            <li>Dilarang membawa sajam dan alkohol</li>
            <li>Dilarang melakukan kekerasan</li>
            <li>Konfirmasi Booking min 3 jam</li>
            <li>Dilarang merusak dan mengambil dari lapangan</li>
            <li>Kehilangan barang bukan tanggung jawab kami</li>
          </ol>
        </div>

      </div>

    </div>

  </div>
</div>

<!-- Sticky booking bar (mobile only) -->
<div class="fixed inset-x-0 bottom-0 z-50 border-t border-zinc-200 bg-white/95 p-6  backdrop-blur lg:hidden shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
  <h1 class="my-2 font-spartan text-3xl font-extrabold tracking-tight"><?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
  <div class="mx-auto flex max-w-6xl items-center justify-between gap-3">
    <div class="min-w-0">
      <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Harga Booking</p>
      <p class="font-spartan text-3xl font-extrabold leading-tight text-vaygor-600"><?php echo htmlspecialchars(p_price($venue['price']), ENT_QUOTES, 'UTF-8'); ?> <span class="text-xs font-medium text-zinc-400">/ Sesi</span></p>
    </div>
    <a href="<?php echo htmlspecialchars($bookUrl, ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center justify-center gap-2 rounded-xl bg-vaygor-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-vaygor-600/30 transition active:scale-[0.98]"><?php echo htmlspecialchars($bookLabel, ENT_QUOTES, 'UTF-8'); ?></a>
  </div>
  <hr class="border-zinc-200 my-6">
</div>

<?php require_once 'includes/footer.php'; ?>