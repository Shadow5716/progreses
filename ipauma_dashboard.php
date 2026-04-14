<?php
// ipauma_dashboard.php
include_once('sesion.php');
require_once 'includes/dbconnection.php';

try {
    // 1. Estadísticas de Departamentos más seleccionados
    $q_dept = "SELECT d.nombre, COUNT(s.id) as total FROM ipauma_solicitudes s 
               JOIN ipauma_departamentos d ON s.departamento_id = d.id 
               GROUP BY d.id ORDER BY total DESC LIMIT 5";
    $res_dept = $pdo->query($q_dept)->fetchAll(PDO::FETCH_ASSOC);

    // 2. Estadísticas de Objetivos más seleccionados
    $q_obj = "SELECT o.descripcion, COUNT(s.id) as total FROM ipauma_solicitudes s 
              JOIN ipauma_objetivos o ON s.objetivo_id = o.id 
              GROUP BY o.id ORDER BY total DESC LIMIT 5";
    $res_obj = $pdo->query($q_obj)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Estadísticas de Actividades más realizadas
    $q_act = "SELECT a.descripcion, COUNT(s.id) as total FROM ipauma_solicitudes s 
              JOIN ipauma_actividades a ON s.actividad_id = a.id 
              GROUP BY a.id ORDER BY total DESC LIMIT 5";
    $res_act = $pdo->query($q_act)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard IPAUMA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #fff; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-new { background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .dashboard-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h3 { margin-top: 0; color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        ul { list-style: none; padding: 0; }
        li { padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
    </style>
</head>
<body>

<div class="header">
    <h1>Dashboard IPAUMA</h1>
    <div>
        <a href="dashboard.php" style="margin-right: 10px; text-decoration: none; color: #666;">Volver al Inicio</a>
        <a href="ipauma_nueva_solicitud.php" class="btn-new">+ Nueva Solicitud IPAUMA</a>
    </div>
</div>

<div class="dashboard-container">
    <div class="card">
        <h3>Departamentos</h3>
        <ul>
            <?php foreach($res_dept as $row): ?>
                <li><span><?= htmlspecialchars($row['nombre']) ?></span> <span class="badge"><?= $row['total'] ?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card">
        <h3>Objetivos</h3>
        <ul>
            <?php foreach($res_obj as $row): ?>
                <li><span><?= htmlspecialchars(substr($row['descripcion'], 0, 50)) ?>...</span> <span class="badge"><?= $row['total'] ?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card">
        <h3>Actividades</h3>
        <ul>
            <?php foreach($res_act as $row): ?>
                <li><span><?= htmlspecialchars(substr($row['descripcion'], 0, 50)) ?>...</span> <span class="badge"><?= $row['total'] ?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

</body>
</html>