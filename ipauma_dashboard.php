<?php
// ipauma_dashboard.php
$conn = new mysqli('localhost', 'root', '', 'tu_base_de_datos');

// 1. Estadísticas de Departamentos más seleccionados
$q_dept = "SELECT d.nombre, COUNT(s.id) as total FROM ipauma_solicitudes s 
           JOIN ipauma_departamentos d ON s.departamento_id = d.id 
           GROUP BY d.id ORDER BY total DESC LIMIT 5";
$res_dept = $conn->query($q_dept);

// 2. Estadísticas de Objetivos más seleccionados
$q_obj = "SELECT o.descripcion, COUNT(s.id) as total FROM ipauma_solicitudes s 
          JOIN ipauma_objetivos o ON s.objetivo_id = o.id 
          GROUP BY o.id ORDER BY total DESC LIMIT 5";
$res_obj = $conn->query($q_obj);

// 3. Estadísticas de Actividades más realizadas
$q_act = "SELECT a.descripcion, COUNT(s.id) as total FROM ipauma_solicitudes s 
          JOIN ipauma_actividades a ON s.actividad_id = a.id 
          GROUP BY a.id ORDER BY total DESC LIMIT 5";
$res_act = $conn->query($q_act);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard IPAUMA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-new { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .dashboard-container { display: flex; gap: 20px; flex-wrap: wrap; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; min-width: 300px; }
        .card h3 { margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        ul { list-style: none; padding: 0; }
        li { padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
    </style>
</head>
<body>

<div class="header">
    <h1>Dashboard IPAUMA</h1>
    <a href="ipauma_nueva_solicitud.php" class="btn-new">+ Nueva Solicitud IPAUMA</a>
</div>

<div class="dashboard-container">
    <div class="card">
        <h3>Departamentos más seleccionados</h3>
        <ul>
            <?php while($row = $res_dept->fetch_assoc()): ?>
                <li><span><?= htmlspecialchars($row['nombre']) ?></span> <span class="badge"><?= $row['total'] ?></span></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="card">
        <h3>Objetivos más seleccionados</h3>
        <ul>
            <?php while($row = $res_obj->fetch_assoc()): ?>
                <li><span><?= htmlspecialchars(substr($row['descripcion'], 0, 50)) ?>...</span> <span class="badge"><?= $row['total'] ?></span></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="card">
        <h3>Actividades más realizadas</h3>
        <ul>
            <?php while($row = $res_act->fetch_assoc()): ?>
                <li><span><?= htmlspecialchars(substr($row['descripcion'], 0, 50)) ?>...</span> <span class="badge"><?= $row['total'] ?></span></li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

</body>
</html>