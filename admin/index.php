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
     JOIN users u ON u.id = b.id_user
     JOIN fields f ON f.id = b.id_field
     ORDER BY b.id DESC
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
        <p class="admin-eyebrow">Panel Admin</p>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-sub">Ringkasan aktivitas VAYGOR hari ini</p>
      </div>
      <div class="admin-top-date"><?= e($today) ?></div>
    </header>

    <section class="admin-stats" aria-label="Statistik ringkas">
      <div class="admin-stat is-revenue" data-animate style="--i:0">
        <span class="admin-stat-tile" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg></span>
        <div class="admin-stat-label">Total Pendapatan</div>
        <div class="admin-stat-value" data-count="<?= (int) $revenue ?>" data-money>Rp <?= number_format($revenue, 0, ',', '.') ?></div>
        <div class="admin-stat-note">Dari pembayaran lunas</div>
      </div>

      <div class="admin-stat is-booking" data-animate style="--i:1">
        <span class="admin-stat-tile" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg></span>
        <div class="admin-stat-label">Total Booking</div>
        <div class="admin-stat-value" data-count="<?= $totalBookings ?>"><?= $totalBookings ?></div>
        <div class="admin-stat-note">Semua status</div>
      </div>

      <a class="admin-stat is-pending" href="bookings.php" data-animate style="--i:2">
        <span class="admin-dot" aria-hidden="true"></span>
        <span class="admin-stat-tile" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg></span>
        <div class="admin-stat-label">Booking Menunggu</div>
        <div class="admin-stat-value" data-count="<?= $pendingBookings ?>"><?= $pendingBookings ?></div>
        <div class="admin-stat-note">Perlu konfirmasi</div>
      </a>

      <div class="admin-stat is-field" data-animate style="--i:3">
        <span class="admin-stat-tile" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 2 8v2h20V8L12 3zM4 12v7h3v-7H4zm6 0v7h4v-7h-4zm7 0v7h3v-7h-3zM2 21h20v2H2v-2z"/></svg></span>
        <div class="admin-stat-label">Total Lapangan</div>
        <div class="admin-stat-value" data-count="<?= $totalFields ?>"><?= $totalFields ?></div>
        <div class="admin-stat-note">Terdaftar di sistem</div>
      </div>

      <div class="admin-stat is-user" data-animate style="--i:4">
        <span class="admin-stat-tile" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></span>
        <div class="admin-stat-label">Total Pengguna</div>
        <div class="admin-stat-value" data-count="<?= $totalUsers ?>"><?= $totalUsers ?></div>
        <div class="admin-stat-note">Akun role user</div>
      </div>
    </section>

    <section class="admin-panel" data-animate style="--i:5">
      <div class="admin-panel-head">
        <h2 class="admin-panel-title">Booking Terbaru</h2>
        <a class="admin-link" href="bookings.php">Lihat semua</a>
      </div>

      <?php if (!$recent): ?>
        <div class="admin-empty">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5z"/></svg>
          Belum ada booking.
        </div>
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
<script>
(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var els = document.querySelectorAll('.admin-stat-value[data-count]');
  els.forEach(function (el, i) {
    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
    var finalText = el.textContent;
    var money = el.hasAttribute('data-money');
    el.textContent = money ? 'Rp 0' : '0';
    var dur = 600, startAt = null;
    setTimeout(function () {
      function step(ts) {
        if (startAt === null) startAt = ts;
        var t = Math.min((ts - startAt) / dur, 1);
        var v = Math.round(target * (1 - Math.pow(1 - t, 3)));
        el.textContent = money ? 'Rp ' + v.toLocaleString('id-ID') : String(v);
        if (t < 1) requestAnimationFrame(step);
        else el.textContent = finalText;
      }
      requestAnimationFrame(step);
    }, 140 + i * 40);
  });
})();
</script>
</body>
</html>
