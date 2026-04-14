<?php
include_once('sesion.php'); // Asegura que la sesión esté iniciada
include_once('includes/dbconnection.php');

// Obtenemos el nombre del administrador actual para mostrarlo en el perfil
$adminid = $_SESSION['adminid'];
$stmt = $pdo->prepare("SELECT AdminName FROM tbladmin WHERE ID = ?");
$stmt->execute([$adminid]);
$adminData = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreMostrar = $adminData['AdminName'] ?? 'Administrador';

// También incluimos el actualizador de mora
include_once 'actualizar_mora.php'; 
// Si $adminActual no está definido, lo buscamos una sola vez
if (!isset($adminActual)) {
    $stmt = $pdo->prepare("SELECT Rango FROM tbladmin WHERE ID = ?");
    $stmt->execute([$adminid]);
    $adminActual = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Creamos una variable simple para evitar errores de escritura
$miRango = $adminActual['Rango'] ?? '';


?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary" style="background-color: #1e1e1e !important;">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <span class="fw-bold d-flex align-items-center text-info">
                <img src="imagenes/logoalcaldia.png" alt="S" style="height: 24px; width: auto; margin-right: 2px;">
                Alcaldia - <span class="text-danger ms-1">SAMI CRÉDITOS</span>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSami" aria-controls="navbarSami" aria-expanded="false" aria-label="Menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSami">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">Dashboard</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Expedientes
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li><a class="dropdown-item" href="expedientes_lista.php">Ver Todos</a></li>
                        <li><a class="dropdown-item" href="expedientes_activos.php">Activos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="expedientes_mora.php">En Mora</a></li>
                        <li><a class="dropdown-item text-success" href="expedientes_finalizados.php">Pagados</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Clientes
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?php echo ($miRango == 'Operador') ? 'vista_clientes.php' : 'directorio_clientes.php'; ?>">
                                <i class="bi bi-person-lines-fill me-2"></i> Directorio
                            </a>
                        </li>

                        <?php if ($miRango == 'Administrador' || $miRango == 'Encargado'): ?>
                            <li>
                                <a class="dropdown-item" href="nuevo_cliente.php">
                                    <i class="bi bi-plus-circle me-2"></i> Registrar
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Finanzas
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li><a class="dropdown-item" href="recibos.php">Recibos de Pago</a></li>

                        <?php 
                        $adminid = $_SESSION['adminid'];
                        $stmtRango = $pdo->prepare("SELECT Rango FROM tbladmin WHERE ID = ?");
                        $stmtRango->execute([$adminid]);
                        $adminActual = $stmtRango->fetch(PDO::FETCH_ASSOC);

                        if(isset($adminActual['Rango']) && ($adminActual['Rango'] == "Administrador" || $adminActual['Rango'] == "Encargado")):?>
                            <li><a class="dropdown-item text-info" href="seleccionar_expediente_cuota.php"><i class="fa fa-plus-circle me-1"></i> Asignar Cuotas</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link fw-bold text-success" href="ipauma_dashboard.php">
                        <i class="bi bi-bank2 me-1"></i> IPAUMA
                    </a>
                </li>
                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nombreMostrar); ?>&background=198754&color=fff" class="rounded-circle me-2" width="28">
                        <?php echo htmlspecialchars($nombreMostrar); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg">
                        <li><a class="dropdown-item"><i class="fa fa-user me-2"></i>Conectado como: <br><strong><?php echo $_SESSION['usuario']; ?></a></li>
                        <li><a class="dropdown-item" href="perfil.php"><i class="fa fa-user me-2"></i> Mi Perfil</a></li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li><a class="dropdown-item text-warning" href="logout.php"><i class="fa fa-sign-out me-2"></i> Salir del Sistema</a></li>
                        
                        <?php 
                        $adminid = $_SESSION['adminid'];
                        $stmtRango = $pdo->prepare("SELECT Rango FROM tbladmin WHERE ID = ?");
                        $stmtRango->execute([$adminid]);
                        $adminActual = $stmtRango->fetch(PDO::FETCH_ASSOC);

                        if(isset($adminActual['Rango']) && $adminActual['Rango'] == "Administrador"): 
                        ?>
                            <li>
                                <a class="dropdown-item" href="adminagregar.php">
                                    <i class="fa fa-user-plus me-2"></i> Crear Administrador
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>