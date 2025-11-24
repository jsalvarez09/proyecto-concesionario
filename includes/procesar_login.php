<?php
// Es CRUCIAL iniciar la sesión al principio de cualquier script que la vaya a usar.
session_start();

// 1. Incluir la conexión a la base de datos
require_once(__DIR__ . '/conexion.php');

// 2. Verificar que los datos lleguen por POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // CORRECCIÓN: Como ambos archivos están en 'includes', la ruta es directa
    header('Location: acceso.php'); 
    exit();
}

// 3. Recoger y sanear los datos
$email = trim($_POST['email']);
$password = trim($_POST['password']);

if (empty($email) || empty($password)) {
    // CORRECCIÓN: Ruta directa
    header('Location: acceso.php?error=Por favor, completa ambos campos');
    exit();
}

// 4. Lógica de autenticación: buscar el email en las 3 tablas de roles

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
        if (password_verify($password, $usuario['password'])) {
            return $usuario; 
        }
    }
    
    $stmt->close();
    return null; 
}

$usuario_encontrado = null;
$rol = null;

if (($usuario_encontrado = verificarCredenciales($conn, 'administradores', $email, $password)) !== null) {
    $rol = 'administrador';
} elseif (($usuario_encontrado = verificarCredenciales($conn, 'vendedores', $email, $password)) !== null) {
    $rol = 'vendedor';
} elseif (($usuario_encontrado = verificarCredenciales($conn, 'clientes', $email, $password)) !== null) {
    $rol = 'cliente';
}

// 5. Manejar el resultado de la autenticación
if ($usuario_encontrado) {
    $_SESSION['usuario_id'] = $usuario_encontrado[$rol . '_id']; 
    $_SESSION['usuario_nombre'] = $usuario_encontrado['nombre'];
    $_SESSION['usuario_email'] = $usuario_encontrado['email'];
    $_SESSION['usuario_rol'] = $rol;
    $_SESSION['autenticado'] = true;

    if ($rol === 'vendedor') {
        $_SESSION['vendedor_id'] = $usuario_encontrado['vendedor_id'];
    }

    // Esta ruta SÍ debe salir de includes e ir a pages/motors
    header('Location: ../pages/motors/cars.php');
    exit();

} else {
    // CORRECCIÓN: Ruta directa si falla el login
    header('Location: acceso.php?error=Correo o contraseña incorrectos');
    exit();
}

$conn->close();
?>