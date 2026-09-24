<?php
if (empty($_SESSION['id'])) { header('Location: login.php'); exit; }
$uid = (int)$_SESSION['id'];
$msg = ''; $msgOk = false;

// deteksi skema: lama = id_user / tanpa duration+payment_method+proof_image
$isOldSchema = false;
try {
    $res = $conn->query("SELECT user_id FROM bookings LIMIT 1");
    $res->fetch_row();
    $res->free();
} catch (Throwable $e) { $isOldSchema = true; }
$hasProofCol = !$isOldSchema;
if ($hasProofCol) {
    try {
        $res = $conn->query("SELECT proof_image FROM payments LIMIT 1");
        $res->fetch_row();
        $res->free();
    } catch (Throwable $e) { $hasProofCol = false; }
}

if (isset($_POST['cancel_id'])) {
    $bid = (int)$_POST['cancel_id'];
    try {
        if ($isOldSchema) {
            $stmt = $conn->prepare("SELECT id, id_user AS uid, status FROM bookings WHERE id=? LIMIT 1");
        } else {
            $stmt = $conn->prepare("SELECT id, user_id AS uid, status FROM bookings WHERE id=? LIMIT 1");
        }
        $stmt->bind_param("i", $bid);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$b || (int)$b['uid'] !== $uid) $msg = 'Pesanan tidak ditemukan.';
        elseif ($b['status'] !== 'pending') $msg = 'Hanya pesanan pending yang bisa dibatalkan.';
        else {
            $stmt = $conn->prepare("UPDATE bookings SET status='cancelled' WHERE id=?");
            $stmt->bind_param("i", $bid);
            $stmt->execute(); $stmt->close();
            $msg = 'Pesanan dibatalkan. Slot kembali tersedia.';
            $msgOk = true;
        }
    } catch(Throwable $e) { $msg = 'Gagal membatalkan.'; }
}
if (isset($_POST['upload_id']) && isset($_FILES['proof'])) {
    if (!$hasProofCol) {
        $msg = 'Upload bukti belum tersedia di database ini.';
    } else {
    $bid = (int)$_POST['upload_id'];
    try {
        if ($isOldSchema) {
            $stmt = $conn->prepare("SELECT b.id, b.id_user AS uid, b.status, p.payment_status, p.id AS pid FROM bookings b JOIN payments p ON p.booking_id=b.id WHERE b.id=? LIMIT 1");
        } else {
            $stmt = $conn->prepare("SELECT b.id, b.user_id AS uid, b.status, p.payment_status, p.id AS pid FROM bookings b JOIN payments p ON p.booking_id=b.id WHERE b.id=? LIMIT 1");
        }
        $stmt->bind_param("i", $bid);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$b || (int)$b['uid'] !== $uid) $msg = 'Pesanan tidak ditemukan.';
        elseif (!in_array($b['status'], ['pending','confirmed'], true) || $b['payment_status']==='paid') $msg = 'Bukti hanya untuk pesanan pending/confirmed yang belum lunas.';
        else {
            $f = $_FILES['proof'];
            if ($f['error']!==UPLOAD_ERR_OK) $msg = 'Gagal upload file.';
            elseif ($f['size']>2*1024*1024) $msg = 'File maksimal 2MB.';
            else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);
                $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/jpg'=>'jpg'];
                if (!isset($allowed[$mime])) $msg = 'Hanya JPG/PNG.';
                else {
                    $ext = $allowed[$mime];
                    $name = 'BOOK-' . $bid . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dir = __DIR__ . '/../assets/uploads/bukti';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $dest = $dir . '/' . $name;
                    if (!move_uploaded_file($f['tmp_name'], $dest)) $msg = 'Gagal menyimpan file.';
                    else {
                        $rel = 'assets/uploads/bukti/' . $name;
                        $stmt = $conn->prepare("UPDATE payments SET proof_image=?, payment_status='pending' WHERE id=?");
                        $stmt->bind_param("si", $rel, $b['pid']);
                        $stmt->execute(); $stmt->close();
                        $msg = 'Bukti terkirim, menunggu verifikasi.';
                        $msgOk = true;
                    }
                }
            }
        }
    } catch(Throwable $e) { $msg = 'Gagal upload.'; }
    }
}
if (isset($_POST['review_id'])) {
    $bid = (int)$_POST['review_id'];
    $rs = (int)($_POST['rate_service'] ?? 0);
    $rc = (int)($_POST['rate_comfort'] ?? 0);
    $rp = (int)($_POST['rate_place'] ?? 0);
    $rv = trim($_POST['review_text'] ?? '');
    $fieldCol = $isOldSchema ? 'id_user' : 'user_id';
    try {
        $stmt = $conn->prepare("SELECT id, status, rate_service FROM bookings WHERE id=? AND $fieldCol=? LIMIT 1");
        $stmt->bind_param("ii", $bid, $uid);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$b) $msg = 'Pesanan tidak ditemukan.';
        elseif ($b['status'] !== 'completed') $msg = 'Hanya pesanan selesai yang bisa diulas.';
        elseif ((int)$b['rate_service'] > 0) $msg = 'Pesanan ini sudah pernah diulas.';
        elseif ($rs < 1 || $rs > 5 || $rc < 1 || $rc > 5 || $rp < 1 || $rp > 5) $msg = 'Rating harus 1 sampai 5.';
        elseif ($rv === '') $msg = 'Tulis ulasan singkat dulu.';
        elseif (mb_strlen($rv) > 100) $msg = 'Ulasan maksimal 100 karakter.';
        else {
            $stmt = $conn->prepare("UPDATE bookings SET rate_service=?, rate_comfort=?, rate_place=?, review=? WHERE id=?");
            $stmt->bind_param("iiisi", $rs, $rc, $rp, $rv, $bid);
            if ($stmt->execute()) {
                $msg = 'Ulasan terkirim. Makasih!';
                $msgOk = true;
            } else $msg = 'Gagal menyimpan ulasan.';
            $stmt->close();
        }
    } catch (Throwable $e) { $msg = 'Gagal menyimpan ulasan.'; }
}

