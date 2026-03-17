<style>
    .navbar-custom {
        /* Degradado de Rojo (Alcaldía) a Azul (Tecnología) */
        background: rgb(210, 0, 90); 
        background: linear-gradient(90deg, rgba(210, 0, 90, 1) 0%, rgba(22, 67, 119, 1) 100%) !important;
        border: none;
        padding-top: 0.8rem;
        padding-bottom: 0.8rem;
    }

    .navbar-custom .navbar-brand {
        color: #ffffff !important;
    }

    .navbar-custom .navbar-brand small {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: normal;
    }

    .btn-solicitud {
        background-color: #4da58e !important;
        color: white !important;
        border-radius: 8px;
        font-weight: 500;
        border: none;
    }

    .btn-solicitud:hover {
        background-color: #3e8a75 !important;
    }

    .user-avatar-circle {
        width: 40px;
        height: 40px;
        background-color: rgba(255, 255, 255, 0.2); /* Círculo semitransparente para que combine */
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow">
    <div class="container-fluid px-5">
<div class="d-flex align-items-center">
    <a href="dashboard.php">
        <img src="imagenes/alcaldia-maracaibo.png" alt="Logo Alcaldía" style="height: 50px; margin-right: 15px; cursor: pointer;">
    </a>
    
    <div>
        <h1 class="text-white mb-0 h4 fw-bold">Programa de Reportes de Gestion </h1>
        <p class="text-white-50 mb-0 small">Dirección de Tecnología</p>
    </div>
</div>
        
<div class="ms-auto d-flex align-items-center">
    <a href="nueva_solicitud.php" class="btn btn-solicitud me-4 shadow-sm">
        <i class="bi bi-plus-lg me-2"></i>Nueva Solicitud
    </a>

<div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle shadow-sm fw-bold" type="button" id="dropdownGraficas" data-bs-toggle="dropdown" aria-expanded="false" style="border-width: 2px; border-radius: 8px;">
        <i class="bi bi-bar-chart-line-fill me-2"></i>Ver Estadísticas
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="dropdownGraficas" style="border-radius: 12px;">
        <li>
            <a class="dropdown-item py-2" href="estadistica.php">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Áreas
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item py-2" href="estadistica_entes.php">
                <i class="bi bi-building me-2 text-primary"></i>Direccion/Entes
            </a>
        </li>
        <li>
            <a class="dropdown-item py-2" href="estadistica_responsables.php">
                <i class="bi bi-person-vcard me-2 text-primary"></i>Personal
            </a>
        </li>
    </ul>
</div>

    <div class="vr mx-2 text-white opacity-25 d-none d-lg-block"></div>

<div class="dropdown text-white">
    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
        <div class="user-avatar-circle me-2 shadow-sm d-flex align-items-center justify-content-center bg-light text-primary fw-bold" style="width: 35px; height: 35px; border-radius: 50%;">
            <?php 
                if (isset($_SESSION['AdminName'])) {
                    // Extrae las iniciales del nombre real
                    $partes = explode(" ", $_SESSION['AdminName']);
                    $iniciales = substr($partes[0], 0, 1);
                    if (count($partes) > 1) $iniciales .= substr($partes[1], 0, 1);
                    echo strtoupper($iniciales);
                } else {
                    echo "AD";
                }
            ?>
        </div>
        <span class="d-none d-sm-inline mx-1">
            <?php echo $_SESSION['AdminName'] ?? 'Administrador'; ?>
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow">
        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
    </ul>
</div>
</div>
    </div>
</nav>