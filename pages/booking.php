<?php
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}
$uid = (int)$_SESSION['id'];
$fid = (int)($_GET['id'] ?? 0);
if ($fid <= 0) { header('Location: index.php?p=browse'); exit; }

$venue = null;
try {
    $stmt = $conn->prepare("SELECT id, name, location, price_per_hour, field_type FROM fields WHERE id=? AND status='available' LIMIT 1");
    $stmt->bind_param("i", $fid);
    $stmt->execute();
    $venue = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Throwable $e) {}
if (!$venue) { http_response_code(404); echo '<div class="pt-28 text-center"><p class="font-spartan font-bold">Venue tidak ditemukan</p><a href="index.php?p=browse" class="mt-3 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-white">Kembali</a></div>'; return; }

$errors = [];
$success = null;
$oldDate = $_GET['date'] ?? date('Y-m-d', strtotime('+1 day'));
$oldStart = $_GET['start'] ?? '16:00';
$oldEnd = $_GET['end'] ?? '18:00';
$oldPay = $_GET['pay'] ?? 'qris';
$oldNotes = '';

if (isset($_POST['book'])) {
    $oldDate = trim($_POST['date'] ?? '');
    $oldStart = trim($_POST['start'] ?? '');
    $oldEnd = trim($_POST['end'] ?? '');
    $oldPay = trim($_POST['pay'] ?? 'qris');
    $oldNotes = trim($_POST['notes'] ?? '');

    if ($oldDate === '' || $oldStart === '' || $oldEnd === '') $errors[] = 'Tanggal dan jam wajib diisi.';
    $d = DateTime::createFromFormat('Y-m-d', $oldDate);
    if (!$d || $d->format('Y-m-d') !== $oldDate) $errors[] = 'Tanggal tidak valid.';
    elseif ($oldDate < date('Y-m-d')) $errors[] = 'Tanggal tidak boleh di masa lalu.';

    $s = DateTime::createFromFormat('H:i', $oldStart);
    $e = DateTime::createFromFormat('H:i', $oldEnd);
    if (!$s || !$e) $errors[] = 'Format jam tidak valid.';
    elseif ($s >= $e) $errors[] = 'Jam selesai harus setelah jam mulai.';

    if (!in_array($oldPay, ['qris','transfer','cash'], true)) $errors[] = 'Metode bayar tidak valid.';

    // cek jam operasional
    if (empty($errors)) {
        $day = $d->format('l'); // Monday etc
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

    // cek bentrok (overlap) dengan booking pending/confirmed
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM bookings WHERE field_id=? AND booking_date=? AND status IN ('pending','confirmed') AND NOT (end_time <= ? OR start_time >= ?) LIMIT 1");
        $st = $oldStart . ':00';
        $et = $oldEnd . ':00';
        $stmt->bind_param("isss", $fid, $oldDate, $st, $et);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = 'Slot sudah dibooking. Pilih jam lain.';
        $stmt->close();
    }

    // hitung durasi & total
    $duration = 0; $total = 0;
    if (empty($errors)) {
        $diff = ($e->getTimestamp() - $s->getTimestamp()) / 3600;
        $duration = (int)ceil($diff);
        if ($duration < 1) $duration = 1;
        $total = $duration * (float)$venue['price_per_hour'];
    }

    if (empty($errors)) {
        $code = 'BOOK-' . date('Ymd', strtotime($oldDate)) . '-' . strtoupper(substr(uniqid(), -4));
        $st = $oldStart . ':00'; $et = $oldEnd . ':00';
        $price = (float)$venue['price_per_hour'];
        $stmt = $conn->prepare("INSERT INTO bookings (booking_code, user_id, field_id, booking_date, start_time, end_time, duration, price_per_hour, total_price, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("siisssidds", $code, $uid, $fid, $oldDate, $st, $et, $duration, $price, $total, $oldNotes);
        if ($stmt->execute()) {
            $bid = $stmt->insert_id;
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO payments (booking_id, payment_method, payment_status, amount) VALUES (?, ?, 'unpaid', ?)");
            $stmt->bind_param("isd", $bid, $oldPay, $total);
            $stmt->execute(); $stmt->close();
            $success = ['code'=>$code, 'date'=>$oldDate, 'start'=>$oldStart, 'end'=>$oldEnd, 'total'=>$total, 'pay'=>$oldPay];
        } else {
            $errors[] = 'Gagal menyimpan booking. Coba lagi.';
            $stmt->close();
        }
    }
}
function fmtRp($n){ return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
?>
<div class="pt-20">
  <div class="mx-auto max-w-3xl px-6 sm:px-8 lg:px-12 py-8">
    <a href="index.php?p=produk&id=<?php echo (int)$venue['id']; ?>" class="text-sm text-zinc-500 hover:text-vaygor-600">← Kembali ke detail</a>
    <h1 class="mt-3 font-spartan text-2xl sm:text-3xl font-extrabold">Booking <?php echo htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="text-sm text-zinc-600"><?php echo htmlspecialchars($venue['location'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(fmtRp($venue['price_per_hour']), ENT_QUOTES, 'UTF-8'); ?>/jam</p>

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
          <ul class="list-disc pl-5"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>
      <form method="post" class="mt-6 rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 shadow-sm space-y-4">
        <div class="grid sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold">Tanggal</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($oldDate, ENT_QUOTES, 'UTF-8'); ?>" required min="<?php echo date('Y-m-d'); ?>" class="mt-1.5 w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:border-vaygor-600 focus:ring-2 focus:ring-vaygor-600/20">
          </div>
          <div>
            <label class="block text-xs font-semibold">Mulai</label>
            <input type="time" name="start" value="<?php echo htmlspecialchars($oldStart, ENT_QUOTES, 'UTF-8'); ?>" required class="mt-1.5 w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold">Selesai</label>
            <input type="time" name="end" value="<?php echo htmlspecialchars($oldEnd, ENT_QUOTES, 'UTF-8'); ?>" required class="mt-1.5 w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm">
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold">Metode bayar</label>
          <select name="pay" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm">
            <option value="qris" <?php echo $oldPay==='qris'?'selected':''; ?>>QRIS</option>
            <option value="transfer" <?php echo $oldPay==='transfer'?'selected':''; ?>>Transfer</option>
            <option value="cash" <?php echo $oldPay==='cash'?'selected':''; ?>>Cash di venue</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold">Catatan (opsional)</label>
          <textarea name="notes" rows="2" placeholder="Kebutuhan tambahan" class="mt-1.5 w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm"><?php echo htmlspecialchars($oldNotes, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <button type="submit" name="book" class="w-full rounded-xl bg-vaygor-600 px-5 py-3.5 text-sm font-bold text-white hover:bg-vaygor-700">Booking Sekarang</button>
        <p class="text-xs text-zinc-500 text-center">Durasi dihitung per jam, dibulatkan ke atas. Cek bentrok otomatis.</p>
      </form>
    <?php endif; ?>
  </div>
</div>
