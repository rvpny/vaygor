
<html lang="id">
  <!DOCTYPE html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VAYGOR - Browse</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@800&family=Poppins:wght@400;600&family=League+Spartan:wght@400;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after {
      margin: 0; padding: 0; box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      background-color: #f0f0f0;
    }

    .mobile-browse {
      position: relative;
      width: 440px;
      max-width: 100vw;
      min-height: 100vh;
      background: #fff;
      overflow-x: hidden;
    }

    /* ── Navbar ── */
    .navbar {
      position: sticky;
      top: 0;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 30px;
      background-color: rgba(27, 112, 58, 0.9);
      backdrop-filter: blur(8px);
    }

    .nav-logo {
      display: flex;
      align-items: center;
      gap: 1px;
      text-decoration: none;
    }

    .nav-logo span {
      font-family: 'Kumbh Sans', sans-serif;
      font-weight: 800;
      font-size: 19px;
      color: #F6F8F5;
      line-height: 1;
    }

    .nav-logo .logo-ball {
      width: 22px; height: 23px;
      display: flex; align-items: center; justify-content: center;
    }

    .nav-logo .logo-ball svg {
      width: 15px; height: 15px; fill: #F6F8F5;
    }

    .nav-icon {
      width: 30px; height: 30px;
      display: flex; align-items: center; justify-content: center;
      color: #F6F8F5; cursor: pointer;
    }

    .nav-icon svg { width: 24px; height: 24px; fill: currentColor; }

    /* ── Hero Gradient ── */
    .hero-gradient {
      height: 200px;
      margin-top: -70px;
      background: linear-gradient(
        to top,
        rgba(27, 112, 58, 1) 0%,
        rgba(27, 112, 58, 0.85) 40%,
        rgba(246, 248, 245, 1) 100%
      );
    }

    /* ── Search Bar ── */
    .search-section {
      padding: 0 24px;
      margin-top: -40px;
      position: relative;
      z-index: 10;
    }

    .search-bar {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #fff;
      border-radius: 50px;
      padding: 16px 20px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .search-bar svg {
      width: 20px; height: 20px; fill: rgba(0,0,0,0.4); flex-shrink: 0;
    }

    .search-bar input {
      border: none; outline: none;
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      color: #000;
      width: 100%;
      background: transparent;
    }

    .search-bar input::placeholder {
      color: rgba(0,0,0,0.6);
    }

    /* ── Filter Tags ── */
    .filter-tags {
      display: flex;
      gap: 10px;
      padding: 24px 24px 0;
      overflow-x: auto;
      scrollbar-width: none;
    }

    .filter-tags::-webkit-scrollbar { display: none; }

    .filter-tag {
      flex-shrink: 0;
      padding: 14px 24px;
      border-radius: 50px;
      border: 1.5px solid rgba(0,0,0,0.15);
      background: transparent;
      font-family: 'League Spartan', sans-serif;
      font-weight: 400;
      font-size: 16px;
      color: rgba(0,0,0,0.6);
      cursor: pointer;
      transition: all 0.2s;
    }

    .filter-tag:hover {
      border-color: #1B703A;
      color: #1B703A;
    }

    /* ── Section Title ── */
    .section-title {
      font-family: 'League Spartan', sans-serif;
      font-weight: 600;
      font-size: 20px;
      color: #1B703A;
      padding: 28px 24px 16px;
    }

    /* ── Event Banner ── */
    .event-banner {
      margin: 0 24px;
      height: 160px;
      border-radius: 20px;
      overflow: hidden;
      background: linear-gradient(135deg, #1B703A, #0d4a24);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .event-banner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .event-banner-placeholder {
      color: #fff;
      font-family: 'League Spartan', sans-serif;
      font-size: 32px;
      font-weight: 600;
      text-align: center;
      padding: 20px;
    }

    /* ── Dot Indicators ── */
    .dot-indicators {
      display: flex;
      justify-content: center;
      gap: 8px;
      padding: 16px 0;
    }

    .dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: rgba(0,0,0,0.15);
    }

    .dot.active {
      width: 24px;
      border-radius: 4px;
      background: #1B703A;
    }

    /* ── Type Grid ── */
    .type-grid {
      display: flex;
      gap: 12px;
      padding: 0 24px;
      overflow-x: auto;
      scrollbar-width: none;
    }

    .type-grid::-webkit-scrollbar { display: none; }

    .type-item {
      flex-shrink: 0;
      width: 79px; height: 79px;
      border-radius: 6px;
      overflow: hidden;
      background: #e0e0e0;
      cursor: pointer;
      transition: transform 0.2s;
    }

    .type-item:hover { transform: scale(1.05); }

    .type-item img {
      width: 100%; height: 100%;
      object-fit: cover;
    }

    /* ── Divider ── */
    .divider {
      height: 5px;
      background: rgba(0,0,0,0.06);
      margin: 24px 0;
    }

    /* ── Active Filters ── */
    .active-filters {
      display: flex;
      gap: 16px;
      padding: 0 27px 16px;
      align-items: center;
    }

    .active-filter {
      font-family: 'League Spartan', sans-serif;
      font-weight: 400;
      font-size: 16px;
      color: #1B703A;
    }

    .active-filter-sep {
      width: 1px;
      height: 16px;
      background: rgba(27,112,58,0.3);
    }

    /* ── Venue Card ── */
    .venue-cards {
      padding: 0 19px;
      display: flex;
      flex-direction: column;
      gap: 32px;
    }

    .venue-card {
      border-radius: 10px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .venue-card-img {
      width: 100%;
      height: 130px;
      object-fit: cover;
      background: #c8d6c8;
    }

    .venue-card-body {
      padding: 14px 16px;
    }

    .venue-card-tags {
      display: flex;
      gap: 8px;
      margin-bottom: 8px;
    }

    .venue-card-tag {
      font-size: 10px;
      color: rgba(0,0,0,0.35);
    }

    .venue-card-name {
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      font-weight: 400;
      color: #000;
      margin-bottom: 4px;
    }

    .venue-card-reviews {
      display: flex;
      align-items: center;
      gap: 4px;
      margin-bottom: 8px;
    }

    .venue-card-stars {
      display: flex;
      gap: 2px;
    }

    .venue-card-stars svg {
      width: 12px; height: 12px; fill: #FACC15;
    }

    .venue-card-reviews span {
      font-size: 10px;
      color: rgba(0,0,0,0.6);
    }

    .venue-card-price {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 16px;
      color: #1B703A;
    }

    /* ── Loading ── */
    .loading-text {
      text-align: center;
      padding: 32px 0;
      font-family: 'League Spartan', sans-serif;
      font-weight: 400;
      font-size: 24px;
      color: rgba(0,0,0,0.35);
    }

    /* ── Bottom Bar ── */
    .bottom-bar {
      position: sticky;
      bottom: 0;
      display: flex;
      justify-content: space-between;
      padding: 15px 35px;
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(10px);
      border-top: 1px solid rgba(0,0,0,0.06);
      z-index: 100;
    }

    .bottom-bar-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      text-decoration: none;
      cursor: pointer;
    }

    .bottom-bar-item svg {
      width: 24px; height: 24px;
      fill: rgba(0,0,0,0.4);
    }

    .bottom-bar-item.active svg {
      fill: #1B703A;
    }

    .bottom-bar-item span {
      font-family: 'League Spartan', sans-serif;
      font-weight: 400;
      font-size: 13px;
      color: #000;
    }

    .bottom-bar-item.active span {
      color: #1B703A;
      font-weight: 600;
    }

    /* ── Footer ── */
    .footer {
      background: #f8f8f8;
      padding: 40px 33px 30px;
    }

    .footer-logo {
      display: flex; align-items: center; gap: 1px;
      margin-bottom: 8px;
    }

    .footer-logo span {
      font-family: 'Kumbh Sans', sans-serif;
      font-weight: 800;
      font-size: 15px;
      color: #1B703A;
    }

    .footer-logo .logo-ball { width: 18px; height: 19px; display: flex; align-items: center; justify-content: center; }
    .footer-logo .logo-ball svg { width: 12px; height: 12px; fill: #1B703A; }

    .footer-company {
      font-weight: 600;
      font-size: 15px;
      color: #000;
      margin-bottom: 4px;
    }

    .footer-address {
      font-size: 13px;
      color: rgba(0,0,0,0.6);
      margin-bottom: 28px;
      line-height: 1.6;
    }

    .footer-social-icons {
      display: flex;
      gap: 12px;
      margin: 16px 0 28px;
    }

    .footer-social-icons a {
      width: 30px; height: 30px;
      border-radius: 50%;
      background: #1B703A;
      display: flex; align-items: center; justify-content: center;
    }

    .footer-social-icons a svg { width: 16px; height: 16px; fill: #fff; }

    .footer-columns {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px 20px;
      margin-bottom: 32px;
    }

    .footer-col h4 {
      font-weight: 600;
      font-size: 15px;
      color: #000;
      margin-bottom: 8px;
    }

    .footer-col ul {
      list-style: none;
    }

    .footer-col li {
      font-size: 13px;
      color: rgba(0,0,0,0.6);
      line-height: 2;
      cursor: pointer;
    }

    .footer-col li:hover {
      color: #1B703A;
    }

    .footer-copyright {
      text-align: center;
      font-size: 13px;
      color: rgba(0,0,0,0.35);
      padding-top: 20px;
      border-top: 1px solid rgba(0,0,0,0.08);
    }

    /* ── Responsive ── */
    @media (max-width: 480px) {
      .mobile-browse { width: 100vw; }
    }
  </style>
</head>
<body>

<div class="mobile-browse">

  <!-- Navbar -->
  <nav class="navbar">
    <a href="#" class="nav-logo">
      <span>V</span><span>A</span><span>Y</span><span>G</span>
      <div class="logo-ball">
        <svg viewBox="0 0 512 512"><path d="M256 25C128.3 25 25 128.3 25 256s103.3 231 231 231 231-103.3 231-231S383.7 25 256 25zm0 30c20.7 0 40.8 3.1 59.7 8.9l-26.4 45.7-33.3-19.2-33.3 19.2-26.4-45.7C215.2 58.1 235.3 55 256 55zm-80.6 19.8l28.2 48.9-38.5 22.2L121 130.6c14.8-22 34-40.5 54.4-55.8zm161.2 0c20.4 15.3 39.6 33.8 54.4 55.8l-44.1 15.3-38.5-22.2 28.2-48.9zM256 131.3l44.5 25.7v51.4L256 234l-44.5-25.6V157l44.5-25.7zM190 149.4v44.5l-38.5 22.2-44.1-15.3c5-22.5 14.2-43.5 27-62l55.6 10.6zm132 0l55.6-10.6c12.8 18.5 22 39.5 27 62l-44.1 15.3-38.5-22.2v-44.5z"/></svg>
      </div>
      <span>R</span>
    </a>
    <div class="nav-icon">
      <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
    </div>
  </nav>

  <!-- Hero Gradient -->
  <div class="hero-gradient"></div>

  <!-- Search -->
  <div class="search-section">
    <div class="search-bar">
      <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
      <input type="text" placeholder="Search for locations, sport or name">
    </div>
  </div>

  <!-- Filter Tags -->
  <div class="filter-tags">
    <button class="filter-tag">Indoor</button>
    <button class="filter-tag">Pancuranmas</button>
    <button class="filter-tag">Bola</button>
    <button class="filter-tag">Murah</button>
    <button class="filter-tag">Murah</button>
  </div>

  <!-- Discover Events -->
  <h2 class="section-title">Discover Events</h2>

  <div class="event-banner">
    <!-- Ganti dengan gambar event kamu -->
    <div class="event-banner-placeholder">
      🏆 HAORNAS 2026
    </div>
  </div>

  <div class="dot-indicators">
    <div class="dot active"></div>
    <div class="dot"></div>
  </div>

  <!-- Type -->
  <h2 class="section-title">Type</h2>

  <div class="type-grid">
    <div class="type-item"><img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=160&q=80" alt="Football"></div>
    <div class="type-item"><img src="https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=160&q=80" alt="Badminton"></div>
    <div class="type-item"><img src="https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=160&q=80" alt="Basketball"></div>
    <div class="type-item"><img src="https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?w=160&q=80" alt="Volleyball"></div>
    <div class="type-item"><img src="https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=160&q=80" alt="Tennis"></div>
  </div>

  <!-- Divider -->
  <div class="divider"></div>

  <!-- Active Filters -->
  <div class="active-filters">
    <span class="active-filter">Magelang</span>
    <div class="active-filter-sep"></div>
    <span class="active-filter">Football</span>
  </div>

  <!-- Venue Cards -->
  <div class="venue-cards">
    <div class="venue-card">
      <img class="venue-card-img" src="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=800&q=80" alt="Lapangan Pancuranmas">
      <div class="venue-card-body">
        <div class="venue-card-tags">
          <span class="venue-card-tag">Football</span>
          <span class="venue-card-tag">Magelang</span>
        </div>
        <div class="venue-card-name">Lapangan Pancuranmas</div>
        <div class="venue-card-reviews">
          <div class="venue-card-stars">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          </div>
          <span>25 Reviews</span>
        </div>
        <div class="venue-card-price">RP. 15,000/jam</div>
      </div>
    </div>
  </div>

  <!-- Loading -->
  <div class="loading-text">Loading...</div>

  <!-- Bottom Bar -->
  <nav class="bottom-bar">
    <a class="bottom-bar-item" href="#">
      <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
      <span>Home</span>
    </a>
    <a class="bottom-bar-item active" href="#">
      <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
      <span>Browse</span>
    </a>
    <a class="bottom-bar-item" href="#">
      <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg>
      <span>Order</span>
    </a>
    <a class="bottom-bar-item" href="#">
      <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
      <span>Profile</span>
    </a>
  </nav>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-logo">
      <span>V</span><span>A</span><span>Y</span><span>G</span>
      <div class="logo-ball">
        <svg viewBox="0 0 512 512"><path d="M256 25C128.3 25 25 128.3 25 256s103.3 231 231 231 231-103.3 231-231S383.7 25 256 25zm0 30c20.7 0 40.8 3.1 59.7 8.9l-26.4 45.7-33.3-19.2-33.3 19.2-26.4-45.7C215.2 58.1 235.3 55 256 55z"/></svg>
      </div>
      <span>R</span>
    </div>
    <div class="footer-company">PT YPCTY</div>
    <div class="footer-address">Jl. Ahmad Yani, Kajoran Tengah, Tel Aviv, Israel City, Israel</div>

    <div class="footer-social-icons">
      <a href="#"><svg viewBox="0 0 24 24"><path d="M22.46 6c-.77.35-1.6.58-2.46.69a4.27 4.27 0 0 0 1.88-2.37 8.59 8.59 0 0 1-2.72 1.04 4.28 4.28 0 0 0-7.29 3.9A12.13 12.13 0 0 1 3.1 4.9a4.28 4.28 0 0 0 1.32 5.71 4.24 4.24 0 0 1-1.94-.54v.05a4.28 4.28 0 0 0 3.43 4.19 4.3 4.3 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.97A8.58 8.58 0 0 1 2 19.54a12.08 12.08 0 0 0 6.56 1.92c7.88 0 12.2-6.53 12.2-12.2l-.01-.56A8.72 8.72 0 0 0 22.46 6z"/></svg></a>
      <a href="#"><svg viewBox="0 0 24 24"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10m0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg></a>
      <a href="#"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg></a>
    </div>

    <div class="footer-columns">
      <div class="footer-col">
        <h4>Tentang Kami</h4>
        <ul>
          <li>Informasi</li>
          <li>Kebijakan & Privasi</li>
          <li>Syarat dan Ketentuan</li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Kontak</h4>
        <ul>
          <li>rvpryg@hotmail</li>
          <li>+6256787881</li>
          <li>Social Media</li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Ekosistem</h4>
        <ul>
          <li>Hosting server</li>
          <li>Afiliasi</li>
          <li>Perlindungan</li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Dukungan</h4>
        <ul>
          <li>Pusat bantuan</li>
          <li>Dokumentasi</li>
          <li>Daftarkan Akun</li>
        </ul>
      </div>
    </div>

    <div class="footer-copyright">© 2026 YPLOVRV Indonesia All Right Reserved</div>
  </footer>

</div>

</body>
</html>
