<?php
session_start();
require_once 'includes/dbconnection.php'; 

if (!isset($pdo)) {
    die("Error de conexión.");
}

try {
    // 1. CONTEO GLOBAL Y PORCENTAJES (PHP)
    $q_totales = $pdo->query("SELECT estatus, COUNT(*) as cantidad FROM solicitudes GROUP BY estatus")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $t_pendientes = (int)($q_totales['Pendiente'] ?? 0);
    $t_proceso    = (int)($q_totales['En Proceso'] ?? 0);
    $t_resueltas  = (int)($q_totales['Resuelto'] ?? 0);
    $total_global = $t_pendientes + $t_proceso + $t_resueltas;

    $p_pen = ($total_global > 0) ? round(($t_pendientes * 100) / $total_global, 1) : 0;
    $p_pro = ($total_global > 0) ? round(($t_proceso * 100) / $total_global, 1) : 0;
    $p_res = ($total_global > 0) ? round(($t_resueltas * 100) / $total_global, 1) : 0;

    // 2. DATOS PARA EL DESGLOSE Y SELECTOR
    $entes = $pdo->query("SELECT id_ente, nombre_ente FROM entes ORDER BY nombre_ente ASC")->fetchAll();
    $q_desglose = $pdo->query("SELECT id_ente, estatus, COUNT(*) as total FROM solicitudes GROUP BY id_ente, estatus")->fetchAll(PDO::FETCH_ASSOC);
    
    $matriz = [];
    foreach ($q_desglose as $row) {
        $matriz[$row['id_ente']][$row['estatus']] = $row['total'];
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
    <title>Estadísticas PROGESAT</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-dark: #164377;
            --pendiente: #f6c23e;
            --proceso: #36b9cc;
            --resuelto: #1cc88a;
        }
        body { background-color: #ffffff; font-family: 'Segoe UI', sans-serif; }
        
        .header-clean { background: #fff; padding: 1.5rem 0; border-bottom: 2px solid #f1f1f1; margin-bottom: 2rem; }
        .brand-title { color: var(--primary-dark); font-weight: 800; font-size: 1.4rem; margin: 0; }
        .brand-subtitle { color: #6c757d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }

        .btn-volver {
            border: 2px solid var(--primary-dark); color: var(--primary-dark);
            font-weight: 700; border-radius: 8px; padding: 0.5rem 1.2rem;
            transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center;
        }
        .btn-volver:hover { background: var(--primary-dark); color: #fff; }

        .card { border-radius: 12px; border: 1px solid #edf0f5; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .nav-tabs .nav-link { color: #6c757d; border: none; font-weight: 600; }
        .nav-tabs .nav-link.active { color: var(--primary-dark); border-bottom: 3px solid var(--primary-dark); }
        .progress { height: 8px; border-radius: 10px; }
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
                    <span class="brand-subtitle">Solicitudes Emitidas por Direccion/Ente</span>
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

<div class="container-fluid px-5 pb-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="tabEstadisticas" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#tab-general" type="button" role="tab">General</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="ente-tab" data-bs-toggle="tab" data-bs-target="#tab-ente" type="button" role="tab">Por Ente</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                            <div class="text-center">
                                <div style="height: 220px; position: relative;">
                                    <canvas id="chartGeneral"></canvas>
                                </div>
                                <div class="mt-4 text-start">
                                    <div class="small fw-bold mb-1 d-flex justify-content-between">
                                        <span>Pendientes (<?php echo $p_pen; ?>%)</span> <span><?php echo $t_pendientes; ?></span>
                                    </div>
                                    <div class="progress mb-3"><div class="progress-bar bg-warning" style="width:<?php echo $p_pen; ?>%"></div></div>
                                    
                                    <div class="small fw-bold mb-1 d-flex justify-content-between">
                                        <span>En Proceso (<?php echo $p_pro; ?>%)</span> <span><?php echo $t_proceso; ?></span>
                                    </div>
                                    <div class="progress mb-3"><div class="progress-bar bg-info" style="width:<?php echo $p_pro; ?>%"></div></div>

                                    <div class="small fw-bold mb-1 d-flex justify-content-between">
                                        <span>Resueltas (<?php echo $p_res; ?>%)</span> <span><?php echo $t_resueltas; ?></span>
                                    </div>
                                    <div class="progress mb-3"><div class="progress-bar bg-success" style="width:<?php echo $p_res; ?>%"></div></div>
                                </div>
                            </div>
                        </div>

                                            <div class="alert alert-primary w-100 text-center mt-3 mb-0 py-2">
                        <h5 class="m-0 fw-bold">Total: <?php echo $total_global; ?></h5>
                    </div>

                        <div class="tab-pane fade" id="tab-ente" role="tabpanel">
                            <div id="wrapper-seleccion" class="text-center py-4">
                                <i class="bi bi-search fs-1 text-muted opacity-50 mb-3 d-block"></i>
                                <select class="form-select border-primary" id="selectEnte" onchange="consultarEnte(this.value)">
                                    <option value="" selected disabled>Seleccionar Ente...</option>
                                    <?php foreach($entes as $e): ?>
                                        <option value="<?php echo $e['id_ente']; ?>"><?php echo $e['nombre_ente']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="wrapper-grafica" class="d-none">
                                <div class="d-flex justify-content-between mb-3 bg-light p-2 rounded">
                                    <small class="fw-bold text-truncate" id="labelEnte" style="max-width: 180px;"></small>
                                    <button class="btn btn-sm btn-link p-0" onclick="resetTab()">Cambiar</button>
                                </div>
                                <div style="height: 180px;"><canvas id="chartEnteDinamico"></canvas></div>
                                <div id="statsEnte" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<div class="col-lg-8">
    <div class="card h-100">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span>Desglose Detallado por Direcciones</span>
            <span class="badge bg-light text-dark border">Total: <?php echo $total_global; ?></span>
        </div>
        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
            <?php foreach($entes as $e): 
                $id = $e['id_ente'];
                $res = isset($matriz[$id]['Resuelto']) ? (int)$matriz[$id]['Resuelto'] : 0;
                $proc = isset($matriz[$id]['En Proceso']) ? (int)$matriz[$id]['En Proceso'] : 0;
                $pend = isset($matriz[$id]['Pendiente']) ? (int)$matriz[$id]['Pendiente'] : 0;
                $total = $res + $proc + $pend;

                // Cálculo de porcentajes para las barras segmentadas
                $pr_res = ($total > 0) ? ($res * 100) / $total : 0;
                $pr_pro = ($total > 0) ? ($proc * 100) / $total : 0;
                $pr_pen = ($total > 0) ? ($pend * 100) / $total : 0;
            ?>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-bold text-dark text-uppercase" style="font-size: 0.75rem;">
                        <i class="bi bi-building me-1"></i><?php echo $e['nombre_ente']; ?>
                    </small>
                    <small class="text-muted fw-bold"><?php echo $total; ?> Solicitudes</small>
                </div>
                
                <div class="progress shadow-sm" style="height: 18px; background-color: #eaecf4;">
                    <?php if($total > 0): ?>
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?php echo $pr_res; ?>%; background-color: var(--resuelto);" 
                             title="Resuelto: <?php echo $res; ?>">
                             <?php echo ($pr_res > 10) ? round($pr_res)."%" : ""; ?>
                        </div>
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?php echo $pr_pro; ?>%; background-color: var(--proceso);" 
                             title="En Proceso: <?php echo $proc; ?>">
                             <?php echo ($pr_pro > 10) ? round($pr_pro)."%" : ""; ?>
                        </div>
                        <div class="progress-bar text-dark" role="progressbar" 
                             style="width: <?php echo $pr_pen; ?>%; background-color: var(--pendiente);" 
                             title="Pendiente: <?php echo $pend; ?>">
                             <?php echo ($pr_pen > 10) ? round($pr_pen)."%" : ""; ?>
                        </div>
                    <?php else: ?>
                        <div class="progress-bar bg-light text-muted w-100" style="border: 1px dashed #ccc; font-size: 0.7rem;">
                            SIN ACTIVIDAD REGISTRADA
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex gap-2 mt-1" style="font-size: 0.65rem;">
                    <span class="text-success fw-bold">R: <?php echo $res; ?></span>
                    <span class="text-info fw-bold">P: <?php echo $proc; ?></span>
                    <span class="text-warning fw-bold">E: <?php echo $pend; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="card-footer bg-white py-2 border-top-0">
            <div class="d-flex justify-content-center gap-4 small text-muted fw-bold">
                <span><i class="bi bi-circle-fill me-1" style="color: var(--resuelto);"></i> Resuelto</span>
                <span><i class="bi bi-circle-fill me-1" style="color: var(--proceso);"></i> En Proceso</span>
                <span><i class="bi bi-circle-fill me-1" style="color: var(--pendiente);"></i> Pendiente</span>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
let chartGen = new Chart(document.getElementById('chartGeneral'), {
    type: 'doughnut',
    data: {
        labels: ['Pendientes', 'En Proceso', 'Resueltas'],
        datasets: [{
            data: [<?php echo "$t_pendientes, $t_proceso, $t_resueltas"; ?>],
            backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a'],
            borderWidth: 0
        }]
    },
    options: { maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
});

let instanciaChartEnte = null;
function consultarEnte(id) {
    document.getElementById('wrapper-seleccion').classList.add('d-none');
    document.getElementById('wrapper-grafica').classList.remove('d-none');

    const sel = document.getElementById('selectEnte');
    document.getElementById('labelEnte').innerText = sel.options[sel.selectedIndex].text;

    const datos = (<?php echo json_encode($matriz); ?>)[id] || { 'Resuelto': 0, 'En Proceso': 0, 'Pendiente': 0 };
    const r = parseInt(datos['Resuelto'] || 0), p = parseInt(datos['En Proceso'] || 0), s = parseInt(datos['Pendiente'] || 0);
    const total = r + p + s;

    const p_s = total > 0 ? ((s * 100) / total).toFixed(1) : 0;
    const p_p = total > 0 ? ((p * 100) / total).toFixed(1) : 0;
    const p_r = total > 0 ? ((r * 100) / total).toFixed(1) : 0;

    if (instanciaChartEnte) instanciaChartEnte.destroy();
    instanciaChartEnte = new Chart(document.getElementById('chartEnteDinamico'), {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Resueltas'],
            datasets: [{ data: [s, p, r], backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a'] }]
        },
        options: { maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
    });

    document.getElementById('statsEnte').innerHTML = `
        <div class="d-flex justify-content-between small mb-1"><span>Pendientes (${p_s}%)</span> <b>${s}</b></div>
        <div class="d-flex justify-content-between small mb-1"><span>En Proceso (${p_p}%)</span> <b>${p}</b></div>
        <div class="d-flex justify-content-between small mb-1"><span>Resueltas (${p_r}%)</span> <b>${r}</b></div>
        <div class="alert alert-primary py-1 text-center mt-3 mb-0" style="font-size:0.8rem">Total Ente: ${total}</div>`;
}

function resetTab() {
    document.getElementById('selectEnte').value = "";
    document.getElementById('wrapper-seleccion').classList.remove('d-none');
    document.getElementById('wrapper-grafica').classList.add('d-none');
}
</script>
</body>
</html>