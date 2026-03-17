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

$nro_expediente = $_GET['nro'] ?? '';
if (empty($nro_expediente)) {
    header("Location: expedientes_finalizados.php");
    exit();
}

// 1. Consultar datos del expediente y cliente
$sqlExp = "SELECT e.*, c.nombre_completo, c.cedula_rif 
           FROM expedientes e 
           JOIN clientes c ON e.id_cliente = c.id_cliente 
           WHERE e.nro_expediente = ?";
$stmtExp = $pdo->prepare($sqlExp);
$stmtExp->execute([$nro_expediente]);
$datos = $stmtExp->fetch();

// 2. Consultar cuotas PAGADAS (Filtro solicitado)

$sqlPlan = "SELECT * FROM plan_amortizacion WHERE nro_expediente = ? ORDER BY id_plan ASC";

$stmtPlan = $pdo->prepare($sqlPlan);
$stmtPlan->execute([$nro_expediente]);
$cuotas = $stmtPlan->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Historial de Pagos</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="bi bi-journal-check me-2 text-success"></i>Detalle de Pagos Recibidos</h3>
            <a href="expedientes_finalizados.php" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver al Listado
            </a>
        </div>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body bg-white rounded">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Cliente:</small>
                        <span class="fw-bold fs-5"><?php echo $datos['nombre_completo']; ?></span>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted d-block">Nro. Expediente:</small>
                        <span class="badge bg-primary fs-6"><?php echo $datos['nro_expediente']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center"># Cuota</th>
                            <th>Fecha Vencimiento</th>
                            <th class="text-end">Monto Cuota</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cuotas as $cuota): ?>
                        <tr>
                            <td class="text-center fw-bold"><?php echo $cuota['id_plan']; // Usamos ID si numero_cuota no existe ?></td>
                            <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                            <td class="text-end fw-bold text-success">$<?php echo number_format($cuota['monto_cuota'], 2); ?></td>
                            <td class="text-center">
                                <?php if($cuota['estatus_cuota'] == 'PAGADO'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> PAGADA</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">PENDIENTE</span>
                                <?php endif; ?>
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