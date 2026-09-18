<?php
if (empty($_SESSION['id'])) { header('Location: login.php'); exit; }
$uid = (int)$_SESSION['id'];
$msg = ''; $msgOk = false;
if (isset($_POST['cancel_id'])) {
    $bid = (int)$_POST['cancel_id'];
    try {
        $stmt = $conn->prepare("SELECT id, user_id, status FROM bookings WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $bid);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$b || (int)$b['user_id'] !== $uid) $msg = 'Pesanan tidak ditemukan.';
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
    $bid = (int)$_POST['upload_id'];
    try {
        $stmt = $conn->prepare("SELECT b.id, b.user_id, b.status, p.payment_status, p.id AS pid FROM bookings b JOIN payments p ON p.booking_id=b.id WHERE b.id=? LIMIT 1");
        $stmt->bind_param("i", $bid);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$b || (int)$b['user_id'] !== $uid) $msg = 'Pesanan tidak ditemukan.';
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
$rows = [];
try {
    $stmt = $conn->prepare("SELECT b.id, b.booking_code, b.booking_date, b.start_time, b.end_time, b.duration, b.total_price, b.status, f.name AS venue_name, p.payment_method, p.payment_status, p.proof_image FROM bookings b JOIN fields f ON f.id=b.field_id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 20");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    while($r=$res->fetch_assoc()) $rows[]=$r;
    $stmt->close();
} catch(Throwable $e) {}
function fmtRp($n){ return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
?>
<div class="pt-20">
  <div class="mx-auto max-w-3xl px-6 sm:px-8 lg:px-12 py-8">
    <h1 class="font-spartan text-2xl font-extrabold">Pesanan saya</h1>
    <p class="text-sm text-zinc-600">Riwayat booking lapangan. Status pending menunggu konfirmasi.</p>
    <?php if($msg!==''): ?>
      <div role="<?php echo $msgOk?'status':'alert'; ?>" class="mt-4 rounded-xl border px-4 py-3 text-sm <?php echo $msgOk?'border-emerald-200 bg-emerald-50 text-emerald-800':'border-red-200 bg-red-50 text-red-700'; ?>"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if(empty($rows)): ?>
      <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-8 text-center">
        <p class="font-semibold">Belum ada pesanan</p>
        <a href="index.php?p=browse" class="mt-3 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white">Cari lapangan</a>
      </div>
    <?php else: ?>
      <div class="mt-6 space-y-3">
        <?php foreach($rows as $r): ?>
          <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
              <div>
                <p class="font-mono text-xs text-zinc-500"><?php echo htmlspecialchars($r['booking_code'], ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="font-semibold"><?php echo htmlspecialchars($r['venue_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="text-sm text-zinc-600"><?php echo htmlspecialchars($r['booking_date'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars(substr($r['start_time'],0,5), ENT_QUOTES, 'UTF-8'); ?>-<?php echo htmlspecialchars(substr($r['end_time'],0,5), ENT_QUOTES, 'UTF-8'); ?> • <?php echo (int)$r['duration']; ?> jam • <?php echo htmlspecialchars(fmtRp($r['total_price']), ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
              <span class="text-xs rounded-full bg-zinc-100 px-3 py-1 self-start"><?php echo htmlspecialchars($r['payment_method'], ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($r['payment_status'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <?php if(!empty($r['proof_image'])): ?>
              <p class="mt-2 text-xs">Bukti: <a href="<?php echo htmlspecialchars($r['proof_image'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="text-vaygor-600 underline">lihat file</a> • menunggu verifikasi</p>
            <?php endif; ?>
            <div class="mt-3 flex flex-wrap gap-2">
              <?php if(in_array($r['status'],['pending','confirmed'],true) && $r['payment_status']!=='paid'): ?>
                <form method="post" enctype="multipart/form-data" class="flex items-center gap-2">
                  <input type="hidden" name="upload_id" value="<?php echo (int)$r['id']; ?>">
                  <input type="file" name="proof" accept=".jpg,.jpeg,.png" required class="text-xs border border-zinc-200 rounded-lg px-2 py-1.5 bg-white">
                  <button type="submit" class="rounded-xl bg-vaygor-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-vaygor-700">Upload bukti</button>
                </form>
              <?php endif; ?>
              <?php if($r['status']==='pending'): ?>
                <form method="post" onsubmit="return confirm('Batalkan pesanan ini?');">
                  <input type="hidden" name="cancel_id" value="<?php echo (int)$r['id']; ?>">
                  <button type="submit" class="rounded-xl border border-red-200 bg-white px-4 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">Batalkan</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
