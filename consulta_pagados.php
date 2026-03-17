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

// Esta consulta busca expedientes donde TODAS sus cuotas estén 'PAGADA'
$sql = "SELECT e.nro_expediente, c.nombre_completo, c.cedula_rif, p.nombre_producto,
               SUM(pa.total_cuota) as monto_total_recaudado
        FROM expedientes e
        JOIN clientes c ON e.id_cliente = c.id_cliente
        JOIN catalogo_productos p ON e.cod_producto = p.cod_producto
        JOIN plan_amortizacion pa ON e.nro_expediente = pa.nro_expediente
        GROUP BY e.nro_expediente
        HAVING COUNT(CASE WHEN pa.estatus_cuota != 'PAGADA' THEN 1 END) = 0";

$expedientes_finalizados = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Expedientes Liquidados</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <h3 class="mb-4 text-success"><i class="bi bi-check-all"></i> Expedientes Finalizados</h3>
        <div class="card shadow-sm border-0">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Expediente</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th class="text-end">Total Pagado</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($expedientes_finalizados as $f): ?>
                    <tr>
                        <td><strong><?php echo $f['nro_expediente']; ?></strong></td>
                        <td><?php echo $f['nombre_completo']; ?> <br><small class="text-muted"><?php echo $f['cedula_rif']; ?></small></td>
                        <td><?php echo $f['nombre_producto']; ?></td>
                        <td class="text-end fw-bold text-success">$<?php echo number_format($f['monto_total_recaudado'], 2); ?></td>
                        <td class="text-center"><span class="badge bg-success">LIQUIDADO</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>