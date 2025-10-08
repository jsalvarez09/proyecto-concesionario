<?php
    require_once('includes/conexion.php');

    // Consulta actualizada para obtener también el precio
    $sql = "SELECT 
                v.vehiculo_id, 
                v.marca, 
                v.modelo,
                v.precio_lista,
                COALESCE(vi.imagen_url, v.imagen_url) AS imagen_final
            FROM 
                vehiculos v
            LEFT JOIN 
                vehiculo_imagenes vi ON v.vehiculo_id = vi.vehiculo_id AND vi.es_principal = 1
            WHERE 
                v.estado = 'Disponible' 
            ORDER BY 
                v.vehiculo_id DESC 
            LIMIT 3";
            
    $resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head> 
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta de Autos | Motors And Dealers</title> 

    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head> 
<body>

    <?php include 'includes/header.php'; ?>

    <section class="home" id="home">
        <div class="home-text">
            <h1>Encuentra el Auto <br>de Tus <span>Sueños</span></h1>
            <p>Explora nuestro catálogo con los mejores modelos del mercado. Calidad y confianza garantizada.</p>
        </div>
    </section>

    <section class="features-section">
        <div class="container features-container">
            <div class="feature-box">
                <i class='bx bxs-car-mechanic'></i>
                <h3>Vehículos Certificados</h3>
                <p>Todos nuestros autos pasan por una rigurosa inspección de calidad para tu tranquilidad.</p>
            </div>
            <div class="feature-box">
                <i class='bx bxs-wallet'></i>
                <h3>Planes de Financiación</h3>
                <p>Ofrecemos opciones flexibles de financiación que se adaptan a tus necesidades y presupuesto.</p>
            </div>
            <div class="feature-box">
                <i class='bx bxs-award'></i>
                <h3>Garantía de Confianza</h3>
                <p>Te acompañamos en todo el proceso de compra con la mejor asesoría y servicio posventa.</p>
            </div>
        </div>
    </section>

    <section class="cars" id="cars">
        <div class="container">
            <div class="heading">
                <span>Nuestros Vehículos</span>
                <h2>Los más recientes en nuestro catálogo</h2>
            </div>
            
            <div class="cars-container">
                <?php
                    if ($resultado && $resultado->num_rows > 0) {
                        while ($vehiculo = $resultado->fetch_assoc()) {
                ?>
                    <div class="box">
                        <a href="pages/motors/vehiculo_detalles.php?id=<?php echo $vehiculo['vehiculo_id']; ?>">
                            <img src="<?php echo htmlspecialchars($vehiculo['imagen_final']); ?>" alt="<?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>">
                            <div class="box-content">
                                <h3><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></h3>
                                <span class="price">$<?php echo number_format($vehiculo['precio_lista'], 0, ',', '.'); ?></span>
                                <span class="btn">Ver Detalles</span>
                            </div>
                        </a>
                    </div>
                <?php
                        }
                    } else {
                        echo "<p>No hay vehículos disponibles en este momento.</p>";
                    }
                    $conn->close();
                ?>
            </div>

            <div class="heading" style="margin-top: 3rem;">
                <a href="pages/motors/cars.php" class="btn">Ver Catálogo Completo</a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-box">
                <a href="#" class="logo">Motors <span>And</span> Dealers</a>
                <div class="social">
                    <a href="#"><i class='bx bxl-facebook'></i></a>
                    <a href="#"><i class='bx bxl-twitter'></i></a>
                    <a href="#"><i class='bx bxl-instagram'></i></a>
                </div>
            </div>
            <div class="footer-box">
                <h3>Página</h3>
                <a href="#">Inicio</a>
                <a href="#">Vehículos</a>
            </div>
            <div class="footer-box">
                <h3>Legal</h3>
                <a href="#">Privacidad</a>
                <a href="#">Política de Cookies</a>
            </div>
        </div>
    </footer>

    <div class="copyright">
        <p>&#169; Motors And Dealers - Todos los Derechos Reservados</p>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>