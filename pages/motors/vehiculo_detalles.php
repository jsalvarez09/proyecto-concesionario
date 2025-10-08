<?php
// session_start() se llama ahora desde header.php, pero lo dejamos aquí por si acaso
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../../includes/conexion.php');

// Validar que el ID del vehículo sea un número válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: No se ha especificado un vehículo válido.");
}
$vehiculo_id = $_GET['id'];

// --- CONSULTA PARA EL VEHÍCULO PRINCIPAL ---
$sql = "SELECT v.*, ven.nombre as vendedor_nombre, ven.apellido as vendedor_apellido, ven.telefono as vendedor_telefono
        FROM vehiculos v
        LEFT JOIN vendedores ven ON v.vendedor_id = ven.vendedor_id
        WHERE v.vehiculo_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehiculo_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) { die("Vehículo no encontrado."); }
$vehiculo = $resultado->fetch_assoc();

// --- CONSULTA PARA LAS IMÁGENES DEL VEHÍCULO PRINCIPAL ---
$sql_imagenes = "SELECT COALESCE(vi.imagen_url, v.imagen_url) AS imagen_url 
                 FROM vehiculos v
                 LEFT JOIN vehiculo_imagenes vi ON v.vehiculo_id = vi.vehiculo_id
                 WHERE v.vehiculo_id = ? ORDER BY vi.es_principal DESC";
$stmt_imagenes = $conn->prepare($sql_imagenes);
$stmt_imagenes->bind_param("i", $vehiculo_id);
$stmt_imagenes->execute();
$resultado_imagenes = $stmt_imagenes->get_result();
$imagenes = [];
while ($row = $resultado_imagenes->fetch_assoc()) {
    if (!empty($row['imagen_url'])) { $imagenes[] = $row['imagen_url']; }
}
if (empty($imagenes)) { $imagenes[] = 'https://via.placeholder.com/1200x800/eee/999?text=Sin+Imagen'; }

// --- NUEVA CONSULTA PARA VEHÍCULOS RELACIONADOS ---
$sql_relacionados = "SELECT 
                        v.vehiculo_id, v.marca, v.modelo, v.precio_lista, v.anio, v.kilometraje,
                        COALESCE(vi.imagen_url, v.imagen_url) AS imagen_final
                    FROM 
                        vehiculos v
                    LEFT JOIN 
                        vehiculo_imagenes vi ON v.vehiculo_id = vi.vehiculo_id AND vi.es_principal = 1
                    WHERE 
                        v.estado = 'Disponible' AND v.vehiculo_id != ?
                    ORDER BY RAND()
                    LIMIT 4";
$stmt_relacionados = $conn->prepare($sql_relacionados);
$stmt_relacionados->bind_param("i", $vehiculo_id);
$stmt_relacionados->execute();
$resultado_relacionados = $stmt_relacionados->get_result();
$vehiculos_relacionados = $resultado_relacionados->fetch_all(MYSQLI_ASSOC);


