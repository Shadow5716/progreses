<?php
require_once 'includes/dbconnection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT id, ruta_archivo, nombre_archivo FROM ipauma_imagenes WHERE solicitud_id = ?");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}
?>