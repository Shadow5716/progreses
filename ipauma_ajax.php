<?php
// ipauma_ajax.php
include_once('sesion.php');
require_once 'includes/dbconnection.php';
header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';

try {
    if ($accion == 'get_objetivos') {
        $departamento_id = intval($_POST['departamento_id']);
        $stmt = $pdo->prepare("SELECT id, descripcion FROM ipauma_objetivos WHERE departamento_id = ?");
        $stmt->execute([$departamento_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } 
    elseif ($accion == 'get_actividades') {
        $objetivo_id = intval($_POST['objetivo_id']);
        $stmt = $pdo->prepare("SELECT id, descripcion FROM ipauma_actividades WHERE objetivo_id = ?");
        $stmt->execute([$objetivo_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}