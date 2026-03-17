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

// Manejo de búsqueda
$search = $_GET['search'] ?? '';
$where = "";
$params = [];

if (!empty($search)) {
    $where = " WHERE nombre_completo LIKE ? OR cedula_rif LIKE ? OR correo LIKE ? ";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$sql = "SELECT * FROM clientes $where ORDER BY nombre_completo ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Directorio de Clientes</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h3><i class="bi bi-person-lines-fill me-2"></i>Directorio de Clientes</h3>
            </div>
            <div class="col-md-6">
                <form class="d-flex" method="GET">
                    <input class="form-control me-2 shadow-sm" type="search" name="search" 
                           placeholder="Nombre, Cédula o Correo..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary shadow-sm" type="submit"><i class="bi bi-search"></i></button>
                    <?php if(!empty($search)): ?>
                        <a href="directorio_clientes.php" class="btn btn-outline-secondary ms-2 border-0"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Cédula / RIF</th>
                            <th>Contacto</th>
                            <th>Actividad Económica</th>
                            <th>Dirección</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($clientes) > 0): ?>
                            <?php foreach($clientes as $c): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?php echo $c['nombre_completo']; ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $c['cedula_rif']; ?></span></td>
                                <td>
                                    <div class="small"><i class="bi bi-telephone me-1"></i><?php echo $c['telefono'] ?: 'S/T'; ?></div>
                                    <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?php echo $c['correo'] ?: 'S/C'; ?></div>
                                </td>
                                <td><small><?php echo $c['actividad_economica']; ?></small></td>
                                <td class="text-truncate" style="max-width: 200px;"><?php echo $c['direccion']; ?></td>

                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">No se encontraron clientes registrados.</p>
                                    <a href="nuevo_cliente.php" class="btn btn-success btn-sm">Registrar mi primer cliente</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script src="vendors/jquery/dist/jquery.min.js"></script>
    
    <script src="vendors/popper.js/dist/umd/popper.min.js"></script>
    
    <script src="vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>


</body>
</html>