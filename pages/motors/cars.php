<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');
$es_privilegiado = isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['administrador', 'vendedor']);

// Lógica de ordenamiento
$sort_option = $_GET['sort'] ?? 'recent';
$order_by_sql = "ORDER BY v.vehiculo_id DESC";
if ($sort_option == 'price_asc') { $order_by_sql = "ORDER BY v.precio_lista ASC"; }
elseif ($sort_option == 'price_desc') { $order_by_sql = "ORDER BY v.precio_lista DESC"; }

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
            <div class="compare-items" id="compareItems">
                </div>
            <button id="compareNowBtn" class="btn" onclick="goToComparePage()">Comparar Ahora</button>
        </div>
    </div>
    <script>
        // Script para mostrar/ocultar formulario
        const toggleBtn = document.getElementById('toggleFormBtn');
        const formSection = document.querySelector('.add-car-section');
        if(toggleBtn && formSection) { /* ... tu código ... */ }
        // Script para nombres de archivo
        const fileInput = document.getElementById('imagenes');
        const fileNameDisplay = document.getElementById('file-name-display');
        if(fileInput && fileNameDisplay) { /* ... tu código ... */ }

        // === INICIO: Nueva Lógica para la Comparación ===
        let compareList = []; // Guarda objetos {id, name, img}
        const MAX_COMPARE_ITEMS = 4;
        const compareBar = document.getElementById('compareBar');
        const compareItemsContainer = document.getElementById('compareItems');
        const compareNowBtn = document.getElementById('compareNowBtn');

        function toggleCompare(buttonElement, vehicleId) {
            const vehicleBox = buttonElement.closest('.box');
            const vehicleName = vehicleBox.dataset.name;
            const vehicleImg = vehicleBox.dataset.img;
            const index = compareList.findIndex(item => item.id === vehicleId);

            if (index > -1) { // Si ya está en la lista, quitarlo
                compareList.splice(index, 1);
                buttonElement.classList.remove('selected');
                buttonElement.innerHTML = "<i class='bx bx-git-compare'></i> Añadir a Comparar";
            } else { // Si no está, añadirlo (si hay espacio)
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
            // Encuentra el botón correspondiente en la tarjeta y simula un clic para quitarlo
            const buttonInCard = document.querySelector(`.box[data-id="${vehicleId}"] .add-compare-btn`);
            if(buttonInCard) {
                toggleCompare(buttonInCard, vehicleId);
            }
        }

        function renderCompareBar() {
            compareItemsContainer.innerHTML = ''; // Limpiar items actuales
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
            // Habilitar/deshabilitar botón principal
            compareNowBtn.disabled = compareList.length < 2;
            compareNowBtn.textContent = `Comparar Ahora (${compareList.length})`;
        }

        function goToComparePage() {
            if (compareList.length > 1) {
                const ids = compareList.map(item => item.id).join(',');
                window.location.href = `compare.php?ids=${ids}`;
            }
        }
        
        // Inicializar la barra al cargar
        renderCompareBar();
        // === FIN: Nueva Lógica para la Comparación ===
    </script>
</body>
</html>