// Filter status (GET)
$statusFilter = trim($_GET['status'] ?? '');
$allowedStatus = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($statusFilter, $allowedStatus, true)) $statusFilter = '';

$statusCounts = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
try {
    $userCol = $isOldSchema ? 'id_user' : 'user_id';
    $sqlC = "SELECT status, COUNT(*) AS c FROM bookings WHERE $userCol=? GROUP BY status";
    $stmt = $conn->prepare($sqlC);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        if (isset($statusCounts[$row['status']])) $statusCounts[$row['status']] = (int)$row['c'];
    }
    $stmt->close();
} catch (Throwable $e) {}

$rows = [];
try {
    if ($isOldSchema) {
        $sql = "SELECT b.id, b.booking_code, b.booking_date, b.start_time, b.end_time, b.total_price, b.status, b.rate_service, b.rate_comfort, b.rate_place, b.review, b.id_field, f.name AS venue_name, p.payment_status FROM bookings b JOIN fields f ON f.id=b.id_field LEFT JOIN payments p ON p.booking_id=b.id WHERE b.id_user=?";
        if ($statusFilter !== '') $sql .= " AND b.status=?";
        $sql .= " ORDER BY b.id DESC LIMIT 20";
        if ($statusFilter !== '') {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $uid, $statusFilter);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $uid);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $r['duration'] = (int)ceil((strtotime($r['booking_date'].' '.$r['end_time']) - strtotime($r['booking_date'].' '.$r['start_time'])) / 3600);
            $r['payment_method'] = '-';
            $r['proof_image'] = '';
            $rows[] = $r;
        }
        $stmt->close();
    } else {
        $sql = "SELECT b.id, b.booking_code, b.booking_date, b.start_time, b.end_time, b.duration, b.total_price, b.status, b.rate_service, b.rate_comfort, b.rate_place, b.review, b.id_field, f.name AS venue_name, p.payment_method, p.payment_status, p.proof_image FROM bookings b JOIN fields f ON f.id=b.field_id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.user_id=?";
        if ($statusFilter !== '') $sql .= " AND b.status=?";
        $sql .= " ORDER BY b.created_at DESC LIMIT 20";
        if ($statusFilter !== '') {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $uid, $statusFilter);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $uid);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
    }
} catch(Throwable $e) {}
function fmtRp($n){ return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function pesanan_status_label($s){
    return ['pending'=>'Menunggu konfirmasi','confirmed'=>'Dikonfirmasi','completed'=>'Selesai','cancelled'=>'Dibatalkan'][$s] ?? $s;
}
function pesanan_status_badge($s){
    return match($s) {
        'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
        'confirmed' => 'bg-sky-50 text-sky-800 border-sky-200',
        'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
        default => 'bg-zinc-100 text-zinc-700 border-zinc-200',
    };
}
function pesanan_filter_href($st){
    $base = 'index.php?p=pesanan';
    return $st === '' ? $base : $base . '&status=' . urlencode($st);
}
$stepsMap = [
    'pending' => 1,
    'confirmed' => 2,
    'completed' => 3,
    'cancelled' => 0,
];
?>
<div class="pt-20">
  <div class="mx-auto max-w-3xl px-6 sm:px-8 lg:px-12 py-8">
    <p class="text-xs font-bold uppercase tracking-[0.2em] text-vaygor-600">Riwayat</p>
    <h1 class="mt-2 font-spartan text-2xl sm:text-3xl font-extrabold">Pesanan saya</h1>
    <p class="mt-1 text-sm text-zinc-600">Status pending menunggu konfirmasi pengelola.</p>
    <?php if($msg!==''): ?>
      <div role="<?php echo $msgOk?'status':'alert'; ?>" class="mt-4 rounded-xl border px-4 py-3 text-sm <?php echo $msgOk?'border-emerald-200 bg-emerald-50 text-emerald-800':'border-red-200 bg-red-50 text-red-700'; ?>"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="mt-5 flex flex-wrap gap-2" role="group" aria-label="Filter status pesanan">
      <?php
      $chips = [
          ['' , 'Semua', array_sum($statusCounts)],
          ['pending', 'Pending', $statusCounts['pending']],
          ['confirmed', 'Confirmed', $statusCounts['confirmed']],
          ['completed', 'Completed', $statusCounts['completed']],
          ['cancelled', 'Cancelled', $statusCounts['cancelled']],
      ];
      foreach ($chips as [$st, $label, $cnt]):
          $active = $statusFilter === $st;
      ?>
        <a href="<?php echo htmlspecialchars(pesanan_filter_href($st), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $active ? ' aria-current="true"' : ''; ?> class="rounded-full border px-3.5 py-1.5 text-xs font-semibold transition <?php echo $active ? 'border-vaygor-600 bg-vaygor-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-vaygor-600 hover:text-vaygor-600'; ?>"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int)$cnt; ?>)</a>
      <?php endforeach; ?>
    </div>

    <?php if(empty($rows)): ?>
      <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-8 text-center">
        <p class="font-semibold"><?php echo $statusFilter !== '' ? 'Tidak ada pesanan dengan status itu' : 'Belum ada pesanan'; ?></p>
        <p class="mt-1 text-sm text-zinc-600"><?php echo $statusFilter !== '' ? 'Coba ganti filter di atas.' : 'Cari lapangan dulu biar muncul di sini.'; ?></p>
        <a href="<?php echo $statusFilter !== '' ? htmlspecialchars(pesanan_filter_href(''), ENT_QUOTES, 'UTF-8') : 'index.php?p=browse'; ?>" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-vaygor-700"><?php echo $statusFilter !== '' ? 'Lihat semua pesanan' : 'Cari lapangan'; ?></a>
      </div>
    <?php else: ?>
      <div class="mt-6 space-y-3">
        <?php foreach($rows as $r):
          $st = $r['status'];
          $step = $stepsMap[$st] ?? 0;
          $canReview = $st === 'completed' && (int)($r['rate_service'] ?? 0) === 0;
        ?>
          <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
              <div>
                <p class="font-mono text-xs text-zinc-500"><?php echo htmlspecialchars($r['booking_code'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="font-semibold"><?php echo htmlspecialchars($r['venue_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="text-sm text-zinc-600"><?php echo htmlspecialchars($r['booking_date'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars(substr($r['start_time'],0,5), ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars(substr($r['end_time'],0,5), ENT_QUOTES, 'UTF-8'); ?> • <?php echo (int)($r['duration'] ?? 0); ?> jam • <?php echo htmlspecialchars(fmtRp($r['total_price']), ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
              <span class="inline-flex items-center self-start rounded-full border px-3 py-1 text-xs font-semibold <?php echo pesanan_status_badge($st); ?>"><?php echo htmlspecialchars(pesanan_status_label($st), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <?php if ($st !== 'cancelled'): ?>
              <ol class="mt-4 grid grid-cols-3 gap-2 text-center text-[11px]" aria-label="Status tahapan pesanan">
                <?php
                $stepLabels = [[1, 'Dipesan'], [2, 'Dikonfirmasi'], [3, 'Selesai']];
                foreach ($stepLabels as [$n, $lab]):
                    $done = $step >= $n;
                ?>
                  <li>
                    <span class="mx-auto flex h-6 w-6 items-center justify-center rounded-full border text-[10px] font-bold <?php echo $done ? 'border-vaygor-600 bg-vaygor-600 text-white' : 'border-zinc-300 bg-white text-zinc-400'; ?>" aria-hidden="true"><?php echo $done ? '✓' : (string)$n; ?></span>
                    <span class="mt-1 block <?php echo $done ? 'font-semibold text-zinc-800' : 'text-zinc-400'; ?>"><?php echo htmlspecialchars($lab, ENT_QUOTES, 'UTF-8'); ?></span>
                  </li>
                <?php endforeach; ?>
              </ol>
            <?php endif; ?>

            <?php if(!empty($r['proof_image'])): ?>
              <p class="mt-2 text-xs">Bukti: <a href="<?php echo htmlspecialchars($r['proof_image'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="text-vaygor-600 underline">lihat file</a> • menunggu verifikasi</p>
            <?php endif; ?>

            <?php if ((int)($r['rate_service'] ?? 0) > 0): ?>
              <div class="mt-3 rounded-xl bg-zinc-50 px-3 py-2 text-xs text-zinc-700">
                Ulasan kamu: <span aria-hidden="true">★</span> <?php echo (int)$r['rate_place']; ?>/<?php echo (int)$r['rate_comfort']; ?>/<?php echo (int)$r['rate_service']; ?> • <?php echo htmlspecialchars((string)$r['review'], ENT_QUOTES, 'UTF-8'); ?>
              </div>
            <?php endif; ?>

            <div class="mt-3 flex flex-wrap gap-2">
              <?php if($hasProofCol && in_array($st, ['pending','confirmed'], true) && ($r['payment_status'] ?? '') !== 'paid'): ?>
                <form method="post" enctype="multipart/form-data" class="flex items-center gap-2">
                  <input type="hidden" name="upload_id" value="<?php echo (int)$r['id']; ?>">
                  <label class="sr-only" for="proof-<?php echo (int)$r['id']; ?>">Unggah bukti bayar</label>
                  <input id="proof-<?php echo (int)$r['id']; ?>" type="file" name="proof" accept=".jpg,.jpeg,.png" required class="text-xs border border-zinc-200 rounded-lg px-2 py-1.5 bg-white">
                  <button type="submit" class="rounded-xl bg-vaygor-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-vaygor-700">Upload bukti</button>
                </form>
              <?php endif; ?>
              <?php if($st === 'pending'): ?>
                <form method="post" onsubmit="return confirm('Batalkan pesanan ini?');">
                  <input type="hidden" name="cancel_id" value="<?php echo (int)$r['id']; ?>">
                  <button type="submit" class="rounded-xl border border-red-200 bg-white px-4 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">Batalkan</button>
                </form>
              <?php endif; ?>
              <?php if ($st === 'confirmed' || $st === 'pending'): ?>
                <a href="index.php?p=booking&id=<?php echo (int)$r['id_field']; ?>" class="rounded-xl border border-zinc-200 bg-white px-4 py-1.5 text-xs font-bold text-zinc-700 hover:border-vaygor-600 hover:text-vaygor-600">Booking lagi</a>
              <?php endif; ?>
            </div>

            <?php if ($canReview): ?>
              <form method="post" class="mt-4 rounded-xl border border-zinc-200 bg-zinc-50 p-4 space-y-3">
                <input type="hidden" name="review_id" value="<?php echo (int)$r['id']; ?>">
                <p class="text-xs font-bold uppercase tracking-wider text-vaygor-700">Beri ulasan</p>
                <div class="grid gap-3 sm:grid-cols-3">
                  <div>
                    <label for="rp-<?php echo (int)$r['id']; ?>" class="block text-xs font-semibold text-zinc-700">Kondisi Lapangan</label>
                    <select id="rp-<?php echo (int)$r['id']; ?>" name="rate_place" required class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-2 py-2 text-sm">
                      <option value="">Pilih</option>
                      <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?php echo $i; ?>"><?php echo $i; ?> ★</option><?php endfor; ?>
                    </select>
                  </div>
                  <div>
                    <label for="rc-<?php echo (int)$r['id']; ?>" class="block text-xs font-semibold text-zinc-700">Kenyamanan</label>
                    <select id="rc-<?php echo (int)$r['id']; ?>" name="rate_comfort" required class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-2 py-2 text-sm">
                      <option value="">Pilih</option>
                      <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?php echo $i; ?>"><?php echo $i; ?> ★</option><?php endfor; ?>
                    </select>
                  </div>
                  <div>
                    <label for="rs-<?php echo (int)$r['id']; ?>" class="block text-xs font-semibold text-zinc-700">Komunikasi</label>
                    <select id="rs-<?php echo (int)$r['id']; ?>" name="rate_service" required class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-2 py-2 text-sm">
                      <option value="">Pilih</option>
                      <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?php echo $i; ?>"><?php echo $i; ?> ★</option><?php endfor; ?>
                    </select>
                  </div>
                </div>
                <div>
                  <label for="rv-<?php echo (int)$r['id']; ?>" class="block text-xs font-semibold text-zinc-700">Ulasan (maks 100 huruf)</label>
                  <textarea id="rv-<?php echo (int)$r['id']; ?>" name="review_text" rows="2" maxlength="100" required placeholder="Ceritain pengalaman main di sini" class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm"></textarea>
                </div>
                <button type="submit" class="rounded-xl bg-vaygor-600 px-5 py-2 text-xs font-bold text-white hover:bg-vaygor-700">Kirim ulasan</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
