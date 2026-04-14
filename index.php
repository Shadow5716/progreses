<?php
// index.php - Pantalla de Inicio de Sesión PROREGES
session_start();
error_reporting(E_ALL);
require_once 'includes/dbconnection.php';

// Si ya hay una sesión activa, saltar el login e ir al dashboard
if (isset($_SESSION['autentificado']) && $_SESSION['autentificado'] == true) {
    header('location:dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROREGES - Acceso al Sistema</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />
    <link rel="icon" type="image/x-icon" href="imagenes/iconito.ico?v=1.1" />

    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --blue-maracaibo: #164377;
            --yellow-maracaibo: #fdb813;
        }

        body {
            font-family: 'Public Sans', sans-serif;
            /* Usamos la imagen de fondo que tenías en el segundo código */
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('imagenes/muro2.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }

        .card-login {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.98);
        }

        .card-header-login {
            background-color: var(--blue-maracaibo);
            border-bottom: 5px solid var(--yellow-maracaibo);
            padding: 30px 20px;
            text-align: center;
        }

        .logo-login {
            max-width: 180px;
            height: auto;
            margin-bottom: 10px;
        }

        .system-title {
            color: white;
            font-weight: 700;
            font-size: 1.4rem;
            margin: 0;
            letter-spacing: 1px;
        }

        .btn-login {
            background-color: #198754; /* Verde del segundo código */
            border: none;
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #146c43;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
            color: white;
        }

        .form-control:focus {
            border-color: var(--blue-maracaibo);
            box-shadow: 0 0 0 0.25rem rgba(22, 67, 119, 0.1);
        }

        .footer-text {
            color: #fff;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        @media (max-width: 380px) {
            .system-title { font-size: 1.1rem; }
            .card-header-login { padding: 20px 10px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="card card-login">
            <div class="card-header-login">
                <img src="imagenes/alcaldia-maracaibo.png" alt="Alcaldía de Maracaibo" class="logo-login">
                <h1 class="system-title">PROREGES</h1>
                <p class="text-white-50 small mb-0">Programa de Reportes de Gestión</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form action="validacion.php" method="POST">
                    <div class="mb-3">
                        <label for="usuario" class="form-label text-muted small fw-bold">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="usuario" id="usuario" class="form-control bg-light border-start-0" placeholder="Ingrese su usuario" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label for="contraseña" class="form-label text-muted small fw-bold">Contraseña</label>
                            <a href="forgot-password.php" class="small text-muted text-decoration-none">¿Olvidó su clave?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="contraseña" id="contraseña" class="form-control bg-light border-start-0" placeholder="Ingrese su clave" required>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn btn-login w-100 mb-3 shadow-sm">
                        ENTRAR AL SISTEMA <i class="bi bi-box-arrow-in-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4 footer-text">
            <p class="small mb-0">&copy; 2026 Alcaldía de Maracaibo</p>
            <p class="small">Desarrollado por: Dirección de Tecnología</p>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>