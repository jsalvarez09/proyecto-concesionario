<?php
// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Detecta la página actual para el menú
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<header>
    <div class="nav container">
        <a href="<?php echo BASE_URL; ?>index.php" class="logo">Motors <span>And</span> Dealers</a>
        <ul class="navbar">
            <li><a href="<?php echo BASE_URL; ?>index.php" class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Inicio</a></li>
            <li><a href="<?php echo BASE_URL; ?>pages/motors/cars.php" class="<?php echo in_array($currentPage, ['cars.php', 'vehiculo_detalles.php', 'editar_vehiculo.php']) ? 'active' : ''; ?>">Vehículos</a></li>
            <?php if(isset($_SESSION['autenticado'])): ?>
                <li><a href="<?php echo BASE_URL; ?>pages/motors/panel.php" class="<?php echo ($currentPage == 'panel.php') ? 'active' : ''; ?>">Mi Panel</a></li>
                <li><a href="<?php echo BASE_URL; ?>includes/logout.php">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>includes/acceso.php" class="<?php echo ($currentPage == 'acceso.php') ? 'active' : ''; ?>">Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>

        <div class="header-icons">
            <i class='bx bx-search' id="search-icon"></i>
        </div>
        <i class='bx bx-menu' id="menu-icon"></i>
        
        <div class="search-box container">
            <input type="search" placeholder="Buscar aquí...">
        </div>
    </div>
</header>