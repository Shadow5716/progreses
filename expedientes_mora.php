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

// Filtramos por estatus 'MORA'
$sql = "SELECT e.*, c.nombre_completo, c.cedula_rif 
        FROM expedientes e 
        JOIN clientes c ON e.id_cliente = c.id_cliente 
        WHERE e.estatus = 'MORA' 
        ORDER BY e.nro_expediente DESC"; 
        
$expedientes = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Expedientes en Mora</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-danger"><i class="bi bi-exclamation-octagon-fill me-2"></i>Expedientes en Mora</h3>
            <span class="badge bg-danger fs-6"><?php echo count($expedientes); ?> Casos detectados</span>
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
                        <?php if (count($expedientes) > 0): ?>
                            <?php foreach($expedientes as $exp): ?>
                            <tr class="table-light">
                                <td class="fw-bold text-danger"><?php echo $exp['nro_expediente']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo $exp['nombre_completo']; ?></div>
                                    <small class="text-muted"><?php echo $exp['cedula_rif']; ?></small>
                                </td>
                                <td>$<?php echo number_format($exp['monto_aprobado'], 2); ?></td>
                                <td><?php echo isset($exp['fecha_creacion']) ? date('d/m/Y', strtotime($exp['fecha_creacion'])) : 'S/F'; ?></td>
                                <td><span class="badge bg-danger">MORA</span></td>
                                <td class="text-center">
<a href="<?php echo ($adminActual['Rango'] == 'Operador') ? 'ver_expediente.php' : 'gestion_expediente.php'; ?>?nro=<?php echo $exp['nro_expediente']; ?>" 
   class="btn btn-sm btn-outline-primary">
    <i class="bi bi-eye"></i> Ver Ficha
</a>


                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay expedientes en mora. ¡Excelente gestión!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>