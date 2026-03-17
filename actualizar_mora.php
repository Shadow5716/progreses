<?php
require_once 'includes/dbconnection.php';

// Configurar zona horaria para Maracaibo/Venezuela
date_default_timezone_set('America/Caracas');
$hoy = date('Y-m-d');

try {
    $pdo->beginTransaction();

    // 1. MARCAR MORA Y ASIGNAR MONTO AUTOMÁTICO
    // Si la cuota venció y estaba PENDIENTE, se cambia a MORA
    // y se asigna el monto_mora igual al monto_cuota automáticamente.
    $sqlMora = "UPDATE plan_amortizacion 
                SET estatus_cuota = 'MORA', 
                    monto_mora = monto_cuota 
                WHERE fecha_vencimiento < ? 
                AND estatus_cuota = 'PENDIENTE'";
    $pdo->prepare($sqlMora)->execute([$hoy]);

    // 2. ACTUALIZAR EXPEDIENTES A 'MORA'
    // Si tienen al menos una cuota en estatus MORA, el expediente pasa a MORA
    $sqlExpMora = "UPDATE expedientes e
                   SET e.estatus = 'MORA'
                   WHERE EXISTS (
                       SELECT 1 FROM plan_amortizacion p 
                       WHERE p.nro_expediente = e.nro_expediente 
                       AND p.estatus_cuota = 'MORA'
                   ) AND e.estatus != 'MORA'";
    $pdo->query($sqlExpMora);

    // 3. REGRESAR A 'ACTIVO' (SI YA NO TIENE CUOTAS EN MORA)
    // Si el expediente estaba en MORA pero ya pagó sus cuotas vencidas, 
    // pero aún tiene cuotas PENDIENTES a futuro.
    $sqlExpActivo = "UPDATE expedientes e
                     SET e.estatus = 'ACTIVO'
                     WHERE e.estatus = 'MORA'
                     AND NOT EXISTS (
                         SELECT 1 FROM plan_amortizacion p 
                         WHERE p.nro_expediente = e.nro_expediente 
                         AND p.estatus_cuota = 'MORA'
                     )
                     AND EXISTS (
                         SELECT 1 FROM plan_amortizacion p 
                         WHERE p.nro_expediente = e.nro_expediente 
                         AND p.estatus_cuota = 'PENDIENTE'
                     )";
    $pdo->query($sqlExpActivo);

    // 4. FINALIZAR A 'PAGADO'
    // Si no quedan cuotas pendientes ni en mora.
    $sqlExpPagado = "UPDATE expedientes e
                     SET e.estatus = 'PAGADO'
                     WHERE e.estatus IN ('ACTIVO', 'MORA')
                     AND EXISTS (SELECT 1 FROM plan_amortizacion p WHERE p.nro_expediente = e.nro_expediente)
                     AND NOT EXISTS (
                         SELECT 1 FROM plan_amortizacion p 
                         WHERE p.nro_expediente = e.nro_expediente 
                         AND p.estatus_cuota IN ('PENDIENTE', 'MORA')
                     )";
    $pdo->query($sqlExpPagado);

    $pdo->commit();
    
    // Opcional: Puedes dejar un log o eco para confirmar que se ejecutó
    // echo "Actualización de mora completada con éxito.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // En scripts automáticos es mejor loguear el error que solo morir
    error_log("Error en actualizar_mora.php: " . $e->getMessage());
    die("Error crítico al actualizar moras.");
}
?>