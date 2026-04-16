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
    $oficio = trim($_POST['oficio']);
    $descripcion = trim($_POST['descripcion']);
    $estado = trim($_POST['estado']);
    
    try {
        $update = "UPDATE ipauma_solicitudes SET parroquia = :parroquia, oficio = :oficio, descripcion = :desc, estado = :estado WHERE id = :id";
        $stmtUpdate = $pdo->prepare($update);
        $stmtUpdate->execute([
            ':parroquia' => $parroquia,
            ':oficio' => $oficio,
            ':desc' => $descripcion,
            ':estado' => $estado,
            ':id' => $id
        ]);
        echo "<script>alert('Reporte modificado con éxito'); window.location.href='ipauma_dashboard.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error al actualizar: " . $e->getMessage() . "');</script>";
    }
}

// Obtener los datos actuales
try {
    $stmt = $pdo->prepare("SELECT * FROM ipauma_solicitudes WHERE id = ?");
    $stmt->execute([$id]);
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reporte) {
        die("El reporte no existe.");
    }
} catch (PDOException $e) {
    die("Error al cargar reporte: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Solicitud IPAUMA</title>
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .form-container { max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-container h2 { text-align: center; margin-bottom: 20px; color: #164377; }
        .form-group { margin-bottom: 15px; }
        .form-group label { font-weight: bold; display: block; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { width: 100%; background: #ffc107; color: #000; font-weight: bold; padding: 10px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn-submit:hover { background: #e0a800; }
        .btn-back { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #666; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Editar Reporte N° <?= $id ?></h2>
    <form method="POST" action="">
        
        <div class="form-group">
            <label>Estado del Reporte</label>
            <select name="estado" class="form-control" required>
                <option value="Pendiente" <?= ($reporte['estado'] == 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                <option value="En Proceso" <?= ($reporte['estado'] == 'En Proceso') ? 'selected' : '' ?>>En Proceso</option>
                <option value="Resuelto" <?= ($reporte['estado'] == 'Resuelto') ? 'selected' : '' ?>>Resuelto</option>
            </select>
        </div>

        <div class="form-group">
            <label>Parroquia</label>
            <input type="text" name="parroquia" class="form-control" value="<?= htmlspecialchars($reporte['parroquia']) ?>" required>
        </div>

        <div class="form-group">
            <label>Oficio</label>
            <input type="text" name="oficio" class="form-control" value="<?= htmlspecialchars($reporte['oficio']) ?>" required>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" rows="5" required><?= htmlspecialchars($reporte['descripcion']) ?></textarea>
        </div>

        <button type="submit" name="actualizar_ipauma" class="btn-submit">Guardar Cambios</button>
        <a href="ipauma_dashboard.php" class="btn-back">Cancelar y volver</a>
    </form>
</div>

</body>
</html>