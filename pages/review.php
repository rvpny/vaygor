<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$uid = (int) $_SESSION['id'];
$bid = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$booking = null;
$error = '';
$success = false;

if ($bid <= 0) {
    $error = 'Pilih pesanan yang ingin diulas terlebih dahulu.';
} else {
    try {
        $stmt = $conn->prepare("
            SELECT b.id, b.booking_code, b.id_field, b.booking_date, b.start_time, b.end_time, b.total_price,
                   b.status, b.rate_service, b.rate_comfort, b.rate_place, b.review,
                   f.name AS venue_name, f.location AS venue_location, f.price AS venue_price,
                   p.payment_status
            FROM bookings b
            JOIN fields f ON f.id = b.id_field
            LEFT JOIN payments p ON p.id = (SELECT p2.id FROM payments p2 WHERE p2.booking_id = b.id ORDER BY p2.id DESC LIMIT 1)
            WHERE b.id = ? AND b.id_user = ?
            LIMIT 1");
        $stmt->bind_param("ii", $bid, $uid);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (Throwable $e) {
        $booking = null;
    }
    if (!$booking) {
        $error = 'Pesanan tidak ditemukan.';
    } else {
        $canReview = false;
        $blockReason = '';
        if ($booking['status'] === 'cancelled') {
            $blockReason = 'Pesanan ini dibatalkan, tidak bisa diulas.';
        } elseif (!in_array($booking['status'], ['confirmed', 'completed'], true)) {
            $blockReason = 'Pesanan ini belum bisa diulas karena masih berstatus "' . htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') . '".';
        } elseif ($booking['payment_status'] !== 'paid') {
            $blockReason = 'Ulasan hanya bisa ditulis setelah pembayaran lunas.';
        } elseif ($booking['status'] === 'confirmed' && $booking['booking_date'] > date('Y-m-d')) {
            $blockReason = 'Jadwal mainmu masih di depan. Ulasan bisa ditulis setelah hari-H tiba.';
        } else {
            $canReview = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking && $canReview) {
    $rs = (int) ($_POST['rate_service'] ?? 0);
    $rc = (int) ($_POST['rate_comfort'] ?? 0);
    $rp = (int) ($_POST['rate_place'] ?? 0);
    $review = trim($_POST['review'] ?? '');

    $booking['rate_service'] = $rs;
    $booking['rate_comfort'] = $rc;
    $booking['rate_place'] = $rp;
    $booking['review'] = $review;

    if ($rs < 1 || $rs > 5 || $rc < 1 || $rc > 5 || $rp < 1 || $rp > 5) {
        $error = 'Lengkapi semua bintang penilaian (minimal 1).';
    } elseif (mb_strlen($review) > 500) {
        $error = 'Ulasan maksimal 500 karakter.';
    } elseif ($review === '') {
        $error = 'Tulis ulasan singkatmu dulu ya.';
    } else {
        try {
            $up = $conn->prepare("UPDATE bookings SET rate_service=?, rate_comfort=?, rate_place=?, review=? WHERE id=? AND id_user=?");
            $up->bind_param("iiisii", $rs, $rc, $rp, $review, $bid, $uid);
            $up->execute();
            $up->close();
            $success = true;
        } catch (Throwable $e) {
            $error = 'Gagal menyimpan ulasan. Coba lagi.';
        }
    }
}

$rService = (int) ($booking['rate_service'] ?? 0);
$rComfort = (int) ($booking['rate_comfort'] ?? 0);
$rPlace = (int) ($booking['rate_place'] ?? 0);
$reviewText = trim((string) ($booking['review'] ?? ''));
$alreadyReviewed = $rService > 0 && $rComfort > 0 && $rPlace > 0 && $reviewText !== '';
$avg = ($rService + $rComfort + $rPlace) > 0 ? round(($rService + $rComfort + $rPlace) / 3, 1) : 0;

function fmtRp($n)
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

function star_group(string $name, string $label, int $value, string $desc): void
{
    ?>
    <div>
      <div class="flex items-center justify-between">
        <p class="text-sm font-semibold text-zinc-800"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></p>
        <span class="text-xs text-zinc-400"><?php echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <div class="star-group mt-2 flex items-center gap-1" data-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo (int) $value; ?>">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <button type="button" class="star-btn text-3xl leading-none transition <?php echo $i <= $value ? 'text-amber-400' : 'text-zinc-200'; ?>" data-val="<?php echo (int) $i; ?>" aria-label="<?php echo (int) $i; ?> bintang">
            <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>
          </button>
        <?php endfor; ?>
      </div>
    </div>
    <?php
}
?>
<div class="pt-20">
  <div class="mx-auto max-w-2xl px-6 sm:px-8 lg:px-12 py-8 pb-24 lg:pb-12">
    <a href="index.php?p=pesanan&tab=done" class="text-sm text-zinc-500 hover:text-vaygor-600">← Kembali ke pesanan</a>

    <h1 class="mt-3 font-spartan text-2xl sm:text-3xl font-extrabold"><?php echo $booking ? ($alreadyReviewed ? 'Perbarui Ulasan' : 'Tulis Ulasan') : 'Ulasan'; ?></h1>
    <p class="mt-1 text-sm text-zinc-600">Nilai pengalamanmu dan beri ulasan untuk lapangan yang kamu mainkan.</p>

    <?php if ($success): ?>
      <div role="status" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
        <p class="font-spartan font-bold text-emerald-800">Ulasan berhasil disimpan!</p>
        <p class="mt-1 text-sm text-emerald-700">Terima kasih sudah memberi masukan.</p>
        <p class="mt-1 text-sm text-emerald-700">Nilai kamu: <span class="text-amber-500 font-bold"><?php echo number_format($avg, 1); ?>/5</span> • <?php echo htmlspecialchars($reviewText, ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="mt-4 flex flex-wrap gap-2">
          <a href="index.php?p=produk&id=<?php echo (int) $booking['id_field']; ?>" class="rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-vaygor-700">Lihat halaman lapangan</a>
          <a href="index.php?p=pesanan&tab=done" class="rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-bold">Kembali ke pesanan</a>
        </div>
      </div>
    <?php elseif (!$booking): ?>
      <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-8 text-center">
        <p class="font-semibold text-zinc-900"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <a href="index.php?p=pesanan" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white">Lihat pesanan saya</a>
      </div>
    <?php else: ?>
      <?php if ($error !== ''): ?>
        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if (!$canReview): ?>
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6">
          <p class="font-semibold text-amber-800">Pesanan belum bisa diulas.</p>
          <p class="mt-1 text-sm text-amber-700"><?php echo $blockReason; ?></p>
          <?php if ($booking['payment_status'] !== 'paid'): ?>
            <p class="mt-1 text-xs text-amber-600">Status pembayaran saat ini: <?php echo htmlspecialchars($booking['payment_status'], ENT_QUOTES, 'UTF-8'); ?>. Selesaikan lewat menu Pesanan &gt; Belum Dibayar.</p>
          <?php endif; ?>
          <a href="index.php?p=pesanan" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white">Cek pesanan</a>
        </div>
      <?php else: ?>

        <!-- Info pesanan -->
        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
          <p class="font-mono text-[11px] text-zinc-400"><?php echo htmlspecialchars($booking['booking_code'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p class="font-spartan text-lg font-bold text-zinc-900"><?php echo htmlspecialchars($booking['venue_name'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p class="mt-0.5 text-sm text-zinc-600">
            <?php echo htmlspecialchars($booking['venue_location'], ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars(date('d M Y', strtotime($booking['booking_date'])), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars(substr($booking['start_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars(substr($booking['end_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars(fmtRp($booking['total_price']), ENT_QUOTES, 'UTF-8'); ?>
          </p>
        </div>

        <form method="post" class="mt-4 rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 shadow-sm space-y-6">
          <input type="hidden" name="id" value="<?php echo (int) $bid; ?>">

          <div class="space-y-5">
            <?php star_group('rate_service', 'Komunikasi', $rService, $rService > 0 ? 'Pilihan saat ini' : 'Belum dinilai'); ?>
            <?php star_group('rate_comfort', 'Kenyamanan & Kebersihan', $rComfort, $rComfort > 0 ? 'Pilihan saat ini' : 'Belum dinilai'); ?>
            <?php star_group('rate_place', 'Kondisi Lapangan', $rPlace, $rPlace > 0 ? 'Pilihan saat ini' : 'Belum dinilai'); ?>
          </div>

          <div>
            <label for="review" class="block text-sm font-semibold text-zinc-800">Ulasan singkat</label>
            <textarea id="review" name="review" rows="4" maxlength="500" required placeholder="Ceritakan pengalaman mainmu di sini..." class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20"><?php echo htmlspecialchars($reviewText, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p class="mt-1 text-xs text-zinc-400">Maksimal 500 karakter.</p>
          </div>

          <button type="submit" class="w-full rounded-xl bg-vaygor-600 px-5 py-3.5 text-sm font-bold text-white hover:bg-vaygor-700">
            <?php echo $alreadyReviewed ? 'Simpan Perubahan Ulasan' : 'Kirim Ulasan'; ?>
          </button>
        </form>

      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<script>
(function () {
  document.querySelectorAll('.star-group').forEach(function (g) {
    var input = g.querySelector('input[type="hidden"]');
    var btns = Array.prototype.slice.call(g.querySelectorAll('.star-btn'));
    var current = parseInt(input.value || '0', 10);

    function paint(v) {
      btns.forEach(function (b, j) {
        b.classList.toggle('text-amber-400', j < v);
        b.classList.toggle('text-zinc-200', j >= v);
      });
    }

    g.addEventListener('mouseleave', function () { paint(current); });
    btns.forEach(function (b, i) {
      b.addEventListener('mouseenter', function () { paint(i + 1); });
      b.addEventListener('click', function () {
        current = i + 1;
        input.value = current;
        paint(current);
      });
    });
    paint(current);
  });
})();
</script>