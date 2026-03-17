<?php
require_once 'includes/dbconnection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

try {
    // 1. Obtener datos de la solicitud actual con sus nombres
    $sql = "SELECT s.*, e.nombre_ente, a.nombre_area, r.nombre_responsable 
            FROM solicitudes s
            LEFT JOIN entes e ON s.id_ente = e.id_ente
            LEFT JOIN areas a ON s.id_area = a.id_area
            LEFT JOIN responsables r ON s.id_responsable = r.id_responsable
            WHERE s.id_solicitud = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) die("Solicitud no encontrada.");

    // 2. Cargar listado de Áreas para el primer SELECT
    $todas_areas = $pdo->query("SELECT * FROM areas ORDER BY nombre_area ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Procesar el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_area = $_POST['id_area'];
        $id_resp = $_POST['id_responsable'];
        $estatus = $_POST['estatus'];
        $descripcion = $_POST['descripcion'];

        $update_sql = "UPDATE solicitudes SET 
                       id_area = :area, 
                       id_responsable = :resp, 
                       estatus = :est, 
                       descripcion = :desc 
                       WHERE id_solicitud = :id";
        
        $stmt_up = $pdo->prepare($update_sql);
        $stmt_up->execute([
            ':area' => $id_area,
            ':resp' => $id_resp,
            ':est'  => $estatus,
            ':desc' => $descripcion,
            ':id'   => $id
        ]);
        
        header("Location: dashboard.php?msg=actualizado");
        exit;
    }
} catch (PDOException $e) {
    die("Error crítico: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
        <link rel="shortcut icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
    <link rel="icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
    <meta charset="UTF-8">
    <title>Editar Solicitud - PROREGES</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
    <?php include 'navbar.php'; ?>

<body class="bg-light">
    <div class="container mt-5 pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Editar Registro #<?php echo $id; ?></h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dirección / Ente Solicitante</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($solicitud['nombre_ente']); ?>" readonly>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Área Específica</label>
                                    <select name="id_area" id="id_area" class="form-select border-primary shadow-sm">
                                        <?php foreach ($todas_areas as $area): ?>
                                            <option value="<?php echo $area['id_area']; ?>" <?php echo ($area['id_area'] == $solicitud['id_area']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($area['nombre_area']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Personal Responsable</label>
                                    <select name="id_responsable" id="id_responsable" class="form-select border-primary shadow-sm">
                                        <option value="<?php echo $solicitud['id_responsable']; ?>" selected>
                                            <?php echo htmlspecialchars($solicitud['nombre_responsable']); ?>
                                        </option>
                                        </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Estatus</label>
                                <select name="estatus" class="form-select border-primary">
                                    <option value="Pendiente" <?php echo ($solicitud['estatus'] == 'Pendiente') ? 'selected' : ''; ?>>🟡 Pendiente</option>
                                    <option value="En Proceso" <?php echo ($solicitud['estatus'] == 'En Proceso') ? 'selected' : ''; ?>>🔵 En Proceso</option>
                                    <option value="Resuelto" <?php echo ($solicitud['estatus'] == 'Resuelto') ? 'selected' : ''; ?>>🟢 Resuelto</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Descripción del Trabajo</label>
                                <textarea name="descripcion" class="form-control" rows="5" required><?php echo htmlspecialchars($solicitud['descripcion']); ?></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="dashboard.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-primary px-5 shadow">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('id_area').addEventListener('change', function() {
        const idArea = this.value;
        const selectResp = document.getElementById('id_responsable');

        // Limpiar y mostrar estado de carga
        selectResp.innerHTML = '<option value="">Cargando técnicos...</option>';

        // Petición al archivo que ya creamos
        const formData = new FormData();
        formData.append('id_area', idArea);

        fetch('get_responsables.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            selectResp.innerHTML = '<option value="">Seleccione un responsable...</option>';
            data.forEach(resp => {
                const option = document.createElement('option');
                option.value = resp.id_responsable;
                option.textContent = resp.nombre_responsable;
                selectResp.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            selectResp.innerHTML = '<option value="">Error al cargar responsables</option>';
        });
    });
    </script>
        <script src="js/bootstrap.bundle.min.js"></script> 

</body>
</html>