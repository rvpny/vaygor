<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VAYGOR - Rent. Play. Win</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@800&family=Poppins:wght@400&family=League+Spartan:wght@600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: #111;
    }

    .mobile-landing {
      position: relative;
      width: 440px;
      max-width: 100vw;
      height: 956px;
      border-radius: 35px;
      overflow: hidden;
    }

    .bg-image {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: 60% center;
    }

    .gradient-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.60) 20%,
        rgba(14, 56, 29, 0.55) 40%,
        rgba(27, 112, 58, 1.00) 95%
      );
    }

    .content {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      height: 100%;
      padding: 101px 33px 55px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 3px;
    }

    .logo-letter {
      font-family: 'Kumbh Sans', sans-serif;
      font-weight: 800;
      font-size: 55px;
      color: #ffffff;
      line-height: 1;
    }

    .logo-icon {
      width: 64px;
      height: 68px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .logo-icon svg {
      width: 45px;
      height: 45px;
      fill: #ffffff;
    }

    .tagline {
      font-family: 'Poppins', sans-serif;
      font-weight: 400;
      font-size: 36px;
      color: #FACC15;
      letter-spacing: 0.04em;
      margin-top: 6px;
    }

    .spacer { flex: 1; }

    .description {
      font-family: 'Poppins', sans-serif;
      font-weight: 400;
      font-size: 15px;
      color: #F6F8F5;
      letter-spacing: 0.08em;
      line-height: 1.55;
      margin-bottom: 22px;
    }

    .cta-button {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 345px;
      padding: 26px 30px;
      background-color: #1E1E1E;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.35);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .cta-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 28px rgba(0, 0, 0, 0.45);
    }

    .cta-button:active {
      transform: translateY(0);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    .cta-button span {
      font-family: 'League Spartan', sans-serif;
      font-weight: 600;
      font-size: 24px;
      color: #FACC15;
    }

    @media (max-width: 480px) {
      .mobile-landing {
        width: 100vw;
        height: 100dvh;
        border-radius: 0;
      }
      .content { padding: 80px 24px 40px; }
      .logo-letter { font-size: 44px; }
      .logo-icon { width: 52px; height: 56px; }
      .logo-icon svg { width: 36px; height: 36px; }
      .tagline { font-size: 28px; }
      .description { font-size: 14px; }
      .cta-button { width: 100%; padding: 22px 24px; }
      .cta-button span { font-size: 20px; }
    }
  </style>
</head>
<body>
  <div class="mobile-landing">
    <!-- Ganti src dengan gambar kamu -->
    <img class="bg-image" src="https://images.unsplash.com/photo-1553778263-73a83bab9b0c?w=900&q=80" alt="Soccer player" />
    <div class="gradient-overlay"></div>
    <div class="content">
      <div class="logo">
        <span class="logo-letter">V</span>
        <span class="logo-letter">A</span>
        <span class="logo-letter">Y</span>
        <span class="logo-letter">G</span>
        <div class="logo-icon">
          <svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
            <path d="M256 25C128.3 25 25 128.3 25 256s103.3 231 231 231 231-103.3 231-231S383.7 25 256 25zm0 30c20.7 0 40.8 3.1 59.7 8.9l-26.4 45.7-33.3-19.2-33.3 19.2-26.4-45.7C215.2 58.1 235.3 55 256 55zm-80.6 19.8l28.2 48.9-38.5 22.2L121 130.6c14.8-22 34-40.5 54.4-55.8zm161.2 0c20.4 15.3 39.6 33.8 54.4 55.8l-44.1 15.3-38.5-22.2 28.2-48.9zM256 131.3l44.5 25.7v51.4L256 234l-44.5-25.6V157l44.5-25.7zM190 149.4v44.5l-38.5 22.2-44.1-15.3c5-22.5 14.2-43.5 27-62l55.6 10.6zm132 0l55.6-10.6c12.8 18.5 22 39.5 27 62l-44.1 15.3-38.5-22.2v-44.5zM90.9 225l46.7 16.2v44.5l-46.7 16.2c-3.5-18.6-5.4-37.7-5.4-57.4-.1-6.6.2-13 .5-19.5H90.9zm330.2 0c.3 6.5.5 12.9.5 19.5 0 19.7-1.9 38.8-5.4 57.4l-46.7-16.2v-44.5L416.2 225h4.9zM195.5 241.2L240 267v51.4l-44.5 25.7-44.5-25.7V267l44.5-25.8zm121 0L361 267v51.4l-44.5 25.7-44.5-25.7V267l44.5-25.8zM95.8 310.3l44.1 15.3 38.5 22.2v44.5L122.8 403c-12.8-18.5-22-39.5-27-62v0zm320.4 0c-5 22.5-14.2 43.5-27 62l-55.6-10.7v-44.5l38.5-22.2 44.1-15.3v.7zM256 310.6l44.5 25.7v51.4L256 413.3l-44.5-25.6v-51.4l44.5-25.7zm-125.3 99l44.1-15.3 38.5 22.2 28.2 48.9c-20.4-15.3-39.6-33.8-54.4-55.8h-56.4zm250.6 0c-14.8 22-34 40.5-54.4 55.8l28.2-48.9 38.5-22.2 44.1 15.3h-56.4zM222.7 419.3l33.3 19.2 33.3-19.2 26.4 45.7c-18.9 5.8-39 8.9-59.7 8.9s-40.8-3.1-59.7-8.9l26.4-45.7z"/>
          </svg>
        </div>
        <span class="logo-letter">R</span>
      </div>
      <h1 class="tagline">RENT. PLAY. WIN</h1>
      <div class="spacer"></div>
      <p class="description">Experience the thrill of the game. Just book, bring your teams and play on</p>
      <button class="cta-button"><span>Browse Now</span></button>
    </div>
  </div>
</body>
</html>
