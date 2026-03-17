<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
include_once 'actualizar_mora.php';
include_once ('sesion.php');

if ($_SESSION['autentificado'] == false){
    header('location:logout.php');
    exit;
}

// Consultamos solo expedientes que NO tengan cuotas aún
$sql = "SELECT e.nro_expediente, c.nombre_completo 
        FROM expedientes e 
        JOIN clientes c ON e.id_cliente = c.id_cliente 
        LEFT JOIN plan_amortizacion p ON e.nro_expediente = p.nro_expediente 
        WHERE p.id_plan IS NULL";
$expedientes_sin_cuotas = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Seleccionar Expediente</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">Seleccione un expediente para asignar cuotas</div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Expediente</th>
                            <th>Cliente</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($expedientes_sin_cuotas as $fila): ?>
                        <tr>
                            <td><?php echo $fila['nro_expediente']; ?></td>
                            <td><?php echo $fila['nombre_completo']; ?></td>
                            <td>
                                <a href="generar_cuotas.php?nro_expediente=<?php echo $fila['nro_expediente']; ?>" class="btn btn-sm btn-success">
                                    Configurar Cuotas
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($expedientes_sin_cuotas)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Todos los expedientes tienen cuotas asignadas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>