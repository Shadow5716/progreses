<?php
require_once 'includes/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. CAPTURA DE DATOS AMPLIADA (Evita el error de datos nulos)
    $id_plan        = $_POST['id_plan'] ?? null;
    $nro_expediente = $_POST['nro'] ?? $_POST['nro_expediente'] ?? null;
    $monto          = $_POST['monto_pagado_usd'] ?? $_POST['monto'] ?? 0;
    $nro_cuota      = $_POST['nro_cuota'] ?? 0;
    $obs            = $_POST['observaciones'] ?? $_POST['referencia'] ?? 'Pago de cuota';

    // Validación crítica
    if (!$id_plan || !$nro_expediente) {
        die("Error: Faltan datos críticos. ID Plan: " . htmlspecialchars($id_plan) . ", Expediente: " . htmlspecialchars($nro_expediente));
    }

    try {
        $pdo->beginTransaction();

        // 2. Registrar el recibo
        $sqlRecibo = "INSERT INTO recibos_pago (nro_expediente, id_plan, nro_cuota_pagada, monto_pagado_usd, fecha_pago, observaciones) 
                      VALUES (?, ?, ?, ?, NOW(), ?)";
        $pdo->prepare($sqlRecibo)->execute([$nro_expediente, $id_plan, $nro_cuota, $monto, $obs]);

        // 3. Marcar cuota como PAGADA
        $pdo->prepare("UPDATE plan_amortizacion SET estatus_cuota = 'PAGADO' WHERE id_plan = ?")
            ->execute([$id_plan]);

        // 4. VERIFICAR Y ACTUALIZAR ESTATUS DEL EXPEDIENTE (En tiempo real)
        $stmtPendientes = $pdo->prepare("SELECT COUNT(*) FROM plan_amortizacion WHERE nro_expediente = ? AND estatus_cuota != 'PAGADO'");
        $stmtPendientes->execute([$nro_expediente]);
        $cuotasPendientes = $stmtPendientes->fetchColumn();
        
        if ($cuotasPendientes == 0) {
            // Si ya no hay cuotas pendientes, el crédito está PAGADO al 100%
            $pdo->prepare("UPDATE expedientes SET estatus = 'PAGADO' WHERE nro_expediente = ?")
                ->execute([$nro_expediente]);
        } else {
            // Revisar si el pago acaba de sacar al cliente de la MORA
            $stmtMora = $pdo->prepare("SELECT COUNT(*) FROM plan_amortizacion WHERE nro_expediente = ? AND estatus_cuota = 'MORA'");
            $stmtMora->execute([$nro_expediente]);
            $cuotasMora = $stmtMora->fetchColumn();
            
            if ($cuotasMora == 0) {
                // Si ya no quedan cuotas en mora, pero sí pendientes, regresa a ACTIVO
                $pdo->prepare("UPDATE expedientes SET estatus = 'ACTIVO' WHERE nro_expediente = ? AND estatus = 'MORA'")
                    ->execute([$nro_expediente]);
            }
        }

        $pdo->commit();

        // 5. REDIRECCIÓN (Soluciona la pantalla en blanco)
        // Redirige de regreso a la vista anterior. 
        // Puedes cambiar 'index.php' por 'detalle_expediente.php?nro='.$nro_expediente si prefieres.
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header("Location: " . $referer);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al procesar el pago: " . $e->getMessage());
    }
}
?>