<?php
session_start();
session_unset();
session_destroy();
$_SESSION['autentificado'] = false;
header('location:index.php');

?>