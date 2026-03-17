<?php
session_start();
require_once 'includes/dbconnection.php'; // Asegúrate de que la ruta sea correcta

// --- PROCESAMIENTO DEL FORMULARIO (Cuando se le da a Confirmar) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $id_ente = $_POST['id_ente'];
        $id_area = $_POST['id_area'];
        $id_responsable = $_POST['id_responsable'];
        $tipo_actividad = $_POST['tipo_actividad'];
        $descripcion = $_POST['descripcion'];
        $nro_oficio = strtoupper($_POST['nro_oficio']); // Aseguramos mayúsculas
        $fecha = date('Y-m-d');
        $estatus = 'Pendiente'; // Estatus automático

        // 1. Generación del Nro de Comisión (SOL-0000000-AÑO)
        $year = date('Y');
        $stmt_count = $pdo->query("SELECT COUNT(*) FROM solicitudes");
        $conteo = $stmt_count->fetchColumn() + 1; // El próximo ingreso
        
        // str_pad rellena con ceros a la izquierda hasta tener 7 dígitos
        $nro_comision = "SOL-" . str_pad($conteo, 7, "0", STR_PAD_LEFT) . "-" . $year;

        // 2. Inserción en la base de datos
        $sql_insert = "INSERT INTO solicitudes (fecha, id_ente, id_area, id_responsable, tipo_actividad, descripcion, estatus, nro_oficio, nro_comision) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$fecha, $id_ente, $id_area, $id_responsable, $tipo_actividad, $descripcion, $estatus, $nro_oficio, $nro_comision]);

        // Redirigir al index con mensaje de éxito
        header("Location: index.php?msg=success");
        exit;

    } catch (PDOException $e) {
        $error_msg = "Error al guardar la solicitud: " . $e->getMessage();
    }
}

// --- OBTENER DATOS PARA LOS SELECTS ---
$entes = $pdo->query("SELECT * FROM entes ORDER BY nombre_ente ASC")->fetchAll();
$areas = $pdo->query("SELECT * FROM areas ORDER BY nombre_area ASC")->fetchAll();
$responsables = $pdo->query("SELECT * FROM responsables ORDER BY nombre_responsable ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
        <link rel="shortcut icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
    <link rel="icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud - Dirección de Tecnología</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fc; }
        .form-card { background: white; border-radius: 12px; border: 1px solid #e3e6f0; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?> <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <?php if(isset($error_msg)): ?>
                    <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <div class="form-card shadow-sm p-4 p-md-5">
                    <h3 class="mb-4 text-primary fw-bold border-bottom pb-2">Registro de Nueva Solicitud</h3>
                    
                    <form action="nueva_solicitud.php" method="POST" id="formSolicitud">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">1. Dirección / Ente que solicita</label>
                            <select name="id_ente" class="form-select" required>
                                <option value="" selected disabled>-- Seleccione el ente solicitante --</option>
                                <?php foreach($entes as $ente): ?>
                                    <option value="<?php echo $ente['id_ente']; ?>">
                                        <?php echo htmlspecialchars($ente['nombre_ente']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">2. Área de Solicitud</label>
                            <select name="id_area" id="id_area" class="form-select" required onchange="filtrarResponsables()">
                                <option value="" selected disabled>-- Seleccione el área correspondiente --</option>
                                <?php foreach($areas as $area): ?>
                                    <option value="<?php echo $area['id_area']; ?>">
                                        <?php echo htmlspecialchars($area['nombre_area']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">3. Personal Responsable</label>
                            <select name="id_responsable" id="id_responsable" class="form-select" required disabled>
                                <option value="" selected disabled>-- Primero seleccione un área --</option>
                                <?php foreach($responsables as $resp): ?>
                                    <option value="<?php echo $resp['id_responsable']; ?>" data-area="<?php echo $resp['id_area']; ?>">
                                        <?php echo htmlspecialchars($resp['nombre_responsable']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Las opciones se habilitarán al elegir el Área.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">4. Tipo de Actividad</label>
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_actividad" id="tipo1" value="MANTENIMIENTO PREDICTIVO" required>
                                    <label class="form-check-label" for="tipo1">Mantenimiento Predictivo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_actividad" id="tipo2" value="MANTENIMIENTO PREVENTIVO">
                                    <label class="form-check-label" for="tipo2">Mantenimiento Preventivo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_actividad" id="tipo3" value="MANTENIMIENTO CORRECTIVO">
                                    <label class="form-check-label" for="tipo3">Mantenimiento Correctivo</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-7 mb-4">
                                <label class="form-label fw-bold">5. Descripción de la Solicitud</label>
                                <textarea name="descripcion" class="form-control" rows="3" maxlength="250" placeholder="Especifique el motivo de la solicitud (Máx 250 caracteres)..." required></textarea>
                            </div>
                            
                            <div class="col-md-5 mb-4">
                                <label class="form-label fw-bold">6. Número de Oficio</label>
                                <input type="text" name="nro_oficio" id="nro_oficio" class="form-control text-uppercase" maxlength="20" placeholder="Ej: CLPP-00005-" required>
                                <small class="text-muted d-block mt-1">Escriba el segundo guion (-) para autocompletar el año.</small>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-2"></i>Confirmar y Generar Solicitud</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    
    <script>
        // 1. Lógica para filtrar responsables según el área seleccionada
        function filtrarResponsables() {
            const areaSelect = document.getElementById('id_area');
            const respSelect = document.getElementById('id_responsable');
            const areaId = areaSelect.value;
            
            // Habilitar el select de responsables y reiniciar valor
            respSelect.disabled = false;
            respSelect.value = "";
            respSelect.options[0].text = "-- Seleccione al responsable --";

            // Recorrer las opciones y mostrar solo las que coinciden con el Área
            Array.from(respSelect.options).forEach((option, index) => {
                if (index === 0) return; // Saltar el placeholder
                
                if (option.getAttribute('data-area') === areaId) {
                    option.style.display = 'block'; // Mostrar si coincide
                } else {
                    option.style.display = 'none';  // Ocultar si no coincide
                }
            });
        }

        // 2. Lógica para autocompletar el año en el Número de Oficio
        const oficioInput = document.getElementById('nro_oficio');
        
        oficioInput.addEventListener('input', function(e) {
            let valor = e.target.value.toUpperCase();
            
            // Contamos cuántos guiones hay en el texto
            let guiones = (valor.match(/-/g) || []).length;

            // Si el usuario acaba de escribir el segundo guion y el texto no termina ya en el año
            const yearActual = new Date().getFullYear().toString();
            
            if (guiones === 2 && valor.endsWith('-') && !valor.endsWith(yearActual)) {
                valor = valor + yearActual;
            }
            
            e.target.value = valor;
        });
    </script>
</body>
</html>