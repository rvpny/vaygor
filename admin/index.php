<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$user = current_user();
$pdo  = db();

$revenue        = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'paid'")->fetchColumn();
$totalBookings  = (int) $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$totalFields    = (int) $pdo->query("SELECT COUNT(*) FROM fields")->fetchColumn();
$totalUsers     = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

$recent = $pdo->query(
    "SELECT b.booking_code, b.booking_date, b.start_time, b.end_time, b.total_price, b.status,
            u.name AS user_name, f.name AS field_name
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     JOIN fields f ON f.id = b.field_id
     ORDER BY b.created_at DESC
     LIMIT 5"
)->fetchAll();

$statusLabel = [
    'pending'   => 'Menunggu',
    'confirmed' => 'Dikonfirmasi',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
];

$bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$today = date('d') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');

$base      = '..';
$pageTitle = 'Dashboard - Admin VAYGOR';
require __DIR__ . '/../includes/head.php';
?>
<div class="admin-shell">

  <?php $adminNav = 'dashboard'; require __DIR__ . '/_sidebar.php'; ?>

  <main class="admin-main">
    <header class="admin-topbar">
      <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-sub">Ringkasan aktivitas VAYGOR hari ini</p>
      </div>
      <div class="admin-top-date"><?= e($today) ?></div>
    </header>

    <section class="admin-stats">
      <div class="admin-stat is-revenue">
        <div class="admin-stat-label">Total Pendapatan</div>
        <div class="admin-stat-value">Rp <?= number_format($revenue, 0, ',', '.') ?></div>
        <div class="admin-stat-note">Dari pembayaran lunas</div>
      </div>
      <div class="admin-stat is-booking">
        <div class="admin-stat-label">Total Booking</div>
        <div class="admin-stat-value"><?= $totalBookings ?></div>
        <div class="admin-stat-note">Semua status</div>
      </div>
      <div class="admin-stat is-pending">
        <div class="admin-stat-label">Booking Menunggu</div>
        <div class="admin-stat-value"><?= $pendingBookings ?></div>
        <div class="admin-stat-note">Perlu konfirmasi</div>
      </div>
      <div class="admin-stat is-field">
        <div class="admin-stat-label">Total Lapangan</div>
        <div class="admin-stat-value"><?= $totalFields ?></div>
        <div class="admin-stat-note">Terdaftar di sistem</div>
      </div>
      <div class="admin-stat is-user">
        <div class="admin-stat-label">Total Pengguna</div>
        <div class="admin-stat-value"><?= $totalUsers ?></div>
        <div class="admin-stat-note">Akun role user</div>
      </div>
    </section>

    <section class="admin-panel">
      <div class="admin-panel-head">
        <h2 class="admin-panel-title">Booking Terbaru</h2>
        <a class="admin-link" href="bookings.php">Lihat semua</a>
      </div>

      <?php if (!$recent): ?>
        <div class="admin-empty">Belum ada booking.</div>
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
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $b): ?>
                <?php $st = $b['status']; ?>
                <tr>
                  <td><span class="admin-code"><?= e($b['booking_code']) ?></span></td>
                  <td><?= e($b['user_name']) ?></td>
                  <td class="admin-muted"><?= e($b['field_name']) ?></td>
                  <td class="admin-muted">
                    <?= e(date('d M Y', strtotime($b['booking_date']))) ?> &middot;
                    <?= e(substr($b['start_time'], 0, 5)) ?>&ndash;<?= e(substr($b['end_time'], 0, 5)) ?>
                  </td>
                  <td>Rp <?= number_format((float) $b['total_price'], 0, ',', '.') ?></td>
                  <td><span class="badge badge-<?= e($st) ?>"><?= e($statusLabel[$st] ?? $st) ?></span></td>
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
