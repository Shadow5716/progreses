<?php
session_start();
require_once 'includes/dbconnection.php';

// Verificar que se haya enviado un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        // Preparar la sentencia de eliminación
        $stmt = $pdo->prepare("DELETE FROM ipauma_solicitudes WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Redireccionar al dashboard con un mensaje de éxito (opcional)
        header("Location: ipauma_dashboard.php?msg=eliminado");
        exit;

    } catch (PDOException $e) {
        die("Error al intentar eliminar el reporte: " . $e->getMessage());
    }
} else {
    // Si no hay ID, regresar al dashboard
    header("Location: ipauma_dashboard.php");
    exit;
}
?>