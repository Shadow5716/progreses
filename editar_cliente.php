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

// 1. Obtener el ID del cliente
$id_cliente = $_GET['id'] ?? '';

if (empty($id_cliente)) {
    header("Location: directorio_clientes.php");
    exit();
}

$mensaje = "";

// 2. Procesar la actualización si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre_completo'];
    $cedula = $_POST['cedula_rif'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo']; // Campo nuevo integrado
    $actividad = $_POST['actividad_economica'];
    $direccion = $_POST['direccion'];

    try {
        $sql = "UPDATE clientes SET 
                nombre_completo = ?, 
                cedula_rif = ?, 
                telefono = ?, 
                correo = ?, 
                actividad_economica = ?, 
                direccion = ? 
                WHERE id_cliente = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $cedula, $telefono, $correo, $actividad, $direccion, $id_cliente]);
        
        $mensaje = "<div class='alert alert-success shadow-sm'>¡Datos actualizados correctamente!</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger shadow-sm'>Error al actualizar: " . $e->getMessage() . "</div>";
    }
}

// 3. Consultar los datos actuales para llenar el formulario
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch();

if (!$cliente) {
    die("Cliente no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Editar Cliente</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3><i class="bi bi-pencil-square me-2"></i>Editar Información del Cliente</h3>
                    <a href="directorio_clientes.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Volver al Directorio
                    </a>
                </div>

                <?php echo $mensaje; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold text-dark">Nombre Completo</label>
                                    <input type="text" name="nombre_completo" class="form-control" 
                                           value="<?php echo htmlspecialchars($cliente['nombre_completo']); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold text-dark">Cédula / RIF</label>
                                    <input type="text" name="cedula_rif" class="form-control" 
                                           value="<?php echo htmlspecialchars($cliente['cedula_rif']); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-dark">Teléfono de Contacto</label>
                                    <input type="text" name="telefono" class="form-control" 
                                           value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-dark">Correo Electrónico</label>
                                    <input type="email" name="correo" class="form-control" 
                                           value="<?php echo htmlspecialchars($cliente['correo'] ?? ''); ?>" placeholder="usuario@correo.com">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold text-dark">Actividad Económica</label>
                                    <input type="text" name="actividad_economica" class="form-control" 
                                           value="<?php echo htmlspecialchars($cliente['actividad_economica']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Dirección de Habitación/Negocio</label>
                                <textarea name="direccion" class="form-control" rows="3"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                    <i class="bi bi-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>