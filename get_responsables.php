<?php
require_once 'includes/dbconnection.php';

if (isset($_POST['id_area'])) {
    $id_area = intval($_POST['id_area']);
    $stmt = $pdo->prepare("SELECT id_responsable, nombre_responsable FROM responsables WHERE id_area = ? ORDER BY nombre_responsable ASC");
    $stmt->execute([$id_area]);
    $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($responsables);
}
?>