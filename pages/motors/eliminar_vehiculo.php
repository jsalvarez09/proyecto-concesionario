<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Seguridad: verificar autenticación y rol
if (!isset($_SESSION['autenticado']) || !in_array($_SESSION['usuario_rol'], ['vendedor', 'administrador'])) {
    header('Location: panel.php?view=vehiculos&error=' . urlencode('Acceso no autorizado.'));
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: panel.php?view=vehiculos&error=' . urlencode('ID de vehículo no válido.'));
    exit();
}

$vehiculo_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'];

$conn->begin_transaction();
try {
    // Para administradores, no es necesario comprobar el vendedor_id
    $sql_verify_owner = ($rol == 'administrador')
        ? "SELECT vehiculo_id FROM vehiculos WHERE vehiculo_id = ?"
        : "SELECT vehiculo_id FROM vehiculos WHERE vehiculo_id = ? AND vendedor_id = ?";

    $stmt_verify = $conn->prepare($sql_verify_owner);
    if ($rol == 'administrador') {
        $stmt_verify->bind_param("i", $vehiculo_id);
    } else {
        $stmt_verify->bind_param("ii", $vehiculo_id, $usuario_id);
    }
    $stmt_verify->execute();
    if ($stmt_verify->get_result()->num_rows === 0) {
        throw new Exception("No tienes permiso para eliminar este vehículo o no existe.");
    }

    // Obtener las URLs de las imágenes para borrarlas del servidor
    $sql_select_imgs = "SELECT imagen_url FROM vehiculo_imagenes WHERE vehiculo_id = ?";
    $stmt_select_imgs = $conn->prepare($sql_select_imgs);
    $stmt_select_imgs->bind_param("i", $vehiculo_id);
    $stmt_select_imgs->execute();
    $resultado_imagenes = $stmt_select_imgs->get_result();

    // Borrar los archivos físicos de imagen
    while ($fila = $resultado_imagenes->fetch_assoc()) {
        $ruta_archivo = __DIR__ . '/../../' . $fila['imagen_url'];
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo);
        }
    }

    // Borrar registros de la base de datos (imágenes, luego vehículo)
    // Nota: Si hay ventas asociadas, MySQL podría impedir el borrado por las claves foráneas.
    // Primero borramos las imágenes, que no tienen dependencias.
    $sql_delete_img = "DELETE FROM vehiculo_imagenes WHERE vehiculo_id = ?";
    $stmt_delete_img = $conn->prepare($sql_delete_img);
    $stmt_delete_img->bind_param("i", $vehiculo_id);
    $stmt_delete_img->execute();

    // Finalmente, borramos el vehículo
    $sql_delete_veh = "DELETE FROM vehiculos WHERE vehiculo_id = ?";
    $stmt_delete_veh = $conn->prepare($sql_delete_veh);
    $stmt_delete_veh->bind_param("i", $vehiculo_id);
    $stmt_delete_veh->execute();

    $conn->commit();
    header('Location: panel.php?view=vehiculos&status=deleted');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    // Captura errores de claves foráneas (ej. si el auto está en una venta)
    if ($e->getCode() == 1451) { // Código de error de MySQL para violación de clave foránea
        $error_msg = "No se puede eliminar el vehículo porque está asociado a una venta registrada.";
    } else {
        $error_msg = $e->getMessage();
    }
    header('Location: panel.php?view=vehiculos&error=' . urlencode($error_msg));
    exit();
}