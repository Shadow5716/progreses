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

// Cambiamos 'fecha_creacion' por 'fecha_registro' (o la columna que tengas)
// Si no tienes ninguna columna de fecha, quita el ORDER BY momentáneamente
$sql = "SELECT e.*, c.nombre_completo, c.cedula_rif 
        FROM expedientes e 
        JOIN clientes c ON e.id_cliente = c.id_cliente 
        ORDER BY e.nro_expediente DESC"; // Ordenamos por número de expediente mientras tanto
        
$expedientes = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Todos los Expedientes</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="bi bi-folder2-open me-2"></i>Todos los Expedientes</h3>
            <a href="nuevo_cliente.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Nuevo</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Expediente</th>
                            <th>Cliente</th>
                            <th>Monto Aprobado</th>
                            <th>Fecha</th>
                            <th>Estatus</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($expedientes as $exp): 
                            $badge = ($exp['estatus'] == 'ACTIVO') ? 'bg-primary' : (($exp['estatus'] == 'MORA') ? 'bg-danger' : 'bg-success');
                        ?>
                        <tr>
                            <td class="fw-bold"><?php echo $exp['nro_expediente']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo $exp['nombre_completo']; ?></div>
                                <small class="text-muted"><?php echo $exp['cedula_rif']; ?></small>
                            </td>
                            <td>$<?php echo number_format($exp['monto_aprobado'], 2); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($exp['fecha_creacion'])); ?></td>
                            <td><span class="badge <?php echo $badge; ?>"><?php echo $exp['estatus']; ?></span></td>
                            <td class="text-center">

<?php 
    // Determinamos el destino según el rango
    // Si es Operador va a ver_expediente.php, de lo contrario a gestion_expediente.php
    $archivo_destino = ($_SESSION['Rango'] == 'Operador') ? 'ver_expediente.php' : 'gestion_expediente.php';
?>

<a href="<?php echo ($adminActual['Rango'] == 'Operador') ? 'ver_expediente.php' : 'gestion_expediente.php'; ?>?nro=<?php echo $exp['nro_expediente']; ?>" 
   class="btn btn-sm btn-outline-primary">
    <i class="bi bi-eye"></i> Ver Ficha
</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>