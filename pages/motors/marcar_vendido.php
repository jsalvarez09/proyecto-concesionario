<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// 1. Seguridad: verificar autenticación y rol
if (!isset($_SESSION['autenticado']) || !in_array($_SESSION['usuario_rol'], ['vendedor', 'administrador'])) {
    header('Location: panel.php?view=vehiculos&error=' . urlencode('Acceso no autorizado.'));
    exit();
}

// 2. Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: panel.php?view=vehiculos&error=' . urlencode('ID de vehículo no válido.'));
    exit();
}

$vehiculo_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'];

try {
    // 3. Actualizar el estado a 'Vendido'
    // El WHERE asegura que solo el dueño (o un admin) pueda venderlo
    $sql = "UPDATE vehiculos 
            SET estado = 'Vendido' 
            WHERE vehiculo_id = ? AND (vendedor_id = ? OR ? = 'administrador')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $vehiculo_id, $usuario_id, $rol);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Éxito: Redirigir con mensaje
        header('Location: panel.php?view=vehiculos&status=sold');
    } else {
        // Fallo: Probablemente no es dueño del vehículo o ya estaba vendido
        header('Location: panel.php?view=vehiculos&error=' . urlencode('No se pudo marcar como vendido. Verifica permisos o el estado actual.'));
    }
    
    $stmt->close();
    $conn->close();
    exit();

} catch (Exception $e) {
    header('Location: panel.php?view=vehiculos&error=' . urlencode('Error: ' . $e->getMessage()));
    exit();
}
?>