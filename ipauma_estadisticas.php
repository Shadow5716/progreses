<?php
session_start();
require_once 'includes/dbconnection.php';

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'departamentos';
$titulo = "Estadísticas";
$query = "";

try {
    // 1. Obtener Estadísticas Globales para la gráfica circular
    $totales_global = $pdo->query("SELECT COUNT(*) FROM ipauma_solicitudes")->fetchColumn();
    $pendientes = $pdo->query("SELECT COUNT(*) FROM ipauma_solicitudes WHERE estado = 'Pendiente'")->fetchColumn();
    $proceso = $pdo->query("SELECT COUNT(*) FROM ipauma_solicitudes WHERE estado = 'En Proceso'")->fetchColumn();
    $resueltos = $pdo->query("SELECT COUNT(*) FROM ipauma_solicitudes WHERE estado = 'Resuelto'")->fetchColumn();

    // Calcular porcentajes
    $pct_pendientes = $totales_global > 0 ? round(($pendientes / $totales_global) * 100, 1) : 0;
    $pct_proceso = $totales_global > 0 ? round(($proceso / $totales_global) * 100, 1) : 0;
    $pct_resueltos = $totales_global > 0 ? round(($resueltos / $totales_global) * 100, 1) : 0;

    // 2. Obtener Estadísticas por Categoría Seleccionada (Usando LEFT JOIN para mostrar todos los registros incluso en 0)
    if ($tipo === 'departamentos') {
        $etiqueta_area = "DEPARTAMENTO";
        $query = "SELECT d.nombre as etiqueta, COUNT(s.id) as total 
                  FROM ipauma_departamentos d 
                  LEFT JOIN ipauma_solicitudes s ON s.departamento_id = d.id 
                  GROUP BY d.id ORDER BY total DESC";
    } elseif ($tipo === 'objetivos') {
        $etiqueta_area = "OBJETIVO";
        $query = "SELECT o.descripcion as etiqueta, COUNT(s.id) as total 
                  FROM ipauma_objetivos o 
                  LEFT JOIN ipauma_solicitudes s ON s.objetivo_id = o.id 
                  GROUP BY o.id ORDER BY total DESC";
    } elseif ($tipo === 'actividades') {
        $etiqueta_area = "ACTIVIDAD";
        $query = "SELECT a.descripcion as etiqueta, COUNT(s.id) as total 
                  FROM ipauma_actividades a 
                  LEFT JOIN ipauma_solicitudes s ON s.actividad_id = a.id 
                  GROUP BY a.id ORDER BY total DESC";
    } elseif ($tipo === 'parroquias') {
        $etiqueta_area = "PARROQUIA";
        
        // Listado estático de las 18 parroquias de Maracaibo para asegurar que salgan todas incluso con 0 reportes
        $parroquias_maracaibo = [
            'Antonio Borjas Romero', 'Bolívar', 'Cacique Mara', 'Caracciolo Parra Pérez',
            'Cecilio Acosta', 'Coquivacoa', 'Cristo de Aranza', 'Chiquinquirá',
            'Francisco Eugenio Bustamante', 'Idelfonso Vásquez', 'Juana de Ávila',
            'Luis Hurtado Higuera', 'Manuel Dagnino', 'Olegario Villalobos',
            'Raúl Leoni', 'San Isidro', 'Santa Lucía', 'Venancio Pulgar'
        ];

        // Construimos una consulta UNION para que aparezcan las 18 parroquias aunque no tengan registros
        $partes_union = [];
        foreach ($parroquias_maracaibo as $p) {
            $partes_union[] = "SELECT '" . $p . "' AS etiqueta";
        }
        $tabla_maestra_parroquias = "(" . implode(" UNION ", $partes_union) . ") AS p";

        $query = "SELECT p.etiqueta, COUNT(s.id) as total 
                  FROM $tabla_maestra_parroquias
                  LEFT JOIN ipauma_solicitudes s ON s.parroquia = p.etiqueta 
                  GROUP BY p.etiqueta 
                  ORDER BY total DESC, p.etiqueta ASC";
    } else {
        die("Tipo de estadística no válido.");
    }

    $stmt = $pdo->query($query);
    $estadisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener el valor máximo para calcular la escala de las barras de progreso
    $max_total = 0;
    foreach ($estadisticas as $est) {
        if ($est['total'] > $max_total) {
            $max_total = $est['total'];
        }
    }

} catch (PDOException $e) {
    die("Error al cargar estadísticas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> - IPAUPMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f4f7f6; }
        .navbar-custom {
            background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important;
            padding: 0.8rem 1rem;
        }
        .stat-panel { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #eaeaea; height: 100%; }
        .progress-sm { height: 10px; border-radius: 5px; }
        .progress-md { height: 16px; border-radius: 4px; background-color: #f0f0f0; }
        .text-sm { font-size: 0.85rem; }
        .chart-container { position: relative; height: 250px; width: 100%; margin: 0 auto 20px auto; }
        .badge-total { background-color: #6c757d; color: white; border-radius: 12px; padding: 3px 10px; font-size: 0.75rem; }
        .bg-color-primary { color: #164377 !important; }
        .bg-color-bar { background-color: #20c997 !important; }
        .btn-total { background-color: #dbe4ff; color: #164377; border: none; width: 100%; padding: 10px; font-weight: bold; border-radius: 6px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand text-white d-flex align-items-center" href="ipauma_dashboard.php">
            <img src="imagenes/alcaldia-maracaibo.png" alt="Logo" class="me-2" style="height: 45px;">
            <div class="d-flex flex-column line-height-1">
                <span class="fw-bold h5 mb-0 text-white">Programa de Reportes de Gestión</span>
                <small class="text-white-50">IPAUPMA</small>
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
                        <li><a class="dropdown-item" href="ipauma_estadisticas.php?tipo=parroquias"><i class="bi bi-map me-2 text-info"></i> Parroquias</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid px-5 pb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 bg-color-primary">Estadisticas</h3>
            <span class="text-secondary text-uppercase text-sm fw-semibold">GRÁFICAS POR ÁREA DE TRABAJO</span>
        </div>
        <a href="ipauma_dashboard.php" class="btn btn-outline-dark bg-white shadow-sm fw-semibold"><i class="bi bi-arrow-left-circle me-2"></i> Volver</a>
    </div>

    <div class="row align-items-stretch">
        
        <div class="col-md-4 mb-4">
            <div class="stat-panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold bg-color-primary mb-0">Estado Global de Solicitudes</h6>
                    <i class="bi bi-pie-chart text-secondary"></i>
                </div>
                
                <div class="chart-container">
                    <canvas id="donutChart"></canvas>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between text-secondary text-sm mb-1">
                        <span>Pendientes (<?= $pct_pendientes ?>%)</span>
                        <span class="fw-bold"><?= $pendientes ?></span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct_pendientes ?>%;"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between text-secondary text-sm mb-1">
                        <span>En Proceso (<?= $pct_proceso ?>%)</span>
                        <span class="fw-bold"><?= $proceso ?></span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= $pct_proceso ?>%;"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between text-secondary text-sm mb-1">
                        <span>Resueltas (<?= $pct_resueltos ?>%)</span>
                        <span class="fw-bold"><?= $resueltos ?></span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-color-bar" role="progressbar" style="width: <?= $pct_resueltos ?>%;"></div>
                    </div>
                </div>

                <div class="btn-total text-center">
                    Total: <?= $totales_global ?>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="stat-panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold bg-color-primary mb-0">Desempeño por <?= $etiqueta_area ?>S</h6>
                    <i class="bi bi-layers text-secondary"></i>
                </div>

                <?php if (count($estadisticas) > 0): ?>
                    <?php foreach ($estadisticas as $row): ?>
                        <?php 
                            // Calcular el porcentaje basado en el ítem que más reportes tenga
                            $porcentaje_barra = $max_total > 0 ? ($row['total'] / $max_total) * 100 : 0; 
                        ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-uppercase fw-semibold text-secondary text-sm"><i class="bi bi-hdd-stack me-2"></i><?= htmlspecialchars($row['etiqueta']) ?></span>
                                <span class="badge-total">TOTAL: <?= $row['total'] ?></span>
                            </div>
                            <div class="progress progress-md">
                                <div class="progress-bar bg-color-bar text-end pe-2 text-white" role="progressbar" style="width: <?= $porcentaje_barra ?>%; font-size: 0.75rem; line-height: 16px;" aria-valuenow="<?= $row['total'] ?>" aria-valuemin="0" aria-valuemax="<?= $max_total ?>">
                                    <?= $row['total'] > 0 ? $row['total'] : '' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-light text-center text-muted py-5 border">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        No hay datos registrados aún para mostrar en gráficas.
                    </div>
                <?php endif; ?>
                
                <div class="text-center text-muted text-sm mt-4 border-top pt-3">
                    <span class="me-3"><i class="bi bi-circle-fill text-color-bar me-1" style="color: #20c997; font-size:0.6rem;"></i> Resuelto</span>
                    <span class="me-3"><i class="bi bi-circle-fill text-secondary me-1" style="font-size:0.6rem;"></i> En Proceso</span>
                    <span><i class="bi bi-circle-fill text-warning me-1" style="font-size:0.6rem;"></i> Pendiente</span>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Inicialización de la gráfica circular (Donut Chart) con Chart.js
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('donutChart').getContext('2d');
        const donutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'En Proceso', 'Resueltas'],
                datasets: [{
                    data: [<?= $pendientes ?>, <?= $proceso ?>, <?= $resueltos ?>],
                    backgroundColor: [
                        '#ffc107', // Warning (Yellow)
                        '#6c757d', // Secondary (Gray)
                        '#20c997'  // Success/Primary Bar (Green)
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%', // Ancho del anillo interior
                plugins: {
                    legend: {
                        display: false // Ocultamos la leyenda nativa porque ya hicimos la nuestra
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>