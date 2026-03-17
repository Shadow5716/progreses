<?php
include_once('sesion.php');
include_once('includes/dbconnection.php');

// 1. VALIDACIÓN DE SEGURIDAD (Solo Administradores pueden crear usuarios)
$adminid = $_SESSION['adminid'];
$stmtCheck = $pdo->prepare("SELECT Rango FROM tbladmin WHERE ID = ?");
$stmtCheck->execute([$adminid]);
$userActual = $stmtCheck->fetch();

if (!$userActual || $userActual['Rango'] !== "Administrador") {
    echo "<script>alert('Acceso Denegado: No tiene permisos de nivel Administrador.'); window.location='dashboard.php';</script>";
    exit;
}

$mensaje = "";

// 2. PROCESAMIENTO DEL FORMULARIO
if (isset($_POST['submit'])) {
    $adminname    = $_POST['adminname'];
    $username     = $_POST['username'];
    $mobilenumber = $_POST['mobilenumber'];
    $email        = $_POST['email'];
    $password     = $_POST['password']; 
    $rango        = $_POST['Rango'];

    try {
        $sql = "INSERT INTO tbladmin (AdminName, UserName, MobileNumber, Email, Password, AdminRegdate, Rango, Estado) 
                VALUES (:name, :user, :phone, :mail, :pass, NOW(), :rango, 'Habilitada')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name'  => $adminname,
            'user'  => $username,
            'phone' => $mobilenumber,
            'mail'  => $email,
            'pass'  => $password,
            'rango' => $rango
        ]);

        $mensaje = "<div class='alert alert-success shadow-sm'><b>¡Éxito!</b> La cuenta de $adminname ha sido creada correctamente.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger shadow-sm'><b>Error:</b> " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SAMI - Registrar Administrador</title>
    <link rel="stylesheet" href="vendors/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .card { border: none; border-radius: 12px; }
        .card-header { border-radius: 12px 12px 0 0 !important; font-weight: bold; }
        .form-label { font-weight: 600; color: #444; }
    </style>
</head>
<body class="bg-light">

<?php include_once('header.php'); // Asegúrate de que el nombre sea correcto ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Configuración</li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo Operador</li>
                </ol>
            </nav>

            <div class="card shadow">
                <div class="card-header bg-dark text-white d-flex align-items-center py-3">
                    <i class="fa fa-user-plus me-3"></i>
                    <h5 class="mb-0">Registrar Nuevo Operador / Administrador</h5>
                </div>
                
                <div class="card-body p-4">
                    <?php echo $mensaje; ?>

                    <form method="post" class="needs-validation">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" name="adminname" class="form-control" placeholder="Ej: Juan Pérez" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre de Usuario</label>
                                <input type="text" name="username" class="form-control" placeholder="Ej: jperez_sami" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo Institucional</label>
                                <input type="email" name="email" class="form-control" placeholder="usuario@maracaibo.gob.ve" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono de Contacto</label>
                                <input type="text" name="mobilenumber" class="form-control" placeholder="04XX-XXXXXXX" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Contraseña Temporal</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rango de Acceso</label>
                                <select name="Rango" class="form-select" required>
                                    <option value="" selected disabled>Seleccione un nivel...</option>
                                    <option value="Operador">Operador (Solo consultas y cobros)</option>
                                    <option value="Encargado">Encargado (Gestion)</option>
                                </select>
                            </div>
                        </div>

                        <div class="border-top pt-4 d-flex justify-content-end">
                            <a href="dashboard.php" class="btn btn-outline-secondary px-4 me-2">
                                <i class="fa fa-arrow-left me-1"></i> Volver al Listado
                            </a>
                            <button type="submit" name="submit" class="btn btn-success px-5 shadow-sm">
                                <i class="fa fa-save me-1"></i> Crear Cuenta
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center text-muted mt-4 small">
                SAMI Créditos - Alcaldía de Maracaibo &copy; 2026
            </p>
        </div>
    </div>
</div>

<script src="vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>