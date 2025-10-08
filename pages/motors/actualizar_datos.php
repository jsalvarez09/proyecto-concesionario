<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Seguridad: verificar que el usuario esté autenticado y los datos lleguen por POST
if (!isset($_SESSION['autenticado']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: acceso.php');
    exit();
}

// Recoger datos del formulario
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$telefono = trim($_POST['telefono']);
$id = $_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'];
$tabla = $rol . 'es'; // Construir nombre de la tabla

// Validar que los campos no estén vacíos
if (empty($nombre) || empty($apellido)) {
    header('Location: panel.php?error=El nombre y el apellido son obligatorios');
    exit();
}

// Preparar y ejecutar la actualización
$sql = "UPDATE {$tabla} SET nombre = ?, apellido = ?, telefono = ? WHERE {$rol}_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $nombre, $apellido, $telefono, $id);

if ($stmt->execute()) {
    // Actualizar el nombre en la sesión por si cambió
    $_SESSION['usuario_nombre'] = $nombre;
    header('Location: panel.php?status=success');
} else {
    header('Location: panel.php?error=No se pudieron actualizar los datos');
}

$stmt->close();
$conn->close();
?>