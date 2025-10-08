<?php
// 1. Incluir el archivo de conexión
// Usamos require_once para asegurar que el archivo se incluya una sola vez y detener la ejecución si no se encuentra.
require_once(__DIR__ . '/conexion.php');


// 2. Verificar que los datos lleguen por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. Recoger y sanear los datos del formulario
    // trim() elimina espacios en blanco al inicio y final
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol'];

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($rol)) {
        die("Error: Todos los campos marcados como requeridos deben ser completados.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: El formato del correo electrónico no es válido.");
    }

    // 4. Hashear la contraseña por seguridad
    // ¡NUNCA guardes contraseñas en texto plano!
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // 5. Preparar la consulta SQL según el rol seleccionado
    $sql = "";
    if ($rol == 'cliente') {
        // Preparar la inserción en la tabla `clientes`
        $sql = "INSERT INTO clientes (nombre, apellido, email, telefono, password) VALUES (?, ?, ?, ?, ?)";
    } elseif ($rol == 'vendedor') {
        // Preparar la inserción en la tabla `vendedores`
        // Asumimos que la fecha de contratación es la fecha actual
        $fecha_contratacion = date('Y-m-d');
        $sql = "INSERT INTO vendedores (nombre, apellido, email, telefono, fecha_contratacion, password) VALUES (?, ?, ?, ?, ?, ?)";
    } else {
        die("Error: Rol no válido seleccionado.");
    }

    // 6. Usar sentencias preparadas para prevenir inyección SQL
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    // 7. Vincular parámetros y ejecutar
    if ($rol == 'cliente') {
        // s = string (cadena de texto)
        $stmt->bind_param("sssss", $nombre, $apellido, $email, $telefono, $password_hashed);
    } elseif ($rol == 'vendedor') {
        // s = string, s = string, s = string...
        $stmt->bind_param("ssssss", $nombre, $apellido, $email, $telefono, $fecha_contratacion, $password_hashed);
    }

    if ($stmt->execute()) {
        echo "¡Registro exitoso! Ahora puedes iniciar sesión.";
        // Aquí podrías redirigir al usuario a la página de login
        // header('Location: login.php');
        // exit();
    } else {
        // Verificar si es un error de correo duplicado
        if ($conn->errno == 1062) { // 1062 es el código de error para entrada duplicada
            echo "Error: El correo electrónico '" . htmlspecialchars($email) . "' ya está registrado.";
        } else {
            echo "Error al registrar el usuario: " . $stmt->error;
        }
    }

    // 8. Cerrar la sentencia y la conexión
    $stmt->close();
    $conn->close();

} else {
    // Si alguien intenta acceder al archivo directamente sin enviar datos
    echo "Acceso no permitido.";
}
?>