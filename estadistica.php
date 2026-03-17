<?php
session_start();
require_once 'includes/dbconnection.php'; 

if (!isset($pdo)) {
    die("Error de conexión.");
}

try {
    // 1. CONTEO GLOBAL PARA LA GRÁFICA CIRCULAR
    $q_totales = $pdo->query("SELECT estatus, COUNT(*) as cantidad FROM solicitudes GROUP BY estatus")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $t_pendientes = (int)($q_totales['Pendiente'] ?? 0);
    $t_proceso    = (int)($q_totales['En Proceso'] ?? 0);
    $t_resueltas  = (int)($q_totales['Resuelto'] ?? 0);
    $total_global = $t_pendientes + $t_proceso + $t_resueltas;

    // Porcentajes
    $p_pen = ($total_global > 0) ? round(($t_pendientes * 100) / $total_global, 1) : 0;
    $p_pro = ($total_global > 0) ? round(($t_proceso * 100) / $total_global, 1) : 0;
    $p_res = ($total_global > 0) ? round(($t_resueltas * 100) / $total_global, 1) : 0;

    // 2. DATOS DETALLADOS POR ÁREA (INCLUSO EN CERO)
    $areas = $pdo->query("SELECT id_area, nombre_area FROM areas ORDER BY nombre_area ASC")->fetchAll();
    $q_desglose = $pdo->query("SELECT id_area, estatus, COUNT(*) as total FROM solicitudes GROUP BY id_area, estatus")->fetchAll(PDO::FETCH_ASSOC);
    
    $matriz = [];
    foreach ($q_desglose as $row) {
        $matriz[$row['id_area']][$row['estatus']] = $row['total'];
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
        <link rel="shortcut icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
    <link rel="icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
    <meta charset="UTF-8">
    <title>Estadísticas Visuales - PROGESAT</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    :root {
        --primary-dark: #164377;
        --accent: #f6c23e;
        --pendiente: #f6c23e;
        --proceso: #36b9cc;
        --resuelto: #1cc88a;
    }
    body { background-color: #ffffff; font-family: 'Segoe UI', Roboto, sans-serif; }
    
    /* Encabezado Limpio sobre Fondo Blanco */
    .header-clean {
        background-color: #ffffff;
        padding: 1.5rem 0;
        border-bottom: 2px solid #f1f1f1;
        margin-bottom: 2rem;
    }
    
    .brand-title {
        color: var(--primary-dark);
        font-weight: 800;
        font-size: 1.4rem;
        letter-spacing: -0.5px;
        margin: 0;
        line-height: 1.2;
    }
    
    .brand-subtitle {
        color: #6c757d;
        font-size: 0.85rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Botón Volver Estilizado */
    .btn-volver {
        border: 2px solid var(--primary-dark);
        color: var(--primary-dark);
        font-weight: 700;
        border-radius: 8px;
        padding: 0.5rem 1.2rem;
        transition: all 0.3s ease;
        background: transparent;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .btn-volver:hover {
        background-color: var(--primary-dark);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(22, 67, 119, 0.2);
    }

    .card { border-radius: 12px; border: 1px solid #edf0f5; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .card-header { background: #fff; border-bottom: 1px solid #edf0f5; font-weight: 700; color: var(--primary-dark); }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<header class="header-clean">
    <div class="container-fluid px-5">
        <div class="row align-items-center">
            <div class="col-md-8 d-flex align-items-center">
                <div style="border-left: 2px solid #dee2e6; padding-left: 20px;">
                    <h1 class="brand-title">Estadisticas</h1>
                    <span class="brand-subtitle">Graficas por Area de trabajo</span>
                </div>
            </div>
            
            <div class="col-md-4 text-end">
                <a href="dashboard.php" class="btn-volver">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i> Volver
                </a>
            </div>
        </div>
    </div>
</header>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span>Estado Global de Solicitudes</span>
                    <i class="bi bi-pie-chart text-muted"></i>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div style="width: 280px; height: 280px;">
                        <canvas id="chartCircular"></canvas>
                    </div>
                    
                    <div class="w-100 mt-4">
                        <div class="d-flex justify-content-between mb-1 small font-weight-bold">
                            <span>Pendientes (<?php echo $p_pen; ?>%)</span>
                            <span><?php echo $t_pendientes; ?></span>
                        </div>
                        <div class="progress mb-3"><div class="progress-bar" style="width: <?php echo $p_pen; ?>%; background-color: var(--pendiente);"></div></div>
                        
                        <div class="d-flex justify-content-between mb-1 small font-weight-bold">
                            <span>En Proceso (<?php echo $p_pro; ?>%)</span>
                            <span><?php echo $t_proceso; ?></span>
                        </div>
                        <div class="progress mb-3"><div class="progress-bar" style="width: <?php echo $p_pro; ?>%; background-color: var(--proceso);"></div></div>

                        <div class="d-flex justify-content-between mb-1 small font-weight-bold">
                            <span>Resueltas (<?php echo $p_res; ?>%)</span>
                            <span><?php echo $t_resueltas; ?></span>
                        </div>
                        <div class="progress mb-3"><div class="progress-bar" style="width: <?php echo $p_res; ?>%; background-color: var(--resuelto);"></div></div>
                    </div>
                    
                    <div class="alert alert-primary w-100 text-center mt-3 mb-0 py-2">
                        <h5 class="m-0 fw-bold">Total: <?php echo $total_global; ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span>Desempeño por Áreas Tecnológicas</span>
                    <i class="bi bi-layers text-muted"></i>
                </div>
                <div class="card-body" style="max-height: 650px; overflow-y: auto;">
                    <?php foreach($areas as $a): 
                        $id = $a['id_area'];
                        $res = $matriz[$id]['Resuelto'] ?? 0;
                        $proc = $matriz[$id]['En Proceso'] ?? 0;
                        $pend = $matriz[$id]['Pendiente'] ?? 0;
                        $total_area = $res + $proc + $pend;
                        
                        // Cálculo para el ancho de la barra apilada
                        $pr_res = ($total_area > 0) ? ($res / $total_area) * 100 : 0;
                        $pr_proc = ($total_area > 0) ? ($proc / $total_area) * 100 : 0;
                        $pr_pend = ($total_area > 0) ? ($pend / $total_area) * 100 : 0;
                    ?>
                    <div class="mb-4 pb-2 border-bottom">
                        <span class="area-title text-uppercase mb-2">
                            <i class="bi bi-cpu me-1"></i> <?php echo $a['nombre_area']; ?> 
                            <span class="badge bg-secondary float-end">Total: <?php echo $total_area; ?></span>
                        </span>
                        
                        <?php if($total_area > 0): ?>
                            <div class="progress shadow-sm">
                                <div class="progress-bar" style="width: <?php echo $pr_res; ?>%; background-color: var(--resuelto);" title="Resueltas"><?php echo $res; ?></div>
                                <div class="progress-bar" style="width: <?php echo $pr_proc; ?>%; background-color: var(--proceso);" title="En Proceso"><?php echo $proc; ?></div>
                                <div class="progress-bar" style="width: <?php echo $pr_pend; ?>%; background-color: var(--pendiente); color: #000;" title="Pendientes"><?php echo $pend; ?></div>
                            </div>
                        <?php else: ?>
                            <div class="progress"><div class="progress-bar bg-light text-muted w-100" style="border: 1px dashed #ccc;">Sin registros</div></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer bg-white py-2">
                    <div class="d-flex justify-content-center gap-3 small text-muted">
                        <span><span class="legend-dot" style="background: var(--resuelto);"></span> Resuelto</span>
                        <span><span class="legend-dot" style="background: var(--proceso);"></span> En Proceso</span>
                        <span><span class="legend-dot" style="background: var(--pendiente);"></span> Pendiente</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('chartCircular').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Resueltas'],
            datasets: [{
                data: [<?php echo $t_pendientes; ?>, <?php echo $t_proceso; ?>, <?php echo $t_resueltas; ?>],
                backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a'],
                hoverOffset: 15,
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '70%'
        }
    });
</script>

<script src="js/bootstrap.bundle.min.js"></script>


</body>
</html>