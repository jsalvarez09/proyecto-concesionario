<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Si el usuario no está autenticado, redirigir
if (!isset($_SESSION['autenticado'])) {
    header('Location: acceso.php');
    exit();
}

// Obtener datos del usuario
$rol = $_SESSION['usuario_rol'];
$id = $_SESSION['usuario_id'];
$tabla = ($rol == 'administrador') ? 'administradores' : $rol . 'es';
$id_column = ($rol == 'administrador') ? 'admin_id' : $rol . '_id';

$sql_usuario = "SELECT nombre, apellido, email, telefono FROM {$tabla} WHERE {$id_column} = ? LIMIT 1";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();

// Obtener los vehículos del usuario
$mis_vehiculos = [];
if ($rol == 'vendedor' || $rol == 'administrador') {
    $sql_vehiculos = "SELECT v.vehiculo_id, v.marca, v.modelo, v.precio_lista, v.estado, (SELECT vi.imagen_url FROM vehiculo_imagenes vi WHERE vi.vehiculo_id = v.vehiculo_id AND vi.es_principal = 1 LIMIT 1) as imagen_principal FROM vehiculos v WHERE v.vendedor_id = ?";
    $stmt_vehiculos = $conn->prepare($sql_vehiculos);
    $stmt_vehiculos->bind_param("i", $id);
    $stmt_vehiculos->execute();
    $mis_vehiculos = $stmt_vehiculos->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Determinar qué vista mostrar
$view = isset($_GET['view']) && $_GET['view'] == 'vehiculos' ? 'vehiculos' : 'datos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Motors And Dealers</title>
    <link rel="stylesheet" href="/NUEVO_FROME/assets/css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        /* Estilos solo para ajustar el título nuevo y el espaciado */
        .panel-header {
            margin-top: 120px; /* Espacio para el header fijo */
            margin-bottom: 2rem;
        }
        .panel-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .panel-header span {
            font-size: 1rem;
            color: #555;
        }
    </style>
</head>
<body>
    
    <?php include '../../includes/header.php'; ?>

    <div class="container panel-header">
        <h1>Panel de Usuario</h1>
        <span>Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>.</span>
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
                    <li><a href="panel.php?view=vehiculos" class="<?php echo ($view == 'vehiculos') ? 'active' : ''; ?>"><i class='bx bxs-car'></i> Mis Vehículos</a></li>
                </ul>
            </nav>
        </aside>

        <main class="panel-content">
            <?php if ($view == 'datos'): ?>
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

            <?php else: // Si la vista es 'vehiculos' ?>
            <div class="content-card vehicle-list">
                <h3>Mis Vehículos Publicados</h3>
                <p class="subtitle">Aquí puedes administrar los vehículos que has subido al inventario.</p>
                
                <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
                    <p style="color: green; font-weight: 500; margin-bottom: 1rem;">¡Vehículo eliminado correctamente!</p>
                <?php elseif(isset($_GET['error'])): ?>
                    <p style="color: red; font-weight: 500; margin-bottom: 1rem;"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr><th>Vehículo</th><th>Estado</th><th>Precio</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($mis_vehiculos as $vehiculo): ?>
                        <tr>
                            <td>
                                <div class="vehicle-info">
                                    <img src="../../<?php echo htmlspecialchars($vehiculo['imagen_principal'] ?? 'assets/img/placeholder.png'); ?>" class="vehicle-image">
                                    <span><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($vehiculo['estado']); ?></td>
                            <td>$<?php echo number_format($vehiculo['precio_lista'], 0, ',', '.'); ?></td>
                            <td class="actions">
                                <a href="editar_vehiculo.php?id=<?php echo $vehiculo['vehiculo_id']; ?>" class="edit-btn" title="Editar"><i class='bx bxs-edit'></i></a>
                                <a href="eliminar_vehiculo.php?id=<?php echo $vehiculo['vehiculo_id']; ?>" class="delete-btn" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este vehículo?');"><i class='bx bxs-trash'></i></a>
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