<?php
session_start();
require_once 'includes/dbconnection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    die("ID no válido.");
}

// Guardar los cambios
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_ipauma'])) {
    $parroquia = trim($_POST['parroquia']);
    $descripcion = trim($_POST['descripcion']);
    $estado = trim($_POST['estado']);
    $departamento_id = intval($_POST['departamento_id']);
    $objetivo_id = intval($_POST['objetivo_id']);
    $actividad_id = intval($_POST['actividad_id']);
    
    try {
        $update = "UPDATE ipauma_solicitudes SET 
                    parroquia = :parroquia, 
                    descripcion = :desc, 
                    estado = :estado,
                    departamento_id = :dept_id,
                    objetivo_id = :obj_id,
                    actividad_id = :act_id
                   WHERE id = :id";
        $stmtUpdate = $pdo->prepare($update);
        $stmtUpdate->execute([
            ':parroquia' => $parroquia,
            ':desc' => $descripcion,
            ':estado' => $estado,
            ':dept_id' => $departamento_id,
            ':obj_id' => $objetivo_id,
            ':act_id' => $actividad_id,
            ':id' => $id
        ]);
        
        // CORRECCIÓN: Estructura garantizada para ejecutar SweetAlert2 y redireccionar
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Acabas de realizar el cambio en el reporte con éxito.',
                    icon: 'success',
                    confirmButtonColor: '#164377',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    window.location.href='ipauma_dashboard.php';
                });
            </script>
        </body>
        </html>";
        exit; // Detenemos la ejecución aquí para que no cargue el formulario de nuevo
              
    } catch (PDOException $e) {
        echo "<script>alert('Error al actualizar: " . $e->getMessage() . "');</script>";
    }
}

// Obtener los datos actuales y catálogos
try {
    $stmt = $pdo->prepare("SELECT * FROM ipauma_solicitudes WHERE id = ?");
    $stmt->execute([$id]);
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reporte) {
        die("Reporte no encontrado.");
    }

    $deptos = $pdo->query("SELECT * FROM ipauma_departamentos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    $objetivos_all = $pdo->query("SELECT * FROM ipauma_objetivos ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);
    $actividades_all = $pdo->query("SELECT * FROM ipauma_actividades ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);

    $parroquias = [
        'ANTONIO BORJAS ROMERO', 'FRANCISCO EUGENIO BUSTAMANTE', 'BOLIVAR', 'COQUIVACOA', 
        'CACIQUE MARA', 'CECILIO ACOSTA', 'CHIQUINQUIRA', 'SAN ISIDRO', 'JUANA DE ÁVILA', 
        'CRISTO DE ARANZA', 'LUIS HURTADO HIGUERA', 'RAÚL LEONI', 'OLEGARIO VILLALOBOS', 
        'CARRACIOLO PARRA PÉREZ', 'IDELFONSO VÁSQUEZ', 'MANUEL DANIGNO', 'SANTA LUCIA', 'VENANCIO PULGAR'
    ];

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reporte IPAUMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important; min-height: 100vh; padding: 20px; }
        .container-main { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); max-width: 800px; margin: 40px auto; }
        h2 { color: #164377; font-weight: bold; margin-bottom: 25px; border-bottom: 3px solid #d2005a; padding-bottom: 10px; }
        .form-label { font-weight: bold; color: #444; }
    </style>
</head>
<body>

<div class="container-main">
    <h2><i class="bi bi-pencil-square me-2"></i>Editar Reporte #<?= $id ?></h2>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Estado del Reporte</label>
                <select name="estado" class="form-select" required>
                    <option value="Pendiente" <?= ($reporte['estado'] == 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En Proceso" <?= ($reporte['estado'] == 'En Proceso') ? 'selected' : '' ?>>En Proceso</option>
                    <option value="Resuelto" <?= ($reporte['estado'] == 'Resuelto') ? 'selected' : '' ?>>Resuelto</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Parroquia</label>
                <select name="parroquia" class="form-select" required>
                    <?php foreach ($parroquias as $p): ?>
                        <option value="<?= $p ?>" <?= (strtoupper($reporte['parroquia']) == $p) ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Departamento</label>
            <select name="departamento_id" id="departamento_id" class="form-select" required onchange="filtrarObjetivos()">
                <option value="">Seleccione un Departamento</option>
                <?php foreach ($deptos as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= ($reporte['departamento_id'] == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Objetivo</label>
            <select name="objetivo_id" id="objetivo_id" class="form-select" required onchange="filtrarActividades()">
                <option value="">Seleccione un Objetivo</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Actividad</label>
            <select name="actividad_id" id="actividad_id" class="form-select" required>
                <option value="">Seleccione una Actividad</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label">Descripción / Observaciones</label>
            <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($reporte['descripcion']) ?></textarea>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="ipauma_dashboard.php" class="btn btn-secondary px-4"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
            <button type="submit" name="actualizar_ipauma" class="btn btn-primary px-4 fw-bold" style="background-color: #164377; border: none;">
                <i class="bi bi-check-circle me-1"></i> Actualizar Reporte
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Datos desde PHP
const objetivosBase = <?= json_encode($objetivos_all) ?>;
const actividadesBase = <?= json_encode($actividades_all) ?>;

// Valores iniciales para la edición
const iniciales = {
    objetivo_id: "<?= $reporte['objetivo_id'] ?>",
    actividad_id: "<?= $reporte['actividad_id'] ?>"
};

function filtrarObjetivos(usarInicial = false) {
    const deptoId = document.getElementById('departamento_id').value;
    const selectObj = document.getElementById('objetivo_id');
    const selectAct = document.getElementById('actividad_id');
    
    selectObj.innerHTML = '<option value="">Seleccione un Objetivo</option>';
    selectAct.innerHTML = '<option value="">Seleccione una Actividad</option>';

    if (deptoId) {
        const filtrados = objetivosBase.filter(o => o.departamento_id == deptoId);
        filtrados.forEach(o => {
            const option = document.createElement('option');
            option.value = o.id;
            option.textContent = o.descripcion;
            if (usarInicial && o.id == iniciales.objetivo_id) option.selected = true;
            selectObj.appendChild(option);
        });
        
        if (usarInicial) filtrarActividades(true);
    }
}

function filtrarActividades(usarInicial = false) {
    const objId = document.getElementById('objetivo_id').value;
    const selectAct = document.getElementById('actividad_id');
    
    selectAct.innerHTML = '<option value="">Seleccione una Actividad</option>';

    if (objId) {
        const filtrados = actividadesBase.filter(a => a.objetivo_id == objId);
        filtrados.forEach(a => {
            const option = document.createElement('option');
            option.value = a.id;
            option.textContent = a.descripcion;
            if (usarInicial && a.id == iniciales.actividad_id) option.selected = true;
            selectAct.appendChild(option);
        });
    }
}

window.onload = function() {
    filtrarObjetivos(true);
};
</script>

</body>
</html>