<?php
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}
$uid = (int)$_SESSION['id'];
$fid = (int)($_GET['id'] ?? 0);
if ($fid <= 0) { header('Location: index.php?p=browse'); exit; }

$venue = null;
$isOldSchema = false;
try {
    $stmt = $conn->prepare("SELECT id, name, location, price_per_hour, field_type FROM fields WHERE id=? AND status='available' LIMIT 1");
    $stmt->bind_param("i", $fid);
    $stmt->execute();
    $venue = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Throwable $e) {
    $venue = null;
    $msg = $e->getMessage();
    if (str_contains($msg, 'Unknown column') || str_contains($msg, "doesn't exist")) {
        $isOldSchema = true;
        try {
            $stmt = $conn->prepare("SELECT id, name, location, price FROM fields WHERE id=? LIMIT 1");
            $stmt->bind_param("i", $fid);
            $stmt->execute();
            $venue = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } catch (Throwable $e2) { $venue = null; }
    }
}
if (!$venue) { http_response_code(404); echo '<div class="pt-28 text-center"><p class="font-spartan font-bold">Venue tidak ditemukan</p><a href="index.php?p=browse" class="mt-3 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-white">Kembali</a></div>'; return; }
$venuePrice = (float)($venue['price_per_hour'] ?? $venue['price'] ?? 0);

function fmtRp($n){ return 'Rp ' . number_format((float)$n, 0, ',', '.'); }

function vaygor_slot_grid(string $date, int $fid, mysqli $conn, bool $isOldSchema): array
{
    $out = [
        'ok' => false,
        'message' => '',
        'slots' => [],
        'open' => '',
        'close' => '',
        'hasAvailable' => false,
    ];
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        $out['message'] = 'Tanggal tidak valid.';
        return $out;
    }
    $day = $d->format('l');
    $sched = null;
    try {
        $stmt = $conn->prepare("SELECT open_time, close_time FROM field_schedules WHERE field_id=? AND day_of_week=? LIMIT 1");
        $stmt->bind_param("is", $fid, $day);
        $stmt->execute();
        $sched = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (Throwable $e) { $sched = null; }
    if (!$sched) {
        $out['message'] = 'Jadwal venue belum tersedia untuk hari itu. Pilih tanggal lain.';
        return $out;
    }

    $openH = (int)substr($sched['open_time'], 0, 2);
    $closeH = (int)substr($sched['close_time'], 0, 2);
    $openM = (int)substr($sched['open_time'], 3, 2);
    $closeM = (int)substr($sched['close_time'], 3, 2);
    $out['ok'] = true;
    $out['open'] = substr($sched['open_time'], 0, 5);
    $out['close'] = substr($sched['close_time'], 0, 5);

    $busy = [];
    $fieldCol = $isOldSchema ? 'id_field' : 'field_id';
    try {
        $stmt = $conn->prepare("SELECT start_time, end_time FROM bookings WHERE $fieldCol=? AND booking_date=? AND status IN ('pending','confirmed')");
        $stmt->bind_param("is", $fid, $date);
        $stmt->execute();
        $r = $stmt->get_result();
        while ($row = $r->fetch_assoc()) $busy[] = $row;
        $stmt->close();
    } catch (Throwable $e) { $busy = []; }

    // Jendela tampil 08:00-23:00; slot di luar jam operasional = Tutup.
    for ($h = 8; $h < 23; $h++) {
        $start = sprintf('%02d:%02d', $h, 0);
        $end = sprintf('%02d:%02d', $h + 1, 0);
        $startMin = $h * 60;
        $endMin = ($h + 1) * 60;
        $openMin = $openH * 60 + $openM;
        $closeMin = $closeH * 60 + $closeM;
        $inHours = ($startMin >= $openMin && $endMin <= $closeMin);
        $status = 'closed';
        if ($inHours) {
            $status = 'available';
            foreach ($busy as $b) {
                $bs = substr((string)$b['start_time'], 0, 5);
                $be = substr((string)$b['end_time'], 0, 5);
                if ($start < $be && $end > $bs) {
                    $status = 'booked';
                    break;
                }
            }
        }
        if ($status === 'available') $out['hasAvailable'] = true;
        $out['slots'][] = ['start' => $start, 'status' => $status];
    }
    if (!$out['hasAvailable']) {
        $out['message'] = 'Belum ada slot tersedia untuk tanggal itu. Coba tanggal lain.';
    }
    return $out;
}

