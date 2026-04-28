<?php
session_start();
require_once 'includes/dbconnection.php';

if (isset($_POST['login'])) {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['contraseña']);
    $modulo_solicitado = $_POST['modulo_solicitado']; // 'proreges', 'ipauma' o 'imtcuma'

    try {
        // Buscamos al usuario en la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuarios_sistema WHERE usuario = :usuario");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificamos si existe y si la contraseña coincide (Se usa comparación directa por ahora como solicitaste: 'sportman')
        if ($user && $user['password'] === $password) {
            
            // Verificamos permisos según el módulo o si es Master
            $tiene_permiso = false;
            
            if ($user['rol'] === 'Master') {
                $tiene_permiso = true;
            } else {
                // Chequear el permiso específico del módulo
                if ($modulo_solicitado == 'proreges' && $user['acceso_proreges'] == 1) $tiene_permiso = true;
                if ($modulo_solicitado == 'ipauma' && $user['acceso_ipauma'] == 1) $tiene_permiso = true;
                if ($modulo_solicitado == 'imtcuma' && $user['acceso_imtcuma'] == 1) $tiene_permiso = true;
            }

            if ($tiene_permiso) {
                // Credenciales correctas y permiso concedido
                $_SESSION['autentificado'] = true;
                $_SESSION['AdminName'] = $user['nombre_completo'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['modulo_activo'] = $modulo_solicitado;
                $_SESSION['usuario_id'] = $user['id']; // Útil para cuando vayan a cambiar la contraseña

                // Redirección según el módulo
                switch ($modulo_solicitado) {
                    case 'ipauma':
                        header("Location: ipauma_dashboard.php");
                        break;
                    case 'imtcuma':
                        header("Location: imtcuma_dashboard.php");
                        break;
                    default:
                        header("Location: dashboard.php");
                        break;
                }
                exit;
            } else {
                echo "<script>alert('No tienes permisos para acceder al módulo: ". strtoupper($modulo_solicitado) ."'); window.location.href='index.php?modulo=".$modulo_solicitado."';</script>";
            }

        } else {
            echo "<script>alert('Usuario o Contraseña incorrectos.'); window.location.href='index.php?modulo=".$modulo_solicitado."';</script>";
        }
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>