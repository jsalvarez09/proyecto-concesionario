<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Seguridad: verificar autenticación, rol y método POST
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
    // 1. ACTUALIZAR DATOS PRINCIPALES
    $sql_update = ($rol == 'administrador') 
        ? "UPDATE vehiculos SET placa=?, marca=?, modelo=?, anio=?, precio_lista=?, descripcion=? WHERE vehiculo_id=?" 
        : "UPDATE vehiculos SET placa=?, marca=?, modelo=?, anio=?, precio_lista=?, descripcion=? WHERE vehiculo_id=? AND vendedor_id=?";
    
    $stmt_update = $conn->prepare($sql_update);
    
    // --- CORRECCIÓN AQUÍ: Cambié la 'd' por una 's' en la 6ta posición ---
    if ($rol == 'administrador') {
        // Antes: "sssisdi" -> Ahora: "sssissi" (descripción es String, no Double)
        $stmt_update->bind_param("sssissi", $placa, $marca, $modelo, $anio, $precio_lista, $descripcion, $vehiculo_id);
    } else {
        // Antes: "sssisdii" -> Ahora: "sssissii"
        $stmt_update->bind_param("sssissii", $placa, $marca, $modelo, $anio, $precio_lista, $descripcion, $vehiculo_id, $usuario_id);
    }
    $stmt_update->execute();

    // 2. ELIMINAR IMÁGENES MARCADAS
    if (!empty($_POST['eliminar_imagenes'])) {
        foreach ($_POST['eliminar_imagenes'] as $imagen_id) {
            // Obtener URL para borrar archivo físico
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
            // Borrar de la BD
            $stmt_delete_img = $conn->prepare("DELETE FROM vehiculo_imagenes WHERE imagen_id = ?");
            $stmt_delete_img->bind_param("i", $imagen_id);
            $stmt_delete_img->execute();
        }
    }

    // 3. AÑADIR NUEVAS IMÁGENES
    if (isset($_FILES['nuevas_imagenes']) && count($_FILES['nuevas_imagenes']['name']) > 0 && $_FILES['nuevas_imagenes']['error'][0] != 4) {
        $directorio_subidas = __DIR__ . '/../../uploads/';
        if (!file_exists($directorio_subidas)) { mkdir($directorio_subidas, 0777, true); }

        foreach ($_FILES['nuevas_imagenes']['name'] as $key => $name) {
            if ($_FILES['nuevas_imagenes']['error'][$key] == 0) {
                $nombre_temporal = $_FILES['nuevas_imagenes']['tmp_name'][$key];
                $nombre_archivo_unico = uniqid() . '-' . basename($name);
                $ruta_archivo_final = $directorio_subidas . $nombre_archivo_unico;
                
                if (move_uploaded_file($nombre_temporal, $ruta_archivo_final)) {
                    $imagen_url_db = 'uploads/' . $nombre_archivo_unico;
                    $es_principal = 0; 
                    
                    $stmt_insert_img = $conn->prepare("INSERT INTO vehiculo_imagenes (vehiculo_id, imagen_url, es_principal) VALUES (?, ?, ?)");
                    $stmt_insert_img->bind_param("isi", $vehiculo_id, $imagen_url_db, $es_principal);
                    $stmt_insert_img->execute();
                }
            }
        }
    }

    // 4. REPARAR IMAGEN PRINCIPAL (Lógica que arreglamos antes)
    $sql_check_main = "SELECT imagen_id FROM vehiculo_imagenes WHERE vehiculo_id = ? AND es_principal = 1 LIMIT 1";
    $stmt_check = $conn->prepare($sql_check_main);
    $stmt_check->bind_param("i", $vehiculo_id);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows === 0) {
        $sql_fix_main = "UPDATE vehiculo_imagenes SET es_principal = 1 WHERE vehiculo_id = ? ORDER BY imagen_id ASC LIMIT 1";
        $stmt_fix = $conn->prepare($sql_fix_main);
        $stmt_fix->bind_param("i", $vehiculo_id);
        $stmt_fix->execute();
    }

    $conn->commit();
    header('Location: panel.php?view=vehiculos&status=updated');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header('Location: panel.php?view=vehiculos&error=' . urlencode('Error al actualizar: ' . $e->getMessage()));
    exit();
}
?>