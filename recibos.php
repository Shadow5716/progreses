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

// Consulta corregida para unir por nro_expediente y nro_cuota
$sql = "SELECT 
            r.id_pago, 
            r.fecha_pago, 
            r.monto_pagado_usd, 
            r.observaciones,
            c.nombre_completo, 
            c.cedula_rif, 
            e.nro_expediente, 
            r.nro_cuota_pagada as nro_cuota
        FROM recibos_pago r
        JOIN expedientes e ON r.nro_expediente = e.nro_expediente
        JOIN clientes c ON e.id_cliente = c.id_cliente
        ORDER BY r.fecha_pago DESC, r.id_pago DESC";

try {
    $stmt = $pdo->query($sql);
    $recibos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Historial de Recibos</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3><i class="bi bi-receipt text-success me-2"></i>Control de Recibos Emitidos</h3>
                <p class="text-muted">Consulta de pagos procesados en el sistema cuotasgestion.</p>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Recibo #</th>
                            <th>Fecha de Pago</th>
                            <th>Cliente</th>
                            <th>Expediente</th>
                            <th class="text-center">Cuota</th>
                            <th class="text-end">Monto (USD)</th>
                            <th>Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recibos) > 0): ?>
                            <?php foreach ($recibos as $r): ?>
                            <tr>
                                <td class="fw-bold text-primary">
                                    <?php echo str_pad($r['id_pago'], 6, "0", STR_PAD_LEFT); ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($r['fecha_pago'])); ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($r['nombre_completo']); ?></div>
                                    <small class="text-muted"><?php echo $r['cedula_rif']; ?> </small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo $r['nro_expediente']; ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">#<?php echo $r['nro_cuota']; ?></span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    $<?php echo number_format($r['monto_pagado_usd'], 2); ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($r['observaciones'] ?? 'N/A'); ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                                    No se encontraron registros de pago en la base de datos.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>