// --- Preparar enlace de WhatsApp ---
$vendedor_telefono = $vehiculo['vendedor_telefono'];
$telefono_whatsapp = "57" . preg_replace('/[^0-9]/', '', $vendedor_telefono); 
$mensaje_whatsapp = urlencode("Hola, estoy interesado en el vehículo " . $vehiculo['marca'] . " " . $vehiculo['modelo'] . " (Año " . $vehiculo['anio'] . ")");
$enlace_whatsapp = "https://wa.me/" . $telefono_whatsapp . "?text=" . $mensaje_whatsapp;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de <?php echo htmlspecialchars($vehiculo['marca']); ?> - Motors And Dealers</title>
    <link rel="stylesheet" href="/NUEVO_FROME/assets/css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f4f4; font-family: Arial, sans-serif; }
        .container { max-width: 1200px; margin: auto; padding: 0 15px; }
        .details-page-wrapper { padding: 2rem 0; }
        .details-card { display: grid; grid-template-columns: 60% 40%; gap: 30px; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .gallery-column .main-slider { position: relative; overflow: hidden; border-radius: 8px; }
        .slider-wrapper { display: flex; transition: transform 0.4s ease; }
        .slide { min-width: 100%; }
        .slide img { width: 100%; display: block; aspect-ratio: 4 / 3; object-fit: cover; }
        .slider-btn { position: absolute; top: 50%; transform: translateY(-50%); background-color: rgba(0,0,0,0.4); color: white; border: none; padding: 8px 12px; font-size: 20px; cursor: pointer; z-index: 10; border-radius: 50%; }
        .slider-btn.prev { left: 15px; }
        .slider-btn.next { right: 15px; }
        .slider-dots { text-align: center; padding: 10px 0; }
        .dot { display: inline-block; height: 10px; width: 10px; background-color: #ccc; border-radius: 50%; margin: 0 5px; cursor: pointer; }
        .dot.active { background-color: #e21c3d; }
        .thumbnail-container { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 15px; }
        .thumbnail-box { border: 2px solid transparent; border-radius: 5px; overflow: hidden; cursor: pointer; }
        .thumbnail-box.active { border-color: #e21c3d; }
        .thumbnail-box img { width: 100%; display: block; aspect-ratio: 4 / 3; object-fit: cover; }
        .info-column { display: flex; flex-direction: column; padding: 0 1rem; }
        .info-column h1 { font-size: 2rem; font-weight: bold; margin: 0 0 10px 0; line-height: 1.2; text-transform: uppercase; }
        .info-column .published-by { font-size: 0.9rem; color: #888; margin-bottom: 15px; }
        .info-column .price { font-size: 2.2rem; font-weight: bold; color: #e21c3d; margin-bottom: 20px; }
        .description-section { margin-top: 20px; padding-bottom: 20px; }
        .description-section h3 { margin: 0 0 10px 0; font-size: 1.2rem; }
        .description-section p { color: #555; line-height: 1.6; margin: 0; overflow-wrap: break-word; }
        .actions-section { display: flex; flex-direction: column; align-items: center; gap: 10px; margin-top: auto; padding-top: 20px; }
        .btn { padding: 15px 40px; border-radius: 8px; text-align: center; font-weight: bold; text-decoration: none; display: inline-block; width: auto; max-width: 100%; }
        .btn-whatsapp { background-color: #25D366; color: white; }
        .btn-whatsapp .bx { vertical-align: middle; margin-right: 5px; }
        .specs-section { border-top: 1px solid #eee; margin-top: 25px; padding-top: 25px; grid-column: 1 / -1; }
        .specs-section h2 { margin: 0 0 20px 0; font-size: 1.5rem; font-weight: 700; }
        .specs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 30px; }
        .spec-item { padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        .spec-label { display: block; font-size: 0.9rem; color: #888; margin-bottom: 5px; }
        .spec-value { display: block; font-size: 1rem; font-weight: 600; color: #333; }
        
        /* --- ESTILOS PARA PRODUCTOS RELACIONADOS --- */
        .related-products-section { padding: 3rem 0; margin-top: 30px; border-top: 1px solid #ddd; }
        .related-products-section h2 { text-transform: uppercase; font-size: 1.2rem; font-weight: 700; color: #333; margin: 0 0 25px 0; letter-spacing: 1px; }
        .related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .product-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s, box-shadow 0.3s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .product-card a { text-decoration: none; color: inherit; }
        .product-card-img { width: 100%; aspect-ratio: 4/3; background-color: #eee; }
        .product-card-img img { width: 100%; height: 100%; object-fit: cover; }
        .product-card-info { padding: 15px; }
        .product-card-info h3 { font-size: 1rem; font-weight: 600; margin: 0 0 8px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-card-info .price { font-size: 1.1rem; font-weight: bold; color: #e21c3d; margin: 0 0 8px 0; }
        .product-card-info .specs { font-size: 0.8rem; color: #777; margin: 0; }
        .product-card-info .btn-details { display: block; background: #333; color: #fff; text-align: center; padding: 10px; margin-top: 15px; border-radius: 5px; font-weight: 600; font-size: 0.9rem; }
        
        @media (max-width: 992px) { .details-card { grid-template-columns: 1fr; } .related-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 576px) { .specs-grid { grid-template-columns: 1fr; } .related-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    
    <?php include '../../includes/header.php'; ?>

    <div class="details-page-wrapper container">
        <div class="details-card">
            <div class="gallery-column">
                <div class="main-slider">
                    <div class="slider-wrapper">
                        <?php foreach ($imagenes as $img): ?>
                            <div class="slide"><img src="../../<?php echo htmlspecialchars($img); ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    <button class="slider-btn prev">&#10094;</button>
                    <button class="slider-btn next">&#10095;</button>
                </div>
                <div class="slider-dots"></div>
                <div class="thumbnail-container">
                    <?php foreach ($imagenes as $index => $img): ?>
                        <div class="thumbnail-box" data-index="<?php echo $index; ?>"><img src="../../<?php echo htmlspecialchars($img); ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="info-column">
                <h1><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></h1>
                <p class="published-by">Vendido por: <?php echo htmlspecialchars($vehiculo['vendedor_nombre'] . ' ' . $vehiculo['vendedor_apellido']); ?></p>
                <p class="price">$<?php echo number_format($vehiculo['precio_lista'], 0, ',', '.'); ?> COP</p>
                <?php if (!empty($vehiculo['descripcion'])): ?>
                <div class="description-section">
                    <h3>Descripción</h3>
                    <p><?php echo nl2br(htmlspecialchars($vehiculo['descripcion'])); ?></p>
                </div>
                <?php endif; ?>
                <div class="actions-section">
                     <?php if (!empty($vendedor_telefono)): ?>
                        <a href="<?php echo $enlace_whatsapp; ?>" class="btn btn-whatsapp" target="_blank"><i class='bx bxl-whatsapp'></i> WhatsApp</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="specs-section">
                <h2>Características</h2>
                <div class="specs-grid">
                    <div class="spec-item"><span class="spec-label">Marca</span><span class="spec-value"><?php echo htmlspecialchars($vehiculo['marca']); ?></span></div>
                    <div class="spec-item"><span class="spec-label">Modelo</span><span class="spec-value"><?php echo htmlspecialchars($vehiculo['modelo']); ?></span></div>
                    <div class="spec-item"><span class="spec-label">Año</span><span class="spec-value"><?php echo htmlspecialchars($vehiculo['anio']); ?></span></div>
                    <div class="spec-item"><span class="spec-label">Placa</span><span class="spec-value"><?php echo htmlspecialchars($vehiculo['placa']); ?></span></div>
                    <div class="spec-item"><span class="spec-label">Kilometraje</span><span class="spec-value"><?php echo number_format($vehiculo['kilometraje'], 0, ',', '.'); ?> km</span></div>
                    <div class="spec-item"><span class="spec-label">Color</span><span class="spec-value"><?php echo htmlspecialchars($vehiculo['color'] ?? 'No especificado'); ?></span></div>
                    <div class="spec-item"><span class="spec-label">Estado</span><span class="spec-value"><?php echo htmlspecialchars($vehiculo['estado']); ?></span></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($vehiculos_relacionados)): ?>
    <section class="related-products-section container">
        <h2>PRODUCTOS RELACIONADOS</h2>
        <div class="related-grid">
            <?php foreach($vehiculos_relacionados as $relacionado): ?>
                <div class="product-card">
                    <a href="vehiculo_detalles.php?id=<?php echo $relacionado['vehiculo_id']; ?>">
                        <div class="product-card-img">
                            <img src="../../<?php echo htmlspecialchars($relacionado['imagen_final']); ?>" alt="<?php echo htmlspecialchars($relacionado['marca'] . ' ' . $relacionado['modelo']); ?>">
                        </div>
                        <div class="product-card-info">
                            <h3><?php echo htmlspecialchars($relacionado['marca'] . ' ' . $relacionado['modelo']); ?></h3>
                            <p class="price">$<?php echo number_format($relacionado['precio_lista'], 0, ',', '.'); ?> COP</p>
                            <p class="specs"><?php echo htmlspecialchars($relacionado['anio']); ?> - <?php echo number_format($relacionado['kilometraje'], 0, ',', '.'); ?> km</p>
                            <span class="btn-details">Ver Detalles</span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.querySelector('.slider-wrapper');
            const prevBtn = document.querySelector('.slider-btn.prev');
            const nextBtn = document.querySelector('.slider-btn.next');
            const dotsContainer = document.querySelector('.slider-dots');
            const thumbnails = document.querySelectorAll('.thumbnail-box');
            const slides = document.querySelectorAll('.slide');
            if (slides.length > 0) {
                const totalSlides = slides.length;
                let currentIndex = 0;
                if (totalSlides <= 1) { prevBtn.style.display = 'none'; nextBtn.style.display = 'none'; }
                for (let i = 0; i < totalSlides; i++) {
                    const dot = document.createElement('span');
                    dot.classList.add('dot');
                    dot.dataset.index = i;
                    dotsContainer.appendChild(dot);
                }
                const dots = document.querySelectorAll('.dot');
                function updateGallery(index) {
                    wrapper.style.transform = `translateX(-${index * 100}%)`;
                    dots.forEach(d => d.classList.remove('active'));
                    if (dots[index]) dots[index].classList.add('active');
                    thumbnails.forEach(t => t.classList.remove('active'));
                    if (thumbnails[index]) thumbnails[index].classList.add('active');
                    currentIndex = index;
                }
                nextBtn.addEventListener('click', () => {
                    let nextIndex = currentIndex + 1;
                    if (nextIndex >= totalSlides) nextIndex = 0;
                    updateGallery(nextIndex);
                });
                prevBtn.addEventListener('click', () => {
                    let prevIndex = currentIndex - 1;
                    if (prevIndex < 0) prevIndex = totalSlides - 1;
                    updateGallery(prevIndex);
                });
                dots.forEach(dot => { dot.addEventListener('click', (e) => { updateGallery(parseInt(e.target.dataset.index)); }); });
                thumbnails.forEach(thumb => { thumb.addEventListener('click', (e) => { updateGallery(parseInt(e.currentTarget.dataset.index)); }); });
                updateGallery(0);
            }
        });
    </script>
</body>
</html>