<?php
// Es CRUCIAL iniciar la sesión al principio de cualquier script que la vaya a usar.
session_start();

// 1. Incluir la conexión a la base de datos
require_once(__DIR__ . '/conexion.php');

// 2. Verificar que los datos lleguen por POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Si no es por POST, redirigimos al login
    header('Location: ../pages/motors/acceso.php'); // Redirigir a la página de acceso unificada
    exit();
}

// 3. Recoger y sanear los datos
$email = trim($_POST['email']);
$password = trim($_POST['password']);

if (empty($email) || empty($password)) {
    // Redirigir con un mensaje de error si los campos están vacíos
    header('Location: ../pages/motors/acceso.php?error=Por favor, completa ambos campos');
    exit();
}

// 4. Lógica de autenticación: buscar el email en las 3 tablas de roles

// Función para buscar el usuario y verificar la contraseña.
function verificarCredenciales($conn, $tabla, $email, $password) {
    $sql = "SELECT * FROM {$tabla} WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        return null;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        // Verificar la contraseña hasheada
        if (password_verify($password, $usuario['password'])) {
            return $usuario; // ¡Credenciales correctas!
        }
    }
    
    $stmt->close();
    return null; // El usuario no se encontró o la contraseña es incorrecta
}

// Intentamos verificar en cada tabla
$usuario_encontrado = null;
$rol = null;

// Primero buscamos en administradores
if (($usuario_encontrado = verificarCredenciales($conn, 'administradores', $email, $password)) !== null) {
    $rol = 'administrador';
} 
// Si no, buscamos en vendedores
elseif (($usuario_encontrado = verificarCredenciales($conn, 'vendedores', $email, $password)) !== null) {
    $rol = 'vendedor';
} 
// Si no, buscamos en clientes
elseif (($usuario_encontrado = verificarCredenciales($conn, 'clientes', $email, $password)) !== null) {
    $rol = 'cliente';
}

// 5. Manejar el resultado de la autenticación
if ($usuario_encontrado) {
    // ¡Inicio de sesión exitoso!
    
    // Guardamos los datos esenciales del usuario en la sesión
    $_SESSION['usuario_id'] = $usuario_encontrado[$rol . '_id']; // admin_id, vendedor_id, etc.
    $_SESSION['usuario_nombre'] = $usuario_encontrado['nombre'];
    $_SESSION['usuario_email'] = $usuario_encontrado['email'];
    $_SESSION['usuario_rol'] = $rol;
    $_SESSION['autenticado'] = true;

    // --- LA CORRECCIÓN CLAVE ESTÁ AQUÍ ---
    // Si el rol es 'vendedor', guardamos también su ID en la variable específica
    if ($rol === 'vendedor') {
        $_SESSION['vendedor_id'] = $usuario_encontrado['vendedor_id'];
    }

    // Redirigimos al usuario a la página de vehículos.
    header('Location: ../pages/motors/cars.php');
    exit();

} else {
    // Si llegamos aquí, es que las credenciales son incorrectas
    header('Location: ../pages/motors/acceso.php?error=Correo o contraseña incorrectos');
    exit();
}

// Cerramos la conexión
$conn->close();
?>