<?php require __DIR__ . '/includes/head.php'; ?>

<div class="site">

  <!-- ── HERO ── -->
  <header class="hero">
    <img class="hero-img" src="assets/images/3x12wclyJrsyLTLEdRiGAcsys_2.png" alt="Futsal">
    <div class="hero-overlay"></div>

    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <div class="hero-content">
      <h1 class="hero-title">Are You<br>Winning Soon</h1>
      <p class="hero-tagline">Best place to book sports field for you</p>
    </div>
  </header>

  <!-- ── FIND VENUE ── -->
  <section class="find-venue">
    <h2 class="find-venue-title">Find Venue</h2>

    <form action="browse.php" method="get">
      <div class="search-field">
        <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        <input type="text" name="q" placeholder="Search by name or location">
      </div>

      <div class="find-venue-row">
        <div class="select-field">
          <select name="type" aria-label="Sports Type">
            <option value="">Sports Type</option>
            <option>Futsal</option>
            <option>Sepak Bola</option>
            <option>Bulutangkis</option>
            <option>Basket</option>
            <option>Voli</option>
          </select>
          <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
        </div>
        <div class="select-field">
          <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5z"/></svg>
          <input type="date" name="date" aria-label="Date &amp; Time">
        </div>
      </div>

      <button class="search-btn" type="submit"><span>Search</span></button>
    </form>
  </section>

  <!-- ── TRENDING IN MAGELANG ── -->
  <section class="section">
    <div class="section-header">
      <h2>Trending in Magelang</h2>
      <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
    </div>
    <p class="section-subtitle">Check trending sport fields near your area, you can modify the result by choosing more options that suitable for you.</p>

    <div class="venue-scroller">
      <?php for ($i = 0; $i < 2; $i++): ?>
      <article class="venue-card">
        <img class="venue-card-img" src="assets/images/<?php echo $i === 0 ? 'wGrqDff8QHtt4PXzMsos8WVXJI_1.png' : 'vKBS9C3RJNkcYZLBU3IHvfLjv0_1.png'; ?>" alt="Lapangan Pancuranmas">
        <div class="venue-card-body">
          <div class="venue-tags">
            <span class="venue-tag">Futsal</span>
            <span class="venue-tag">Magelang</span>
            <span class="venue-tag">Indoor</span>
          </div>
          <div class="venue-name">Lapangan Pancuranmas</div>
          <div class="venue-rating">
            <div class="stars">
              <?php for ($s = 0; $s < 5; $s++): ?>
              <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <?php endfor; ?>
            </div>
            <span class="review-count">25 Reviews</span>
          </div>
          <div class="venue-price">RP. 15,000/jam</div>
        </div>
      </article>
      <?php endfor; ?>
    </div>
  </section>

  <!-- ── SPORTS TYPE ── -->
  <section class="section">
    <div class="section-header">
      <h2>Sports Type</h2>
      <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
    </div>

    <div class="type-scroller">
      <button class="type-item" type="button">
        <span class="type-thumb"><img src="assets/images/0CzrUdIITts0xeeGVC53q6AHgc.png" alt="Futsal"></span>
        <span class="type-label">Futsal</span>
      </button>
      <button class="type-item" type="button">
        <span class="type-thumb"><img src="assets/images/Kq6VosSy8jdclArIT8XO0NKR3nI.png" alt="Sepak Bola"></span>
        <span class="type-label">Sepak Bola</span>
      </button>
      <button class="type-item" type="button">
        <span class="type-thumb tile-green"></span>
        <span class="type-label">Badminton</span>
      </button>
      <button class="type-item" type="button">
        <span class="type-thumb tile-dark"></span>
        <span class="type-label">Basket</span>
      </button>
      <button class="type-item" type="button">
        <span class="type-thumb tile-green"></span>
        <span class="type-label">Voli</span>
      </button>
    </div>
  </section>

  <!-- ── LAST BOOKED ── -->
  <section class="section">
    <div class="section-header">
      <h2>Last Booked</h2>
      <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
    </div>

    <article class="wide-card">
      <img class="wide-card-img" src="assets/images/BI1q9FfyMAeVA3VVQYv2goJnig_2.png" alt="Lapangan Pancuranmas">
      <div class="wide-card-body">
        <div class="venue-tags">
          <span class="venue-tag">Football</span>
          <span class="venue-tag">Magelang</span>
          <span class="venue-tag">Indoor</span>
        </div>
        <div class="venue-name">Lapangan Pancuranmas</div>
        <div class="venue-rating">
          <div class="stars">
            <?php for ($s = 0; $s < 5; $s++): ?>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <?php endfor; ?>
          </div>
          <span class="review-count">25 Reviews</span>
        </div>
        <div class="venue-price">RP. 15,000/jam</div>
      </div>
    </article>
  </section>

  <!-- ── BROWSE ── -->
  <section class="section">
    <div class="section-header">
      <h2>Browse</h2>
      <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
    </div>

    <div class="browse-meta">
      <span>GET YOUR ARSE MOVIN</span>
      <svg viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>
    </div>

    <article class="wide-card">
      <img class="wide-card-img" src="assets/images/wvhSZZkg2I4c45k7QRN2vyO68.jpg" alt="Lapangan Pancuranmas">
      <div class="wide-card-body">
        <div class="venue-tags">
          <span class="venue-tag">Futsal</span>
          <span class="venue-tag">Magelang</span>
          <span class="venue-tag">Indoor</span>
        </div>
        <div class="venue-name">Lapangan Pancuranmas</div>
        <div class="venue-rating">
          <div class="stars">
            <?php for ($s = 0; $s < 5; $s++): ?>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <?php endfor; ?>
          </div>
          <span class="review-count">25 Reviews</span>
        </div>
        <div class="venue-price">RP. 15,000/jam</div>
      </div>
    </article>
  </section>

  <?php require __DIR__ . '/includes/footer.php'; ?>

</div>

<?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>