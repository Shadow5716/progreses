<?php
session_start();

// 1. Conexión a la base de datos
require_once 'includes/dbconnection.php'; 

// 2. Verificar existencia de $pdo
if (!isset($pdo)) {
    die("Error: La variable de conexión \$pdo no existe. Revisa el archivo dbconnection.php");
}

try {
    // --- 1. ESTADÍSTICAS GLOBALES (Siempre muestran el total real) ---
    $total_val = $pdo->query("SELECT COUNT(*) FROM solicitudes")->fetchColumn();
    $pendientes_val = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estatus = 'Pendiente'")->fetchColumn();
    $proceso_val = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estatus = 'En Proceso'")->fetchColumn();
    $resueltas_val = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estatus = 'Resuelto'")->fetchColumn();

    // --- 2. CAPTURAR FILTROS Y PAGINACIÓN ---
    $filas_por_pagina = 14;
    $pagina_actual = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;
    $offset = ($pagina_actual - 1) * $filas_por_pagina;

    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
    $estatus_filtro = isset($_GET['estatus']) ? trim($_GET['estatus']) : '';

// --- 3. CONSTRUIR CONSULTA DINÁMICA ---
    $where_sql = "WHERE 1=1";
    $params = [];

    // Filtro de texto (Corregido para PDO y mejorado para múltiples palabras)
    if (!empty($busqueda)) {
        // Dividimos la búsqueda por espacios para buscar cada palabra por separado
        $palabras = explode(' ', $busqueda);
        
        foreach ($palabras as $index => $palabra) {
            $palabra = trim($palabra);
            if ($palabra !== '') {
                // PDO exige nombres de parámetros únicos. Creamos identificadores únicos por cada palabra y columna.
                $p_ente = "ente_" . $index;
                $p_resp = "resp_" . $index;
                $p_desc = "desc_" . $index;
                $p_ofi  = "ofi_" . $index;
                $p_area = "area_" . $index;
                
                // Exigimos que esta palabra coincida en alguna de las columnas
                $where_sql .= " AND (e.nombre_ente LIKE :$p_ente 
                                OR r.nombre_responsable LIKE :$p_resp 
                                OR s.descripcion LIKE :$p_desc 
                                OR s.nro_oficio LIKE :$p_ofi 
                                OR a.nombre_area LIKE :$p_area)";
                                
                // Asignamos el valor a cada parámetro único
                $param_value = "%" . $palabra . "%";
                $params[$p_ente] = $param_value;
                $params[$p_resp] = $param_value;
                $params[$p_desc] = $param_value;
                $params[$p_ofi]  = $param_value;
                $params[$p_area] = $param_value;
            }
        }
    }

    // Filtro de Estatus
    if (!empty($estatus_filtro)) {
        $where_sql .= " AND s.estatus = :estatus";
        $params['estatus'] = $estatus_filtro;
    }

    // --- 4. CALCULAR TOTAL DE PÁGINAS (Respetando el filtro) ---
    $sql_count = "SELECT COUNT(*) FROM solicitudes s
                  INNER JOIN entes e ON s.id_ente = e.id_ente
                  INNER JOIN areas a ON s.id_area = a.id_area
                  INNER JOIN responsables r ON s.id_responsable = r.id_responsable
                  $where_sql";
    
    $stmt_count = $pdo->prepare($sql_count);
    foreach ($params as $key => $val) {
        $stmt_count->bindValue(":$key", $val);
    }
    $stmt_count->execute();
    $total_filtrados = $stmt_count->fetchColumn();
    
    $total_paginas = ceil($total_filtrados / $filas_por_pagina);
    if ($total_paginas == 0) $total_paginas = 1; // Mínimo 1 página

    // --- 5. CONSULTA FINAL PARA LA TABLA ---
    $sql_tabla = "SELECT s.*, e.nombre_ente, a.nombre_area, r.nombre_responsable 
                  FROM solicitudes s
                  INNER JOIN entes e ON s.id_ente = e.id_ente
                  INNER JOIN areas a ON s.id_area = a.id_area
                  INNER JOIN responsables r ON s.id_responsable = r.id_responsable
                  $where_sql
                  ORDER BY s.fecha DESC
                  LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql_tabla);
    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val);
    }
    // Bind especiales para LIMIT y OFFSET (Deben ser INT)
    $stmt->bindValue(':limit', $filas_por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $solicitudes = $stmt->fetchAll();

} catch (PDOException $e) {
    $total_val = $pendientes_val = $proceso_val = $resueltas_val = 0;
    $solicitudes = [];
    $total_paginas = 1;
}

// Generar variable extra para la paginación (Para no perder el filtro al cambiar de página)
$query_string = "";
if (!empty($busqueda)) $query_string .= "&busqueda=" . urlencode($busqueda);
if (!empty($estatus_filtro)) $query_string .= "&estatus=" . urlencode($estatus_filtro);
?>

