<?php
$pageTitle = 'Lapangan Pancuranmas - VAYGOR';
$hideBottomNav = true;
require __DIR__ . '/includes/head.php';
?>

<div class="site page-detail">

  <!-- ── Header ── -->
  <header class="page-header">
    <a class="page-back" href="index.php" aria-label="Kembali">
      <svg viewBox="0 0 24 24"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
    </a>
    <h1>Lapangan Pancuranmas</h1>
  </header>

  <!-- ── Gallery ── -->
  <div class="gallery">
    <img src="assets/images/wGrqDff8QHtt4PXzMsos8WVXJI_1.png" alt="Lapangan Pancuranmas">
    <img src="assets/images/BI1q9FfyMAeVA3VVQYv2goJnig_2.png" alt="Lapangan Pancuranmas">
    <img src="assets/images/vKBS9C3RJNkcYZLBU3IHvfLjv0_1.png" alt="Lapangan Pancuranmas">
  </div>

  <!-- ── Price / info ── -->
  <section class="detail-info">
    <div class="detail-price">
      <strong>Rp. 15,000</strong>
      <span>/ jam</span>
    </div>
    <h2 class="detail-name">Lapangan Pancuranmas</h2>
    <p class="detail-location">Kota Magelang, Jawa Tengah</p>
    <div class="detail-rating">
      <div class="stars">
        <?php for ($s = 0; $s < 5; $s++): ?>
        <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <?php endfor; ?>
      </div>
      <span class="review-count">25 Reviews</span>
    </div>
  </section>

  <!-- ── Tags ── -->
  <div class="tag-list">
    <span class="tag">Indoor</span>
    <span class="tag">Football</span>
    <span class="tag">24 Hour</span>
    <span class="tag">Agusify</span>
  </div>

  <hr class="divider">

  <!-- ── Description ── -->
  <section class="detail-section">
    <h3 class="detail-heading">Description</h3>
    <p class="detail-text">Lapangan Pancuranmas adalah lapangan futsal yang dikelola BUMDes Pancuranmas, Magelang. Berada di Desa Pancuranmas dekat rumah Yoiv. Lapangann ini dapat disewa seharga 15, cocok untuk yang ingin bermain futsal.</p>
    <img class="diagram-img" src="assets/images/Ayt7dcCT9fAjGZaPk6wuKGJslMU_1.png" alt="Denah ukuran lapangan">
  </section>

  <!-- ── Venue details ── -->
  <section class="detail-section">
    <dl class="info-rows">
      <div class="info-row"><dt>Luas Lapangan</dt><dd>25 x 11</dd></div>
      <div class="info-row"><dt>Harga</dt><dd>Rp. 15,000 / jam</dd></div>
      <div class="info-row"><dt>Kontak</dt><dd>083456789 (P.Agus)</dd></div>
      <div class="info-row"><dt>Lokasi</dt><dd>Desa Pancuranmas, samping rumah Yovi Magelang</dd></div>
    </dl>
  </section>

  <!-- ── Location ── -->
  <section class="detail-section">
    <div class="location-card">
      <img class="location-card-img" src="assets/images/PhExImUwbpxAs1tNEBY2rU2rO5k_2.png" alt="Peta lokasi Lapangan Pancuranmas">
      <div class="location-card-body">
        <div class="location-card-label">Lokasi Lapangan</div>
        <p class="location-card-address">Jalan Yopiuawal, Desa Pancuranmas, RT01/RW11, Kota Magelang, Magelang Utara, Jawa Tengah</p>
        <a class="location-card-link" href="#">Buka Peta &gt;</a>
      </div>
    </div>
  </section>

  <hr class="divider">

  <!-- ── Rules ── -->
  <section class="detail-section">
    <h3 class="detail-heading">Rules By Nael</h3>
    <p class="rules-intro">Aturan Pemakaian (Denda jika tidak mematuhi):</p>
    <ol class="rules">
      <li>Dilarang membawa sajam dan alkohol</li>
      <li>Dilarang melakukan kekerasan</li>
      <li>Konfirmasi Booking min 3 jam</li>
      <li>Dilarang merusak dan mengambil dari lapangan</li>
      <li>Kehilangan barang bukan tanggung jawab kami</li>
    </ol>
  </section>

  <hr class="divider">

  <!-- ── Reviews ── -->
  <section class="detail-section">
    <h3 class="detail-heading">Ulasan Penyewa</h3>

    <div class="review-summary">
      <div class="review-score">4.4</div>
      <div class="review-meta">
        <div class="stars">
          <?php for ($s = 0; $s < 5; $s++): ?>
          <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <?php endfor; ?>
        </div>
        <span class="review-count">25 Reviews</span>
      </div>
    </div>

    <div class="rating-bars">
      <?php
      $breakdown = [['Kondisi Lapangan', '4.00'], ['Kenyamanan &amp; Kebersihan', '3.70'], ['Komunikasi', '4.50']];
      foreach ($breakdown as $row):
        $pct = ((float) str_replace(',', '.', $row[1]) / 5) * 100;
      ?>
      <div class="rating-row">
        <span><?php echo $row[0]; ?></span>
        <b><?php echo $row[1]; ?></b>
        <div class="rating-track"><div class="rating-fill" style="width: <?php echo $pct; ?>%"></div></div>
      </div>
      <?php endforeach; ?>
    </div>

    <article class="review-card">
      <div class="review-card-head">
        <img class="review-avatar" src="assets/images/Kq6VosSy8jdclArIT8XO0NKR3nI.png" alt="@Basis nama tengahku">
        <div>
          <div class="review-author">@Basis nama tengahku</div>
          <div class="review-date">07 Agustus 2026</div>
        </div>
      </div>
      <p class="review-text">WOYY itu danis suruh pulang cok... ganggu ae</p>
      <div class="review-reply">
        <div class="review-reply-label">Balasan dari Lapangan Pancuranmas (P.Agus):</div>
        <p>Bayar harus dikejar dulu sampai masjid lu, untung tertangkap kau SUKI!!!</p>
      </div>
    </article>

    <button class="review-all" type="button">Lihat semua ulasan</button>
  </section>

  <!-- ── Venue lain ── -->
  <section class="section">
    <div class="section-header">
      <h2>Venue Lain di Magelang</h2>
      <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
    </div>

    <div class="venue-scroller">
      <?php for ($i = 0; $i < 3; $i++): ?>
      <article class="venue-card">
        <img class="venue-card-img" src="assets/images/wGrqDff8QHtt4PXzMsos8WVXJI_1.png" alt="Lapangan Hvman">
        <div class="venue-card-body">
          <div class="venue-tags">
            <span class="venue-tag">Football</span>
            <span class="venue-tag">Outdoor</span>
          </div>
          <div class="venue-name">Lapangan Hvman</div>
          <div class="venue-rating">
            <div class="stars">
              <?php for ($s = 0; $s < 5; $s++): ?>
              <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <?php endfor; ?>
            </div>
            <span class="review-count">5.0</span>
          </div>
          <div class="venue-price">RP. 150,000</div>
        </div>
      </article>
      <?php endfor; ?>
    </div>
  </section>

  <?php require __DIR__ . '/includes/footer.php'; ?>

</div>

<!-- ── Fixed action bar ── -->
<nav class="action-bar">
  <button class="action-icon" id="bookmarkBtn" type="button" aria-label="Simpan venue">
    <svg viewBox="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2zm0 15-5-2.18L7 18V5h10v13z"/></svg>
  </button>
  <a class="action-icon chat" href="#" aria-label="Chat pemilik">
    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
  </a>
  <button class="action-btn-schedule" type="button">Schedule</button>
</nav>

<?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>