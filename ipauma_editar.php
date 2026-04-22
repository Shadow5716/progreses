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
    $fecha_ejecucion = !empty($_POST['fecha_ejecucion']) ? $_POST['fecha_ejecucion'] : null;
    $departamento_id = intval($_POST['departamento_id']);
    $objetivo_id = intval($_POST['objetivo_id']);
    $actividad_id = intval($_POST['actividad_id']);
    
    try {
        $pdo->beginTransaction();

        $update = "UPDATE ipauma_solicitudes SET 
                    parroquia = :parroquia, 
                    descripcion = :desc, 
                    estado = :estado,
                    fecha_ejecucion = :fecha_ejecucion,
                    departamento_id = :dept_id,
                    objetivo_id = :obj_id,
                    actividad_id = :act_id
                   WHERE id = :id";
        $stmtUpdate = $pdo->prepare($update);
        $stmtUpdate->execute([
            ':parroquia' => $parroquia,
            ':desc' => $descripcion,
            ':estado' => $estado,
            ':fecha_ejecucion' => $fecha_ejecucion,
            ':dept_id' => $departamento_id,
            ':obj_id' => $objetivo_id,
            ':act_id' => $actividad_id,
            ':id' => $id
        ]);
        
        // 1. Eliminar imágenes marcadas
        if (!empty($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
            foreach ($_POST['eliminar_imagenes'] as $img_id) {
                $stmtImg = $pdo->prepare("SELECT ruta_archivo FROM ipauma_imagenes WHERE id = ?");
                $stmtImg->execute([$img_id]);
                $img = $stmtImg->fetch();
                if ($img && file_exists($img['ruta_archivo'])) {
                    unlink($img['ruta_archivo']); // Borra archivo físico
                }
                $pdo->prepare("DELETE FROM ipauma_imagenes WHERE id = ?")->execute([$img_id]);
            }
        }

        // 2. Procesamiento de Nuevas Imágenes
        if (!empty($_FILES['nuevas_imagenes']['name'][0])) {
            $uploadDir = 'uploads/ipauma/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

            foreach ($_FILES['nuevas_imagenes']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['nuevas_imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                    $nombre_original = basename($_FILES['nuevas_imagenes']['name'][$key]);
                    $nombre_seguro = uniqid('img_') . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $nombre_original);
                    $ruta_destino = $uploadDir . escapeshellcmd($nombre_seguro);
                    
                    if (move_uploaded_file($tmp_name, $ruta_destino)) {
                        $stmtImgInsert = $pdo->prepare("INSERT INTO ipauma_imagenes (solicitud_id, ruta_archivo, nombre_archivo) VALUES (?, ?, ?)");
                        $stmtImgInsert->execute([$id, $ruta_destino, $nombre_original]);
                    }
                }
            }
        }

        $pdo->commit();

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
                    text: 'Acabas de realizar los cambios en el reporte con éxito.',
                    icon: 'success',
                    confirmButtonColor: '#164377',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    window.location.href='ipauma_dashboard.php';
                });
            </script>
        </body>
        </html>";
        exit;
              
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('Error al actualizar: " . $e->getMessage() . "');</script>";
    }
}

