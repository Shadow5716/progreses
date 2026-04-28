<?php
session_start();
require_once 'includes/dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibe el tipo de exportación y los IDs (si los hay)
    $tipo = isset($_POST['tipo_exportacion']) ? $_POST['tipo_exportacion'] : 'todos';
    $ids = isset($_POST['ids_seleccionados']) ? $_POST['ids_seleccionados'] : '';

    $query = "SELECT * FROM imtcuma_vehiculos";
    $params = [];

    // Si se eligió exportar seleccionados y hay IDs válidos, filtramos la consulta
    if ($tipo === 'seleccionados' && !empty($ids)) {
        $ids_array = explode(',', $ids);
        $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
        $query .= " WHERE id IN ($placeholders)";
        $params = $ids_array;
    }
    
    $query .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configuramos las cabeceras para forzar la descarga del Excel
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=IMTCUMA_Reporte_' . date('d-m-Y') . '.csv');
    
    $output = fopen('php://output', 'w');
    // BOM para UTF-8 (esto permite que Excel reconozca tildes y letras como la Ñ)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Cabeceras de las columnas en el Excel (Actualizado con Puntos y Estado)
    fputcsv($output, [
        'ID', 'PLACA', 'ORGANIZACIÓN', 'MODALIDAD', 'DIRECTIVO', 'CARGO', 'TEL DIRECTIVO',
        'PROPIETARIO', 'CONDUCTOR', 'MARCA', 'MODELO', 'AÑO', 'PUNTOS EVALUACIÓN', 'ESTATUS EVALUACIÓN'
    ], ';');

    // Llenado de datos
    foreach ($data as $v) {
        fputcsv($output, [
            $v['id'] ?? '',
            $v['placa'] ?? '',
            $v['org_nombre'] ?? '',
            $v['org_modalidad'] ?? '',
            $v['dir_nombre'] ?? '',
            $v['dir_cargo'] ?? '',
            $v['dir_telefono'] ?? '',
            $v['propietario_nombre'] ?? '',
            $v['conductor_nombre'] ?? '',
            $v['marca'] ?? '',
            $v['modelo'] ?? '',
            $v['anio'] ?? '',
            $v['evaluacion_puntos'] ?? '0',
            $v['evaluacion_estado'] ?? 'Sin Evaluar'
        ], ';');
    }
    fclose($output);
    exit();
}
?>