<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php'); // Asegúrate de que la ruta sea correcta
$es_privilegiado = isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['administrador', 'vendedor']);

// Lógica de ordenamiento
$sort_option = $_GET['sort'] ?? 'recent';
$order_by_sql = "ORDER BY v.vehiculo_id DESC";
if ($sort_option == 'price_asc') { $order_by_sql = "ORDER BY v.precio_lista ASC"; }
elseif ($sort_option == 'price_desc') { $order_by_sql = "ORDER BY v.precio_lista DESC"; }

// Consulta de vehículos
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

    <div class="inventory-page container">
        
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c3e6cb;">
                ¡Vehículo publicado exitosamente!
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                Error: <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="inventory-controls">
             <div class="inventory-header-info">
                <h1>Nuestro Inventario</h1>
                <p><?php echo $total_vehiculos; ?> vehículos encontrados</p>
            </div>
            <div class="inventory-actions">
                <div class="sort-options">
                    <label for="sort">Ordenar por:</label>
                    <select name="sort" id="sort" onchange="window.location.href='cars.php?sort='+this.value">
                        <option value="recent" <?php echo ($sort_option == 'recent') ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="price_asc" <?php echo ($sort_option == 'price_asc') ? 'selected' : ''; ?>>Precio: Menor a mayor</option>
                        <option value="price_desc" <?php echo ($sort_option == 'price_desc') ? 'selected' : ''; ?>>Precio: Mayor a menor</option>
                    </select>
                </div>
                
                <?php if ($es_privilegiado): ?>
                <div class="toggle-form-container">
                    <button id="toggleFormBtn" class="btn"><i class='bx bx-plus'></i> Agregar Vehículo</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div> 

    <?php if ($es_privilegiado): ?>
    <section class="add-car-section">
        <div class="container">
            <div class="add-car-form-container">
                <form action="agregar_vehiculo.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="placa">Placa</label>
                            <input type="text" id="placa" name="placa" placeholder="AAA123" required pattern="[A-Z]{3}[0-9]{3}" title="3 Letras mayúsculas y 3 números">
                        </div>
                        <div class="form-group">
                            <label for="marca">Marca</label>
                            <input type="text" id="marca" name="marca" placeholder="Ej: Toyota" required>
                        </div>
                        <div class="form-group">
                            <label for="modelo">Modelo</label>
                            <input type="text" id="modelo" name="modelo" placeholder="Ej: Corolla" required>
                        </div>
                        <div class="form-group">
                            <label for="anio">Año</label>
                            <input type="number" id="anio" name="anio" placeholder="2024" required min="1900" max="2099">
                        </div>
                        <div class="form-group full-width">
                            <label for="precio_lista">Precio</label>
                            <input type="number" id="precio_lista" name="precio_lista" placeholder="Ej: 50000000" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Detalles del vehículo, estado, extras..."></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label class="file-upload-label" for="imagenes">
                                <i class='bx bxs-image-add'></i> Seleccionar Imágenes
                            </label>
                            <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*" required>
                            <div id="file-name-display">Ningún archivo seleccionado</div>
                        </div>
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">Publicar Vehículo</button>
                </form>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="cars" id="cars" style="padding-top: 0;"> 
        <div class="container">
            <div class="cars-container">
                <?php
                if ($total_vehiculos > 0) {
                    $resultado->data_seek(0); 
                    while ($vehiculo = $resultado->fetch_assoc()) {
                ?>
                    <div class="box" data-id="<?php echo $vehiculo['vehiculo_id']; ?>" data-name="<?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>" data-img="../../<?php echo htmlspecialchars($vehiculo['imagen_final']); ?>">
                        <a href="vehiculo_detalles.php?id=<?php echo $vehiculo['vehiculo_id']; ?>">
                            <img src="../../<?php echo htmlspecialchars($vehiculo['imagen_final']); ?>" alt="<?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>">
                        </a>
                        <div class="box-content">
                            <h3><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></h3>
                            <span class="price">$<?php echo number_format($vehiculo['precio_lista'], 0, ',', '.'); ?></span>
                            
                            <button class="add-compare-btn" onclick="toggleCompare(this, <?php echo $vehiculo['vehiculo_id']; ?>)">
                                <i class='bx bx-git-compare'></i> Añadir a Comparar
                            </button>
                            
                            <a href="vehiculo_detalles.php?id=<?php echo $vehiculo['vehiculo_id']; ?>" class="btn" style="margin-top: 10px;">Ver Detalles</a>
                        </div>
                    </div>
                <?php
                    }
                } else {
                    echo "<p style='text-align: center; padding: 2rem; grid-column: 1 / -1;'>No hay vehículos disponibles.</p>"; 
                }
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <div class="compare-bar" id="compareBar">
        <div class="compare-bar-content">
            <div class="compare-items" id="compareItems"></div>
            <button id="compareNowBtn" class="btn" onclick="goToComparePage()">Comparar Ahora</button>
        </div>
    </div>

    <script>
        // === 1. Lógica del Botón Agregar Vehículo ===
        const toggleBtn = document.getElementById('toggleFormBtn');
        const formSection = document.querySelector('.add-car-section');

        if(toggleBtn && formSection) {
            toggleBtn.addEventListener('click', () => {
                // Alternar la clase que muestra/oculta el formulario (definida en tu style.css)
                formSection.classList.toggle('is-visible');
                
                // Cambiar el texto del botón visualmente
                if (formSection.classList.contains('is-visible')) {
                    toggleBtn.innerHTML = "<i class='bx bx-minus'></i> Cancelar";
                    toggleBtn.style.backgroundColor = "#666"; // Opcional: cambiar color a gris
                } else {
                    toggleBtn.innerHTML = "<i class='bx bx-plus'></i> Agregar Vehículo";
                    toggleBtn.style.backgroundColor = ""; // Volver al color original
                }
            });
        }

        // === 2. Lógica para mostrar nombres de archivos al subir fotos ===
        const fileInput = document.getElementById('imagenes');
        const fileNameDisplay = document.getElementById('file-name-display');

        if(fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', () => {
                if(fileInput.files.length > 0) {
                    fileNameDisplay.textContent = `${fileInput.files.length} archivos seleccionados`;
                    fileNameDisplay.style.color = "#d90429"; // Color rojo de tu tema
                } else {
                    fileNameDisplay.textContent = 'Ningún archivo seleccionado';
                    fileNameDisplay.style.color = "#6c757d";
                }
            });
        }

        // === 3. Lógica de Comparación (Ya la tenías, la mantengo igual) ===
        let compareList = []; 
        const MAX_COMPARE_ITEMS = 4;
        const compareBar = document.getElementById('compareBar');
        const compareItemsContainer = document.getElementById('compareItems');
        const compareNowBtn = document.getElementById('compareNowBtn');

        function toggleCompare(buttonElement, vehicleId) {
            const vehicleBox = buttonElement.closest('.box');
            const vehicleName = vehicleBox.dataset.name;
            const vehicleImg = vehicleBox.dataset.img;
            const index = compareList.findIndex(item => item.id === vehicleId);

            if (index > -1) { 
                compareList.splice(index, 1);
                buttonElement.classList.remove('selected');
                buttonElement.innerHTML = "<i class='bx bx-git-compare'></i> Añadir a Comparar";
            } else { 
                if (compareList.length >= MAX_COMPARE_ITEMS) {
                    alert(`Puedes comparar un máximo de ${MAX_COMPARE_ITEMS} vehículos.`);
                    return;
                }
                compareList.push({ id: vehicleId, name: vehicleName, img: vehicleImg });
                buttonElement.classList.add('selected');
                buttonElement.innerHTML = "<i class='bx bx-check-circle'></i> Añadido";
            }
            renderCompareBar();
        }

        function removeFromCompare(vehicleId) {
            const buttonInCard = document.querySelector(`.box[data-id="${vehicleId}"] .add-compare-btn`);
            if(buttonInCard) {
                toggleCompare(buttonInCard, vehicleId);
            }
        }

        function renderCompareBar() {
            compareItemsContainer.innerHTML = ''; 
            if (compareList.length > 0) {
                compareBar.classList.add('visible');
                compareList.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.classList.add('compare-item');
                    itemElement.innerHTML = `
                        <img src="${item.img}" alt="">
                        <span>${item.name}</span>
                        <button onclick="removeFromCompare(${item.id})" title="Quitar">&times;</button>
                    `;
                    compareItemsContainer.appendChild(itemElement);
                });
            } else {
                compareBar.classList.remove('visible');
            }
            compareNowBtn.disabled = compareList.length < 2;
            compareNowBtn.textContent = `Comparar Ahora (${compareList.length})`;
        }

        function goToComparePage() {
            if (compareList.length > 1) {
                const ids = compareList.map(item => item.id).join(',');
                window.location.href = `compare.php?ids=${ids}`;
            }
        }
        
        renderCompareBar();
    </script>
</body>
</html>