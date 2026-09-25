<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$uid = (int) $_SESSION['id'];
$msg = '';
$msgOk = false;

$TABS = [
    'unpaid'   => ['label' => 'Belum Dibayar',                  'desc' => 'Pesanan yang pembayarannya belum diselesaikan.'],
    'upcoming' => ['label' => 'Mendatang / Konfirmasi',          'desc' => 'Pesanan yang sudah dibayar, menunggu konfirmasi, atau jadwal masih di depan.'],
    'done'     => ['label' => 'Sudah Selesai',                   'desc' => 'Pesanan yang sudah selesai dimainkan atau dibatalkan.'],
];
$tab = (isset($_GET['tab']) && isset($TABS[$_GET['tab']])) ? $_GET['tab'] : 'unpaid';

function vaygor_tab_cond(string $t): string
{
    switch ($t) {
        case 'unpaid':
            return "b.status <> 'cancelled' AND p.payment_status IN ('unpaid','failed')";
        case 'upcoming':
            return "b.status IN ('pending','confirmed') AND p.payment_status IN ('pending','paid')";
        default:
            return "b.status IN ('completed','cancelled')";
    }
}

function vaygor_pay_sub(): string
{
    return "LEFT JOIN payments p ON p.id = (SELECT p2.id FROM payments p2 WHERE p2.booking_id = b.id ORDER BY p2.id DESC LIMIT 1)";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bid = (int) ($_POST['booking_id'] ?? 0);
    if ($bid <= 0) {
        $msg = 'Parameter tidak valid.';
    } elseif ($_POST['action'] === 'cancel') {
        $stmt = $conn->prepare("SELECT id, id_user, status FROM bookings WHERE id=? LIMIT 1");
        $stmt->bind_param('i', $bid);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$b || (int) $b['id_user'] !== $uid) {
            $msg = 'Pesanan tidak ditemukan.';
        } elseif ($b['status'] !== 'pending') {
            $msg = 'Hanya pesanan pending yang bisa dibatalkan.';
        } else {
            $up = $conn->prepare("UPDATE bookings SET status='cancelled' WHERE id=?");
            $up->bind_param('i', $bid);
            $up->execute();
            $up->close();
            $msg = 'Pesanan dibatalkan. Slot kembali tersedia.';
            $msgOk = true;
        }
    } elseif ($_POST['action'] === 'pay') {
        $stmt = $conn->prepare("SELECT b.id, b.id_user, b.status, p.payment_status, p.id AS pid
            FROM bookings b
            LEFT JOIN payments p ON p.booking_id = b.id
            WHERE b.id=? ORDER BY p.id DESC LIMIT 1");
        $stmt->bind_param('i', $bid);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$b || (int) $b['id_user'] !== $uid) {
            $msg = 'Pesanan tidak ditemukan.';
        } elseif ($b['status'] === 'cancelled') {
            $msg = 'Pesanan sudah dibatalkan.';
        } elseif ($b['payment_status'] === 'paid') {
            $msg = 'Pembayaran sudah lunas.';
        } else {
            $pid = (int) $b['pid'];
            $up = $conn->prepare("UPDATE payments SET payment_status='pending', payment_date=NOW() WHERE id=?");
            $up->bind_param('i', $pid);
            $up->execute();
            $up->close();
            $msg = 'Pembayaran dikirim, menunggu verifikasi admin.';
            $msgOk = true;
        }
    }
}

$perPage = 5;
$page = max(1, (int) ($_GET['hal'] ?? 1));

