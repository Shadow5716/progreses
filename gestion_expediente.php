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

if ($_SESSION['Rango'] == 'Operador') {
    header('location:ver_expediente.php?nro=' . $_GET['nro']);
    exit;
}


// Capturamos el número de expediente
$nro = $_GET['nro'] ?? $_GET['nro_expediente'] ?? '';

if (empty($nro)) {
    die("<div class='alert alert-danger'>Error: No se recibió el número de expediente.</div>");
}

try {
    // 1. Consulta de datos del expediente y cliente
    $stmt = $pdo->prepare("SELECT e.*, c.nombre_completo, c.cedula_rif, c.direccion, c.actividad_economica, c.telefono,
                                   p.nombre_producto, p.tasa_interes
                            FROM expedientes e 
                            INNER JOIN clientes c ON e.id_cliente = c.id_cliente 
                            INNER JOIN catalogo_productos p ON e.cod_producto = p.cod_producto
                            WHERE e.nro_expediente = ?");
    $stmt->execute([$nro]);
    $exp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exp) {
        die("<div class='alert alert-warning'>No se encontraron datos para el expediente: $nro.</div>");
    }

    $stmtCuotas = $pdo->prepare("SELECT * FROM plan_amortizacion WHERE nro_expediente = ? ORDER BY nro_cuota ASC");
    $stmtCuotas->execute([$nro]);
    $cuotas = $stmtCuotas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha: <?php echo $nro; ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'pago_ok'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <strong>¡Cobro Registrado!</strong> El pago se ha procesado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">Datos del Cliente</div>
                    <div class="card-body">
                        <h5><?php echo $exp['nombre_completo']; ?></h5>
                        <p class="mb-1 text-muted">ID: <?php echo $exp['cedula_rif']; ?></p>
                        <hr>
                        <p><strong>Actividad:</strong> <?php echo $exp['actividad_economica']; ?></p>
                        <p><strong>Dirección:</strong> <?php echo $exp['direccion']; ?></p>
                        <p><strong>Teléfono:</strong> <?php echo $exp['telefono'] ?? 'N/A'; ?></p>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">Resumen del Crédito</div>
                    <div class="card-body">
                        <p><strong>Producto:</strong> <?php echo $exp['nombre_producto']; ?></p>
                        <p><strong>Monto Aprobado:</strong> $<?php echo number_format($exp['monto_aprobado'], 2); ?></p>
                        <p><strong>Tasa:</strong> <?php echo $exp['tasa_interes']; ?>%</p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Cronograma de Pagos</h5>
                        <span class="badge bg-light text-dark">Total Cuotas: <?php echo count($cuotas); ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Vencimiento</th>
                                    <th>Capital</th>
                                    <th>Interés</th>
                                    <th>Cuota Total</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $hoy = date('Y-m-d');
                                foreach($cuotas as $c): 
                                    $is_vencido = ($c['fecha_vencimiento'] < $hoy && $c['estatus_cuota'] != 'PAGADO');
                                    $clase_fila = $is_vencido ? 'table-danger' : '';
                                ?>
<tr>
    <td class="fw-bold">Cuota #<?php echo $c['nro_cuota']; ?></td>
    <td><?php echo date('d/m/Y', strtotime($c['fecha_vencimiento'])); ?></td>
    <td>$<?php echo number_format($c['monto_capital'], 2); ?></td>
    <td>$<?php echo number_format($c['monto_interes'], 2); ?></td>
    
<td class="fw-bold">
    $<?php echo number_format($c['monto_cuota'], 2); ?>
</td>
    
    <td>
        <span class="badge <?php echo ($c['estatus_cuota'] == 'PAGADO') ? 'bg-success' : 'bg-warning text-dark'; ?>">
            <?php echo $c['estatus_cuota'] ?: 'PENDIENTE'; ?>
        </span>
    </td>
    <td class="text-center">
        <?php if($c['estatus_cuota'] != 'PAGADO'): ?>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCobro" 
                    onclick="prepararCobro('<?php echo $c['id_plan']; ?>', '<?php echo $c['nro_cuota']; ?>', '<?php echo $c['monto_cuota']; ?>')">
                <i class="bi bi-cash-stack"></i> Cobrar
            </button>
        <?php else: ?>
            <i class="bi bi-check-circle-fill text-success"></i>
        <?php endif; ?>
    </td>





    
</tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="modal fade" id="modalCobro" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="cobrar_cuota.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Registrar Cobro - Cuota #<span id="txtCuota"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_plan" id="input_id_plan">
        <input type="hidden" name="nro_expediente" value="<?php echo $nro; ?>">
        <input type="hidden" name="nro_cuota" id="input_nro_cuota">
        
        <div class="mb-3">
          <label class="form-label">Monto a Cobrar (USD)</label>
          <input type="number" name="monto_pagado_usd" id="input_monto" class="form-control" step="0.01" readonly>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Referencia Bancaria / Observación</label>
          <input type="text" name="observaciones" class="form-control" placeholder="Ej: Pago móvil 4589" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Confirmar Pago</button>
      </div>
    </form>
  </div>
</div>

<script>
function prepararCobro(id, nro, monto) {
    document.getElementById('input_id_plan').value = id;
    document.getElementById('txtCuota').innerText = nro;
    document.getElementById('input_nro_cuota').value = nro;
    document.getElementById('input_monto').value = monto; // Enviamos solo el número
}
</script>

</body>
</html>

