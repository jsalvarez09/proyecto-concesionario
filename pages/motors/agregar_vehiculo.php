<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// 1. Seguridad y verificación de rol
if (!isset($_SESSION['usuario_rol']) || !in_array($_SESSION['usuario_rol'], ['administrador', 'vendedor'])) {
    die("Acceso denegado. No tienes permiso para realizar esta acción.");
}

// 2. Verificar que los datos lleguen por POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: cars.php');
    exit();
}

// 3. Recoger datos del formulario
$placa = strtoupper(trim($_POST['placa']));
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$anio = $_POST['anio'];
$precio_lista = $_POST['precio_lista'];
$descripcion = trim($_POST['descripcion']);
$vendedor_id = $_SESSION['vendedor_id'];

// LÍNEA CLAVE: Aquí aseguramos que el estado siempre sea 'Disponible'
$estado = 'Disponible'; 

// 4. Validación del formato de la placa en el servidor
if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $placa)) {
    header('Location: cars.php?error=El formato de la placa es incorrecto. Deben ser 3 letras mayúsculas y 3 números.');
    exit();
}

// 5. Iniciar transacción
$conn->begin_transaction();

try {
    // 6. Insertar datos principales del vehículo (con descripción)
    $sql_vehiculo = "INSERT INTO vehiculos (placa, marca, modelo, anio, precio_lista, estado, vendedor_id, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_vehiculo = $conn->prepare($sql_vehiculo);
    // Se incluye la variable $estado en la consulta
    $stmt_vehiculo->bind_param("sssisdis", $placa, $marca, $modelo, $anio, $precio_lista, $estado, $vendedor_id, $descripcion);
    $stmt_vehiculo->execute();

    // 7. Obtener el ID del vehículo insertado
    $vehiculo_id = $conn->insert_id;
    if ($vehiculo_id == 0) {
        throw new Exception("No se pudo obtener el ID del nuevo vehículo.");
    }

    // 8. Procesar y guardar las imágenes subidas
    if (isset($_FILES['imagenes']) && count($_FILES['imagenes']['name']) > 0 && $_FILES['imagenes']['error'][0] != 4) {
        $directorio_subidas = __DIR__ . '/../../uploads/';
        $es_primera_imagen = true;

        foreach ($_FILES['imagenes']['name'] as $key => $name) {
            if ($_FILES['imagenes']['error'][$key] == 0) {
                $nombre_temporal = $_FILES['imagenes']['tmp_name'][$key];
                $nombre_archivo_unico = uniqid() . '-' . basename($name);
                $ruta_archivo_final = $directorio_subidas . $nombre_archivo_unico;

                if (move_uploaded_file($nombre_temporal, $ruta_archivo_final)) {
                    $imagen_url_db = 'uploads/' . $nombre_archivo_unico;
                    $es_principal = $es_primera_imagen ? 1 : 0;

                    $sql_imagen = "INSERT INTO vehiculo_imagenes (vehiculo_id, imagen_url, es_principal) VALUES (?, ?, ?)";
                    $stmt_imagen = $conn->prepare($sql_imagen);
                    $stmt_imagen->bind_param("isi", $vehiculo_id, $imagen_url_db, $es_principal);
                    $stmt_imagen->execute();
                    $stmt_imagen->close();

                    $es_primera_imagen = false;
                }
            }
        }
    } else {
        throw new Exception("Es obligatorio subir al menos una imagen para el vehículo.");
    }

    // 9. Si todo salió bien, confirmar los cambios
    $conn->commit();
    $stmt_vehiculo->close();
    $conn->close();

    // Redirección en caso de ÉXITO
    header('Location: cars.php?status=success');
    exit();

} catch (Exception $e) {
    // 10. Si algo falló, revertir todos los cambios
    $conn->rollback();
    
    // Redirección en caso de ERROR
    header('Location: cars.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>