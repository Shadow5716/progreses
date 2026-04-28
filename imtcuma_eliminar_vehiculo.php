<?php
session_start();
require_once 'includes/dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    try {
        $id = $_POST['id'];
        $sql = "DELETE FROM imtcuma_vehiculos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: imtcuma_dashboard.php?msg=eliminado");
        exit();
    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: imtcuma_dashboard.php");
    exit();
}
?>