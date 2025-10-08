<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Motors And Dealers</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        /* --- 1. CONFIGURACIÓN GLOBAL Y FUENTES --- */
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700;800&display=swap');

        :root {
            --primary-red: #D90429;
            --dark-color: #0D1B2A;
            --gray-color: #6c757d;
            --background-light: #f6f5f7;
            --white-color: #FFFFFF;
            --font-main: 'Montserrat', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: var(--font-main);
        }

        body {
            background: var(--background-light);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- 2. CONTENEDOR PRINCIPAL Y ANIMACIÓN --- */
        .container {
            background-color: var(--white-color);
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 900px; /* Ancho aumentado para dos paneles */
            min-height: 600px;
        }

        /* --- 3. FORMULARIOS (SIGN-IN Y SIGN-UP) --- */
        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        form {
            background-color: var(--white-color);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
        }

        h1 {
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--dark-color);
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin-bottom: 1rem;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #ccc;
        }

        input, select {
            background-color: #eee;
            border: none;
            padding: 12px 15px 12px 45px;
            width: 100%;
            border-radius: 8px;
            outline: none;
        }
        
        select {
             padding: 12px 15px; /* Quitar padding izquierdo extra para el select */
        }


        button {
            border-radius: 20px;
            border: 1px solid var(--primary-red);
            background-color: var(--primary-red);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: transform 80ms ease-in, background-color 0.3s;
        }

        button:hover {
            background-color: #b80021;
        }

        button:active {
            transform: scale(0.95);
        }

        button.ghost {
            background-color: transparent;
            border-color: #FFFFFF;
        }

        /* --- 4. PANEL DESLIZANTE (OVERLAY) --- */
        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .overlay {
            background: var(--primary-red);
            background: linear-gradient(to right, #FF4B2B, var(--primary-red));
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: #FFFFFF;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }
        
        .overlay-panel h1 {
            color: var(--white-color);
        }

        .overlay-panel p {
            font-size: 14px;
            font-weight: 100;
            line-height: 20px;
            letter-spacing: 0.5px;
            margin: 20px 0 30px;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        /* --- 5. LÓGICA DE LA ANIMACIÓN CON CLASE .right-panel-active --- */
        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        @keyframes show {
            0%, 49.99% { opacity: 0; z-index: 1; }
            50%, 100% { opacity: 1; z-index: 5; }
        }

        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

    </style>
</head>
<body>

    <div class="container" id="container">
        
        <div class="form-container sign-up-container">
            <form action="procesar_registro.php" method="POST">
                <h1>Crear Cuenta</h1>
                
                <div class="input-group">
                    <i class='bx bxs-user'></i>
                    <input type="text" name="nombre" placeholder="Nombre" required />
                </div>
                <div class="input-group">
                    <i class='bx bxs-user'></i>
                    <input type="text" name="apellido" placeholder="Apellido" required />
                </div>
                <div class="input-group">
                    <i class='bx bxs-envelope'></i>
                    <input type="email" name="email" placeholder="Correo Electrónico" required />
                </div>
                 <div class="input-group">
                    <i class='bx bxs-phone'></i>
                    <input type="tel" name="telefono" placeholder="Teléfono" />
                </div>
                <div class="input-group">
                    <i class='bx bxs-lock-alt'></i>
                    <input type="password" name="password" placeholder="Contraseña" required />
                </div>
                <div class="input-group">
                     <select name="rol" required>
                        <option value="" disabled selected>-- Registrarme como --</option>
                        <option value="cliente">Cliente</option>
                        <option value="vendedor">Vendedor</option>
                    </select>
                </div>

                <button type="submit">Registrarme</button>
            </form>
        </div>

        <div class="form-container sign-in-container">
            <form action="procesar_login.php" method="POST">
                <h1>Iniciar Sesión</h1>

                 <?php
                    if (isset($_GET['error'])) {
                        echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>';
                    }
                ?>
                
                <div class="input-group">
                    <i class='bx bxs-envelope'></i>
                    <input type="email" name="email" placeholder="Correo Electrónico" required />
                </div>
                <div class="input-group">
                    <i class='bx bxs-lock-alt'></i>
                    <input type="password" name="password" placeholder="Contraseña" required />
                </div>
                <button type="submit">Entrar</button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>¡Bienvenido de Nuevo!</h1>
                    <p>Para mantenerte conectado con nosotros, por favor inicia sesión con tu información personal.</p>
                    <button class="ghost" id="signIn">Iniciar Sesión</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>¡Hola, Amigo!</h1>
                    <p>Introduce tus datos personales y comienza tu viaje con nosotros.</p>
                    <button class="ghost" id="signUp">Regístrate</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>

</body>
</html>