<?php
include('includes/dbconnection.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos los datos del formulario
    $fecha = $_POST['fecha'];
    $id_ente = $_POST['id_ente'];
    $id_area = $_POST['id_area'];
    $id_responsable = $_POST['id_responsable'];
    $tipo_actividad = $_POST['tipo'];
    $descripcion = $_POST['descripcion'];
    $estatus = 'Pendiente'; // Estatus inicial por defecto

    // --- GENERACIÓN AUTOMÁTICA DEL OFICIO ---
    $year = date("Y", strtotime($fecha));
    // Contamos cuantas solicitudes van en el año para el correlativo
    $query_count = "SELECT COUNT(*) as total FROM solicitudes WHERE YEAR(fecha) = ?";
    $stmt_count = $conn->prepare($query_count);
    $stmt_count->bind_param("s", $year);
    $stmt_count->execute();
    $res_count = $stmt_count->get_result()->fetch_assoc();
    
    $nuevo_correlativo = str_pad($res_count['total'] + 1, 3, "0", STR_PAD_LEFT);
    $nro_oficio = "OFC-" . $year . "-" . $nuevo_correlativo;

    // --- INSERCIÓN EN LA BASE DE DATOS ---
    $sql = "INSERT INTO solicitudes (fecha, id_ente, id_area, id_responsable, tipo_actividad, descripcion, estatus, nro_oficio) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiissss", $fecha, $id_ente, $id_area, $id_responsable, $tipo_actividad, $descripcion, $estatus, $nro_oficio);

    if ($stmt->execute()) {
        header("Location: index.php?msg=success");
    } else {
        echo "Error al insertar: " . $conn->error;
    }
}
?>