// Obtener los datos actuales
try {
    $stmt = $pdo->prepare("SELECT * FROM ipauma_solicitudes WHERE id = ?");
    $stmt->execute([$id]);
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reporte) { die("Reporte no encontrado."); }

    $stmtImgs = $pdo->prepare("SELECT * FROM ipauma_imagenes WHERE solicitud_id = ?");
    $stmtImgs->execute([$id]);
    $imagenes_actuales = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);

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
        .container-main { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); max-width: 900px; margin: 40px auto; }
        h2 { color: #164377; font-weight: bold; margin-bottom: 25px; border-bottom: 3px solid #d2005a; padding-bottom: 10px; }
        .form-label { font-weight: bold; color: #444; }
        /* Dropzone */
        .dropzone-area { border: 2px dashed #164377; background: #f8f9fa; cursor: pointer; transition: 0.3s; padding: 20px; border-radius: 8px; text-align: center; }
        .dropzone-area.dragover { background: #e9ecef; border-color: #d2005a; }
        .img-preview { position: relative; width: 80px; height: 80px; overflow: hidden; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .img-preview img { width: 100%; height: 100%; object-fit: cover; }
        .img-remove { position: absolute; top: 2px; right: 2px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .old-img-card { transition: 0.3s; }
        .old-img-card:hover { transform: scale(1.02); border-color: #d2005a !important; }
    </style>
</head>
<body>

<div class="container-main">
    <h2><i class="bi bi-pencil-square me-2"></i>Editar Reporte #<?= $id ?></h2>
    
    <form method="POST" enctype="multipart/form-data" id="formEditar">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Estado del Reporte</label>
                <select name="estado" class="form-select" required>
                    <option value="Pendiente" <?= ($reporte['estado'] == 'Pendiente') ? 'selected' : '' ?>>🔴 Pendiente</option>
                    <option value="En Proceso" <?= ($reporte['estado'] == 'En Proceso') ? 'selected' : '' ?>>🟡 En Proceso</option>
                    <option value="Resuelto" <?= ($reporte['estado'] == 'Resuelto') ? 'selected' : '' ?>>🟢 Resuelto</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Fecha de Ejecución</label>
                <input type="date" name="fecha_ejecucion" class="form-control" value="<?= htmlspecialchars($reporte['fecha_ejecucion']) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
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
                    <option value="<?= $d['id'] ?>" <?= ($reporte['departamento_id'] == $d['id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Objetivo</label>
            <select name="objetivo_id" id="objetivo_id" class="form-select" required onchange="filtrarActividades()"></select>
        </div>

        <div class="mb-3">
            <label class="form-label">Actividad</label>
            <select name="actividad_id" id="actividad_id" class="form-select" required></select>
        </div>

        <div class="mb-4">
            <label class="form-label">Descripción / Observaciones</label>
            <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($reporte['descripcion']) ?></textarea>
        </div>

        <hr>
        <div class="mb-4 mt-3">
            <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-images me-1 text-primary"></i> Imágenes Actuales</span>
                <?php if(count($imagenes_actuales) > 0): ?>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-success me-1" onclick="descargarSeleccionadasEdit()"><i class="bi bi-file-zip"></i> Descargar Seleccionadas</button>
                        <button type="button" class="btn btn-sm btn-dark" onclick="descargarTodasEdit(<?= $id ?>)"><i class="bi bi-cloud-download"></i> Descargar Todas</button>
                    </div>
                <?php endif; ?>
            </label>
            
            <div class="p-3 bg-light rounded border">
                <?php if(count($imagenes_actuales) > 0): ?>
                    <div class="d-flex flex-wrap gap-3">
                        <?php foreach($imagenes_actuales as $img): ?>
                            <div class="card old-img-card shadow-sm text-center p-2" style="width: 150px;">
                                <a href="<?= $img['ruta_archivo'] ?>" target="_blank">
                                    <img src="<?= $img['ruta_archivo'] ?>" class="rounded mb-2" style="width:100%; height:90px; object-fit:cover;">
                                </a>
                                <div class="d-flex flex-column gap-1 text-start">
                                    <label style="font-size: 0.75rem; cursor: pointer;">
                                        <input type="checkbox" class="form-check-input check-descargar me-1" value="<?= $img['id'] ?>"> Descargar
                                    </label>
                                    <label style="font-size: 0.75rem; color: #dc3545; cursor: pointer; font-weight: bold;">
                                        <input type="checkbox" name="eliminar_imagenes[]" value="<?= $img['id'] ?>" class="form-check-input me-1"> Eliminar Foto
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted d-block mt-2">* Marca la casilla "Eliminar Foto" y presiona "Actualizar Reporte" para borrar permanentemente.</small>
                <?php else: ?>
                    <p class="text-muted small mb-0"><i class="bi bi-info-circle"></i> No hay imágenes registradas previamente.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold"><i class="bi bi-plus-circle me-1 text-success"></i> Añadir Nuevas Imágenes</label>
            <div id="dropzone" class="dropzone-area">
                <i class="bi bi-cloud-arrow-up display-5 text-secondary"></i>
                <p class="mt-2 mb-0 text-muted">Haz clic, arrastra imágenes aquí o presiona Ctrl+V para pegar</p>
                <input type="file" id="fileInput" name="nuevas_imagenes[]" accept="image/*" multiple class="d-none">
            </div>
            <div id="preview-container" class="d-flex flex-wrap gap-2 mt-3"></div>
        </div>

        <div class="d-flex justify-content-between mt-5">
            <a href="ipauma_dashboard.php" class="btn btn-secondary px-4"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
            <button type="submit" name="actualizar_ipauma" class="btn btn-primary px-4 fw-bold" style="background-color: #164377; border: none;">
                <i class="bi bi-check-circle me-1"></i> Actualizar Reporte
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Selects Dinámicos
const objetivosBase = <?= json_encode($objetivos_all) ?>;
const actividadesBase = <?= json_encode($actividades_all) ?>;
const iniciales = { objetivo_id: "<?= $reporte['objetivo_id'] ?>", actividad_id: "<?= $reporte['actividad_id'] ?>" };

function filtrarObjetivos(usarInicial = false) {
    const deptoId = document.getElementById('departamento_id').value;
    const selectObj = document.getElementById('objetivo_id');
    const selectAct = document.getElementById('actividad_id');
    selectObj.innerHTML = '<option value="">Seleccione un Objetivo</option>';
    selectAct.innerHTML = '<option value="">Seleccione una Actividad</option>';

    if (deptoId) {
        objetivosBase.filter(o => o.departamento_id == deptoId).forEach(o => {
            const option = document.createElement('option'); option.value = o.id; option.textContent = o.descripcion;
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
        actividadesBase.filter(a => a.objetivo_id == objId).forEach(a => {
            const option = document.createElement('option'); option.value = a.id; option.textContent = a.descripcion;
            if (usarInicial && a.id == iniciales.actividad_id) option.selected = true;
            selectAct.appendChild(option);
        });
    }
}

window.onload = function() { filtrarObjetivos(true); };

// Lógica de Imágenes Nuevas (Dropzone)
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const previewContainer = document.getElementById('preview-container');
let currentFiles = new DataTransfer();

dropzone.addEventListener('click', () => fileInput.click());
dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
dropzone.addEventListener('drop', (e) => { e.preventDefault(); dropzone.classList.remove('dragover'); handleFiles(e.dataTransfer.files); });
document.addEventListener('paste', (e) => { if(e.clipboardData.files.length > 0 && e.target.nodeName !== 'TEXTAREA' && e.target.nodeName !== 'INPUT') handleFiles(e.clipboardData.files); });
fileInput.addEventListener('change', () => handleFiles(fileInput.files));

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if(file.type.startsWith('image/')) { currentFiles.items.add(file); renderPreview(file); }
    });
    fileInput.files = currentFiles.files;
}

function renderPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        const div = document.createElement('div');
        div.className = 'img-preview';
        div.innerHTML = `<img src="${e.target.result}"><button type="button" class="img-remove" onclick="removeFile(this, '${file.name}')"><i class="bi bi-x"></i></button>`;
        previewContainer.appendChild(div);
    };
    reader.readAsDataURL(file);
}

window.removeFile = function(btn, fileName) {
    const newDt = new DataTransfer();
    Array.from(currentFiles.files).forEach(file => { if(file.name !== fileName) newDt.items.add(file); });
    currentFiles = newDt;
    fileInput.files = currentFiles.files;
    btn.parentElement.remove();
}

// Funciones de descarga con formularios dinámicos
function descargarSeleccionadasEdit() {
    let checkboxes = document.querySelectorAll('.check-descargar:checked');
    if(checkboxes.length === 0) { alert('Seleccione al menos una imagen para descargar.'); return; }
    
    let form = document.createElement('form');
    form.method = 'POST';
    form.action = 'ipauma_descargar_imagenes.php';
    checkboxes.forEach(cb => {
        let input = document.createElement('input');
        input.type = 'hidden'; input.name = 'img_ids[]'; input.value = cb.value;
        form.appendChild(input);
    });
    document.body.appendChild(form); form.submit(); document.body.removeChild(form);
}

function descargarTodasEdit(id) {
    let form = document.createElement('form');
    form.method = 'POST';
    form.action = 'ipauma_descargar_imagenes.php';
    let input = document.createElement('input');
    input.type = 'hidden'; input.name = 'solicitud_id'; input.value = id;
    form.appendChild(input);
    document.body.appendChild(form); form.submit(); document.body.removeChild(form);
}
</script>
</body>
</html>