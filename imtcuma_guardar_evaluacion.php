<?php
session_start();
require_once 'includes/dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $vehiculo_id = $_POST['vehiculo_id'];
        $evaluacion_puntos = $_POST['evaluacion_puntos'];
        $evaluacion_estado = $_POST['evaluacion_estado'];

        $sql = "UPDATE imtcuma_vehiculos SET 
                    evaluacion_puntos = ?, 
                    evaluacion_estado = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacion_puntos, $evaluacion_estado, $vehiculo_id]);

        header("Location: imtcuma_dashboard.php?msg=evaluado");
        exit();
    } catch (PDOException $e) {
        die("Error al procesar la evaluación física: " . $e->getMessage());
    }
}
?>