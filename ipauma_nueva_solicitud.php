<?php
session_start();
require_once 'includes/dbconnection.php';

$mensaje = '';

try {
    // Cargar Departamentos correctamente usando PDO
    $stmtDept = $pdo->query("SELECT * FROM ipauma_departamentos ORDER BY nombre ASC");
    $departamentos = $stmtDept->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar departamentos: Verifique que la tabla exista. " . $e->getMessage());
}

// Procesar guardado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_ipauma'])) {
    $dep_id = intval($_POST['departamento_id']);
    $obj_id = intval($_POST['objetivo_id']);
    $act_id = intval($_POST['actividad_id']);
    $parroquia = trim($_POST['parroquia']);
    $oficio = trim($_POST['oficio']);
    $descripcion = trim($_POST['descripcion']);
    
    try {
        $insert = "INSERT INTO ipauma_solicitudes (departamento_id, objetivo_id, actividad_id, parroquia, oficio, descripcion, estado, fecha) 
                   VALUES (:dep, :obj, :act, :parroquia, :oficio, :desc, 'Pendiente', NOW())";
        $stmt = $pdo->prepare($insert);
        $stmt->execute([
            ':dep' => $dep_id,
            ':obj' => $obj_id,
            ':act' => $act_id,
            ':parroquia' => $parroquia,
            ':oficio' => $oficio,
            ':desc' => $descripcion
        ]);
        echo "<script>alert('Solicitud IPAUMA registrada con éxito'); window.location.href='ipauma_dashboard.php';</script>";
        exit;
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error al guardar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Solicitud IPAUMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 40px; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 form-container">
            <h3 class="mb-4 text-center fw-bold text-primary">Registrar Nuevo Reporte IPAUMA</h3>
            <?= $mensaje ?>
            
            <form method="POST">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Oficio N°</label>
                        <input type="text" name="oficio" class="form-control" placeholder="Ej: 001-2026" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Parroquia</label>
                        <input type="text" name="parroquia" class="form-control" placeholder="Nombre de la parroquia" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Departamento</label>
                    <select id="departamento_id" name="departamento_id" class="form-select" required>
                        <option value="">-- Seleccione un Departamento --</option>
                        <?php foreach($departamentos as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Objetivo</label>
                    <select id="objetivo_id" name="objetivo_id" class="form-select" required disabled>
                        <option value="">-- Primero seleccione un departamento --</option>
                    </select>
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Las opciones se habilitarán al elegir el Departamento.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Actividad</label>
                    <select id="actividad_id" name="actividad_id" class="form-select" required disabled>
                        <option value="">-- Primero seleccione un objetivo --</option>
                    </select>
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Las opciones se habilitarán al elegir el Objetivo.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Descripción del Reporte</label>
                    <textarea name="descripcion" class="form-control" rows="4" placeholder="Detalle la solicitud o el problema..." required></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="ipauma_dashboard.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="guardar_ipauma" class="btn btn-success fw-bold px-4">Guardar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// El código AJAX original mantenido intacto pero mejorado para Bootstrap
document.getElementById('departamento_id').addEventListener('change', function() {
    let dep_id = this.value;
    let objSelect = document.getElementById('objetivo_id');
    let actSelect = document.getElementById('actividad_id');
    
    objSelect.innerHTML = '<option value="">Cargando...</option>';
    actSelect.innerHTML = '<option value="">-- Primero seleccione un objetivo --</option>';
    actSelect.disabled = true;

    if(dep_id) {
        let formData = new FormData();
        formData.append('accion', 'get_objetivos');
        formData.append('departamento_id', dep_id);

        fetch('ipauma_ajax.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            objSelect.innerHTML = '<option value="">-- Seleccione un Objetivo --</option>';
            data.forEach(item => {
                objSelect.innerHTML += `<option value="${item.id}">${item.descripcion}</option>`;
            });
            objSelect.disabled = false;
        });
    } else {
        objSelect.innerHTML = '<option value="">-- Primero seleccione un departamento --</option>';
        objSelect.disabled = true;
    }
});

document.getElementById('objetivo_id').addEventListener('change', function() {
    let obj_id = this.value;
    let actSelect = document.getElementById('actividad_id');
    
    actSelect.innerHTML = '<option value="">Cargando...</option>';

    if(obj_id) {
        let formData = new FormData();
        formData.append('accion', 'get_actividades');
        formData.append('objetivo_id', obj_id);

        fetch('ipauma_ajax.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            actSelect.innerHTML = '<option value="">-- Seleccione una Actividad --</option>';
            data.forEach(item => {
                actSelect.innerHTML += `<option value="${item.id}">${item.descripcion}</option>`;
            });
            actSelect.disabled = false;
        });
    } else {
        actSelect.innerHTML = '<option value="">-- Primero seleccione un objetivo --</option>';
        actSelect.disabled = true;
    }
});
</script>
</body>
</html>