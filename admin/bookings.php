<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$pdo = db();

$allowedStatus = ['pending', 'confirmed', 'completed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id     = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    $back   = (string) ($_POST['back'] ?? '');

    if ($id > 0 && in_array($status, $allowedStatus, true)) {
        $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        flash_set('ok', 'Status booking berhasil diperbarui.');
    } else {
        flash_set('err', 'Perubahan status tidak valid.');
    }

    redirect('bookings.php' . ($back !== '' ? '?status=' . urlencode($back) : ''));
}

$filter = (string) ($_GET['status'] ?? '');
if (!in_array($filter, $allowedStatus, true)) {
    $filter = '';
}

$sql = "SELECT b.id, b.booking_code, b.booking_date, b.start_time, b.end_time, b.duration,
               b.total_price, b.status, b.notes,
               u.name AS user_name, u.phone AS user_phone,
               f.name AS field_name,
               p.payment_status, p.payment_method
        FROM bookings b
        JOIN users u ON u.id = b.user_id
        JOIN fields f ON f.id = b.field_id
        LEFT JOIN payments p ON p.booking_id = b.id";
$params = [];
if ($filter !== '') {
    $sql .= ' WHERE b.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY b.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$counts = $pdo->query('SELECT status, COUNT(*) FROM bookings GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

$statusLabel = [
    'pending'   => 'Menunggu',
    'confirmed' => 'Dikonfirmasi',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
];

$paymentLabel = [
    'paid'     => 'Lunas',
    'unpaid'   => 'Belum Bayar',
    'pending'  => 'Pending',
    'failed'   => 'Gagal',
    'refunded' => 'Refund',
];

$filters = [
    ''          => 'Semua',
    'pending'   => 'Menunggu',
    'confirmed' => 'Dikonfirmasi',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
];

$base      = '..';
$pageTitle = 'Kelola Booking - Admin VAYGOR';
require __DIR__ . '/../includes/head.php';
?>
<div class="admin-shell">

  <?php $adminNav = 'booking'; require __DIR__ . '/_sidebar.php'; ?>

  <main class="admin-main">
    <header class="admin-topbar">
      <div>
        <h1 class="admin-page-title">Kelola Booking</h1>
        <p class="admin-page-sub">Ubah status booking sesuai pembayaran dan jadwal</p>
      </div>
      <div class="admin-top-date"><?= array_sum($counts) ?> booking</div>
    </header>

    <?php if ($msg = flash_get('ok')): ?>
      <div class="admin-flash is-ok"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash_get('err')): ?>
      <div class="admin-flash is-err"><?= e($msg) ?></div>
    <?php endif; ?>

    <nav class="admin-filters">
      <?php foreach ($filters as $key => $label): ?>
        <?php $count = $key === '' ? array_sum($counts) : (int) ($counts[$key] ?? 0); ?>
        <a class="admin-filter<?= $filter === $key ? ' active' : '' ?>"
           href="bookings.php<?= $key !== '' ? '?status=' . urlencode($key) : '' ?>">
          <?= e($label) ?> <b><?= $count ?></b>
        </a>
      <?php endforeach; ?>
    </nav>

    <section class="admin-panel">
      <?php if (!$bookings): ?>
        <div class="admin-empty">Tidak ada booking untuk filter ini.</div>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Kode</th>
                <th>Penyewa</th>
                <th>Lapangan</th>
                <th>Jadwal</th>
                <th>Total</th>
                <th>Pembayaran</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $b): ?>
                <?php
                  $st  = $b['status'];
                  $pay = $b['payment_status'];
                ?>
                <tr>
                  <td><span class="admin-code"><?= e($b['booking_code']) ?></span></td>
                  <td>
                    <?= e($b['user_name']) ?>
                    <?php if ($b['user_phone']): ?>
                      <div class="admin-sub"><?= e($b['user_phone']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="admin-muted"><?= e($b['field_name']) ?></td>
                  <td class="admin-muted">
                    <?= e(date('d M Y', strtotime($b['booking_date']))) ?> &middot;
                    <?= e(substr($b['start_time'], 0, 5)) ?>&ndash;<?= e(substr($b['end_time'], 0, 5)) ?>
                    <div class="admin-sub"><?= (int) $b['duration'] ?> jam</div>
                  </td>
                  <td>Rp <?= number_format((float) $b['total_price'], 0, ',', '.') ?></td>
                  <td>
                    <?php if ($pay): ?>
                      <span class="badge badge-pay-<?= e($pay) ?>"><?= e($paymentLabel[$pay] ?? $pay) ?></span>
                      <div class="admin-sub"><?= e(strtoupper($b['payment_method'])) ?></div>
                    <?php else: ?>
                      <span class="admin-muted">&mdash;</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <form method="post" class="admin-status-form">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                      <input type="hidden" name="back" value="<?= e($filter) ?>">
                      <select name="status" class="admin-select" aria-label="Status booking">
                        <?php foreach ($allowedStatus as $s): ?>
                          <option value="<?= e($s) ?>"<?= $st === $s ? ' selected' : '' ?>><?= e($statusLabel[$s]) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="admin-btn-sm" type="submit">Simpan</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

</div>
</body>
</html>
