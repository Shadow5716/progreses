<?php
session_start();
require_once 'includes/dbconnection.php';

$mensaje = '';

try {
    $stmtDept = $pdo->query("SELECT * FROM ipauma_departamentos ORDER BY nombre ASC");
    $departamentos = $stmtDept->fetchAll(PDO::FETCH_ASSOC);

    $objetivos_all = $pdo->query("SELECT * FROM ipauma_objetivos ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);
    $actividades_all = $pdo->query("SELECT * FROM ipauma_actividades ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar datos: Verifique que las tablas existan. " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_ipauma'])) {
    $dep_id = intval($_POST['departamento_id']);
    $obj_id = intval($_POST['objetivo_id']);
    $act_id = intval($_POST['actividad_id']);
    $parroquia = trim($_POST['parroquia']);
    $fecha_ejecucion = !empty($_POST['fecha_ejecucion']) ? $_POST['fecha_ejecucion'] : null;
    $descripcion = trim($_POST['descripcion']);
    
    try {
        $pdo->beginTransaction();

        $insert = "INSERT INTO ipauma_solicitudes (departamento_id, objetivo_id, actividad_id, parroquia, descripcion, estado, fecha, fecha_ejecucion) 
                   VALUES (:dep, :obj, :act, :parroquia, :desc, 'Pendiente', NOW(), :fecha_ejecucion)";
        $stmt = $pdo->prepare($insert);
        $stmt->execute([
            ':dep' => $dep_id,
            ':obj' => $obj_id,
            ':act' => $act_id,
            ':parroquia' => $parroquia,
            ':desc' => $descripcion,
            ':fecha_ejecucion' => $fecha_ejecucion
        ]);
        
        $solicitud_id = $pdo->lastInsertId();

        // Procesamiento de Imágenes
        if (!empty($_FILES['imagenes']['name'][0])) {
            $uploadDir = 'uploads/ipauma/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                    $nombre_original = basename($_FILES['imagenes']['name'][$key]);
                    $nombre_seguro = uniqid('img_') . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $nombre_original);
                    $ruta_destino = $uploadDir . escapeshellcmd($nombre_seguro);
                    
                    if (move_uploaded_file($tmp_name, $ruta_destino)) {
                        $stmtImg = $pdo->prepare("INSERT INTO ipauma_imagenes (solicitud_id, ruta_archivo, nombre_archivo) VALUES (?, ?, ?)");
                        $stmtImg->execute([$solicitud_id, $ruta_destino, $nombre_original]);
                    }
                }
            }
        }

        $pdo->commit();

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                window.onload = function() {
                    Swal.fire({
                        title: '¡Registrado!',
                        text: 'Acabas de realizar la nueva solicitud con éxito.',
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        window.location.href='ipauma_dashboard.php';
                    });
                };
              </script>";
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $mensaje = "<div class='alert alert-danger'>Error al guardar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud IPAUMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important; }
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 40px; margin-bottom: 40px; }
        .navbar-custom { border: 1px solid; border-top-left-radius: -50px; border-top-right-radius: 0; background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important; padding: 0.8rem 1rem; border-color: #000000; }
        
        /* Estilos del Dropzone */
        .dropzone-area { border: 2px dashed #164377; background: #f8f9fa; cursor: pointer; transition: 0.3s; padding: 30px; border-radius: 8px; text-align: center; }
        .dropzone-area.dragover { background: #e9ecef; border-color: #d2005a; }
        .img-preview { position: relative; width: 100px; height: 100px; overflow: hidden; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .img-preview img { width: 100%; height: 100%; object-fit: cover; }
        .img-remove { position: absolute; top: 2px; right: 2px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
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
                <li class="nav-item me-2"><a href="dashboard.php" class="btn btn-outline-light border-0"><i class="bi bi-house me-1"></i> Volver a Gestión</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 form-container">
            <h3 class="mb-4 text-center fw-bold text-primary">Registrar Nuevo Reporte IPAUPMA</h3>
            <?= $mensaje ?>
            
            <form method="POST" enctype="multipart/form-data">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Parroquia</label>
                        <select name="parroquia" class="form-select" required>
                            <option value="">-- Seleccione una Parroquia --</option>
                            <option value="ANTONIO BORJAS ROMERO">ANTONIO BORJAS ROMERO</option>
                            <option value="FRANCISCO EUGENIO BUSTAMANTE">FRANCISCO EUGENIO BUSTAMANTE</option>
                            <option value="BOLIVAR">BOLIVAR</option>
                            <option value="COQUIVACOA">COQUIVACOA</option>
                            <option value="CACIQUE MARA">CACIQUE MARA</option>
                            <option value="CECILIO ACOSTA">CECILIO ACOSTA</option>
                            <option value="CHIQUINQUIRA">CHIQUINQUIRA</option>
                            <option value="SAN ISIDRO">SAN ISIDRO</option>
                            <option value="JUANA DE ÁVILA">JUANA DE ÁVILA</option>
                            <option value="CRISTO DE ARANZA">CRISTO DE ARANZA</option>
                            <option value="LUIS HURTADO HIGUERA">LUIS HURTADO HIGUERA</option>
                            <option value="RAÚL LEONI">RAÚL LEONI</option>
                            <option value="OLEGARIO VILLALOBOS">OLEGARIO VILLALOBOS</option>
                            <option value="CARRACIOLO PARRA PÉREZ">CARRACIOLO PARRA PÉREZ</option>
                            <option value="IDELFONSO VÁSQUEZ">IDELFONSO VÁSQUEZ</option>
                            <option value="MANUEL DANIGNO">MANUEL DANIGNO</option>
                            <option value="SANTA LUCIA">SANTA LUCIA</option>
                            <option value="VENANCIO PULGAR">VENANCIO PULGAR</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha de Ejecución</label>
                        <input type="date" name="fecha_ejecucion" class="form-control" required>
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
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Actividad</label>
                    <select id="actividad_id" name="actividad_id" class="form-select" required disabled>
                        <option value="">-- Primero seleccione un objetivo --</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Descripción del Reporte</label>
                    <textarea name="descripcion" class="form-control" rows="4" placeholder="Detalle la solicitud o el problema..." required></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Imágenes de Respaldo</label>
                    <div id="dropzone" class="dropzone-area">
                        <i class="bi bi-images display-4 text-secondary"></i>
                        <p class="mt-2 mb-0 text-muted">Haz clic, arrastra imágenes aquí o presiona Ctrl+V para pegar</p>
                        <input type="file" id="fileInput" name="imagenes[]" accept="image/*" multiple class="d-none">
                    </div>
                    <div id="preview-container" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="ipauma_dashboard.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="guardar_ipauma" class="btn btn-success fw-bold px-4">Guardar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Lógica de Selects Dinámicos
const objetivosBase = <?= json_encode($objetivos_all) ?>;
const actividadesBase = <?= json_encode($actividades_all) ?>;

document.getElementById('departamento_id').addEventListener('change', function() {
    let dep_id = this.value;
    let objSelect = document.getElementById('objetivo_id');
    let actSelect = document.getElementById('actividad_id');
    objSelect.innerHTML = '<option value="">-- Seleccione un Objetivo --</option>';
    actSelect.innerHTML = '<option value="">-- Primero seleccione un objetivo --</option>';
    actSelect.disabled = true;
    if(dep_id) {
        objetivosBase.filter(o => o.departamento_id == dep_id).forEach(o => {
            const option = document.createElement('option'); option.value = o.id; option.textContent = o.descripcion; objSelect.appendChild(option);
        });
        objSelect.disabled = false;
    } else { objSelect.disabled = true; }
});

document.getElementById('objetivo_id').addEventListener('change', function() {
    let obj_id = this.value;
    let actSelect = document.getElementById('actividad_id');
    actSelect.innerHTML = '<option value="">-- Seleccione una Actividad --</option>';
    if(obj_id) {
        actividadesBase.filter(a => a.objetivo_id == obj_id).forEach(a => {
            const option = document.createElement('option'); option.value = a.id; option.textContent = a.descripcion; actSelect.appendChild(option);
        });
        actSelect.disabled = false;
    } else { actSelect.disabled = true; }
});

// Lógica de Imágenes (Drag, Drop, Paste, Select)
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const previewContainer = document.getElementById('preview-container');
let currentFiles = new DataTransfer();

dropzone.addEventListener('click', () => fileInput.click());

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

// Evita que la foto se pegue si estás escribiendo dentro de la caja de descripción
document.addEventListener('paste', (e) => {
    if (e.target.nodeName === 'TEXTAREA' || e.target.nodeName === 'INPUT') return;
    if(e.clipboardData.files.length > 0) {
        handleFiles(e.clipboardData.files);
    }
});

fileInput.addEventListener('change', () => handleFiles(fileInput.files));

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if(file.type.startsWith('image/')) {
            currentFiles.items.add(file);
            renderPreview(file);
        }
    });
    fileInput.files = currentFiles.files; 
}

function renderPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        const div = document.createElement('div');
        div.className = 'img-preview';
        div.innerHTML = `
            <img src="${e.target.result}" alt="Preview">
            <button type="button" class="img-remove" onclick="removeFile(this, '${file.name}')"><i class="bi bi-x"></i></button>
        `;
        previewContainer.appendChild(div);
    };
    reader.readAsDataURL(file);
}

window.removeFile = function(btn, fileName) {
    const newDt = new DataTransfer();
    Array.from(currentFiles.files).forEach(file => {
        if(file.name !== fileName) newDt.items.add(file);
    });
    currentFiles = newDt;
    fileInput.files = currentFiles.files;
    btn.parentElement.remove();
}
</script>
</body>
</html>