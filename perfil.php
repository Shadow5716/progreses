<?php
include_once('sesion.php'); // El guardián de la sesión
include_once('includes/dbconnection.php');

$adminid = $_SESSION['adminid'];
$mensaje = "";

// --- LÓGICA PARA ACTUALIZAR DATOS ---
if (isset($_POST['update'])) {
    $nombre = $_POST['adminname'];
    $movil  = $_POST['mobilenumber'];
    $email  = $_POST['email'];

    try {
        $sql = "UPDATE tbladmin SET AdminName = :nom, MobileNumber = :tel, Email = :mail WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nom'  => $nombre,
            'tel'  => $movil,
            'mail' => $email,
            'id'   => $adminid
        ]);
        $mensaje = "<div class='alert alert-success'>¡Perfil actualizado con éxito!</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error al actualizar: " . $e->getMessage() . "</div>";
    }
}

// --- CONSULTA PARA CARGAR DATOS ACTUALES ---
$stmt = $pdo->prepare("SELECT * FROM tbladmin WHERE ID = ?");
$stmt->execute([$adminid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>SAMI - Mi Perfil</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="vendors/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <div id="right-panel" class="right-panel">
        <div class="breadcrumbs">
            <div class="col-sm-4">
                <div class="page-header float-left">
                    <div class="page-title">
                        <h1>Configuración de Perfil</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="content mt-3">
            <div class="animated fadeIn">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <strong class="card-title">Mis Datos de Administrador</strong>
                            </div>
                            <div class="card-body card-block">
                                <?php echo $mensaje; ?>
                                
                                <form action="" method="post" class="form-horizontal">
                                    <div class="row form-group mb-3">
                                        <div class="col col-md-3"><label class="form-control-label">Usuario</label></div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" class="form-control" value="<?php echo $row['UserName']; ?>" readonly disabled>
                                            <small class="form-text text-muted">El nombre de usuario no se puede cambiar.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row form-group mb-3">
                                        <div class="col col-md-3"><label class="form-control-label">Nombre Completo</label></div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" name="adminname" class="form-control" value="<?php echo $row['AdminName']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row form-group mb-3">
                                        <div class="col col-md-3"><label class="form-control-label">Correo Electrónico</label></div>
                                        <div class="col-12 col-md-9">
                                            <input type="email" name="email" class="form-control" value="<?php echo $row['Email']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row form-group mb-3">
                                        <div class="col col-md-3"><label class="form-control-label">Teléfono / Móvil</label></div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" name="mobilenumber" class="form-control" value="<?php echo $row['MobileNumber']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row form-group mb-3">
                                        <div class="col col-md-3"><label class="form-control-label">Último Acceso</label></div>
                                        <div class="col-12 col-md-9">
                                            <span class="badge badge-info"><?php echo $row['LastLogin']; ?></span>
                                        </div>
                                    </div>

                                    <hr>
                                    <div class="text-right">
                                        <button type="submit" name="update" class="btn btn-success btn-sm">
                                            <i class="fa fa-dot-circle-o"></i> Guardar Cambios
                                        </button>
                                        <a href="dashboard.php" class="btn btn-danger btn-sm">
                                            <i class="fa fa-ban"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendors/jquery/dist/jquery.min.js"></script>
    <script src="vendors/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>