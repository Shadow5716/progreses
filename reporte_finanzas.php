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

// 1. Consulta para el resumen total
$sqlTotal = "SELECT SUM(monto_pagado_usd) as gran_total FROM recibos_pago";
$totalGeneral = $pdo->query($sqlTotal)->fetch()['gran_total'] ?? 0;

// 2. Consulta de ingresos por mes (Agrupados)
$sqlMensual = "SELECT 
                DATE_FORMAT(fecha_pago, '%Y-%m') as mes_anio,
                SUM(monto_pagado_usd) as total_mes,
                COUNT(id_recibo) as cantidad_pagos
               FROM recibos_pago 
               GROUP BY mes_anio 
               ORDER BY mes_anio DESC";
$ingresosMensuales = $pdo->query($sqlMensual)->fetchAll();

// 3. Últimos 10 pagos registrados (Detalle)
$sqlRecientes = "SELECT r.*, c.nombre_completo 
                 FROM recibos_pago r
                 JOIN expedientes e ON r.nro_expediente = e.nro_expediente
                 JOIN clientes c ON e.id_cliente = c.id_cliente
                 ORDER BY r.fecha_pago DESC 
                 LIMIT 10";
$pagosRecientes = $pdo->query($sqlRecientes)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Reporte de Finanzas</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h3 class="mb-4"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Control de Ingresos (USD)</h3>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-dark text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-uppercase small">Recaudación Total Histórica</h6>
                        <h2 class="display-6 fw-bold">$<?php echo number_format($totalGeneral, 2); ?></h2>
                        <i class="bi bi-currency-dollar position-absolute top-50 end-0 translate-middle-y me-3 opacity-25 fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-uppercase small">Ingresos Mes Actual</h6>
                        <?php 
                        $mesActual = date('Y-m');
                        $totalMesActual = 0;
                        foreach($ingresosMensuales as $m) {
                            if($m['mes_anio'] == $mesActual) $totalMesActual = $m['total_mes'];
                        }
                        ?>
                        <h2 class="display-6 fw-bold">$<?php echo number_format($totalMesActual, 2); ?></h2>
                        <i class="bi bi-calendar-check position-absolute top-50 end-0 translate-middle-y me-3 opacity-25 fs-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Histórico Mensual</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ingresosMensuales as $m): ?>
                                <tr>
                                    <td><?php echo date('M Y', strtotime($m['mes_anio'] . "-01")); ?></td>
                                    <td class="text-end fw-bold">$<?php echo number_format($m['total_mes'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Detalle de Últimos 10 Cobros</div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Ref / Obs</th>
                                    <th class="text-end">Monto USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pagosRecientes as $p): ?>
                                <tr>
                                    <td><small><?php echo date('d/m/Y H:i', strtotime($p['fecha_pago'])); ?></small></td>
                                    <td class="fw-bold"><?php echo $p['nombre_completo']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $p['observaciones'] ?: 'Efectivo'; ?></span></td>
                                    <td class="text-end text-success fw-bold">$<?php echo number_format($p['monto_pagado_usd'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>