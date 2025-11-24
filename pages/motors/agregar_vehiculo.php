<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

if (!isset($_SESSION['usuario_rol']) || !in_array($_SESSION['usuario_rol'], ['administrador', 'vendedor'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: cars.php');
    exit();
}

$placa = strtoupper(trim($_POST['placa']));
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$anio = (int) $_POST['anio']; // Castear a entero
$precio_lista = (float) $_POST['precio_lista']; // Castear a float/decimal
$descripcion = trim($_POST['descripcion']);
$vendedor_id = $_SESSION['vendedor_id'];
$estado = 'Disponible'; 

if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $placa)) {
    header('Location: cars.php?error=Formato de placa inválido');
    exit();
}

$conn->begin_transaction();

try {
    // CORRECCIÓN: Tipos de datos -> s=string, i=integer, d=double
    // placa(s), marca(s), modelo(s), anio(i), precio(d), estado(s), vendedor(i), desc(s)
    $sql_vehiculo = "INSERT INTO vehiculos (placa, marca, modelo, anio, precio_lista, estado, vendedor_id, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_vehiculo = $conn->prepare($sql_vehiculo);
    $stmt_vehiculo->bind_param("sssidsis", $placa, $marca, $modelo, $anio, $precio_lista, $estado, $vendedor_id, $descripcion);
    $stmt_vehiculo->execute();

    $vehiculo_id = $conn->insert_id;

    if (isset($_FILES['imagenes']) && count($_FILES['imagenes']['name']) > 0) {
        $directorio_subidas = __DIR__ . '/../../uploads/';
        if (!file_exists($directorio_subidas)) { mkdir($directorio_subidas, 0777, true); }
        
        $es_primera_imagen = true;
        // Tipos MIME permitidos
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        foreach ($_FILES['imagenes']['name'] as $key => $name) {
            if ($_FILES['imagenes']['error'][$key] == 0) {
                $tmp_name = $_FILES['imagenes']['tmp_name'][$key];
                
                // VALIDACIÓN DE SEGURIDAD (MIME TYPE)
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->file($tmp_name);
                
                if (!in_array($mime_type, $allowed_mime_types)) {
                    throw new Exception("El archivo $name no es una imagen válida.");
                }

                $nombre_archivo_unico = uniqid() . '-' . basename($name);
                $ruta_archivo_final = $directorio_subidas . $nombre_archivo_unico;

                if (move_uploaded_file($tmp_name, $ruta_archivo_final)) {
                    $imagen_url_db = 'uploads/' . $nombre_archivo_unico;
                    $es_principal = $es_primera_imagen ? 1 : 0;

                    $sql_imagen = "INSERT INTO vehiculo_imagenes (vehiculo_id, imagen_url, es_principal) VALUES (?, ?, ?)";
                    $stmt_imagen = $conn->prepare($sql_imagen);
                    $stmt_imagen->bind_param("isi", $vehiculo_id, $imagen_url_db, $es_principal);
                    $stmt_imagen->execute();
                    
                    $es_primera_imagen = false;
                }
            }
        }
    }

    $conn->commit();
    header('Location: cars.php?status=success');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header('Location: cars.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>