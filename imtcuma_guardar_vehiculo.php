<?php
session_start();
require_once 'includes/dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "INSERT INTO imtcuma_vehiculos (
                    org_nombre, org_modalidad, org_periodo_desde, org_periodo_hasta,
                    dir_nombre, dir_cedula, dir_cargo, dir_direccion, dir_telefono,
                    placa, propietario_nombre, propietario_cedula, conductor_nombre, conductor_cedula, 
                    marca, modelo, anio, color, capacidad, serial_carroceria, serial_motor, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['org_nombre'], 
            $_POST['org_modalidad'], 
            $_POST['org_periodo_desde'], 
            $_POST['org_periodo_hasta'],
            $_POST['dir_nombre'], 
            $_POST['dir_cedula'], 
            $_POST['dir_cargo'], 
            $_POST['dir_direccion'], 
            $_POST['dir_telefono'],
            $_POST['placa'], 
            $_POST['propietario_nombre'], 
            $_POST['propietario_cedula'], 
            $_POST['conductor_nombre'], 
            $_POST['conductor_cedula'], 
            $_POST['marca'], 
            $_POST['modelo'], 
            $_POST['anio'], 
            $_POST['color'], 
            $_POST['capacidad'], 
            $_POST['serial_carroceria'], 
            $_POST['serial_motor']
        ]);

        header("Location: imtcuma_dashboard.php?msg=guardado");
        exit();
    } catch (PDOException $e) {
        die("Error crítico al intentar guardar el registro: " . $e->getMessage());
    }
} else {
    header("Location: imtcuma_nueva_solicitud.php");
    exit();
}
?>