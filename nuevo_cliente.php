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

// 1. GENERACIÓN AUTOMÁTICA DEL NÚMERO DE EXPEDIENTE
// Formato: 2026-0001 (Año actual + correlativo)
$anioActual = date("Y");
$queryUltimo = $pdo->query("SELECT nro_expediente FROM expedientes WHERE nro_expediente LIKE '$anioActual-%' ORDER BY nro_expediente DESC LIMIT 1");
$ultimoExp = $queryUltimo->fetchColumn();

if ($ultimoExp) {
    $partes = explode('-', $ultimoExp);
    $nuevoCorrelativo = str_pad($partes[1] + 1, 4, '0', STR_PAD_LEFT);
} else {
    $nuevoCorrelativo = "0001";
}
$nroSugerido = $anioActual . "-" . $nuevoCorrelativo;

// 2. CONSULTA DE PRODUCTOS (Para el select del formulario)
$productos = $pdo->query("SELECT cod_producto, nombre_producto FROM catalogo_productos")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Nuevo Registro</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php include 'header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-white p-3">
                    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Registro de Nuevo Cliente y Expediente</h5>
                </div>
                <div class="card-body p-4">
                    <form action="procesar_registro.php" method="POST">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Número de Expediente</label>
                                <input type="text" name="nro_expediente" class="form-control bg-light fw-bold text-primary" value="<?php echo $nroSugerido; ?>" readonly>
                                <small class="text-muted">Generado automáticamente</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Fecha de Registro</label>
                                <input type="date" name="fecha_creacion" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <hr>

<h6 class="text-success mb-3 border-bottom pb-2">Información del Cliente</h6>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label fw-bold">Cédula / RIF</label>
        <input type="text" name="cedula_rif" class="form-control" placeholder="V-00000000" required>
    </div>
    <div class="col-md-8">
        <label class="form-label fw-bold">Nombre Completo</label>
        <input type="text" name="nombre_completo" class="form-control" placeholder="Ej: Juan Pérez" required>
    </div>
    
    <div class="col-md-6">
        <label class="form-label fw-bold">Teléfono</label>
        <input type="text" name="telefono" class="form-control" placeholder="0412-0000000">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Correo Electrónico</label>
        <input type="email" name="correo" class="form-control" placeholder="usuario@correo.com">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold text-primary">Actividad Económica</label>
        <input type="text" name="actividad_economica" class="form-control" placeholder="Ej: Comercio, Agricultura, Servicio" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold text-primary">Dirección de Domicilio</label>
        <textarea name="direccion" class="form-control" rows="1" placeholder="Calle, Sector, Ciudad..."></textarea>
    </div>
</div>

                        <h6 class="text-success mb-3">Detalles del Crédito</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Producto / Convenio</label>
                                <select name="cod_producto" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach($productos as $p): ?>
                                        <option value="<?php echo $p['cod_producto']; ?>"><?php echo $p['nombre_producto']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Monto Aprobado (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="monto_aprobado" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-light me-md-2">Cancelar</a>
                            <button type="submit" class="btn btn-success px-5">Crear Expediente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>