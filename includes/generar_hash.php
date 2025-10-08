<?php
// Elige una contraseña segura
$contrasenaParaAdmin = 'superadmin123';

// Genera el hash
$hashSeguro = password_hash($contrasenaParaAdmin, PASSWORD_DEFAULT);

// Muestra el hash para que lo copies
echo "Copia este hash para tu base de datos: " . $hashSeguro;
?>