$counts = ['unpaid' => 0, 'upcoming' => 0, 'done' => 0];
try {
    $pat = "SELECT b.status, p.payment_status FROM bookings b %s WHERE b.id_user=?";
    $union = sprintf($pat, vaygor_pay_sub()) . " AND b.status <> 'cancelled' AND p.payment_status IN ('unpaid','failed')
        UNION ALL
        SELECT b.status, p.payment_status FROM bookings b " . vaygor_pay_sub() . " WHERE b.id_user=? AND b.status IN ('pending','confirmed') AND p.payment_status IN ('pending','paid')
        UNION ALL
        SELECT b.status, p.payment_status FROM bookings b " . vaygor_pay_sub() . " WHERE b.id_user=? AND b.status IN ('completed','cancelled')";
    $qq = $conn->prepare($union);
    $qq->bind_param('iii', $uid, $uid, $uid);
    $qq->execute();
    $tmpCounts = ['unpaid' => 0, 'upcoming' => 0, 'done' => 0];
    $qr = $qq->get_result();
    while ($cc = $qr->fetch_assoc()) {
        if ($cc['status'] !== 'cancelled' && in_array($cc['payment_status'], ['unpaid', 'failed'], true)) {
            $tmpCounts['unpaid']++;
        } elseif (in_array($cc['status'], ['pending', 'confirmed'], true) && in_array($cc['payment_status'], ['pending', 'paid'], true)) {
            $tmpCounts['upcoming']++;
        } elseif (in_array($cc['status'], ['completed', 'cancelled'], true)) {
            $tmpCounts['done']++;
        }
    }
    $qq->close();
    $counts = $tmpCounts;
} catch (Throwable $e) {
}

$total = $counts[$tab] ?? 0;
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = max(0, ($page - 1) * $perPage);

$cond = vaygor_tab_cond($tab);
$feed = "FROM bookings b
    JOIN fields f ON f.id = b.id_field
    " . vaygor_pay_sub() . "
    WHERE b.id_user = ? AND " . $cond;

