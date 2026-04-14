<?php
session_start();
require_once 'includes/dbconnection.php';

// Cabeceras para forzar la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Historial_IPAUMA_" . date('Y-m-d_H-i') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$where = "1=1";
$params = [];

// Verificar si se exportan solo seleccionados o todos
if (isset($_POST['ids_seleccionados']) && !empty($_POST['ids_seleccionados'])) {
    // Escapar los IDs para seguridad
    $ids = explode(',', $_POST['ids_seleccionados']);
    $inQuery = implode(',', array_fill(0, count($ids), '?'));
    $where = "s.id IN ($inQuery)";
    $params = $ids;
}

try {
    $query = "SELECT s.id, s.fecha, d.nombre as depto, o.descripcion as obj, a.descripcion as act, s.oficio, s.parroquia, s.estado, s.descripcion 
              FROM ipauma_solicitudes s
              LEFT JOIN ipauma_departamentos d ON s.departamento_id = d.id
              LEFT JOIN ipauma_objetivos o ON s.objetivo_id = o.id
              LEFT JOIN ipauma_actividades a ON s.actividad_id = a.id
              WHERE $where ORDER BY s.fecha DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Imprimir Tabla HTML para que Excel la interprete
    echo '<table border="1">';
    echo '<tr style="background-color:#164377; color:white;">';
    echo '<th>N° Reporte</th>';
    echo '<th>Fecha</th>';
    echo '<th>Departamento</th>';
    echo '<th>Objetivo</th>';
    echo '<th>Actividad</th>';
    echo '<th>Oficio</th>';
    echo '<th>Parroquia</th>';
    echo '<th>Estado</th>';
    echo '<th>Descripción</th>';
    echo '</tr>';

    foreach ($datos as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . date('d/m/Y h:i A', strtotime($row['fecha'])) . '</td>';
        echo '<td>' . htmlspecialchars($row['depto']) . '</td>';
        echo '<td>' . htmlspecialchars($row['obj']) . '</td>';
        echo '<td>' . htmlspecialchars($row['act']) . '</td>';
        echo '<td>' . htmlspecialchars($row['oficio']) . '</td>';
        echo '<td>' . htmlspecialchars($row['parroquia']) . '</td>';
        echo '<td>' . htmlspecialchars($row['estado']) . '</td>';
        echo '<td>' . htmlspecialchars($row['descripcion']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

} catch (PDOException $e) {
    echo "Error al exportar los datos.";
}
exit;
?>