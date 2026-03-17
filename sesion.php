<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no está autentificado, lo expulsa al login
if (!isset($_SESSION['autentificado']) || $_SESSION['autentificado'] !== true) {
    header("Location: index.php");
    exit;
}
?>