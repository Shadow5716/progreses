<?php
session_start();
require_once 'includes/dbconnection.php';

// Captura de Filtros de Búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';

// Construir la consulta SQL dinámica basada en los filtros
$where_sql = "1=1";
$params = [];

if ($busqueda != '') {
    $where_sql .= " AND (s.id LIKE :busqueda OR s.descripcion LIKE :busqueda OR s.oficio LIKE :busqueda OR d.nombre LIKE :busqueda OR o.descripcion LIKE :busqueda OR a.descripcion LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}
if ($estado_filtro != '') {
    $where_sql .= " AND s.estado = :estado";
    $params[':estado'] = $estado_filtro;
}

try {
    // 1. Tarjetas de Conteo (Estadísticas Globales)
    $totales = $pdo->query("SELECT COUNT(*) FROM ipauma_solicitudes")->fetchColumn();
    $pendientes = $pdo->query("SELECT COUNT(*) FROM ipauma_solicitudes WHERE estado = 'Pendiente'")->fetchColumn();
    $proceso = $pdo->query("SELECT COUNT(*) FROM ipauma_solicitudes WHERE estado = 'En Proceso'")->fetchColumn();
    $resueltos = $pdo->query("SELECT COUNT(*) FROM ipauma_solicitudes WHERE estado = 'Resuelto'")->fetchColumn();

    // 2. Consulta Principal para la Tabla
    $query_tabla = "SELECT s.*, d.nombre as depto_nombre, o.descripcion as obj_desc, a.descripcion as act_desc 
                    FROM ipauma_solicitudes s
                    LEFT JOIN ipauma_departamentos d ON s.departamento_id = d.id
                    LEFT JOIN ipauma_objetivos o ON s.objetivo_id = o.id
                    LEFT JOIN ipauma_actividades a ON s.actividad_id = a.id
                    WHERE $where_sql ORDER BY s.fecha DESC";
    
    $stmt = $pdo->prepare($query_tabla);
    $stmt->execute($params);
    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Reportes IPAUMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .navbar-custom {
            background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important;
            padding: 0.8rem 1rem;
        }
        .stat-card { border-radius: 10px; color: white; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-total { background-color: #ffffff; color: #000; }
        .card-pendiente { background-color: #ffffff; color: #000; }
        .card-proceso { background-color: #ffffff; color: #000; }
        .card-resuelto { background-color: #ffffff; color: #000; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand text-white d-flex align-items-center" href="ipauma_dashboard.php">
            <img src="imagenes/alcaldia-maracaibo.png" alt="Logo" class="me-2" style="height: 45px;">
            <div class="d-flex flex-column line-height-1">
                <span class="fw-bold h5 mb-0 text-white">IPAUMA</span>
                <small class="text-white-50">Dirección de Tecnología</small>
            </div>
        </a>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-2">
                    <a href="dashboard.php" class="btn btn-outline-light border-0"><i class="bi bi-house me-1"></i> Volver a Gestión</a>
                </li>
                <li class="nav-item dropdown me-2">
                    <button class="btn btn-outline-light border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-pie-chart me-1"></i> Estadísticas
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="ipauma_estadisticas.php?tipo=departamentos"><i class="bi bi-building me-2 text-primary"></i> Departamentos</a></li>
                        <li><a class="dropdown-item" href="ipauma_estadisticas.php?tipo=objetivos"><i class="bi bi-bullseye me-2 text-danger"></i> Objetivos</a></li>
                        <li><a class="dropdown-item" href="ipauma_estadisticas.php?tipo=actividades"><i class="bi bi-list-task me-2 text-success"></i> Actividades</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <h2 class="mb-4 fw-bold">Historial de Reportes IPAUMA</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card card-total">
                <h5>Total de Reportes</h5>
                <h2 class="mb-0 fw-bold"><?= $totales ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card card-pendiente">
                <h5>Pendientes</h5>
                <h2 class="mb-0 fw-bold"><?= $pendientes ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card card-proceso">
                <h5>En Proceso</h5>
                <h2 class="mb-0 fw-bold"><?= $proceso ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card card-resuelto">
                <h5>Resueltos</h5>
                <h2 class="mb-0 fw-bold"><?= $resueltos ?></h2>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <form action="ipauma_exportar.php" method="POST" id="formExportarSelected" class="d-inline">
                <input type="hidden" name="ids_seleccionados" id="input_ids_seleccionados" value="">
                <button type="button" onclick="exportarSeleccionados()" class="btn btn-success shadow-sm">
                    <i class="bi bi-file-earmark-excel"></i> Generar Excel (Seleccionados)
                </button>
            </form>
            <form action="ipauma_exportar.php" method="POST" class="d-inline">
                <input type="hidden" name="exportar_todos" value="1">
                <button type="submit" class="btn btn-success shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Generar Excel (Todos)
                </button>
            </form>
        </div>
        <a href="ipauma_nueva_solicitud.php" class="btn btn-primary shadow-sm fw-bold">
            <i class="bi bi-plus-lg"></i> Nueva Solicitud
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 p-3">
        <form method="GET" action="ipauma_dashboard.php" class="row g-2 align-items-center">
            <div class="col-md-7">
                <input type="text" name="busqueda" class="form-control" placeholder="Buscar por N°, Descripción, Oficio, Departamento..." value="<?= htmlspecialchars($busqueda) ?>">
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente" <?= $estado_filtro == 'Pendiente' ? 'selected' : '' ?>>Pendientes</option>
                    <option value="En Proceso" <?= $estado_filtro == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                    <option value="Resuelto" <?= $estado_filtro == 'Resuelto' ? 'selected' : '' ?>>Resueltos</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i> Buscar</button>
            </div>
        </form>
    </div>

    <div class="table-container table-responsive">
        <table class="table table-hover table-bordered align-middle text-center text-nowrap">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="selectAll" class="form-check-input" onclick="toggleSelectAll()"></th>
                    <th>N°</th>
                    <th>Fecha</th>
                    <th>Departamento</th>
                    <th>Objetivo</th>
                    <th>Actividad</th>
                    <th>Oficio</th>
                    <th>Parroquia</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reportes) > 0): ?>
                    <?php foreach ($reportes as $row): ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input row-checkbox" value="<?= $row['id'] ?>"></td>
                        <td class="fw-bold text-primary">#<?= $row['id'] ?></td>
                        <td><?= date('d/m/Y h:i A', strtotime($row['fecha'])) ?></td>
                        <td title="<?= htmlspecialchars($row['depto_nombre']) ?>"><?= htmlspecialchars(substr($row['depto_nombre'], 0, 20)) ?>...</td>
                        <td title="<?= htmlspecialchars($row['obj_desc']) ?>"><?= htmlspecialchars(substr($row['obj_desc'], 0, 20)) ?>...</td>
                        <td title="<?= htmlspecialchars($row['act_desc']) ?>"><?= htmlspecialchars(substr($row['act_desc'], 0, 20)) ?>...</td>
                        <td><?= htmlspecialchars($row['oficio']) ?></td>
                        <td><?= htmlspecialchars($row['parroquia']) ?></td>
                        <td>
                            <?php 
                                $badgeColor = $row['estado'] == 'Resuelto' ? 'bg-success' : ($row['estado'] == 'En Proceso' ? 'bg-warning text-dark' : 'bg-danger');
                            ?>
                            <span class="badge <?= $badgeColor ?>"><?= htmlspecialchars($row['estado']) ?></span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Opciones
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item text-primary" href="ipauma_editar.php?id=<?= $row['id'] ?>"><i class="bi bi-eye"></i> Ver / Editar</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="ipauma_eliminar.php?id=<?= $row['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este reporte?');"><i class="bi bi-trash"></i> Eliminar</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-muted py-4">No se encontraron reportes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Función para seleccionar todos los checkboxes
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }

    // Función para recolectar IDs seleccionados y enviar a Excel
    function exportarSeleccionados() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Por favor, selecciona al menos un reporte para exportar.');
            return;
        }
        let ids = [];
        checkboxes.forEach(cb => ids.push(cb.value));
        document.getElementById('input_ids_seleccionados').value = ids.join(',');
        document.getElementById('formExportarSelected').submit();
    }
</script>
</body>
</html>