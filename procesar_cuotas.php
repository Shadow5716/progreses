<?php
require_once 'includes/dbconnection.php';

$cuotas = (int)$_POST['cantidad_cuotas'];

if ($cuotas < 4 || $cuotas > 12) {
    die("Error: El número de cuotas debe estar entre 4 y 12.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nro = $_POST['nro_expediente'];
    $cuotas = (int)$_POST['cantidad_cuotas'];
    $fecha_vencimiento = new DateTime($_POST['fecha_inicio']);

    // 1. Validamos que el expediente exista y traemos su información financiera
    $stmt = $pdo->prepare("SELECT e.monto_aprobado, p.tasa_interes 
                           FROM expedientes e 
                           INNER JOIN catalogo_productos p ON e.cod_producto = p.cod_producto 
                           WHERE e.nro_expediente = ?");
    $stmt->execute([$nro]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificación de seguridad
    if (!$datos) {
        die("Error: No se encontró el producto asociado al expediente $nro. Verifique el catálogo.");
    }

    // 2. Cálculo de Amortización (Interés Simple)
    $monto_aprobado = (float)$datos['monto_aprobado'];
    $tasa_anual = (float)$datos['tasa_interes'];
    
    $capital_por_cuota = $monto_aprobado / $cuotas;
    // Interés mensual = (Monto * %tasa) / 100 / meses
    $interes_total = ($monto_aprobado * ($tasa_anual / 100));
    $interes_por_cuota = $interes_total / $cuotas;
    $monto_total_cuota = $capital_por_cuota + $interes_por_cuota;

    try {
        $pdo->beginTransaction();

        // 3. Insertar las cuotas una por una
        for ($i = 1; $i <= $cuotas; $i++) {
$sql = "INSERT INTO plan_amortizacion 
        (nro_expediente, nro_cuota, monto_cuota, monto_capital, monto_interes, fecha_vencimiento, estatus_cuota, fecha_registro) 
        VALUES (?, ?, ?, ?, ?, ?, 'PENDIENTE', NOW())";

$stmtInsert = $pdo->prepare($sql);
$stmtInsert->execute([
    $nro,
    $i,
    $monto_total_cuota,
    $capital_por_cuota,
    $interes_por_cuota,
    $fecha_vencimiento->format('Y-m-d')
]);

            

            // Sumar un mes para la siguiente fecha de cobro
            $fecha_vencimiento->modify('+1 month');
        }

        $pdo->commit();
        header("Location: index.php?msg=cuotas_listas");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error en la base de datos: " . $e->getMessage());
    }
}