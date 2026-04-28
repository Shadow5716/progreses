<?php
session_start();
require_once 'includes/dbconnection.php';

// Redireccionar si no ha iniciado sesión o no está en el módulo correcto
if (!isset($_SESSION['autentificado']) || ($_SESSION['modulo_activo'] !== 'imtcuma' && $_SESSION['rol'] !== 'Master')) {
    header('location:index.php?modulo=imtcuma');
    exit;
}

try {
    $totales = $pdo->query("SELECT COUNT(*) FROM imtcuma_vehiculos")->fetchColumn();
    $activos = $pdo->query("SELECT COUNT(*) FROM imtcuma_vehiculos WHERE estado = 'Activo'")->fetchColumn();
    
    $stmt = $pdo->query("SELECT * FROM imtcuma_vehiculos ORDER BY fecha_registro DESC");
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Vehículos - IMTCUMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .bg-gradient-custom {
            background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important;
        }
        .navbar-custom { padding: 0.8rem 1rem; }
        .stat-card { border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-total, .card-activo { background-color: #212529; color: #fff; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-gradient-custom navbar-custom shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand text-white d-flex align-items-center" href="imtcuma_dashboard.php">
            <img src="imagenes/alcaldia-maracaibo.png" alt="Logo" class="me-2" style="height: 45px;">
            <div class="d-flex flex-column line-height-1">
                <span class="fw-bold h5 mb-0 text-white">Programa de Reportes de Gestión</span>
                <small class="text-white-50">IMTCUMA</small>
            </div>
        </a>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3 text-white">
                    <i class="bi bi-person-circle"></i> <?= $_SESSION['AdminName'] ?> (<?= $_SESSION['rol'] ?>)
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-outline-light border-0"><i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <h2 class="mb-4 fw-bold">Registro de Vehículos de Transporte Público</h2>

    <div class="row mb-4">
        <div class="col-md-3"><div class="stat-card card-total"><h5>Total Registrados</h5><h2 class="mb-0 fw-bold"><?= $totales ?></h2></div></div>
        <div class="col-md-3"><div class="stat-card card-activo"><h5>Vehículos Activos</h5><h2 class="mb-0 fw-bold"><?= $activos ?></h2></div></div>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <form action="imtcuma_exportar_excel.php" method="POST" id="formExportar" class="mb-3">
    <input type="hidden" name="tipo_exportacion" id="tipo_exportacion" value="todos">
    <input type="hidden" name="ids_seleccionados" id="ids_seleccionados" value="">
    <button type="button" class="btn btn-success" onclick="exportarExcel('todos')">
        <i class="bi bi-file-earmark-excel"></i> Exportar Todos
    </button>
    <button type="button" class="btn btn-primary" onclick="exportarExcel('seleccionados')">
        <i class="bi bi-check-square"></i> Exportar Seleccionados
    </button>
</form>

<script>
function exportarExcel(tipo) {
    document.getElementById('tipo_exportacion').value = tipo;
    
    if (tipo === 'seleccionados') {
        let seleccionados = [];
        // Asegúrate de que las casillas de tu tabla tengan la clase "check-vehiculo"
        document.querySelectorAll('.check-vehiculo:checked').forEach(function(checkbox) {
            seleccionados.push(checkbox.value);
        });
        
        if (seleccionados.length === 0) {
            alert("Por favor, selecciona al menos un vehículo para exportar.");
            return;
        }
        document.getElementById('ids_seleccionados').value = seleccionados.join(',');
    }
    
    document.getElementById('formExportar').submit();
}
</script>
        <a href="imtcuma_nueva_solicitud.php" class="btn text-white shadow-sm fw-bold bg-gradient-custom">
            <i class="bi bi-plus-lg"></i> Nuevo Registro de Vehículo
        </a>
    </div>

    <div class="table-container table-responsive">
    <table class="table table-hover table-bordered align-middle text-center" style="font-size: 0.9rem;">
        <thead class="table-dark text-nowrap">
            <tr>
                <th>Placa</th>
                <th>Propietario</th>
                <th>C.I. Propietario</th>
                <th>Conductor</th>
                <th>C.I. Conductor</th>
                <th>Vehículo (Marca/Modelo)</th>
                <th>Año</th>
                <th>Color</th>
                <th>Capacidad</th>
                <th>Serial Carrocería</th>
                <th>Serial Motor</th>
                <th>Evaluación</th> <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($vehiculos) > 0): ?>
                <?php foreach ($vehiculos as $row): ?>
                <tr>
                    <td class="text-primary fw-bold text-uppercase"><?= htmlspecialchars($row['placa']) ?></td>
                    <td><?= htmlspecialchars($row['propietario_nombre']) ?></td>
                    <td><?= htmlspecialchars($row['propietario_cedula']) ?></td>
                    <td><?= htmlspecialchars($row['conductor_nombre']) ?></td>
                    <td><?= htmlspecialchars($row['conductor_cedula']) ?></td>
                    <td><?= htmlspecialchars($row['marca'] . ' ' . $row['modelo']) ?></td>
                    <td><?= htmlspecialchars($row['anio']) ?></td>
                    <td><?= htmlspecialchars($row['color']) ?></td>
                    <td><?= htmlspecialchars($row['capacidad']) ?></td>
                    <td><?= htmlspecialchars($row['serial_carroceria']) ?></td>
                    <td><?= htmlspecialchars($row['serial_motor']) ?></td>
                    
                    <td>
                        <?php if (!empty($row['estado_evaluacion'])): ?>
                            <?php 
                                $badgeClass = 'bg-secondary';
                                if ($row['estado_evaluacion'] === 'BUENA') $badgeClass = 'bg-success';
                                elseif ($row['estado_evaluacion'] === 'REGULAR') $badgeClass = 'bg-warning text-dark';
                                elseif ($row['estado_evaluacion'] === 'MALA') $badgeClass = 'bg-danger';
                            ?>
                            <span class="badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($row['puntaje_evaluacion']) ?> pts <br>
                                <?= htmlspecialchars($row['estado_evaluacion']) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Sin Evaluar</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm text-white bg-gradient-custom dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Opciones
                            </button>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item fw-bold text-success" href="imtcuma_evaluar_vehiculo.php?id=<?= $row['id'] ?>"><i class="bi bi-clipboard2-check"></i> Evaluar</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="imtcuma_eliminar_vehiculo.php" method="POST" onsubmit="return confirm('¿Está seguro que desea eliminar este vehículo del sistema?');">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="dropdown-item text-danger fw-bold"><i class="bi bi-trash3"></i> Eliminar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="13" class="text-muted py-4">No hay vehículos registrados en el sistema.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>