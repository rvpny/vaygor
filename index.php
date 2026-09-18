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
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet"> -->
</head>


<body>

  <?php

  include 'includes/navbar.php';
  $p = $_GET['p'] ?? 'beranda';
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