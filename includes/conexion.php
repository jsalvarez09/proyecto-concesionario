<?php
// Parámetros de la base de datos
$servername = "localhost"; // Generalmente es 'localhost'
$username = "root";        // Tu usuario de la base de datos (por defecto en XAMPP es 'root')
$password = "";            // Tu contraseña de la base de datos (por defecto en XAMPP está vacía)
$dbname = "concesionario_db"; // El nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Establecer el juego de caracteres a UTF-8 para evitar problemas con tildes y ñ
$conn->set_charset("utf8");

// Verificar la conexión
if ($conn->connect_error) {
    // Si hay un error, se termina la ejecución y se muestra el error
    die("Conexión fallida: " . $conn->connect_error);
}

// Si todo va bien, el script continúa. No es necesario un 'else'.
?>