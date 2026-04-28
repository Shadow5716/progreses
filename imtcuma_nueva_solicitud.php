<?php
session_start();
if (!isset($_SESSION['autentificado'])) { header('location:index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Registro - IMTCUMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { 
            background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important; 
        }
        .bg-gradient-custom { 
            background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important; 
        }
        .card-header { color: white; font-weight: bold; }
        .navbar-custom { background: rgba(0, 0, 0, 0.2); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="imtcuma_dashboard.php">IMTCUMA - Sistema de Control</a>
    </div>
</nav>

<div class="container pb-5">
    <a href="imtcuma_dashboard.php" class="btn btn-light mb-3 fw-bold"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
    
    <div class="card shadow border-0">
        <div class="card-header bg-gradient-custom">
            <h4 class="mb-0">Registro de Unidad (GT5)</h4>
        </div>
        <div class="card-body">
            <form action="imtcuma_guardar_vehiculo.php" method="POST">
                
                <h5 class="mt-2 mb-3 border-bottom pb-2 text-primary fw-bold">Registro de Junta Directiva / Datos de la Organización</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="fw-bold">Nombre de la Organización</label>
                        <input type="text" name="org_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Modalidad</label>
                        <select name="org_modalidad" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="CARRO POR PUESTO (CCP)">CARRO POR PUESTO (CCP)</option>
                            <option value="AUTOBUSES (AB)">AUTOBUSES (AB)</option>
                            <option value="MICROBUS (MB)">MICROBUS (MB)</option>
                            <option value="VAN">VAN</option>
                            <option value="MOTOTAXI">MOTOTAXI</option>
                            <option value="TAXI">TAXI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Periodo Desde</label>
                        <input type="date" name="org_periodo_desde" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Periodo Hasta</label>
                        <input type="date" name="org_periodo_hasta" class="form-control">
                    </div>
                </div>

                <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary fw-bold">Datos del Directivo</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="fw-bold">Nombre(s) y Apellido(s)</label>
                        <input type="text" name="dir_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Cédula de Identidad</label>
                        <input type="text" name="dir_cedula" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Cargo Ocupado</label>
                        <input type="text" name="dir_cargo" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Dirección de Habitación</label>
                        <input type="text" name="dir_direccion" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Número de Contacto</label>
                        <input type="text" name="dir_telefono" class="form-control" required>
                    </div>
                </div>

                <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary fw-bold">Datos del Propietario y Vehículo</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="fw-bold">Placa</label>
                        <input type="text" name="placa" class="form-control text-uppercase" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Nombre Propietario</label>
                        <input type="text" name="propietario_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Cédula Propietario</label>
                        <input type="text" name="propietario_cedula" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Nombre Conductor</label>
                        <input type="text" name="conductor_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Cédula Conductor</label>
                        <input type="text" name="conductor_cedula" class="form-control" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="fw-bold">Marca</label>
                        <input type="text" name="marca" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold">Modelo</label>
                        <input type="text" name="modelo" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold">Año</label>
                        <input type="number" name="anio" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold">Color</label>
                        <input type="text" name="color" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold">Capacidad</label>
                        <input type="number" name="capacidad" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Serial de Carrocería</label>
                        <input type="text" name="serial_carroceria" class="form-control text-uppercase" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Serial de Motor</label>
                        <input type="text" name="serial_motor" class="form-control text-uppercase" required>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn text-white bg-gradient-custom px-4 py-2 fw-bold shadow-sm">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>