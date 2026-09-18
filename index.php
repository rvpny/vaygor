<?php
include __DIR__ . '/database/konfig.php';
session_start();
$id = $_SESSION['id'] ?? '';

if (!empty($id)) {
  $_SESSION['status'] = true;
} else {
  $_SESSION['status'] = false;
}
?>
<!doctype html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style type="text/tailwindcss">
    @theme {
      --color-vaygor-50: #f0fdf4;
      --color-vaygor-100: #dcfce7;
      --color-vaygor-500: #16a34a;
      --color-vaygor-600: #1B703A;
      --color-vaygor-700: #14522a;
      --color-vaygor-950: #0a2e16;
      --color-lime-ball: #B6F500;
      --font-inter: "Inter", ui-sans-serif, system-ui, sans-serif;
      --font-spartan: "League Spartan", ui-sans-serif, system-ui, sans-serif;
      --radius-card: 16px;
      --radius-hero: 28px;
      --radius-pill: 999px;
    }
  </style>
  <style>
    :focus-visible { outline: 2px solid #1B703A; outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition: none !important; animation: none !important; } }
  </style>
</head>


<body class="bg-[#f6f8f5] text-zinc-900 antialiased">

  <?php

  include 'includes/navbar.php';
  $allowed = ['beranda', 'browse', 'produk', 'booking', 'pesanan'];
  $p = $_GET['p'] ?? 'beranda';
  if (!in_array($p, $allowed, true)) { $p = 'beranda'; }
  include 'pages/' . $p . '.php';
  ?>




  <!-- <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        AOS.init({
            duration: 700,
            easing: 'ease-out-cubic',
            once: true,
            offset: 80
        });
    </script> -->
</body>


</html>