$rows = [];
$dbErr = '';
try {
    $stmt = $conn->prepare("SELECT b.id, b.booking_code, b.booking_date, b.start_time, b.end_time, b.total_price,
        b.status, b.rate_service, b.rate_comfort, b.rate_place, b.review,
        b.id_field AS fid, f.name AS venue_name, f.image AS venue_image,
        p.payment_status, p.payment_date, p.amount AS payment_amount
        $feed
        ORDER BY b.booking_date DESC, b.start_time DESC
        LIMIT ? OFFSET ?");
    $stmt->bind_param('iii', $uid, $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $stmt->close();
} catch (Throwable $e) {
    $dbErr = 'Gagal memuat pesanan. Coba lagi.';
}

function fmtRp($n)
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

function vaygor_badge(string $status, ?string $pay): array
{
    if ($status === 'cancelled') return ['Dibatalkan', 'bg-zinc-100 text-zinc-600'];
    if ($status === 'completed') return ['Selesai', 'bg-emerald-100 text-emerald-700'];
    if ($pay === 'paid') {
        return $status === 'pending'
            ? ['Menunggu konfirmasi', 'bg-amber-100 text-amber-700']
            : ['Terkonfirmasi', 'bg-emerald-100 text-emerald-700'];
    }
    if ($pay === 'pending') return ['Menunggu verifikasi pembayaran', 'bg-amber-100 text-amber-700'];
    return ['Belum dibayar', 'bg-red-100 text-red-700'];
}

function vaygor_tab_href(string $t, int $pg): string
{
    $parts = ['index.php?p=pesanan'];
    $parts[] = 'tab=' . $t;
    if ($pg > 1) $parts[] = 'hal=' . $pg;
    return implode('&', $parts);
}

function vaygor_order_img(string $name): string
{
    if ($name !== '') {
        if (file_exists(__DIR__ . '/../assets/uploads/' . $name)) return 'assets/uploads/' . $name;
        if (file_exists(__DIR__ . '/../assets/images/' . $name)) return 'assets/images/' . $name;
    }
    return 'assets/images/lapangan.png';
}
?>
<div class="pt-20">
  <div class="mx-auto max-w-4xl px-6 sm:px-8 lg:px-12 py-8 pb-24 lg:pb-12">
    <h1 class="font-spartan text-2xl sm:text-3xl font-extrabold">Pesanan saya</h1>
    <p class="mt-1 text-sm text-zinc-600">Pantau pembayaran, konfirmasi, dan tulis ulasan setelah selesai bermain.</p>

    <?php if ($msg !== ''): ?>
      <div role="<?php echo $msgOk ? 'status' : 'alert'; ?>" class="mt-4 rounded-xl border px-4 py-3 text-sm <?php echo $msgOk ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700'; ?>">
        <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <!-- Tab bar -->
    <div class="mt-6 flex gap-1 overflow-x-auto rounded-2xl border border-zinc-200 bg-white p-1 shadow-sm">
      <?php foreach ($TABS as $key => $t): ?>
        <?php $active = $key === $tab; ?>
        <a href="<?php echo htmlspecialchars(vaygor_tab_href($key, 1), ENT_QUOTES, 'UTF-8'); ?>"
           class="flex flex-shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition <?php echo $active ? 'bg-vaygor-600 text-white shadow' : 'text-zinc-600 hover:bg-vaygor-50 hover:text-vaygor-700'; ?>">
          <span><?php echo htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="rounded-full px-2 py-0.5 text-[11px] font-extrabold <?php echo $active ? 'bg-white/20 text-white' : 'bg-zinc-100 text-zinc-600'; ?>"><?php echo (int) ($counts[$key] ?? 0); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <p class="mt-3 text-xs text-zinc-500"><?php echo htmlspecialchars($TABS[$tab]['desc'], ENT_QUOTES, 'UTF-8'); ?></p>

    <?php if ($dbErr !== ''): ?>
      <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($dbErr, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif ($total === 0): ?>
      <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-10 text-center">
        <p class="font-semibold text-zinc-900">Tidak ada pesanan di bagian ini</p>
        <?php if ($tab === 'unpaid'): ?>
          <p class="mt-1 text-sm text-zinc-600">Pesanan baru yang belum dibayar akan tampil di sini.</p>
        <?php elseif ($tab === 'upcoming'): ?>
          <p class="mt-1 text-sm text-zinc-600">Pesanan yang sudah dibayar / menunggu konfirmasi akan tampil di sini.</p>
        <?php else: ?>
          <p class="mt-1 text-sm text-zinc-600">Pesanan yang sudah selesai atau dibatalkan akan tampil di sini.</p>
        <?php endif; ?>
        <a href="index.php?p=browse" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-vaygor-700">Cari lapangan</a>
      </div>
    <?php else: ?>
      <div class="mt-6 space-y-3">
        <?php foreach ($rows as $r): ?>
          <?php
            [$badgeTxt, $badgeCls] = vaygor_badge($r['status'], $r['payment_status']);
            $isUnpaid = $r['status'] !== 'cancelled' && in_array($r['payment_status'], ['unpaid', 'failed'], true);
            $hasReview = (int) $r['rate_service'] > 0 && (int) $r['rate_comfort'] > 0 && (int) $r['rate_place'] > 0 && trim((string) $r['review']) !== '';
            $avg = ((int) $r['rate_service'] + (int) $r['rate_comfort'] + (int) $r['rate_place']) > 0
                ? round(((int) $r['rate_service'] + (int) $r['rate_comfort'] + (int) $r['rate_place']) / 3, 1) : 0;
          ?>
          <div class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:flex-row sm:items-start">
            <a href="index.php?p=produk&id=<?php echo (int) $r['fid']; ?>" class="block h-24 w-full flex-shrink-0 overflow-hidden rounded-xl bg-zinc-100 sm:w-32">
              <img src="<?php echo htmlspecialchars(vaygor_order_img((string) $r['venue_image']), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($r['venue_name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover" loading="lazy">
            </a>

            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                  <p class="font-mono text-[11px] text-zinc-400"><?php echo htmlspecialchars($r['booking_code'], ENT_QUOTES, 'UTF-8'); ?></p>
                  <a href="index.php?p=produk&id=<?php echo (int) $r['fid']; ?>" class="font-spartan text-base font-bold text-zinc-900 hover:text-vaygor-700">
                    <?php echo htmlspecialchars($r['venue_name'], ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                  <p class="mt-0.5 text-sm text-zinc-600">
                    <?php echo htmlspecialchars(date('d M Y', strtotime($r['booking_date'])), ENT_QUOTES, 'UTF-8'); ?>
                    • <?php echo htmlspecialchars(substr($r['start_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars(substr($r['end_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>
                    • <?php echo htmlspecialchars(fmtRp($r['total_price']), ENT_QUOTES, 'UTF-8'); ?>
                  </p>
                </div>
                <span class="flex-shrink-0 rounded-full px-3 py-1 text-xs font-bold <?php echo htmlspecialchars($badgeCls, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($badgeTxt, ENT_QUOTES, 'UTF-8'); ?></span>
              </div>

              <?php if ($tab === 'done' && $r['status'] === 'completed' && $hasReview): ?>
                <div class="mt-3 flex flex-wrap items-center gap-2 rounded-xl bg-zinc-50 px-3 py-2 text-xs text-zinc-600">
                  <span class="text-amber-500">★★★★★</span>
                  <span class="font-bold"><?php echo number_format($avg, 1); ?></span>
                  <span class="truncate"><?php echo htmlspecialchars(mb_strimwidth(trim((string) $r['review']), 0, 80, '…'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
              <?php endif; ?>

              <div class="mt-3 flex flex-wrap items-center gap-2">
                <?php if ($isUnpaid): ?>
                  <form method="post" onsubmit="return confirm('Kirim pembayaran untuk pesanan ini?');">
                    <input type="hidden" name="action" value="pay">
                    <input type="hidden" name="booking_id" value="<?php echo (int) $r['id']; ?>">
                    <button type="submit" class="rounded-xl bg-vaygor-600 px-4 py-2 text-xs font-bold text-white hover:bg-vaygor-700">Bayar Sekarang</button>
                  </form>
                <?php endif; ?>

                <?php if ($r['status'] === 'pending'): ?>
                  <form method="post" onsubmit="return confirm('Batalkan pesanan ini?');">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="booking_id" value="<?php echo (int) $r['id']; ?>">
                    <button type="submit" class="rounded-xl border border-red-200 bg-white px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50">Batalkan</button>
                  </form>
                <?php endif; ?>

                <?php if ($tab === 'done' && $r['status'] === 'completed'): ?>
                  <a href="index.php?p=review&id=<?php echo (int) $r['id']; ?>"
                     class="rounded-xl bg-vaygor-600 px-4 py-2 text-xs font-bold text-white hover:bg-vaygor-700">
                    <?php echo $hasReview ? 'Perbarui Ulasan' : 'Tulis Ulasan'; ?>
                  </a>
                <?php endif; ?>

                <a href="index.php?p=produk&id=<?php echo (int) $r['fid']; ?>" class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-xs font-bold text-zinc-700 hover:border-vaygor-600 hover:text-vaygor-700">Lihat lapangan</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPages > 1): ?>
        <?php $range = range(max(1, $page - 2), min($totalPages, $page + 2)); ?>
        <nav class="mt-8 flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
          <?php if ($page > 1): ?>
            <a href="<?php echo htmlspecialchars(vaygor_tab_href($tab, $page - 1), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-sm font-semibold text-zinc-700 transition hover:border-vaygor-600 hover:text-vaygor-600">&larr;</a>
          <?php endif; ?>
          <?php foreach ($range as $pg): ?>
            <a href="<?php echo htmlspecialchars(vaygor_tab_href($tab, $pg), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold transition <?php echo $pg === $page ? 'bg-vaygor-600 text-white' : 'border border-zinc-200 bg-white text-zinc-700 hover:border-vaygor-600 hover:text-vaygor-600'; ?>"><?php echo (int) $pg; ?></a>
          <?php endforeach; ?>
          <?php if ($page < $totalPages): ?>
            <a href="<?php echo htmlspecialchars(vaygor_tab_href($tab, $page + 1), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-sm font-semibold text-zinc-700 transition hover:border-vaygor-600 hover:text-vaygor-600">&rarr;</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>