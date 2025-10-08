<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Seguridad: verificar autenticación, rol y que los datos lleguen por POST
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['autenticado']) || !in_array($_SESSION['usuario_rol'], ['vendedor', 'administrador'])) {
    die("Acceso no autorizado.");
}

// Recoger datos del formulario
$vehiculo_id = $_POST['vehiculo_id'];
$placa = strtoupper(trim($_POST['placa']));
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$anio = $_POST['anio'];
$precio_lista = $_POST['precio_lista'];
$descripcion = trim($_POST['descripcion']);
$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'];

$conn->begin_transaction();
try {
    // 1. ACTUALIZAR DATOS PRINCIPALES DEL VEHÍCULO
    $sql_update = "UPDATE vehiculos SET placa=?, marca=?, modelo=?, anio=?, precio_lista=?, descripcion=? WHERE vehiculo_id=? AND (vendedor_id=? OR ? = 'administrador')";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssisdisi", $placa, $marca, $modelo, $anio, $precio_lista, $descripcion, $vehiculo_id, $usuario_id, $rol);
    $stmt_update->execute();

    // 2. ELIMINAR IMÁGENES MARCADAS
    if (!empty($_POST['eliminar_imagenes'])) {
        foreach ($_POST['eliminar_imagenes'] as $imagen_id) {
            // Primero, obtener la URL para borrar el archivo físico
            $stmt_get_url = $conn->prepare("SELECT imagen_url FROM vehiculo_imagenes WHERE imagen_id = ?");
            $stmt_get_url->bind_param("i", $imagen_id);
            $stmt_get_url->execute();
            $imagen = $stmt_get_url->get_result()->fetch_assoc();
            if ($imagen) {
                $ruta_archivo = __DIR__ . '/../../' . $imagen['imagen_url'];
                if (file_exists($ruta_archivo)) {
                    unlink($ruta_archivo);
                }
            }
            // Luego, borrar de la base de datos
            $stmt_delete_img = $conn->prepare("DELETE FROM vehiculo_imagenes WHERE imagen_id = ?");
            $stmt_delete_img->bind_param("i", $imagen_id);
            $stmt_delete_img->execute();
        }
    }

    // 3. AÑADIR NUEVAS IMÁGENES
    if (isset($_FILES['nuevas_imagenes']) && $_FILES['nuevas_imagenes']['error'][0] != 4) {
        $directorio_subidas = __DIR__ . '/../../uploads/';
        foreach ($_FILES['nuevas_imagenes']['name'] as $key => $name) {
            if ($_FILES['nuevas_imagenes']['error'][$key] == 0) {
                $nombre_temporal = $_FILES['nuevas_imagenes']['tmp_name'][$key];
                $nombre_archivo_unico = uniqid() . '-' . basename($name);
                $ruta_archivo_final = $directorio_subidas . $nombre_archivo_unico;
                if (move_uploaded_file($nombre_temporal, $ruta_archivo_final)) {
                    $imagen_url_db = 'uploads/' . $nombre_archivo_unico;
                    $es_principal = 0; // Las nuevas imágenes no serán principales por defecto
                    $stmt_insert_img = $conn->prepare("INSERT INTO vehiculo_imagenes (vehiculo_id, imagen_url, es_principal) VALUES (?, ?, ?)");
                    $stmt_insert_img->bind_param("isi", $vehiculo_id, $imagen_url_db, $es_principal);
                    $stmt_insert_img->execute();
                }
            }
        }
    }

    $conn->commit();
    header('Location: panel.php?view=vehiculos&status=updated');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header('Location: panel.php?view=vehiculos&error=' . urlencode('Error al actualizar: ' . $e->getMessage()));
    exit();
}