<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Seguridad y obtención de datos del vehículo...
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("ID de vehículo no válido."); }
$vehiculo_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'];
$sql_vehiculo = ($rol == 'administrador') ? "SELECT * FROM vehiculos WHERE vehiculo_id = ?" : "SELECT * FROM vehiculos WHERE vehiculo_id = ? AND vendedor_id = ?";
$stmt_vehiculo = $conn->prepare($sql_vehiculo);
if ($rol == 'administrador') { $stmt_vehiculo->bind_param("i", $vehiculo_id); } else { $stmt_vehiculo->bind_param("ii", $vehiculo_id, $usuario_id); }
$stmt_vehiculo->execute();
$vehiculo = $stmt_vehiculo->get_result()->fetch_assoc();
if (!$vehiculo) { die("Vehículo no encontrado o no tienes permiso para editarlo."); }
$sql_imagenes = "SELECT imagen_id, imagen_url FROM vehiculo_imagenes WHERE vehiculo_id = ?";
$stmt_imagenes = $conn->prepare($sql_imagenes);
$stmt_imagenes->bind_param("i", $vehiculo_id);
$stmt_imagenes->execute();
$imagenes = $stmt_imagenes->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Vehículo - <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></title>
    
    <link rel="stylesheet" href="/NUEVO_FROME/assets/css/style.css">
    
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <style>
        /* Estilos generales del formulario de edición */
        .add-car-section { padding: 3rem 0; }
        .add-car-form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: var(--bg-color);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.07);
        }
        .add-car-form-container .form-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 1.5rem; 
            margin-bottom: 1.5rem; 
        }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 500; margin-bottom: 0.5rem; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 0.3rem;
            outline: none;
            font-size: 0.9rem;
            background: #f6f6f6;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus { 
            border-color: var(--main-color); 
            box-shadow: 0 0 0 3px rgba(217, 4, 41, 0.15); 
        }
        
        /* Estilos para la gestión de imágenes */
        .image-management { margin-top: 1.5rem; border-top: 1px solid #eee; padding-top: 1.5rem; }
        .current-images-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; }
        .image-thumbnail { position: relative; }
        .image-thumbnail img { width: 100%; height: 80px; object-fit: cover; border-radius: 6px; }
        .image-thumbnail label { 
            position: absolute; 
            top: 5px; 
            right: 5px; 
            display: block; 
            width: 20px; 
            height: 20px; 
            background-color: white; 
            border-radius: 50%; 
            cursor: pointer; 
            border: 1px solid #ccc; 
        }
        .image-thumbnail input[type="checkbox"] { opacity: 0; }
        .image-thumbnail input[type="checkbox"]:checked + label::after { 
            content: '×'; 
            color: red; 
            font-weight: bold; 
            font-size: 20px; 
            position: absolute; 
            top: -5px; 
            left: 3px; 
        }
        .file-upload-label { display: flex; align-items: center; justify-content: center; padding: 12px; background: #e9ecef; border: 2px dashed #ccc; border-radius: 0.3rem; cursor: pointer; transition: all 0.3s; }
        .file-upload-label:hover { border-color: var(--main-color); }
        .file-upload-label i { font-size: 1.5rem; margin-right: 0.5rem; }
        #file-name-display { font-size: 0.8rem; color: #6c757d; margin-top: 0.5rem; }
    </style>
    </head>
<body>
    <?php include '../../includes/header.php'; ?>
    <main class="page-title">
        <div class="heading container">
            <h2>Editar Vehículo</h2>
            <p>Modifica los detalles de tu vehículo y guarda los cambios.</p>
        </div>
    </main>

    <section class="add-car-section">
        <div class="container">
            <div class="add-car-form-container">
                <form action="actualizar_vehiculo.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="vehiculo_id" value="<?php echo $vehiculo_id; ?>">
                    <div class="form-grid">
                        <div class="form-group"><label for="placa">Placa</label><input type="text" id="placa" name="placa" value="<?php echo htmlspecialchars($vehiculo['placa']); ?>" required></div>
                        <div class="form-group"><label for="marca">Marca</label><input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($vehiculo['marca']); ?>" required></div>
                        <div class="form-group"><label for="modelo">Modelo</label><input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($vehiculo['modelo']); ?>" required></div>
                        <div class="form-group"><label for="anio">Año</label><input type="number" id="anio" name="anio" value="<?php echo htmlspecialchars($vehiculo['anio']); ?>" required></div>
                        <div class="form-group full-width"><label for="precio_lista">Precio</label><input type="number" step="1000" id="precio_lista" name="precio_lista" value="<?php echo htmlspecialchars($vehiculo['precio_lista']); ?>" required></div>
                        <div class="form-group full-width"><label for="descripcion">Descripción</label><textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($vehiculo['descripcion']); ?></textarea></div>
                    </div>
                    <div class="image-management">
                        <div class="form-group full-width"><label>Imágenes Actuales (marca para eliminar)</label>
                            <div class="current-images-grid">
                                <?php foreach($imagenes as $img): ?>
                                    <div class="image-thumbnail">
                                        <img src="../../<?php echo htmlspecialchars($img['imagen_url']); ?>">
                                        <input type="checkbox" name="eliminar_imagenes[]" value="<?php echo $img['imagen_id']; ?>" id="delete_img_<?php echo $img['imagen_id']; ?>">
                                        <label for="delete_img_<?php echo $img['imagen_id']; ?>"></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-group full-width" style="margin-top: 1.5rem;">
                            <label for="nuevas_imagenes" class="file-upload-label"><i class='bx bxs-image-add'></i><span>Añadir Nuevas Imágenes</span></label>
                            <input type="file" name="nuevas_imagenes[]" id="nuevas_imagenes" multiple accept="image/jpeg, image/png, image/webp">
                            <span id="file-name-display">Ningún archivo nuevo seleccionado</span>
                        </div>
                    </div>
                    <button type="submit" class="btn" style="width: 100%; margin-top: 1.5rem; font-size: 1.1rem; font-weight: 600;">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </section>
    <script>
        const fileInput = document.getElementById('nuevas_imagenes');
        const fileNameDisplay = document.getElementById('file-name-display');
        if (fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    let fileNames = Array.from(fileInput.files).map(f => f.name);
                    fileNameDisplay.textContent = fileNames.join(', ');
                } else {
                    fileNameDisplay.textContent = 'Ningún archivo nuevo seleccionado';
                }
            });
        }
    </script>
</body>
</html>