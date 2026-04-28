<?php
session_start();
require_once 'includes/dbconnection.php';
if (!isset($_GET['id'])) { header('location:imtcuma_dashboard.php'); exit; }
$vehiculo_id = $_GET['id'];

$mensaje = '';

// Procesar el formulario cuando se presiona "Guardar Evaluación"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $total_pts = 0;
    
    // Lista de todos los name="" de los inputs de evaluación
    $campos = [
        'frenos_conv', 'salida_emerg', 'extintor', 'cono', 'freno_emerg', 'limpiaparabrisas',
        'latoneria', 'pintura', 'piso', 'asientos', 'ventanas_lat', 'vidrio_del', 'vidrio_tras',
        'parachoque_del', 'parachoque_tras', 'caucho_del', 'caucho_tras', 'escape',
        'luz_alta', 'luz_baja', 'luz_stop', 'luz_dir', 'luz_retro', 'luz_inter', 'luz_int',
        'tablero_temp', 'tablero_amp', 'tablero_aceite', 'tablero_vel', 'espejo_ext', 'espejo_int'
    ];

    // Sumar los valores enviados
    foreach ($campos as $campo) {
        if (isset($_POST[$campo]) && is_numeric($_POST[$campo])) {
            $total_pts += (int)$_POST[$campo];
        }
    }

    // Calcular el estado final adaptado a un máximo ideal de 180
    // Ajuste de escala: Buena (>= 120), Regular (90 - 119), Mala (< 90)
    if ($total_pts >= 120) {
        $estado_final = "BUENA";
    } elseif ($total_pts >= 90) {
        $estado_final = "REGULAR";
    } else {
        $estado_final = "MALA";
    }

    // Actualizar en la base de datos
    try {
        $sql = "UPDATE imtcuma_vehiculos SET puntaje_evaluacion = :puntaje, estado_evaluacion = :estado WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':puntaje' => $total_pts,
            ':estado' => $estado_final,
            ':id' => $vehiculo_id
        ]);
        $mensaje = "<div class='alert alert-success fw-bold text-center mt-3 shadow-sm'>¡Evaluación guardada con éxito! Puntaje: $total_pts ($estado_final).</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger text-center mt-3'>Error al guardar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación de Unidad - IMTCUMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-gradient-custom { background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important; color: white;}
        .seccion-titulo { background-color: #f8f9fa; border-left: 5px solid rgba(210, 0, 90, 1); padding: 5px 15px; margin-top: 20px; font-weight: bold;}
        .eval-select { cursor: pointer; border: 1px solid #ced4da; }
        .eval-select:focus { border-color: rgba(210, 0, 90, 0.5); box-shadow: 0 0 0 0.2rem rgba(210, 0, 90, 0.25); }
    </style>
</head>
<body class="bg-light pb-5">

<div class="container mt-4">
    <a href="imtcuma_dashboard.php" class="btn btn-secondary mb-3">Volver</a>
    
    <?= $mensaje ?>

    <div class="card shadow">
        <div class="card-header bg-gradient-custom">
            <h4 class="mb-0">Evaluación de Unidad Técnica</h4>
        </div>
        <div class="card-body">
            <form id="formEvaluacion" method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="seccion-titulo">Seguridad</div>
                        <?= generarFilaEval('Frenos Convencionales', 'frenos_conv') ?>
                        <?= generarFilaEval('Salida de Emergencia', 'salida_emerg') ?>
                        <?= generarFilaEval('Extintor de Incendios', 'extintor') ?>
                        <?= generarFilaEval('Cono o Triangulo', 'cono') ?>
                        <?= generarFilaEval('Freno de Emergencia', 'freno_emerg') ?>
                        <?= generarFilaEval('Limpiaparabrisas', 'limpiaparabrisas') ?>

                        <div class="seccion-titulo">Carrocería</div>
                        <?= generarFilaEval('Latonería', 'latoneria') ?>
                        <?= generarFilaEval('Pintura', 'pintura') ?>
                        <?= generarFilaEval('Piso', 'piso') ?>

                        <div class="seccion-titulo">Asientos y Ventanas</div>
                        <?= generarFilaEval('Asientos', 'asientos') ?>
                        <?= generarFilaEval('Ventanas Laterales', 'ventanas_lat') ?>
                        <?= generarFilaEval('Vidrio Delantero', 'vidrio_del') ?>
                        <?= generarFilaEval('Vidrio Trasero', 'vidrio_tras') ?>

                        <div class="seccion-titulo">Parachoques</div>
                        <?= generarFilaEval('Delantero', 'parachoque_del') ?>
                        <?= generarFilaEval('Trasero', 'parachoque_tras') ?>
                    </div>

                    <div class="col-md-6">
                        <div class="seccion-titulo">Cauchos y Escape</div>
                        <?= generarFilaEval('Cauchos Delanteros', 'caucho_del') ?>
                        <?= generarFilaEval('Cauchos Traseros', 'caucho_tras') ?>
                        <?= generarFilaEval('Sistema de Escape', 'escape') ?>

                        <div class="seccion-titulo">Iluminación</div>
                        <?= generarFilaEval('Altas', 'luz_alta') ?>
                        <?= generarFilaEval('Bajas', 'luz_baja') ?>
                        <?= generarFilaEval('Stop', 'luz_stop') ?>
                        <?= generarFilaEval('Direccionales', 'luz_dir') ?>
                        <?= generarFilaEval('Marcha Atrás', 'luz_retro') ?>
                        <?= generarFilaEval('Intermitentes', 'luz_inter') ?>
                        <?= generarFilaEval('Iluminación Interior', 'luz_int') ?>

                        <div class="seccion-titulo">Tablero</div>
                        <?= generarFilaEval('Temperatura', 'tablero_temp') ?>
                        <?= generarFilaEval('Amperímetro', 'tablero_amp') ?>
                        <?= generarFilaEval('Presión de Aceite', 'tablero_aceite') ?>
                        <?= generarFilaEval('Velocímetro', 'tablero_vel') ?>

                        <div class="seccion-titulo">Espejos y Retrovisores</div>
                        <?= generarFilaEval('Exteriores', 'espejo_ext') ?>
                        <?= generarFilaEval('Interiores', 'espejo_int') ?>
                    </div>
                </div>

                <hr class="mt-4">
                <div class="row text-center mt-4 p-3 rounded" style="background: #e9ecef;">
                    <div class="col-md-6">
                        <h4>PUNTAJE TOTAL: <span id="total_pts" class="text-primary fw-bold">0</span> / 180</h4>
                    </div>
                    <div class="col-md-6">
                        <h4>ESTADO: <span id="estado_final" class="badge bg-secondary">PENDIENTE</span></h4>
                    </div>
                    <small class="text-muted mt-2">Buena: 120 o más | Regular: 90 - 119 | Mala: Menos de 90</small>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn text-white bg-gradient-custom px-5 py-2 fw-bold">Guardar Evaluación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
function generarFilaEval($label, $name) {
    // Generar las opciones del 1 al 10 dinámicamente
    $opciones = "<option value='' disabled selected>--</option>";
    for ($i = 1; $i <= 10; $i++) {
        $opciones .= "<option value='$i'>$i</option>";
    }

    return "
    <div class='d-flex justify-content-between align-items-center mb-2 border-bottom pb-1'>
        <span class='text-secondary fw-semibold'>$label</span>
        <div style='width: 90px;'>
            <select name='$name' class='form-select form-select-sm eval-select text-center fw-bold' required>
                $opciones
            </select>
        </div>
    </div>";
}
?>

<script>
    // Escuchar cambios en los selects en lugar de los radios
    document.querySelectorAll('.eval-select').forEach(select => {
        select.addEventListener('change', calcularTotal);
    });

    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('.eval-select').forEach(select => {
            if(select.value !== '') {
                total += parseInt(select.value);
            }
        });
        
        document.getElementById('total_pts').innerText = total;
        
        let estadoBadge = document.getElementById('estado_final');
        
        // Nueva escala basada en 180
        if (total >= 120) {
            estadoBadge.innerText = "BUENA";
            estadoBadge.className = "badge bg-success";
        } else if (total >= 90) {
            estadoBadge.innerText = "REGULAR";
            estadoBadge.className = "badge bg-warning text-dark";
        } else {
            estadoBadge.innerText = "MALA";
            estadoBadge.className = "badge bg-danger";
        }
    }
</script>
</body>
</html>