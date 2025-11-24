<?php
require_once(__DIR__ . '/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol'];

    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($rol)) {
        die("Error: Todos los campos marcados como requeridos deben ser completados.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: El formato del correo electrónico no es válido.");
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $sql = "";
    if ($rol == 'cliente') {
        $sql = "INSERT INTO clientes (nombre, apellido, email, telefono, password) VALUES (?, ?, ?, ?, ?)";
    } elseif ($rol == 'vendedor') {
        $fecha_contratacion = date('Y-m-d');
        $sql = "INSERT INTO vendedores (nombre, apellido, email, telefono, fecha_contratacion, password) VALUES (?, ?, ?, ?, ?, ?)";
    } else {
        die("Error: Rol no válido seleccionado.");
    }

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    if ($rol == 'cliente') {
        $stmt->bind_param("sssss", $nombre, $apellido, $email, $telefono, $password_hashed);
    } elseif ($rol == 'vendedor') {
        $stmt->bind_param("ssssss", $nombre, $apellido, $email, $telefono, $fecha_contratacion, $password_hashed);
    }

    if ($stmt->execute()) {
        // CORRECCIÓN: Redirección con mensaje de éxito
        header('Location: acceso.php?status=success_register');
        exit();
    } else {
        if ($conn->errno == 1062) {
            echo "Error: El correo electrónico '" . htmlspecialchars($email) . "' ya está registrado.";
        } else {
            echo "Error al registrar el usuario: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();

} else {
    echo "Acceso no permitido.";
}
?>