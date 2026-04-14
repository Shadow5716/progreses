<?php
// ipauma_ajax.php
header('Content-Type: application/json');

// IMPORTANTE: Configura aquí tu conexión
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tu_base_de_datos';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión']));
}

$accion = $_POST['accion'] ?? '';

if ($accion == 'get_objetivos') {
    $departamento_id = intval($_POST['departamento_id']);
    $query = "SELECT id, descripcion FROM ipauma_objetivos WHERE departamento_id = $departamento_id";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} 
elseif ($accion == 'get_actividades') {
    $objetivo_id = intval($_POST['objetivo_id']);
    $query = "SELECT id, descripcion FROM ipauma_actividades WHERE objetivo_id = $objetivo_id";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}
$conn->close();
?>