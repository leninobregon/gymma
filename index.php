<?php
session_start();

// Si ya tiene sesión, mandarlo al DASHBOARD (no al registro de socios directamente)
if (isset($_SESSION['user_id'])) {
    header("Location: views/dashboard.php");
    exit();
}

// Si no, al login
header("Location: views/login.php");
exit();