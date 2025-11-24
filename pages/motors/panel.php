<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Si el usuario no está autenticado, redirigir
if (!isset($_SESSION['autenticado'])) {
    header('Location: ../../includes/acceso.php');
    exit();
}

// Obtener datos del usuario
$rol = $_SESSION['usuario_rol'];
$id = $_SESSION['usuario_id'];

// --- CORRECCIÓN DEL ERROR DE TABLA 'CLIENTEES' ---
// Definimos manualmente la tabla y el ID según el rol para evitar errores de plural
if ($rol == 'administrador') {
    $tabla = 'administradores';
    $id_column = 'admin_id';
} elseif ($rol == 'vendedor') {
    $tabla = 'vendedores';
    $id_column = 'vendedor_id';
} else {
    // Caso para cliente
    $tabla = 'clientes';
    $id_column = 'cliente_id';
}

$sql_usuario = "SELECT nombre, apellido, email, telefono FROM {$tabla} WHERE {$id_column} = ? LIMIT 1";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();

// --- Determinar qué vista mostrar ---
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard'; 

// --- LÓGICA PARA EL DASHBOARD (ESTADÍSTICAS) ---
$stats = [
    'activos' => 0,
    'vendidos' => 0,
    'ganancias' => 0
];

if ($rol == 'vendedor' || $rol == 'administrador') {
    $sql_stats = "SELECT 
        COUNT(CASE WHEN estado = 'Disponible' THEN 1 END) as activos,
        COUNT(CASE WHEN estado = 'Vendido' THEN 1 END) as vendidos,
        SUM(CASE WHEN estado = 'Vendido' THEN precio_lista ELSE 0 END) as ganancias
        FROM vehiculos WHERE vendedor_id = ?";
        
    $stmt_stats = $conn->prepare($sql_stats);
    $stmt_stats->bind_param("i", $id);
    $stmt_stats->execute();
    $result_stats = $stmt_stats->get_result()->fetch_assoc();
    
    $stats['activos'] = $result_stats['activos'] ?? 0;
    $stats['vendidos'] = $result_stats['vendidos'] ?? 0;
    $stats['ganancias'] = $result_stats['ganancias'] ?? 0;
}

