<?php
// ipauma_nueva_solicitud.php

// Conexión a BD
$conn = new mysqli('localhost', 'root', '', 'tu_base_de_datos');
$departamentos = $conn->query("SELECT * FROM ipauma_departamentos");

// Procesar guardado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_ipauma'])) {
    $dep_id = intval($_POST['departamento_id']);
    $obj_id = intval($_POST['objetivo_id']);
    $act_id = intval($_POST['actividad_id']);
    
    $insert = "INSERT INTO ipauma_solicitudes (departamento_id, objetivo_id, actividad_id) VALUES ($dep_id, $obj_id, $act_id)";
    if($conn->query($insert)){
        echo "<script>alert('Solicitud IPAUMA registrada con éxito'); window.location.href='ipauma_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error al guardar');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Solicitud IPAUMA</title>
    <style>
        .form-container { max-width: 600px; margin: 20px auto; font-family: Arial, sans-serif; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        select, button { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; font-size: 16px; margin-top: 10px;}
        button:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Nueva Solicitud IPAUMA</h2>
    <form method="POST" action="">
        
        <div class="form-group">
            <label>Departamento</label>
            <select name="departamento_id" id="departamento_id" required>
                <option value="">-- Seleccione un Departamento --</option>
                <?php while($row = $departamentos->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nombre'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Objetivos Específicos</label>
            <select name="objetivo_id" id="objetivo_id" required disabled>
                <option value="">-- Primero seleccione un departamento --</option>
            </select>
        </div>

        <div class="form-group">
            <label>Actividad</label>
            <select name="actividad_id" id="actividad_id" required disabled>
                <option value="">-- Primero seleccione un objetivo --</option>
            </select>
        </div>

        <button type="submit" name="guardar_ipauma">Registrar Solicitud</button>
        <a href="ipauma_dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#007bff; text-decoration:none;">Volver al Dashboard</a>
    </form>
</div>

<script>
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