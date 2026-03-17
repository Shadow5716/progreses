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

// 1. OBTENER EL ID DEL CLIENTE DESDE LA URL
$id_cliente = $_GET['id_cliente'] ?? '';

if (empty($id_cliente)) {
    header("Location: directorio_clientes.php");
    exit();
}

// 2. BUSCAR LOS DATOS DEL CLIENTE PARA MOSTRARLOS
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch();

if (!$cliente) {
    die("Error: El cliente no existe.");
}

// 3. GENERACIÓN AUTOMÁTICA DEL PRÓXIMO NÚMERO DE EXPEDIENTE
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

// 4. CONSULTA DE PRODUCTOS PARA EL SELECT
$productos = $pdo->query("SELECT cod_producto, nombre_producto FROM catalogo_productos")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Nuevo Expediente</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php include 'header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white p-3">
                    <h5 class="mb-0"><i class="bi bi-folder-plus me-2"></i>Nuevo Expediente para: <?php echo $cliente['nombre_completo']; ?></h5>
                </div>
                <div class="card-body p-4">
                    <form action="procesar_registro.php" method="POST">
                        
                        <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                        <input type="hidden" name="cedula_rif" value="<?php echo $cliente['cedula_rif']; ?>">
                        <input type="hidden" name="nombre_completo" value="<?php echo $cliente['nombre_completo']; ?>">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Número de Expediente</label>
                                <input type="text" name="nro_expediente" class="form-control bg-light fw-bold text-success" value="<?php echo $nroSugerido; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fecha de Inicio</label>
                                <input type="date" name="fecha_creacion" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>Datos del Cliente:</strong> <?php echo $cliente['cedula_rif']; ?> | <?php echo $cliente['telefono']; ?>
                        </div>

                        <h6 class="text-success mb-3 border-bottom pb-2">Detalles del Nuevo Crédito</h6>
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
                                    <input type="number" step="0.01" name="monto_aprobado" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="directorio_clientes.php" class="btn btn-light me-md-2">Cancelar</a>
                            <button type="submit" class="btn btn-success px-5 shadow-sm">
                                <i class="bi bi-check-circle me-2"></i>Crear Expediente
                            </button>
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