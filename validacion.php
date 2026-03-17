<?php
session_start();
include('includes/dbconnection.php');

if (isset($_POST['login'])) {
    $user = $_POST['usuario']; 
    $pass = $_POST['contraseña']; 

    try {
        // AÑADIDO: AdminName en el SELECT
        $sql = "SELECT ID, UserName, AdminName, Password FROM tbladmin WHERE UserName = :u AND Password = :p AND Estado = 'Habilitada' LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['u' => $user, 'p' => $pass]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            $_SESSION['adminid'] = $admin['ID'];
            $_SESSION['usuario'] = $admin['UserName'];
            // CLAVE: Guardamos el nombre real para el Navbar
            $_SESSION['AdminName'] = $admin['AdminName']; 
            $_SESSION['autentificado'] = true;

            $update = "UPDATE tbladmin SET LastLogin = NOW() WHERE ID = ?";
            $pdo->prepare($update)->execute([$admin['ID']]);

            header("Location: dashboard.php"); // Asegúrate de que apunte a tu dashboard
            exit;
        } else {
            echo "<script>alert('Usuario o Clave incorrectos.'); window.location='index.php';</script>";
        }
    } catch (PDOException $e) {
        die("Error crítico: " . $e->getMessage());
    }
}
?>