$errors = [];
$success = null;
$oldDate = trim($_GET['date'] ?? $_POST['date'] ?? date('Y-m-d', strtotime('+1 day')));
$oldPay = 'qris';
$oldNotes = '';
$selectedSlots = [];

if (isset($_POST['book'])) {
    $oldDate = trim($_POST['date'] ?? '');
    $oldPay = trim($_POST['pay'] ?? 'qris');
    $oldNotes = trim($_POST['notes'] ?? '');

    $rawSlots = $_POST['slots'] ?? [];
    if (!is_array($rawSlots)) $rawSlots = [$rawSlots];
    $clean = [];
    foreach ($rawSlots as $t) {
        $t = substr(trim((string)$t), 0, 5);
        if (preg_match('/^\d{2}:\d{2}$/', $t)) $clean[] = $t;
    }
    $clean = array_values(array_unique($clean));
    sort($clean, SORT_STRING);
    $selectedSlots = $clean;

    if ($oldDate === '') $errors[] = 'Tanggal wajib diisi.';
    $d = DateTime::createFromFormat('Y-m-d', $oldDate);
    if (!$d || $d->format('Y-m-d') !== $oldDate) $errors[] = 'Tanggal tidak valid.';
    elseif ($oldDate < date('Y-m-d')) $errors[] = 'Tanggal tidak boleh di masa lalu.';

    if (empty($clean)) {
        $errors[] = 'Pilih minimal satu slot jam.';
    } else {
        for ($i = 1, $n = count($clean); $i < $n; $i++) {
            $prev = DateTime::createFromFormat('H:i', $clean[$i - 1]);
            $cur = DateTime::createFromFormat('H:i', $clean[$i]);
            if (!$prev || !$cur || ($cur->getTimestamp() - $prev->getTimestamp()) !== 3600) {
                $errors[] = 'Slot harus berurutan tanpa jeda.';
                break;
            }
        }
    }

    if (!in_array($oldPay, ['qris','transfer','cash'], true)) $errors[] = 'Metode bayar tidak valid.';

    $oldStart = '';
    $oldEnd = '';
    $s = null;
    $e = null;
    if (empty($clean)) {
        $oldStart = trim($_POST['start'] ?? '');
        $oldEnd = trim($_POST['end'] ?? '');
    } else {
        $oldStart = $clean[0];
        $last = DateTime::createFromFormat('H:i', $clean[count($clean) - 1]);
        $last->modify('+1 hour');
        $oldEnd = $last->format('H:i');
    }

    if ($oldStart !== '' && $oldEnd !== '') {
        $s = DateTime::createFromFormat('H:i', $oldStart);
        $e = DateTime::createFromFormat('H:i', $oldEnd);
        if (!$s || !$e) $errors[] = 'Format jam tidak valid.';
        elseif ($s >= $e) $errors[] = 'Jam selesai harus setelah jam mulai.';
    }

    if (empty($errors)) {
        $day = $d->format('l');
        $stmt = $conn->prepare("SELECT open_time, close_time FROM field_schedules WHERE field_id=? AND day_of_week=? LIMIT 1");
        $stmt->bind_param("is", $fid, $day);
        $stmt->execute();
        $sched = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$sched) $errors[] = 'Jadwal venue belum tersedia untuk hari itu.';
        else {
            $open = substr($sched['open_time'],0,5);
            $close = substr($sched['close_time'],0,5);
            if ($oldStart < $open || $oldEnd > $close) $errors[] = "Jam operasional $day: $open - $close.";
        }
    }

    if (empty($errors)) {
        $fieldCol = $isOldSchema ? 'id_field' : 'field_id';
        $stmt = $conn->prepare("SELECT id FROM bookings WHERE $fieldCol=? AND booking_date=? AND status IN ('pending','confirmed') AND NOT (end_time <= ? OR start_time >= ?) LIMIT 1");
        $st = $oldStart . ':00';
        $et = $oldEnd . ':00';
        $stmt->bind_param("isss", $fid, $oldDate, $st, $et);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = 'Slot sudah dibooking. Pilih jam lain.';
        $stmt->close();
    }

    $duration = 0; $total = 0;
    if (empty($errors) && $s && $e) {
        $diff = ($e->getTimestamp() - $s->getTimestamp()) / 3600;
        $duration = (int)ceil($diff);
        if ($duration < 1) $duration = 1;
        $total = $duration * $venuePrice;
    }

    if (empty($errors)) {
        $code = 'BOOK-' . date('Ymd', strtotime($oldDate)) . '-' . strtoupper(substr(uniqid(), -4));
        $st = $oldStart . ':00'; $et = $oldEnd . ':00';
        if ($isOldSchema) {
            $stmt = $conn->prepare("INSERT INTO bookings (booking_code, id_user, id_field, booking_date, start_time, end_time, total_price, status, rate_service, rate_comfort, rate_place, review) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 0, 0, 0, '')");
            $stmt->bind_param("siisssd", $code, $uid, $fid, $oldDate, $st, $et, $total);
        } else {
            $price = $venuePrice;
            $stmt = $conn->prepare("INSERT INTO bookings (booking_code, user_id, field_id, booking_date, start_time, end_time, duration, price_per_hour, total_price, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->bind_param("siisssidds", $code, $uid, $fid, $oldDate, $st, $et, $duration, $price, $total, $oldNotes);
        }
        if ($stmt->execute()) {
            $bid = $stmt->insert_id;
            $stmt->close();
            if ($isOldSchema) {
                $stmt = $conn->prepare("INSERT INTO payments (booking_id, payment_status, amount) VALUES (?, 'unpaid', ?)");
                $stmt->bind_param("id", $bid, $total);
            } else {
                $stmt = $conn->prepare("INSERT INTO payments (booking_id, payment_method, payment_status, amount) VALUES (?, ?, 'unpaid', ?)");
                $stmt->bind_param("isd", $bid, $oldPay, $total);
            }
            $stmt->execute(); $stmt->close();
            $success = ['code'=>$code, 'date'=>$oldDate, 'start'=>$oldStart, 'end'=>$oldEnd, 'total'=>$total, 'pay'=>$oldPay];
        } else {
            $errors[] = 'Gagal menyimpan booking. Coba lagi.';
            $stmt->close();
        }
    }
}

