<?php
require_once 'includes/dbconnection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // CORRECCIÓN: Cambiar 'id' por 'id_solicitud'
        $sql = "DELETE FROM solicitudes WHERE id_solicitud = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: dashboard.php?msg=eliminado");
    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
}