<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base         = $base ?? '';
$_pageTitle   = $pageTitle ?? 'VAYGOR - Rent. Play. Win';
$isAdminShell = ($adminShell ?? false) === true;
$assetRoot    = $base !== '' ? $base . '/' : '';
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($_pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="icon" href="<?= htmlspecialchars($assetRoot, ENT_QUOTES, 'UTF-8') ?>assets/images/logo(1).svg">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style type="text/tailwindcss">
    @theme {
      --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
      --font-spartan: "League Spartan", sans-serif;

      --color-vaygor-50: #eef7f0;
      --color-vaygor-100: #d6eedd;
      --color-vaygor-200: #b0dcc0;
      --color-vaygor-300: #82c49c;
      --color-vaygor-400: #4fa673;
      --color-vaygor-500: #2f8c58;
      --color-vaygor-600: #1b703a;
      --color-vaygor-700: #16582f;
      --color-vaygor-800: #124726;
      --color-vaygor-900: #0e3a1f;
      --color-vaygor-950: #062211;
      --color-neon: #b6f500;
      --color-cream: #fff4c7;
    }

    @layer base {
      html {
        scroll-behavior: smooth;
        margin: 0;
        padding: 0;
      }

      body {
        @apply m-0 p-0 bg-[#F6F8F5] text-gray-900 antialiased;
      }
    }
  </style>
  <?php if ($isAdminShell): ?>
  <link rel="stylesheet" href="<?= htmlspecialchars($assetRoot, ENT_QUOTES, 'UTF-8') ?>assets/css/admin.css">
  <?php endif; ?>
</head>

<body class="font-sans">