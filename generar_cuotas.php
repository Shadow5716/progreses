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

// 1. Obtener número de expediente (Desde URL o desde Formulario)
$nro_expediente = $_GET['nro_expediente'] ?? $_POST['nro_expediente'] ?? '';

if (empty($nro_expediente)) {
    header("Location: seleccionar_expediente_cuota.php");
    exit();
}

// 2. Consultar datos para el diseño y cálculos
try {
    $stmt = $pdo->prepare("SELECT e.*, c.nombre_completo, p.tasa_interes, p.plazo_meses 
                           FROM expedientes e 
                           JOIN clientes c ON e.id_cliente = c.id_cliente 
                           JOIN catalogo_productos p ON e.cod_producto = p.cod_producto 
                           WHERE e.nro_expediente = ?");
    $stmt->execute([$nro_expediente]);
    $exp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exp) die("Expediente no encontrado.");
} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}

// 3. PROCESAMIENTO LOGICO (Cuando se presiona el botón)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cantidad_cuotas'])) {
    $num_cuotas = intval($_POST['cantidad_cuotas']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $monto_aprobado = floatval($exp['monto_aprobado']);
    $tasa_interes_decimal = floatval($exp['tasa_interes']) / 100;

    // Cálculos base (Amortización simple)
    $capital_cuota = $monto_aprobado / $num_cuotas;
    $interes_cuota = ($monto_aprobado * $tasa_interes_decimal) / $num_cuotas;
    $total_cuota = $capital_cuota + $interes_cuota;

    try {
        $pdo->beginTransaction();

        // Limpiar cuotas previas para evitar duplicados
        $pdo->prepare("DELETE FROM plan_amortizacion WHERE nro_expediente = ?")->execute([$nro_expediente]);

        $fecha = new DateTime($fecha_inicio);

        // CORRECCIÓN: Usamos 'nro_cuota' en lugar de 'numero_cuota' para evitar el Error 1054
        // CORRECCIÓN: Usamos 'monto_capital', 'monto_interes' y 'monto_cuota' según tu esquema
        $sql = "INSERT INTO plan_amortizacion 
                (nro_expediente, nro_cuota, monto_capital, monto_interes, monto_cuota, fecha_vencimiento, estatus_cuota) 
                VALUES (?, ?, ?, ?, ?, ?, 'PENDIENTE')";
        
        $stmtInsert = $pdo->prepare($sql);

        for ($i = 1; $i <= $num_cuotas; $i++) {
            $stmtInsert->execute([
                $nro_expediente, 
                $i, 
                $capital_cuota, 
                $interes_cuota, 
                $total_cuota, 
                $fecha->format('Y-m-d')
            ]);
            $fecha->modify('+1 month'); // Incrementa un mes para la siguiente cuota
        }

        // ACTIVAR EXPEDIENTE: Cambia de 'SOLICITUD' o 'PAGADO' a 'ACTIVO'
        $pdo->prepare("UPDATE expedientes SET estatus = 'ACTIVO' WHERE nro_expediente = ?")
            ->execute([$nro_expediente]);

        $pdo->commit();
        
        // Redirigir a la vista del expediente con éxito
        header("Location: ver_expediente.php?nro=$nro_expediente&msg=cuotas_generadas");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Error al asignar cuotas: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Generar Cuotas</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<?php include 'header.php'; ?>

<div class="container py-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Asignar Plan de Cuotas: <?php echo htmlspecialchars($exp['nombre_completo']); ?></h5>
            <span class="badge bg-light text-primary"><?php echo $nro_expediente; ?></span>
        </div>
        <div class="card-body">
            <form action="generar_cuotas.php" method="POST">
                <input type="hidden" name="nro_expediente" value="<?php echo $nro_expediente; ?>">
                
                <div class="row text-center mb-4">
                    <div class="col-md-3 border-end">
                        <label class="form-label fw-bold text-muted small">Monto Aprobado</label>
                        <p class="h5 text-primary">$<?php echo number_format($exp['monto_aprobado'], 2); ?></p>
                    </div>
                    <div class="col-md-3 border-end">
                        <label class="form-label fw-bold text-muted small">Tasa Anual</label>
                        <p class="h5 text-dark"><?php echo $exp['tasa_interes']; ?>%</p>
                    </div>
                    <div class="col-md-3 border-end">
                        <label class="form-label fw-bold text-muted small">Plazo Sugerido</label>
                        <p class="h5 text-dark"><?php echo $exp['plazo_meses']; ?> Meses</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Estatus Actual</label>
                        <p class="h5"><span class="badge bg-warning"><?php echo $exp['estatus']; ?></span></p>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Número de Cuotas</label>
                        <input type="number" 
                               name="cantidad_cuotas" 
                               class="form-control" 
                               value="<?php echo $exp['plazo_meses']; ?>" 
                               min="4" 
                               max="36" 
                               required>
                        <div class="form-text">Ajuste el plazo según la negociación.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fecha de Primera Cuota</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-calendar-check me-2"></i> Generar Tabla y Activar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



</body>
</html>