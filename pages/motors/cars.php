<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');
$es_privilegiado = isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['administrador', 'vendedor']);

// Lógica para ordenar los vehículos
$sort_option = $_GET['sort'] ?? 'recent';
$order_by_sql = "ORDER BY v.vehiculo_id DESC"; // Por defecto

if ($sort_option == 'price_asc') {
    $order_by_sql = "ORDER BY v.precio_lista ASC";
} elseif ($sort_option == 'price_desc') {
    $order_by_sql = "ORDER BY v.precio_lista DESC";
}

$sql = "SELECT 
            v.vehiculo_id, v.marca, v.modelo, v.precio_lista,
            COALESCE(vi.imagen_url, v.imagen_url) AS imagen_final
        FROM vehiculos v
        LEFT JOIN vehiculo_imagenes vi ON v.vehiculo_id = vi.vehiculo_id AND vi.es_principal = 1
        WHERE v.estado = 'Disponible'
        $order_by_sql";

$resultado = $conn->query($sql);
$total_vehiculos = $resultado ? $resultado->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestro Inventario - Motors And Dealers</title>
    <link rel="stylesheet" href="/NUEVO_FROME/assets/css/style.css"> 
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    
    <?php include '../../includes/header.php'; ?>

    <section class="page-title">
        <div class="heading container">
            <span>Catálogo</span>
            <h2>Nuestro Inventario</h2>
            <p>Explora nuestros vehículos disponibles.</p>
        </div>
    </section>
    
    <?php if ($es_privilegiado): ?>
    <div class="toggle-form-container">
        <button id="toggleFormBtn" class="btn"><i class='bx bx-plus'></i> Agregar Nuevo Vehículo</button>
    </div>
    <?php endif; ?>
    <?php if ($es_privilegiado): ?>
    <section class="add-car-section">
        <div class="container">
            <div class="add-car-form-container">
                <div class="heading" style="text-align: left; margin-bottom: 30px;">
                    <h2 style="font-size: 1.8rem;">Agregar Nuevo Vehículo</h2>
                </div>
                
                <form action="agregar_vehiculo.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group"><label for="placa">Placa</label><input type="text" id="placa" name="placa" placeholder="Ej. ABC123" required maxlength="6" pattern="[A-Z]{3}[0-9]{3}"></div>
                        <div class="form-group"><label for="marca">Marca</label><input type="text" id="marca" name="marca" placeholder="Ej. Toyota" required></div>
                        <div class="form-group"><label for="modelo">Modelo</label><input type="text" id="modelo" name="modelo" placeholder="Ej. Corolla" required></div>
                        <div class="form-group"><label for="anio">Año</label><input type="number" id="anio" name="anio" placeholder="Ej. 2024" required min="1900" max="2099"></div>
                        <div class="form-group full-width"><label for="precio_lista">Precio</label><input type="number" step="1000" id="precio_lista" name="precio_lista" placeholder="Ej. 95000000" required></div>
                        <div class="form-group full-width"><label for="descripcion">Descripción del Vehículo</label><textarea id="descripcion" name="descripcion" rows="4" placeholder="Añade detalles importantes sobre el vehículo..."></textarea></div>
                        <div class="form-group full-width">
                            <label for="imagenes" class="file-upload-label"><i class='bx bxs-cloud-upload'></i><span>Seleccionar Imágenes</span></label>
                            <input type="file" name="imagenes[]" id="imagenes" required multiple accept="image/jpeg, image/png, image/webp">
                            <span id="file-name-display">Ningún archivo seleccionado</span>
                        </div>
                    </div>
                    <button type="submit" class="btn">Publicar Vehículo</button>
                </form>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="cars" id="cars" style="padding-top: 1rem;">
        <div class="container">
            <div class="inventory-header" style="border-bottom: none; margin-bottom: 0;">
                <div>
                    <p style="text-align: left; font-weight: 500;"><?php echo $total_vehiculos; ?> vehículos encontrados</p>
                </div>
                <div class="sort-options">
                    <label for="sort">Ordenar por:</label>
                    <select name="sort" id="sort" onchange="window.location.href='cars.php?sort='+this.value">
                        <option value="recent" <?php echo ($sort_option == 'recent') ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="price_asc" <?php echo ($sort_option == 'price_asc') ? 'selected' : ''; ?>>Precio: Menor a mayor</option>
                        <option value="price_desc" <?php echo ($sort_option == 'price_desc') ? 'selected' : ''; ?>>Precio: Mayor a menor</option>
                    </select>
                </div>
            </div>

            <div class="cars-container">
                <?php
                if ($total_vehiculos > 0) {
                    $resultado->data_seek(0); 
                    while ($vehiculo = $resultado->fetch_assoc()) {
                ?>
                    <div class="box">
                        <a href="vehiculo_detalles.php?id=<?php echo $vehiculo['vehiculo_id']; ?>">
                            <img src="../../<?php echo htmlspecialchars($vehiculo['imagen_final']); ?>">
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
                    echo "<p style='text-align: center; padding: 2rem;'>No hay vehículos disponibles.</p>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <script>
        // Lógica para mostrar los nombres de los archivos seleccionados en el formulario
        const fileInput = document.getElementById('imagenes');
        const fileNameDisplay = document.getElementById('file-name-display');
        if(fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    fileNameDisplay.textContent = Array.from(fileInput.files).map(f => f.name).join(', ');
                } else {
                    fileNameDisplay.textContent = 'Ningún archivo seleccionado';
                }
            });
        }

        // Lógica para mostrar/ocultar el formulario
        const toggleBtn = document.getElementById('toggleFormBtn');
        const formSection = document.querySelector('.add-car-section');
        if(toggleBtn && formSection) {
            toggleBtn.addEventListener('click', () => {
                const isVisible = formSection.classList.toggle('is-visible');
                if (isVisible) {
                    toggleBtn.innerHTML = "<i class='bx bx-minus'></i> Ocultar Formulario";
                    // Desplazarse suavemente hasta el formulario
                    formSection.scrollIntoView({ behavior: 'smooth' });
                } else {
                    toggleBtn.innerHTML = "<i class='bx bx-plus'></i> Agregar Nuevo Vehículo";
                }
            });
        }
    </script>
    </body>
</html>