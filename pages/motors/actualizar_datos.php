<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Seguridad: verificar que el usuario esté autenticado y los datos lleguen por POST
if (!isset($_SESSION['autenticado']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ../../includes/acceso.php');
    exit();
}

// Recoger datos del formulario
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$telefono = trim($_POST['telefono']);
$id = $_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'];

// --- CORRECCIÓN: Definición correcta de la tabla e ID según rol ---
if ($rol == 'administrador') {
    $tabla = 'administradores';
    $id_columna = 'admin_id';
} elseif ($rol == 'vendedor') {
    $tabla = 'vendedores';
    $id_columna = 'vendedor_id';
} else {
    $tabla = 'clientes';
    $id_columna = 'cliente_id';
}

// Validar que los campos no estén vacíos
if (empty($nombre) || empty($apellido)) {
    header('Location: panel.php?view=datos&error=El nombre y el apellido son obligatorios');
    exit();
}

// Preparar y ejecutar la actualización
// Usamos dinámicamente $tabla y $id_columna
$sql = "UPDATE {$tabla} SET nombre = ?, apellido = ?, telefono = ? WHERE {$id_columna} = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $nombre, $apellido, $telefono, $id);

if ($stmt->execute()) {
    // Actualizar el nombre en la sesión por si cambió
    $_SESSION['usuario_nombre'] = $nombre;
    header('Location: panel.php?view=datos&status=success');
} else {
    header('Location: panel.php?view=datos&error=No se pudieron actualizar los datos');
}

$stmt->close();
$conn->close();
?>