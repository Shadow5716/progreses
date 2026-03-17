<?php
session_start();
// Desactivar error_reporting(0) para poder ver si algo falla en el desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/dbconnection.php');

// Si no vienen de forgot-password, los regresamos
if (!isset($_SESSION['email']) || !isset($_SESSION['contactno'])) {
    header('location:forgot-password.php');
    exit;
}

$msg = "";

if (isset($_POST['submit'])) {
    $email = $_SESSION['email'];
    $contactno = $_SESSION['contactno'];
    $newpassword = $_POST['newpassword'];
    $confirmpassword = $_POST['confirmpassword'];

    if ($newpassword !== $confirmpassword) {
        $msg = "Las contraseñas no coinciden.";
    } else {
        try {
            // Usamos PDO para actualizar la contraseña
            $sql = "UPDATE tbladmin SET Password = :pass WHERE Email = :email AND MobileNumber = :contact";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'pass'    => $newpassword,
                'email'   => $email,
                'contact' => $contactno
            ]);

            echo "<script>alert('Contraseña actualizada con éxito. Inicie sesión con su nueva clave.'); window.location='index.php';</script>";
            session_destroy();
            exit;
            
        } catch (PDOException $e) {
            $msg = "Error al actualizar: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <title>SAMI - Reiniciar Contraseña</title>
    <link rel="stylesheet" href="vendors/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark" style="background-image: url('imagenes/muro2.png'); background-size: cover;">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5 bg-light p-4 rounded shadow">
                <center><img src="imagenes/alcaldia-maracaibo.png" width="60%" class="mb-3"></center>
                <h4 class="text-center">Nueva Contraseña</h4>
                <hr>
                
                <?php if($msg): ?>
                    <div class="alert alert-danger small"><?php echo $msg; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group mb-3">
                        <label>Nueva Contraseña</label>
                        <input type="password" class="form-control" name="newpassword" placeholder="Mínimo 8 caracteres" required>
                    </div>
                    <div class="form-group mb-4">
                        <label>Confirmar Contraseña</label>
                        <input type="password" class="form-control" name="confirmpassword" placeholder="Repita la contraseña" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-success w-100">Actualizar Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>