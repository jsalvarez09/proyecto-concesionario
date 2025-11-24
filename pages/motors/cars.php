<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php'); 
$es_privilegiado = isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['administrador', 'vendedor']);

$sort_option = $_GET['sort'] ?? 'recent';
$order_by_sql = "ORDER BY v.vehiculo_id DESC";
if ($sort_option == 'price_asc') { $order_by_sql = "ORDER BY v.precio_lista ASC"; }
elseif ($sort_option == 'price_desc') { $order_by_sql = "ORDER BY v.precio_lista DESC"; }

$sql = "SELECT v.vehiculo_id, v.marca, v.modelo, v.precio_lista, COALESCE(vi.imagen_url, v.imagen_url) AS imagen_final
        FROM vehiculos v
        LEFT JOIN vehiculo_imagenes vi ON v.vehiculo_id = vi.vehiculo_id AND vi.es_principal = 1
        WHERE v.estado = 'Disponible' $order_by_sql";

$resultado = $conn->query($sql);
$total_vehiculos = $resultado ? $resultado->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Motors And Dealers</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css"> 
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    
    <?php include '../../includes/header.php'; ?>

    <div class="inventory-page container">
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;">¡Vehículo publicado!</div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;">Error: <?php echo htmlspecialchars($_GET['error']); ?></div>
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
                        <div class="form-group"><label for="placa">Placa</label><input type="text" id="placa" name="placa" placeholder="AAA123" required pattern="[A-Z]{3}[0-9]{3}"></div>
                        <div class="form-group"><label for="marca">Marca</label><input type="text" id="marca" name="marca" required></div>
                        <div class="form-group"><label for="modelo">Modelo</label><input type="text" id="modelo" name="modelo" required></div>
                        <div class="form-group"><label for="anio">Año</label><input type="number" id="anio" name="anio" required></div>
                        <div class="form-group full-width"><label for="precio_lista">Precio</label><input type="number" id="precio_lista" name="precio_lista" required></div>
                        <div class="form-group full-width"><label for="descripcion">Descripción</label><textarea id="descripcion" name="descripcion" rows="3"></textarea></div>
                        <div class="form-group full-width"><label class="file-upload-label" for="imagenes"><i class='bx bxs-image-add'></i> Imágenes</label><input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*" required><div id="file-name-display">Ningún archivo</div></div>
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">Publicar</button>
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
                        // Construcción segura de la ruta de imagen usando BASE_URL
                        $img_path = empty($vehiculo['imagen_final']) ? 'assets/img/placeholder.png' : $vehiculo['imagen_final'];
                        $img_full_url = BASE_URL . $img_path;
                ?>
                    <div class="box" data-id="<?php echo $vehiculo['vehiculo_id']; ?>" data-name="<?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>" data-img="<?php echo $img_full_url; ?>">
                        <a href="vehiculo_detalles.php?id=<?php echo $vehiculo['vehiculo_id']; ?>">
                            <img src="<?php echo $img_full_url; ?>" alt="Vehículo">
                        </a>
                        <div class="box-content">
                            <h3><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></h3>
                            <span class="price">$<?php echo number_format($vehiculo['precio_lista'], 0, ',', '.'); ?></span>
                            <button class="add-compare-btn" onclick="toggleCompare(this, <?php echo $vehiculo['vehiculo_id']; ?>)"><i class='bx bx-git-compare'></i> Comparar</button>
                            <a href="vehiculo_detalles.php?id=<?php echo $vehiculo['vehiculo_id']; ?>" class="btn" style="margin-top: 10px;">Ver Detalles</a>
                        </div>
                    </div>
                <?php
                    }
                } else {
                    echo "<p style='text-align: center; width: 100%;'>No hay vehículos disponibles.</p>"; 
                }
                ?>
            </div>
        </div>
    </section>

    <div class="compare-bar" id="compareBar"><div class="compare-bar-content"><div class="compare-items" id="compareItems"></div><button id="compareNowBtn" class="btn" onclick="goToComparePage()">Comparar Ahora</button></div></div>
    
    <script>
        // ... (Tu código JS para el toggle del formulario y comparación va aquí, igual que antes) ...
        // Asegúrate de copiar el script de tu archivo original cars.php
        const toggleBtn = document.getElementById('toggleFormBtn');
        const formSection = document.querySelector('.add-car-section');
        if(toggleBtn && formSection) {
            toggleBtn.addEventListener('click', () => {
                formSection.classList.toggle('is-visible');
                if (formSection.classList.contains('is-visible')) { toggleBtn.innerHTML = "<i class='bx bx-minus'></i> Cancelar"; } 
                else { toggleBtn.innerHTML = "<i class='bx bx-plus'></i> Agregar Vehículo"; }
            });
        }
        // ... Resto de la lógica de comparación ...
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
                buttonElement.innerHTML = "<i class='bx bx-git-compare'></i> Comparar";
            } else { 
                if (compareList.length >= MAX_COMPARE_ITEMS) { alert(`Máximo ${MAX_COMPARE_ITEMS} vehículos.`); return; }
                compareList.push({ id: vehicleId, name: vehicleName, img: vehicleImg });
                buttonElement.classList.add('selected');
                buttonElement.innerHTML = "<i class='bx bx-check-circle'></i> Añadido";
            }
            renderCompareBar();
        }
        function renderCompareBar() {
            compareItemsContainer.innerHTML = ''; 
            if (compareList.length > 0) {
                compareBar.classList.add('visible');
                compareList.forEach(item => {
                    compareItemsContainer.innerHTML += `<div class="compare-item"><img src="${item.img}"><span>${item.name}</span></div>`;
                });
            } else { compareBar.classList.remove('visible'); }
            compareNowBtn.disabled = compareList.length < 2;
        }
        function goToComparePage() {
            if (compareList.length > 1) {
                const ids = compareList.map(item => item.id).join(',');
                window.location.href = `compare.php?ids=${ids}`;
            }
        }
    </script>
</body>
</html>