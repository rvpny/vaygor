<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('index.php?p=browse');

$vid = (int) ($_GET['id'] ?? $_POST['id_field'] ?? 0);

$venue = null;
$scheds = [];
$errors = [];
$success = [];

if ($vid > 0) {
    $stmt = $conn->prepare("SELECT * FROM fields WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $venue = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($venue === null) {
    header('Location: index.php?p=browse');
    exit;
}

// Jadwal operasional per hari
try {
    $stmt = $conn->prepare("SELECT day_of_week, open_time, close_time FROM field_schedules WHERE field_id=? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) $scheds[$row['day_of_week']] = $row;
    $stmt->close();
} catch (Throwable $e) {
}

function booking_price($n)
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

// ── Proses booking ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking'])) {
    $date     = trim($_POST['date'] ?? '');
    $start    = trim($_POST['start'] ?? '');
    $duration = (int) ($_POST['duration'] ?? 1);
    $userId   = (int) ($_SESSION['id'] ?? 0);

    $dayName = $date !== '' ? date('l', strtotime($date)) : '';

    if ($date === '' || $start === '' || $duration < 1 || $duration > 4) {
        $errors[] = 'Pilih tanggal, jam mulai, dan durasi (1-4 jam).';
    } elseif (strtotime($date . ' ' . $start) <= strtotime(date('Y-m-d H:i'))) {
        $errors[] = 'Tanggal & jam main harus masih di masa depan.';
    } elseif ($scheds === [] || !isset($scheds[$dayName])) {
        $errors[] = 'Venue tutup di hari yang dipilih.';
    } else {
        $sched    = $scheds[$dayName];
        $openTime = substr($sched['open_time'], 0, 5);
        $closeTime = substr($sched['close_time'], 0, 5);
        $startTs  = strtotime($start);
        $endTime  = date('H:i', $startTs + $duration * 3600);

        if ($start < $openTime || $endTime > $closeTime) {
            $errors[] = "Jam main di luar jam operasional ($openTime - $closeTime).";
        } else {
            // Cek bentrok dengan booking lain
            $startFull = $start . ':00';
            $endFull   = $endTime . ':00';
            $check = $conn->prepare("SELECT COUNT(*) AS c
                                     FROM bookings
                                     WHERE id_field = ? AND booking_date = ? AND status <> 'cancelled'
                                       AND start_time < ? AND end_time > ?");
            $check->bind_param("isss", $vid, $date, $endFull, $startFull);
            $check->execute();
            $row = $check->get_result()->fetch_assoc();
            $check->close();

            if ($row['c'] > 0) {
                $errors[] = 'Slot tersebut sudah dibooking orang lain. Pilih jam lain.';
            } else {
                // Generate kode booking unik
                do {
                    $code = 'BOOK-' . date('Ymd') . '-' . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);
                    $cek = $conn->query("SELECT id FROM bookings WHERE booking_code = '" . $conn->real_escape_string($code) . "' LIMIT 1");
                    $exists = $cek && $cek->num_rows > 0;
                } while ($exists);

                $total = round((float) $venue['price'] * $duration, 2);

                $ins = $conn->prepare("INSERT INTO bookings (booking_code, id_user, id_field, booking_date, start_time, end_time, total_price, status, rate_service, rate_comfort, rate_place, review) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 0, 0, 0, '')");
                $ins->bind_param("siisssd", $code, $userId, $vid, $date, $startFull, $endFull, $total);
                if ($ins->execute()) {
                    $bookingId = $ins->insert_id;
                    $ins->close();

                    $pay = $conn->prepare("INSERT INTO payments (booking_id, payment_status, amount) VALUES (?, 'unpaid', ?)");
                    $pay->bind_param("id", $bookingId, $total);
                    $pay->execute();
                    $pay->close();

                    $success = [
                        'code'  => $code,
                        'venue' => $venue['name'],
                        'date'  => date('d M Y', strtotime($date)),
                        'time'  => $startFull . ' - ' . $endFull,
                        'total' => booking_price($total),
                    ];
                } else {
                    $errors[] = 'Gagal menyimpan booking. Silakan coba lagi.';
                    $ins->close();
                }
            }
        }
    }
}

// Opsi jam mulai dari jadwal hari yang dipilih
$selDate = $_POST['date'] ?? date('Y-m-d');
$selDay  = date('l', strtotime($selDate));
$slotSched = $scheds[$selDay] ?? null;
$slots = [];
if ($slotSched) {
    $t = strtotime($slotSched['open_time']);
    $end = strtotime($slotSched['close_time']) - 3600;
    while ($t <= $end) {
        $slots[] = date('H:i', $t);
        $t += 3600;
    }
}
?>
<div class="pt-20 pb-16">
  <div class="mx-auto max-w-5xl px-6 sm:px-8 lg:px-12 py-6">

    <a href="index.php?p=produk&id=<?php echo (int) $venue['id']; ?>" class="inline-flex items-center gap-1 text-sm text-zinc-600 hover:text-vaygor-600">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z" /></svg>
      Kembali ke <?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?>
    </a>

    <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8">
      <p class="inline-flex rounded-full bg-vaygor-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-vaygor-700 ring-1 ring-vaygor-100">Booking Lapangan</p>
      <h1 class="mt-3 font-spartan text-2xl sm:text-3xl font-extrabold tracking-tight"><?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
      <p class="mt-1 text-sm text-zinc-600"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?></p>
      <div class="mt-4 flex flex-wrap items-baseline gap-2 rounded-xl bg-vaygor-50 px-4 py-3">
        <span class="font-spartan text-2xl font-extrabold text-vaygor-600"><?php echo booking_price($venue['price']); ?></span>
        <span class="text-sm text-zinc-500">/ jam</span>
      </div>

      <?php if (!empty($success)): ?>
        <div role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5">
          <p class="font-spartan text-lg font-bold text-emerald-800">Booking berhasil dibuat!</p>
          <dl class="mt-3 grid gap-2 text-sm text-emerald-900 sm:grid-cols-2">
            <div class="rounded-lg bg-white/70 px-3 py-2"><dt class="text-xs font-semibold uppercase tracking-wide opacity-70">Kode Booking</dt><dd class="font-bold"><?php echo htmlspecialchars($success['code'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
            <div class="rounded-lg bg-white/70 px-3 py-2"><dt class="text-xs font-semibold uppercase tracking-wide opacity-70">Venue</dt><dd class="font-bold"><?php echo htmlspecialchars($success['venue'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
            <div class="rounded-lg bg-white/70 px-3 py-2"><dt class="text-xs font-semibold uppercase tracking-wide opacity-70">Tanggal &amp; Jam</dt><dd class="font-bold"><?php echo htmlspecialchars($success['date'] . ' • ' . $success['time'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
            <div class="rounded-lg bg-white/70 px-3 py-2"><dt class="text-xs font-semibold uppercase tracking-wide opacity-70">Total (unpaid)</dt><dd class="font-bold"><?php echo htmlspecialchars($success['total'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
          </dl>
          <p class="mt-4 text-sm text-emerald-800/80">Pembayaran dilakukan di venue saat main. Simpan kode booking sebagai bukti.</p>
          <a href="index.php?p=browse" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-vaygor-700">Jelajahi lapangan lain</a>
        </div>

      <?php else: ?>
        <?php foreach ($errors as $e): ?>
          <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endforeach; ?>

        <form action="" method="post" class="mt-6 grid gap-5 sm:grid-cols-2" novalidate>
          <input type="hidden" name="id_field" value="<?php echo (int) $venue['id']; ?>">

          <div>
            <label for="bdate" class="block text-sm font-semibold text-zinc-800">Tanggal Main</label>
            <input id="bdate" type="date" name="date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($selDate, ENT_QUOTES, 'UTF-8'); ?>" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
          </div>

          <div>
            <label for="bdur" class="block text-sm font-semibold text-zinc-800">Durasi</label>
            <select id="bdur" name="duration" class="mt-2 w-full cursor-pointer rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
              <option value="1">1 jam</option>
              <option value="2">2 jam</option>
              <option value="3">3 jam</option>
              <option value="4">4 jam</option>
            </select>
          </div>

          <div class="sm:col-span-2">
            <label for="bstart" class="block text-sm font-semibold text-zinc-800">Jam Mulai</label>
            <?php if ($slots): ?>
              <div class="mt-2 flex flex-wrap gap-2" id="slotWrap">
                <?php foreach ($slots as $s): $active = ($_POST['start'] ?? '') === $s; ?>
                  <label class="cursor-pointer">
                    <input type="radio" name="start" value="<?php echo $s; ?>" class="peer sr-only" <?php echo $active ? 'checked' : ''; ?>>
                    <span class="inline-flex items-center rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 transition peer-checked:border-vaygor-600 peer-checked:bg-vaygor-600 peer-checked:text-white hover:border-vaygor-400"><?php echo $s; ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <p class="mt-2 text-xs text-zinc-500"><?php echo $slotSched['day_of_week']; ?>: <?php echo substr($slotSched['open_time'], 0, 5); ?> - <?php echo substr($slotSched['close_time'], 0, 5); ?> WIB</p>
            <?php else: ?>
              <p class="mt-2 text-sm text-zinc-500">Pilih tanggal dulu, jam main akan muncul sesuai jadwal operasional.</p>
            <?php endif; ?>
          </div>

          <div class="sm:col-span-2">
            <button type="submit" name="booking" class="w-full rounded-xl bg-vaygor-600 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-vaygor-700">Konfirmasi Booking</button>
            <p class="mt-2 text-center text-xs text-zinc-500">Setiap jam dikenakan <?php echo booking_price($venue['price']); ?>. Bayar di venue saat main.</p>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>