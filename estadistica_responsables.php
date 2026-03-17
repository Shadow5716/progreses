<?php
session_start();
require_once 'includes/dbconnection.php'; 

if (!isset($pdo)) {
    die("Error de conexión.");
}

try {
    // 1. DATOS PARA EL SELECTOR Y LISTADO DE RESPONSABLES
    // Obtenemos todos los responsables y sus estadísticas de estatus
    $q_responsables = $pdo->query("SELECT id_responsable, nombre_responsable FROM responsables ORDER BY nombre_responsable ASC")->fetchAll();
    
    $q_stats = $pdo->query("SELECT id_responsable, estatus, COUNT(*) as total FROM solicitudes GROUP BY id_responsable, estatus")->fetchAll(PDO::FETCH_ASSOC);
    
    $matriz_resp = [];
    foreach ($q_stats as $row) {
        $matriz_resp[$row['id_responsable']][$row['estatus']] = $row['total'];
    }

    // Totales generales para el balance inicial
    $t_pendientes = 0; $t_proceso = 0; $t_resueltas = 0;
    foreach($matriz_resp as $r) {
        $t_pendientes += ($r['Pendiente'] ?? 0);
        $t_proceso    += ($r['En Proceso'] ?? 0);
        $t_resueltas  += ($r['Resuelto'] ?? 0);
    }
    $total_global = $t_pendientes + $t_proceso + $t_resueltas;

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
    <title>Estadísticas por Responsable - PROGESAT</title>
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
        .btn-volver { border: 2px solid var(--primary-dark); color: var(--primary-dark); font-weight: 700; border-radius: 8px; padding: 0.5rem 1.2rem; text-decoration: none; display: inline-flex; align-items: center; transition: 0.3s; }
        .btn-volver:hover { background: var(--primary-dark); color: #fff; }
        .card { border-radius: 12px; border: 1px solid #edf0f5; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .progress { height: 18px; border-radius: 10px; background-color: #eaecf4; font-size: 0.7rem; font-weight: bold; }
        .legend-dot { height: 12px; width: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; }
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
                    <span class="text-muted">Solicitudes para Personal Responsable</span>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <a href="dashboard.php" class="btn-volver"><i class="bi bi-arrow-left-circle-fill me-2"></i> Volver</a>
            </div>
        </div>
    </div>
</header>

<div class="container-fluid px-5 pb-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white py-3">
                    <span class="fw-bold"><i class="bi bi-person-badge me-2"></i>Analizar Responsable</span>
                </div>
                <div class="card-body">
                    <div id="wrapper-selector" class="text-center py-4">
                        <select class="form-select border-primary shadow-sm" id="selectResp" onchange="cargarGraficaResponsable(this.value)">
                            <option value="" selected disabled>Seleccione un responsable...</option>
                            <?php foreach($q_responsables as $res): ?>
                                <option value="<?php echo $res['id_responsable']; ?>"><?php echo $res['nombre_responsable']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="wrapper-grafica-resp" class="d-none">
                        <div class="text-center mb-3">
                            <h6 id="nombreCargado" class="fw-bold text-primary"></h6>
                            <button class="btn btn-sm btn-link text-decoration-none" onclick="location.reload()"><i class="bi bi-arrow-repeat"></i> Cambiar</button>
                        </div>
                        <div style="height: 200px; position: relative;">
                            <canvas id="chartRespDinamico"></canvas>
                        </div>
                        <div id="statsDetalleResp" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Carga de Trabajo por Personal</span>
                    <span class="badge bg-primary">Total: <?php echo $total_global; ?></span>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php foreach($q_responsables as $resp): 
                        $id = $resp['id_responsable'];
                        $r = $matriz_resp[$id]['Resuelto'] ?? 0;
                        $p = $matriz_resp[$id]['En Proceso'] ?? 0;
                        $s = $matriz_resp[$id]['Pendiente'] ?? 0;
                        $total = $r + $p + $s;

                        $pr_r = ($total > 0) ? ($r * 100) / $total : 0;
                        $pr_p = ($total > 0) ? ($p * 100) / $total : 0;
                        $pr_s = ($total > 0) ? ($s * 100) / $total : 0;
                    ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold"><i class="bi bi-person-circle me-1"></i><?php echo $resp['nombre_responsable']; ?></small>
                            <small class="text-muted"><?php echo $total; ?> Asignadas</small>
                        </div>
                        <div class="progress shadow-sm">
                            <?php if($total > 0): ?>
                                <div class="progress-bar" style="width:<?php echo $pr_r; ?>%; background-color: var(--resuelto);"><?php echo ($pr_r > 10) ? round($pr_r)."%" : ""; ?></div>
                                <div class="progress-bar" style="width:<?php echo $pr_p; ?>%; background-color: var(--proceso);"><?php echo ($pr_p > 10) ? round($pr_p)."%" : ""; ?></div>
                                <div class="progress-bar text-dark" style="width:<?php echo $pr_s; ?>%; background-color: var(--pendiente);"><?php echo ($pr_s > 10) ? round($pr_s)."%" : ""; ?></div>
                            <?php else: ?>
                                <div class="progress-bar bg-light text-muted w-100">SIN ASIGNACIONES</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer bg-white py-2 text-center border-top-0">
                    <div class="d-flex justify-content-center gap-4 small text-muted fw-bold">
                        <span><span class="legend-dot" style="background: var(--resuelto);"></span> Resuelto</span>
                        <span><span class="legend-dot" style="background: var(--proceso);"></span> En Proceso</span>
                        <span><span class="legend-dot" style="background: var(--pendiente);"></span> Pendiente</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
let miChart = null;

function cargarGraficaResponsable(id) {
    document.getElementById('wrapper-selector').classList.add('d-none');
    document.getElementById('wrapper-grafica-resp').classList.remove('d-none');

    const sel = document.getElementById('selectResp');
    document.getElementById('nombreCargado').innerText = sel.options[sel.selectedIndex].text;

    const datosResp = <?php echo json_encode($matriz_resp); ?>;
    const d = datosResp[id] || { 'Resuelto': 0, 'En Proceso': 0, 'Pendiente': 0 };
    
    const r = parseInt(d['Resuelto'] || 0), p = parseInt(d['En Proceso'] || 0), s = parseInt(d['Pendiente'] || 0);
    const total = r + p + s;

    // Calcular porcentajes para el texto
    const por_r = total > 0 ? ((r * 100) / total).toFixed(1) : 0;
    const por_p = total > 0 ? ((p * 100) / total).toFixed(1) : 0;
    const por_s = total > 0 ? ((s * 100) / total).toFixed(1) : 0;

    if (miChart) miChart.destroy();
    const ctx = document.getElementById('chartRespDinamico').getContext('2d');
    
    miChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Resueltas'],
            datasets: [{ data: [s, p, r], backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a'], borderWidth: 2 }]
        },
        options: { 
            maintainAspectRatio: false, cutout: '75%', 
            plugins: { legend: { display: false } } 
        },
        plugins: [{
            id: 'centerText',
            beforeDraw: function(chart) {
                var width = chart.width, height = chart.height, ctx = chart.ctx;
                ctx.restore();
                ctx.font = "bold 2em sans-serif";
                ctx.textBaseline = "middle";
                ctx.fillStyle = "#164377";
                var text = total, textX = Math.round((width - ctx.measureText(text).width) / 2), textY = height / 2;
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
    });

    document.getElementById('statsDetalleResp').innerHTML = `
        <div class="d-flex justify-content-between small mb-1"><span>Pendientes (${por_s}%)</span> <b>${s}</b></div>
        <div class="d-flex justify-content-between small mb-1"><span>En Proceso (${por_p}%)</span> <b>${p}</b></div>
        <div class="d-flex justify-content-between small mb-1"><span>Resueltas (${por_r}%)</span> <b>${r}</b></div>
    `;
}
</script>
</body>
</html>