<!DOCTYPE html>
<html lang="es">
<head>
        <link rel="shortcut icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
    <link rel="icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
        <script src="https://unpkg.com/xlsx@latest/dist/xlsx.full.min.js"></script>

<script src="https://unpkg.com/file-saverjs@latest/FileSaver.min.js"></script>

<script src="https://unpkg.com/tableexport@latest/dist/js/tableexport.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
        <script src="js/excel.js"></script> 


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dirección de Tecnología - Alcaldía de Maracaibo</title>
    <link rel="stylesheet" href="css/bootstrap.min.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fc; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        .navbar-custom { background-color: #164377 !important; padding-top: 1rem; padding-bottom: 1rem; }
        .navbar-custom .navbar-brand { color: white; font-weight: bold; font-size: 1.5rem; }
        .navbar-custom .navbar-brand small { font-weight: normal; font-size: 0.9rem; color: #d1d1d1; display: block; }
        
        .stat-card { background: white; border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; border: 1px solid #e3e6f0; }
        .stat-card-title { font-size: 0.85rem; color: #858796; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; }
        .stat-card-value { font-size: 2.5rem; font-weight: bold; color: #4e73df; }
        .stat-card-icon { font-size: 2.5rem; color: #dddfeb; }
        
        .icon-pendientes { color: #f6c23e; } 
        .icon-enproceso { color: #36b9cc; } 
        .icon-resueltas { color: #1cc88a; } 
        
        /* El contenedor de búsqueda ahora será form, mantenemos estilos visuales */
        .search-container { background: white; border-radius: 12px; padding: 1rem; border: 1px solid #e3e6f0; }
        .search-input { border-radius: 8px; border: 1px solid #d1d3e2; }
        
        .main-table-card { background: white; border-radius: 12px; padding: 1rem; border: 1px solid #e3e6f0; }
        .table thead th { font-size: 0.8rem; color: #858796; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e3e6f0; }
        .table tbody td { font-size: 0.95rem; color: #5a5c69; vertical-align: middle; padding: 1rem 0.75rem; }
        .table-responsive { overflow-x: auto; }
        
        .status-badge { font-size: 0.8rem; padding: 0.4rem 0.8rem; border-radius: 20px; font-weight: 500; display: inline-block; white-space: nowrap; text-align: center; min-width: 90px; }
        .badge-pendiente { background-color: #fdf2d9; color: #c48c0a; }
        .badge-enproceso { background-color: #e1f5fe; color: #0277bd; border: none; }
        .badge-resuelto { background-color: #e8f5e9; color: #2e7d32; }
        
        .pagination .page-link { display: flex; align-items: center; justify-content: center; height: 40px; padding: 0 1rem; color: #4e73df; border: 1px solid #dddfeb; }
        .pagination .page-link i { font-size: 0.8rem; line-height: 0; }
        .pagination .page-item.active .page-link { background-color: #007bff !important; border-color: #007bff !important; color: white !important; }
    </style>
</head>
<body>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        </div>
    <a href="ipauma_dashboard.php" class="btn btn-success fw-bold shadow-sm">
        <i class="bi bi-arrow-right-short fs-5"></i> ENTRAR AL MÓDULO IPAUMA
    </a>
</div>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid mt-5 px-5">
        
        <div class="row mb-5">
            <div class="col-12 d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0 text-gray-800">Dashboard de Solicitudes</h2>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card shadow-sm">
                    <div>
                        <div class="stat-card-title">Total Solicitudes</div>
                        <div class="stat-card-value"><?php echo $total_val; ?></div>
                    </div>
                    <i class="bi bi-file-earmark-text stat-card-icon"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card shadow-sm">
                    <div>
                        <div class="stat-card-title">Pendientes</div>
                        <div class="stat-card-value text-warning"><?php echo $pendientes_val; ?></div>
                    </div>
                    <i class="bi bi-exclamation-triangle stat-card-icon icon-pendientes"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card shadow-sm">
                    <div>
                        <div class="stat-card-title">En Proceso</div>
                        <div class="stat-card-value text-info"><?php echo $proceso_val; ?></div>
                    </div>
                    <i class="bi bi-clock-history stat-card-icon icon-enproceso"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card shadow-sm">
                    <div>
                        <div class="stat-card-title">Resueltas</div>
                        <div class="stat-card-value text-success"><?php echo $resueltas_val; ?></div>
                    </div>
                    <i class="bi bi-check-circle stat-card-icon icon-resueltas"></i>
                </div>
            </div>
        </div>


        <div class="row mb-4">
            <div class="col-12">
                <form method="GET" action="dashboard.php" class="search-container shadow-sm d-flex justify-content-between align-items-center">
                    
                    <div class="d-flex align-items-center flex-grow-1 me-4">
                        <i class="bi bi-search text-muted me-3"></i>
                        <input type="text" name="busqueda" class="form-control form-control-lg search-input flex-grow-1" placeholder="Buscar por dirección, responsable o descripción..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>

                    
                    
                    <div class="d-flex align-items-center">
                        <i class="bi bi-funnel text-muted me-3"></i>
                        <select name="estatus" class="form-select form-select-lg" onchange="this.form.submit()" style="border-radius: 8px; border: 1px solid #d1d3e2; width: auto;">
                            <option value="">Todos</option>
                            <option value="Pendiente" <?php echo ($estatus_filtro == 'Pendiente') ? 'selected' : ''; ?>>Pendientes</option>
                            <option value="En Proceso" <?php echo ($estatus_filtro == 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                            <option value="Resuelto" <?php echo ($estatus_filtro == 'Resuelto') ? 'selected' : ''; ?>>Resueltas</option>
                        </select>
                        <button type="submit" class="btn btn-primary ms-2 d-none">Buscar</button>
                    </div>

                </form>
                
            </div>
        </div>

        

<div class="row">
    <div class="col-12">
        <div class="main-table-card shadow-sm">
            <div class="table-responsive">
                <div class="mb-3">
                    <button id="btnExportar" class="btn btn-success shadow-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> Generar Reporte de Gestión (Excel)
                    </button>
                </div>

                <table id="tabla" class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Fecha</th>
                            <th>Dirección / Ente</th>
                            <th>Área</th>
                            <th>Responsable</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Estatus</th>
                            <th>Oficio</th>
                            <th class="text-center tableexport-ignore">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($solicitudes) > 0): ?>
                            <?php foreach ($solicitudes as $row): 
                                $clase = 'badge-pendiente';
                                if ($row['estatus'] == 'En Proceso') $clase = 'badge-enproceso';
                                if ($row['estatus'] == 'Resuelto') $clase = 'badge-resuelto';
                            ?>
                            <tr>
                                <td><?php echo $row['id_solicitud']; ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['fecha'])); ?></td>
                                <td class="text-uppercase" style="max-width: 250px; font-size: 0.85rem;">
                                    <?php echo htmlspecialchars(trim($row['nombre_ente'])); ?>
                                </td>
                                <td class="text-uppercase"><?php echo htmlspecialchars(trim($row['nombre_area'])); ?></td>
                                <td class="text-uppercase"><?php echo htmlspecialchars(trim($row['nombre_responsable'])); ?></td>
                                <td class="text-uppercase" style="font-size: 0.85rem;">
                                    <?php echo htmlspecialchars(trim($row['tipo_actividad'])); ?>
                                </td>
                                <td style="min-width: 200px; max-width: 300px; white-space: normal; word-wrap: break-word;">
                                    <?php echo htmlspecialchars(trim($row['descripcion'])); ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $clase; ?>">
                                        <?php echo htmlspecialchars($row['estatus']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(trim($row['nro_oficio'])); ?></td>
<td class="text-center tableexport-ignore">
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow">
            
            <li>
                <a class="dropdown-item d-flex align-items-center" href="editar_solicitud.php?id=<?php echo $row['id_solicitud']; ?>">
                    <i class="bi bi-pencil-square me-2 text-primary"></i> 
                    <span>Editar / Ver</span>
                </a>
            </li>

            <li><hr class="dropdown-divider border-secondary"></li>

            <li>
                <a class="dropdown-item d-flex align-items-center text-danger" 
                   href="borrar_solicitud.php?id=<?php echo $row['id_solicitud']; ?>"
                   onclick="return confirm('¿Estás seguro de eliminar permanentemente la solicitud No. <?php echo $row['id_solicitud']; ?>?');">
                    <i class="bi bi-trash me-2"></i> 
                    <span>Eliminar</span>
                </a>
            </li>
            
        </ul>
    </div>
</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center text-muted py-4">No se encontraron resultados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
        
        <div class="row mt-4 mb-5">
            <div class="col-12 d-flex justify-content-end">
                <nav aria-label="Navegación de solicitudes">
                    <ul class="pagination pagination-md shadow-sm">
                        
                        <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link d-flex align-items-center" href="?p=<?php echo $pagina_actual - 1 . $query_string; ?>" <?php echo ($pagina_actual <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                <i class="bi bi-chevron-left me-1"></i> 
                                <span class="d-none d-sm-inline">Anterior</span>
                            </a>
                        </li>

                        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                            <a class="page-link" href="?p=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                            <a class="page-link d-flex align-items-center" href="?p=<?php echo $pagina_actual + 1 . $query_string; ?>" <?php echo ($pagina_actual >= $total_paginas) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                <span class="d-none d-sm-inline">Siguiente</span>
                                <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </li>
                        
                    </ul>
                </nav>
            </div>
        </div>

    </div>

    <script src="js/bootstrap.bundle.min.js"></script> 
</body>
</html>