// --- LÓGICA PARA MIS VEHÍCULOS ---
$mis_vehiculos = [];
if ($view == 'vehiculos' && ($rol == 'vendedor' || $rol == 'administrador')) {
    $sql_vehiculos = "SELECT v.vehiculo_id, v.marca, v.modelo, v.precio_lista, v.estado, (SELECT vi.imagen_url FROM vehiculo_imagenes vi WHERE vi.vehiculo_id = v.vehiculo_id AND vi.es_principal = 1 LIMIT 1) as imagen_principal FROM vehiculos v WHERE v.vendedor_id = ?";
    $stmt_vehiculos = $conn->prepare($sql_vehiculos);
    $stmt_vehiculos->bind_param("i", $id);
    $stmt_vehiculos->execute();
    $mis_vehiculos = $stmt_vehiculos->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Motors And Dealers</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        /* Estilos específicos del panel */
        .panel-header { margin-top: 120px; margin-bottom: 2rem; }
        .panel-header h1 { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .panel-header span { font-size: 1rem; color: #555; }
        
        /* Estilos del Dashboard */
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #d90429; }
        .stat-card.active-cars { border-left-color: #007bff; }
        .stat-card.sold-cars { border-left-color: #28a745; }
        .stat-card.earnings { border-left-color: #ffc107; }
        .stat-info h4 { font-size: 0.9rem; color: #777; margin-bottom: 5px; text-transform: uppercase; }
        .stat-info p { font-size: 1.8rem; font-weight: 700; color: #333; margin: 0; }
        .stat-icon { font-size: 2.5rem; color: #eee; }

        .actions .sold-btn { 
            color: #28a745; 
            font-size: 1.4rem;
            margin-left: 8px;
        }
        .actions .sold-btn:hover { 
            color: #1e7e34; 
            transform: scale(1.1); 
        }
        .actions .sold-btn.disabled {
            color: #ccc;
            pointer-events: none;
            cursor: default;
        }
    </style>
</head>
<body>
    
    <?php include '../../includes/header.php'; ?>

    <div class="container panel-header">
        <h1>Panel de Usuario</h1>
        <span>Bienvenido de nuevo, <?php echo htmlspecialchars($usuario['nombre']); ?>.</span>
    </div>
    
    <div class="panel-layout">
        <aside class="panel-sidebar">
            <div class="user-profile">
                <div class="user-avatar"><i class='bx bxs-user'></i></div>
                <div class="user-name"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></div>
                <div class="user-role"><?php echo ucfirst($rol); ?></div>
            </div>
            <nav class="panel-nav">
                <ul>
                    <li><a href="panel.php?view=datos" class="<?php echo ($view == 'datos') ? 'active' : ''; ?>"><i class='bx bxs-user-detail'></i> Mis Datos</a></li>
                    
                    <?php if($rol == 'vendedor' || $rol == 'administrador'): ?>
                        <li><a href="panel.php?view=vehiculos" class="<?php echo ($view == 'vehiculos') ? 'active' : ''; ?>"><i class='bx bxs-car'></i> Mis Vehículos</a></li>
                        <li><a href="panel.php?view=dashboard" class="<?php echo ($view == 'dashboard') ? 'active' : ''; ?>"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <main class="panel-content">
            
            <?php if ($view == 'dashboard' && ($rol == 'vendedor' || $rol == 'administrador')): ?>
            <div class="content-card">
                <h3>Resumen de Actividad</h3>
                <p class="subtitle">Estadísticas generales de tus ventas e inventario.</p>

                <div class="dashboard-grid">
                    <div class="stat-card active-cars">
                        <div class="stat-info"><h4>Activos</h4><p><?php echo $stats['activos']; ?></p></div>
                        <div class="stat-icon"><i class='bx bxs-car'></i></div>
                    </div>
                    <div class="stat-card sold-cars">
                        <div class="stat-info"><h4>Vendidos</h4><p><?php echo $stats['vendidos']; ?></p></div>
                        <div class="stat-icon"><i class='bx bxs-check-circle'></i></div>
                    </div>
                    <div class="stat-card earnings">
                        <div class="stat-info"><h4>Ganancias Totales</h4><p>$<?php echo number_format($stats['ganancias'], 0, ',', '.'); ?></p></div>
                        <div class="stat-icon"><i class='bx bxs-wallet'></i></div>
                    </div>
                </div>
                <?php if($stats['vendidos'] == 0): ?>
                <div style="background: #e9ecef; padding: 20px; border-radius: 8px; text-align: center;">
                    <p>Aún no has registrado ventas. ¡Recuerda marcar tus vehículos como <strong>"Vendido"</strong> cuando cierres un trato!</p>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($view == 'datos'): ?>
            <div class="content-card">
                <h3>Mis Datos Personales</h3>
                <p class="subtitle">Aquí puedes ver y actualizar tu información personal.</p>
                
                <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <p style="color: green; font-weight: 500; margin-bottom: 1rem;">¡Datos actualizados correctamente!</p>
                <?php elseif(isset($_GET['error'])): ?>
                    <p style="color: red; font-weight: 500; margin-bottom: 1rem;"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php endif; ?>

                <form action="actualizar_datos.php" method="POST">
                    <div class="form-grid">
                        <div class="form-group"><label for="nombre">Nombre</label><input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required></div>
                        <div class="form-group"><label for="apellido">Apellido</label><input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required></div>
                        <div class="form-group"><label for="email">Email (no se puede cambiar)</label><input type="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled></div>
                        <div class="form-group"><label for="telefono">Teléfono</label><input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>"></div>
                        <div class="form-group"><label for="rol">Rol</label><input type="text" id="rol" value="<?php echo ucfirst($rol); ?>" disabled></div>
                    </div>
                    <button type="submit" class="btn">Actualizar Datos</button>
                </form>
            </div>

            <?php elseif ($view == 'vehiculos' && ($rol == 'vendedor' || $rol == 'administrador')): ?>
            <div class="content-card vehicle-list">
                <h3>Mis Vehículos Publicados</h3>
                <p class="subtitle">Aquí puedes administrar los vehículos que has subido al inventario.</p>
                
                <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
                    <p style="color: green; font-weight: 500; margin-bottom: 1rem;">¡Vehículo eliminado correctamente!</p>
                <?php elseif(isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
                    <p style="color: green; font-weight: 500; margin-bottom: 1rem;">¡Vehículo actualizado correctamente!</p>
                <?php elseif(isset($_GET['status']) && $_GET['status'] == 'sold'): ?>
                    <p style="color: #28a745; font-weight: 500; margin-bottom: 1rem;">¡Felicidades por tu venta! El vehículo ha sido marcado como vendido.</p>
                <?php elseif(isset($_GET['error'])): ?>
                    <p style="color: red; font-weight: 500; margin-bottom: 1rem;"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr><th>Vehículo</th><th>Estado</th><th>Precio</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($mis_vehiculos as $vehiculo): 
                            // Construir ruta de imagen usando BASE_URL si está disponible
                            $img_src = !empty($vehiculo['imagen_principal']) ? BASE_URL . $vehiculo['imagen_principal'] : BASE_URL . 'assets/img/placeholder.png';
                        ?>
                        <tr>
                            <td>
                                <div class="vehicle-info">
                                    <img src="<?php echo $img_src; ?>" class="vehicle-image">
                                    <span><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span style="padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; background-color: <?php echo ($vehiculo['estado'] == 'Disponible') ? '#d4edda' : '#cce5ff'; ?>; color: <?php echo ($vehiculo['estado'] == 'Disponible') ? '#155724' : '#004085'; ?>;">
                                    <?php echo htmlspecialchars($vehiculo['estado']); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($vehiculo['precio_lista'], 0, ',', '.'); ?></td>
                            <td class="actions">
                                <a href="editar_vehiculo.php?id=<?php echo $vehiculo['vehiculo_id']; ?>" class="edit-btn" title="Editar"><i class='bx bxs-edit'></i></a>
                                <a href="eliminar_vehiculo.php?id=<?php echo $vehiculo['vehiculo_id']; ?>" class="delete-btn" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este vehículo?');"><i class='bx bxs-trash'></i></a>
                                
                                <?php if ($vehiculo['estado'] == 'Disponible'): ?>
                                    <a href="marcar_vendido.php?id=<?php echo $vehiculo['vehiculo_id']; ?>" 
                                       class="sold-btn" 
                                       title="Marcar como Vendido"
                                       onclick="return confirm('¿Confirmas que has vendido este vehículo? Pasará a sumar a tus ganancias.');">
                                       <i class='bx bxs-dollar-circle'></i>
                                    </a>
                                <?php else: ?>
                                    <span class="sold-btn disabled" title="Ya vendido"><i class='bx bxs-check-circle'></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($mis_vehiculos)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 20px;">No has publicado ningún vehículo.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>