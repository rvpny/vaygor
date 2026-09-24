<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$pdo = db();

$allowedStatus = ['unpaid', 'pending', 'paid', 'failed', 'refunded'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id     = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    $back   = (string) ($_POST['back'] ?? '');

    if ($id > 0 && in_array($status, $allowedStatus, true)) {
        $stmt = $pdo->prepare(
            "UPDATE payments SET payment_status = ?,
                    payment_date = IF(? = 'paid' AND payment_date IS NULL, NOW(), payment_date)
             WHERE id = ?"
        );
        $stmt->execute([$status, $status, $id]);
        flash_set('ok', 'Status pembayaran berhasil diperbarui.');
    } else {
        flash_set('err', 'Perubahan status tidak valid.');
    }

    redirect('payments.php' . ($back !== '' ? '?status=' . urlencode($back) : ''));
}

$filter = (string) ($_GET['status'] ?? '');
if (!in_array($filter, $allowedStatus, true)) {
    $filter = '';
}

$sql = "SELECT p.id, p.amount, p.payment_date, p.payment_status,
               b.booking_code,
               u.name AS user_name,
               f.name AS field_name
        FROM payments p
        JOIN bookings b ON b.id = p.booking_id
        JOIN users u ON u.id = b.id_user
        JOIN fields f ON f.id = b.id_field";
$params = [];
if ($filter !== '') {
    $sql .= ' WHERE p.payment_status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY p.created_at DESC, p.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

$counts   = $pdo->query('SELECT payment_status, COUNT(*) FROM payments GROUP BY payment_status')->fetchAll(PDO::FETCH_KEY_PAIR);
$totalPaid = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'paid'")->fetchColumn();

$paymentLabel = [
    'paid'     => 'Lunas',
    'unpaid'   => 'Belum Bayar',
    'pending'  => 'Pending',
    'failed'   => 'Gagal',
    'refunded' => 'Refund',
];

$filters = [
    ''          => 'Semua',
    'unpaid'    => 'Belum Bayar',
    'pending'   => 'Pending',
    'paid'      => 'Lunas',
    'failed'    => 'Gagal',
    'refunded'  => 'Refund',
];

$base      = '..';
$pageTitle = 'Kelola Pembayaran - Admin VAYGOR';
require __DIR__ . '/../includes/head.php';
?>
<div class="admin-shell">

  <?php $adminNav = 'pembayaran'; require __DIR__ . '/_sidebar.php'; ?>

  <main class="admin-main">
    <header class="admin-topbar">
      <div>
        <h1 class="admin-page-title">Kelola Pembayaran</h1>
        <p class="admin-page-sub">Status pembayaran tiap booking</p>
      </div>
      <div class="admin-top-date">Rp <?= number_format($totalPaid, 0, ',', '.') ?> lunas</div>
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
           href="payments.php<?= $key !== '' ? '?status=' . urlencode($key) : '' ?>">
          <?= e($label) ?> <b><?= $count ?></b>
        </a>
      <?php endforeach; ?>
    </nav>

    <section class="admin-panel">
      <?php if (!$payments): ?>
        <div class="admin-empty">Tidak ada pembayaran untuk filter ini.</div>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Booking</th>
                <th>Penyewa</th>
                <th>Lapangan</th>
                <th>Jumlah</th>
                <th>Tanggal Bayar</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $p): ?>
                <tr>
                  <td><span class="admin-code"><?= e($p['booking_code']) ?></span></td>
                  <td><?= e($p['user_name']) ?></td>
                  <td class="admin-muted"><?= e($p['field_name']) ?></td>
                  <td>Rp <?= number_format((float) $p['amount'], 0, ',', '.') ?></td>
                  <td class="admin-muted">
                    <?= e($p['payment_date'] ? date('d M Y, H:i', strtotime($p['payment_date'])) : '—') ?>
                  </td>
                  <td>
                    <form method="post" class="admin-status-form">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                      <input type="hidden" name="back" value="<?= e($filter) ?>">
                      <select name="status" class="admin-select" aria-label="Status pembayaran">
                        <?php foreach ($allowedStatus as $s): ?>
                          <option value="<?= e($s) ?>"<?= $p['payment_status'] === $s ? ' selected' : '' ?>><?= e($paymentLabel[$s]) ?></option>
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