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

// Consulta solo expedientes con estatus PAGADO
$sql = "SELECT e.*, c.nombre_completo, c.cedula_rif, p.nombre_producto 
        FROM expedientes e
        JOIN clientes c ON e.id_cliente = c.id_cliente
        JOIN catalogo_productos p ON e.cod_producto = p.cod_producto
        WHERE e.estatus = 'PAGADO'
        ORDER BY e.fecha_creacion DESC";

$stmt = $pdo->query($sql);
$finalizados = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Créditos Liquidados</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-file-earmark-check text-success me-2"></i>Expedientes Pagados</h3>
            <span class="badge bg-success"><?php echo count($finalizados); ?> Finalizados</span>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Expediente</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th class="text-end">Monto Total</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($finalizados) > 0): ?>
                            <?php foreach($finalizados as $f): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $f['nro_expediente']; ?></td>
                                <td><?php echo $f['nombre_completo']; ?></td>
                                <td><?php echo $f['nombre_producto']; ?></td>
                                <td class="text-end fw-bold">$<?php echo number_format($f['monto_aprobado'], 2); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-success px-3">PAGADA</span>
                                </td>
                                <td class="text-center">
                                    <a href="amortizacion.php?nro=<?php echo $f['nro_expediente']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Ver Historial
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay expedientes pagados actualmente.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>