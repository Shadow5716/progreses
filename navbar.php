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

    /* Estilo específico para el botón de IPAUMA */
    .btn-ipauma {
        background-color: #28a745 !important;
        color: white !important;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-ipauma:hover {
        background-color: #218838 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .user-avatar-circle {
        width: 40px;
        height: 40px;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-custom shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="imagenes/alcaldia-maracaibo.png" alt="Logo" class="me-2" style="height: 45px; width: auto;">
            <div class="d-flex flex-column line-height-1">
                <span class="fw-bold h5 mb-0">Programa de Reportes de Gestión</span>
                <small>Dirección de Tecnología</small>
            </div>
        </a>

        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <i class="bi bi-list fs-2"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                
               <div class="ms-auto d-flex align-items-center">
                    <a href="nueva_solicitud.php" class="btn btn-solicitud me-2 shadow-sm">
                        <i class="bi bi-plus-lg me-2"></i>Nueva Solicitud
                    </a>

                <li class="nav-item me-2">
                    <a href="ipauma_dashboard.php" class="btn btn-ipauma shadow-sm d-flex align-items-center">
                        <i class="bi bi-bank2 me-2"></i>
                        <span>Entrar a IPAUPMA</span>
                    </a>
                </li>

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
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                        <li>
                            <a class="dropdown-item py-2" href="adminprofile.php">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="change-password.php">
                                <i class="bi bi-shield-lock me-2"></i>Seguridad
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>

            </ul>
        </div>
    </div>
</nav>