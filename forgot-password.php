<?php
session_start();
require_once('includes/dbconnection.php');

$msg = "";

if(isset($_POST['submit'])) {
    $contactno = $_POST['contactno'];
    $email = $_POST['email'];

    try {
        // Usamos PDO para la consulta
        $sql = "SELECT ID FROM tbladmin WHERE Email = :email AND MobileNumber = :phone AND Estado = 'Habilitada' LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email, 'phone' => $contactno]);
        $admin = $stmt->fetch();

        if($admin) {
            $_SESSION['contactno'] = $contactno;
            $_SESSION['email'] = $email;
            header('location:resetpassword.php');
            exit;
        } else {
            $msg = "Datos incorrectos o cuenta inhabilitada. Verifique e intente de nuevo.";
        }
    } catch (PDOException $e) {
        $msg = "Error en el sistema: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <title>SAMI - Recuperar Contraseña</title>
    <link rel="stylesheet" href="vendors/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark" style="background-image: url('imagenes/muro2.png'); background-size: cover;">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5 bg-light p-4 rounded shadow">
                <center><img src="imagenes/alcaldia-maracaibo.png" width="60%" class="mb-3"></center>
                <h4 class="text-center">Recuperar Acceso</h4>
                <hr>
                
                <?php if($msg): ?>
                    <div class="alert alert-danger small"><?php echo $msg; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group mb-3">
                        <label>Correo Electrónico</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Número Telefónico</label>
                        <input type="text" class="form-control" name="contactno" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-success w-100">Verificar Datos</button>
                    <div class="text-center mt-3">
                        <a href="index.php" class="small">Regresar al Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <script src="vendors/jquery/dist/jquery.min.js"></script>
    <script src="vendors/popper.js/dist/umd/popper.min.js"></script>
    <script src="vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>

</body>
</html>