if ($oldDate === '') $oldDate = date('Y-m-d', strtotime('+1 day'));
$grid = vaygor_slot_grid($oldDate, $fid, $conn, $isOldSchema);
?>
<div class="pt-20">
  <div class="mx-auto max-w-3xl px-6 sm:px-8 lg:px-12 py-8">
    <a href="index.php?p=produk&id=<?php echo (int)$venue['id']; ?>" class="text-sm text-zinc-500 hover:text-vaygor-600">← Kembali ke detail</a>
    <p class="mt-3 text-xs font-bold uppercase tracking-[0.2em] text-vaygor-600">Booking</p>
    <h1 class="mt-1 font-spartan text-2xl sm:text-3xl font-extrabold">Booking <?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="mt-1 text-sm text-zinc-600"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars(fmtRp($venuePrice), ENT_QUOTES, 'UTF-8'); ?>/jam</p>

    <?php if ($success): ?>
      <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
        <p class="font-spartan font-bold text-emerald-800">Booking tersimpan!</p>
        <p class="mt-1 text-sm text-emerald-700">Kode: <span class="font-mono font-bold"><?php echo htmlspecialchars($success['code'], ENT_QUOTES, 'UTF-8'); ?></span> • <?php echo htmlspecialchars($success['date'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($success['start'], ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars($success['end'], ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars(fmtRp($success['total']), ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($success['pay'], ENT_QUOTES, 'UTF-8'); ?>)</p>
        <div class="mt-4 flex gap-2">
          <a href="index.php?p=pesanan" class="rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white">Lihat pesanan</a>
          <a href="index.php?p=browse" class="rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-bold">Cari lagi</a>
        </div>
      </div>
    <?php else: ?>
      <?php if (!empty($errors)): ?>
        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <ul class="list-disc pl-5"><?php foreach($errors as $er): ?><li><?php echo htmlspecialchars($er, ENT_QUOTES, 'UTF-8'); ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <form method="get" action="index.php" class="mt-6 rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 shadow-sm">
        <input type="hidden" name="p" value="booking">
        <input type="hidden" name="id" value="<?php echo (int)$fid; ?>">
        <label for="book-date" class="block text-xs font-semibold">Tanggal</label>
        <div class="mt-1.5 flex flex-wrap gap-2">
          <input id="book-date" type="date" name="date" value="<?php echo htmlspecialchars($oldDate, ENT_QUOTES, 'UTF-8'); ?>" min="<?php echo date('Y-m-d'); ?>" required class="w-full sm:w-auto flex-1 min-w-0 rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:border-vaygor-600 focus:ring-2 focus:ring-vaygor-600/20">
          <button type="submit" class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm font-semibold hover:border-vaygor-600 hover:text-vaygor-600">Tampilkan slot</button>
        </div>
        <?php if ($grid['ok']): ?>
          <p class="mt-2 text-xs text-zinc-500">Jam operasional <?php echo htmlspecialchars($oldDate, ENT_QUOTES, 'UTF-8'); ?>: <?php echo htmlspecialchars($grid['open'], ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars($grid['close'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
      </form>

      <form id="booking-slot-form" method="post" class="mt-4 rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 shadow-sm space-y-4" data-price="<?php echo htmlspecialchars((string)$venuePrice, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($oldDate, ENT_QUOTES, 'UTF-8'); ?>">

        <div>
          <p class="text-xs font-semibold" id="slot-label">Pilih jam main</p>
          <div class="mt-2 flex flex-wrap gap-3 text-xs text-zinc-600" aria-hidden="true">
            <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded border border-vaygor-600 bg-white"></span> Tersedia</span>
            <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded border border-zinc-400 bg-zinc-200"></span> Dipesan</span>
            <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded border border-zinc-700 bg-zinc-800"></span> Tutup</span>
          </div>

          <?php if (!$grid['ok'] || empty($grid['slots'])): ?>
            <div class="mt-3 rounded-xl border border-zinc-200 bg-zinc-50 p-5 text-center" role="status">
              <p class="text-sm font-semibold text-zinc-700"><?php echo htmlspecialchars($grid['message'] !== '' ? $grid['message'] : 'Slot belum tersedia.', ENT_QUOTES, 'UTF-8'); ?></p>
              <p class="mt-1 text-xs text-zinc-500">Ganti tanggal di atas, lalu tekan Tampilkan slot.</p>
            </div>
          <?php else: ?>
            <div class="mt-3 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2" role="group" aria-labelledby="slot-label">
              <?php foreach ($grid['slots'] as $slot): ?>
                <?php
                  $st = $slot['status'];
                  $label = $slot['start'];
                  if ($st === 'available'):
                    $isChecked = in_array($label, $selectedSlots, true) ? ' checked' : '';
                ?>
                  <label class="cursor-pointer">
                    <input type="checkbox" name="slots[]" value="<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $isChecked; ?> class="peer sr-only">
                    <span class="flex min-h-11 flex-col items-center justify-center rounded-xl border px-2 py-2.5 text-sm font-semibold transition border-vaygor-600 bg-white text-vaygor-700 hover:bg-vaygor-50 peer-checked:bg-vaygor-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-vaygor-600 peer-focus-visible:ring-offset-2">
                      <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                      <span class="text-[10px] font-normal opacity-80">Tersedia</span>
                    </span>
                  </label>
                <?php elseif ($st === 'booked'): ?>
                  <div class="flex min-h-11 flex-col items-center justify-center rounded-xl border border-zinc-300 bg-zinc-200 px-2 py-2.5 text-sm font-semibold text-zinc-700" aria-disabled="true">
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    <span class="text-[10px] font-normal">Dipesan</span>
                  </div>
                <?php else: ?>
                  <div class="flex min-h-11 flex-col items-center justify-center rounded-xl border border-zinc-700 bg-zinc-800 px-2 py-2.5 text-sm font-semibold text-zinc-400" aria-disabled="true">
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    <span class="text-[10px] font-normal">Tutup</span>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
            <?php if ($grid['message'] !== '' && $grid['hasAvailable']): ?>
              <p class="mt-2 text-xs text-amber-700"><?php echo htmlspecialchars($grid['message'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <div id="slot-summary" class="rounded-xl bg-zinc-50 px-4 py-3 text-sm text-zinc-700" aria-live="polite">
          Pilih minimal satu slot jam di atas.
        </div>

        <div>
          <label class="block text-xs font-semibold" for="pay-method">Metode bayar</label>
          <select id="pay-method" name="pay" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm">
            <option value="qris" <?php echo $oldPay==='qris'?'selected':''; ?>>QRIS</option>
            <option value="transfer" <?php echo $oldPay==='transfer'?'selected':''; ?>>Transfer</option>
            <option value="cash" <?php echo $oldPay==='cash'?'selected':''; ?>>Cash di venue</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold" for="book-notes">Catatan (opsional)</label>
          <textarea id="book-notes" name="notes" rows="2" placeholder="Kebutuhan tambahan" class="mt-1.5 w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm"><?php echo htmlspecialchars($oldNotes, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <button type="submit" name="book" <?php echo (!$grid['ok'] || !$grid['hasAvailable']) ? 'disabled' : ''; ?> class="w-full rounded-xl bg-vaygor-600 px-5 py-3.5 text-sm font-bold text-white hover:bg-vaygor-700 disabled:cursor-not-allowed disabled:opacity-50">Booking Sekarang</button>
        <p class="text-xs text-zinc-500 text-center">Durasi dihitung per jam dari slot terpilih. Cek bentrok otomatis.</p>
      </form>
    <?php endif; ?>
  </div>
</div>
<script>
(function () {
  var form = document.getElementById('booking-slot-form');
  if (!form) return;
  var boxes = Array.prototype.slice.call(form.querySelectorAll('input[name="slots[]"]'));
  if (!boxes.length) return;
  var price = parseFloat(form.getAttribute('data-price') || '0');
  var summary = document.getElementById('slot-summary');
  var anchor = null;
  var byHour = {};
  boxes.forEach(function (b) {
    byHour[parseInt(b.value.split(':')[0], 10)] = b;
  });

  function hourOf(v) {
    return parseInt(v.split(':')[0], 10);
  }

  function pad(h) {
    return (h < 10 ? '0' : '') + h + ':00';
  }

  function fmtRp(n) {
    return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function selected() {
    return boxes.filter(function (b) { return b.checked; }).sort(function (a, b) {
      return hourOf(a.value) - hourOf(b.value);
    });
  }

  function updateSummary() {
    var sel = selected();
    if (!sel.length) {
      summary.textContent = 'Pilih minimal satu slot jam di atas.';
      return;
    }
    var hours = sel.length;
    var first = sel[0].value;
    var end = pad(hourOf(sel[sel.length - 1].value) + 1);
    summary.textContent = first + '-' + end + ' • ' + hours + ' jam • ' + fmtRp(hours * price);
  }

  form.addEventListener('change', function (ev) {
    var cb = ev.target;
    if (!cb || !cb.matches || !cb.matches('input[name="slots[]"]')) return;
    if (!cb.checked) {
      boxes.forEach(function (b) { b.checked = false; });
      anchor = null;
      updateSummary();
      return;
    }
    if (!anchor || !byHour[hourOf(anchor.value)] || !anchor.checked) {
      boxes.forEach(function (b) { b.checked = (b === cb); });
      anchor = cb;
      updateSummary();
      return;
    }
    var lo = Math.min(hourOf(anchor.value), hourOf(cb.value));
    var hi = Math.max(hourOf(anchor.value), hourOf(cb.value));
    var ok = true;
    for (var h = lo; h <= hi; h++) {
      if (!byHour[h]) {
        ok = false;
        break;
      }
    }
    boxes.forEach(function (b) { b.checked = false; });
    if (ok) {
      for (var h2 = lo; h2 <= hi; h2++) byHour[h2].checked = true;
      anchor = cb;
    } else {
      cb.checked = true;
      anchor = cb;
    }
    updateSummary();
  });

  updateSummary();
})();
</script>
