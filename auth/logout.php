<?php
require_once __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    logout();
}

redirect('login.php');
