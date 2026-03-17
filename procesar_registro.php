<?php
require_once 'includes/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // 1. DETERMINAR EL ID DEL CLIENTE
        // Si viene de 'nuevo_expediente.php' (Directorio), ya trae un id_cliente.
        // Si viene de 'nuevo_cliente.php', el id_cliente estará vacío.
        $idCliente = $_POST['id_cliente'] ?? null;

        if (empty($idCliente)) {
            // LÓGICA PARA CLIENTE NUEVO: Insertamos en la tabla 'clientes'
            $sqlCliente = "INSERT INTO clientes (cedula_rif, nombre_completo, telefono, correo, direccion, actividad_economica) 
                           VALUES (:cedula, :nombre, :tlf, :correo, :dir, :act)";
            
            $stmtC = $pdo->prepare($sqlCliente);
            $stmtC->execute([
                ':cedula' => $_POST['cedula_rif'],
                ':nombre' => $_POST['nombre_completo'],
                ':tlf'    => $_POST['telefono'],
                ':correo' => $_POST['correo'],
                ':dir'    => $_POST['direccion'],
                ':act'    => $_POST['actividad_economica']
            ]);

            // Obtenemos el ID generado para este nuevo cliente
            $idCliente = $pdo->lastInsertId();
        } else {
            // LÓGICA PARA CLIENTE EXISTENTE: Actualizamos sus datos por si cambiaron
            $sqlUpd = "UPDATE clientes SET 
                        telefono = :tlf, 
                        correo = :correo, 
                        direccion = :dir, 
                        actividad_economica = :act 
                       WHERE id_cliente = :id";
            
            $stmtU = $pdo->prepare($sqlUpd);
            $stmtU->execute([
                ':tlf'    => $_POST['telefono'],
                ':correo' => $_POST['correo'],
                ':dir'    => $_POST['direccion'],
                ':act'    => $_POST['actividad_economica'],
                ':id'     => $idCliente
            ]);
        }

        // 2. INSERTAR EL EXPEDIENTE (Común para ambos casos)
        // Se vincula al $idCliente (sea nuevo o recuperado del directorio)
        $sqlExp = "INSERT INTO expedientes (nro_expediente, id_cliente, cod_producto, monto_aprobado, fecha_creacion, estatus) 
                   VALUES (:nro, :id_c, :prod, :monto, :fecha, 'ACTIVO')";
        
        $stmtE = $pdo->prepare($sqlExp);
        $stmtE->execute([
            ':nro'   => $_POST['nro_expediente'],
            ':id_c'  => $idCliente,
            ':prod'  => $_POST['cod_producto'],
            ':monto' => $_POST['monto_aprobado'],
            ':fecha' => $_POST['fecha_creacion'] // Incluimos la fecha del formulario
        ]);

        $pdo->commit();

        // 3. REDIRECCIÓN
        // Te envío a generar_cuotas.php para que el flujo sea automático
        header("Location: generar_cuotas.php?nro_expediente=" . $_POST['nro_expediente']);
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Mensaje detallado en caso de error de base de datos
        die("Error crítico al procesar el registro: " . $e->getMessage());
    }
}