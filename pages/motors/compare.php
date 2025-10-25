<?php
session_start();
require_once(__DIR__ . '/../../includes/conexion.php');

// Validar que se recibieron IDs
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    die("No se seleccionaron vehículos para comparar.");
}

// Limpiar y obtener los IDs como un array de números
$ids_str = $_GET['ids'];
$ids_array = explode(',', $ids_str);
$ids_sanitized = [];
foreach ($ids_array as $id) {
    if (is_numeric($id)) {
        $ids_sanitized[] = (int)$id;
    }
}

if (count($ids_sanitized) < 2) {
    die("Selecciona al menos dos vehículos para comparar.");
}

$max_compare = 4;
$ids_sanitized = array_slice($ids_sanitized, 0, $max_compare);

$placeholders = implode(',', array_fill(0, count($ids_sanitized), '?'));
$types = str_repeat('i', count($ids_sanitized));

// Consulta para obtener los datos de los vehículos seleccionados
$sql = "SELECT
            v.vehiculo_id, v.marca, v.modelo, v.anio, v.precio_lista, v.kilometraje, v.color, v.descripcion,
            COALESCE((SELECT vi.imagen_url FROM vehiculo_imagenes vi WHERE vi.vehiculo_id = v.vehiculo_id AND vi.es_principal = 1 LIMIT 1), v.imagen_url) AS imagen_final
        FROM vehiculos v
        WHERE v.vehiculo_id IN ({$placeholders})";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$ids_sanitized);
$stmt->execute();
$resultado = $stmt->get_result();
$vehiculos = $resultado->fetch_all(MYSQLI_ASSOC);

if (count($vehiculos) < 2) {
    die("No se encontraron suficientes vehículos válidos para comparar.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparar Vehículos - Motors And Dealers</title>
    <link rel="stylesheet" href="/NUEVO_FROME/assets/css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; }
        .compare-page {
            margin-top: 100px; /* Espacio para el header */
            padding-bottom: 3rem;
        }
        .compare-page h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 2.5rem;
        }
        .compare-table-wrapper {
            overflow-x: auto; /* Permite scroll horizontal */
        }
        .compare-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--bg-color);
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
            border-radius: 10px;
            min-width: 800px;
        }
        .compare-table th,
        .compare-table td {
            padding: 1.2rem 1rem;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }

        /* --- Columna de Características (Izquierda) --- */
        .compare-table th {
            font-weight: 600;
            color: var(--text-color);
            background-color: #e9ecef; /* <-- Fondo gris más oscuro */
            width: 20%;
            border-right: 1px solid #ddd; /* Borde derecho más visible */
        }
        /* --- Fila Superior (Cabecera de Vehículos) --- */
        .compare-table thead td {
            text-align: center;
            border-bottom: 2px solid #ccc; /* Línea inferior más gruesa */
            background-color: #e9ecef; /* <-- Fondo gris más oscuro */
        }
        .compare-table thead img {
            width: 100%;
            max-width: 200px;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
        }
         .compare-table thead .vehicle-name {
            font-weight: 600;
            font-size: 1.1rem;
         }

        /* Esquina superior izquierda */
        .compare-table thead th {
             background-color: #e9ecef; /* <-- Mismo fondo oscuro */
             border-bottom: 2px solid #ccc;
        }

        /* Celdas de datos */
        .compare-table td {
            font-size: 0.95rem;
            color: #333;
            text-align: center; /* Centrar datos */
        }
         /* Alinear texto de descripción a la izquierda */
        .compare-table td.description-cell {
            text-align: left;
        }

        .compare-table .price {
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--main-color);
        }
        .compare-table tbody tr:last-child th,
        .compare-table tbody tr:last-child td {
            border-bottom: none;
        }
        /* Rayado suave de filas */
        .compare-table tbody tr:nth-child(even) {
            background-color: #fdfdfd;
        }
         /* Quitar rayado si la celda th tiene fondo oscuro */
        .compare-table tbody tr:nth-child(even) th {
             background-color: #e9ecef;
        }


        @media (max-width: 768px) {
            .compare-table { min-width: auto; }
            .compare-table th, .compare-table td { padding: 0.8rem; }
            .compare-table thead img { max-width: 120px; }
            .compare-table thead .vehicle-name { font-size: 1rem; }
        }
    </style>
     </head>
<body>

    <?php include '../../includes/header.php'; ?>

    <div class="compare-page container">
        <h1>Comparativa de Vehículos</h1>

        <div class="compare-table-wrapper">
            <table class="compare-table">
                <thead>
                    <tr>
                        <th></th>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <td>
                                <img src="../../<?php echo htmlspecialchars($vehiculo['imagen_final'] ?? 'assets/img/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($vehiculo['marca'].' '.$vehiculo['modelo']); ?>">
                                <div class="vehicle-name"><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Precio</th>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <td class="price">$<?php echo number_format($vehiculo['precio_lista'], 0, ',', '.'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Año</th>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <td><?php echo htmlspecialchars($vehiculo['anio']); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Kilometraje</th>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <td><?php echo number_format($vehiculo['kilometraje'], 0, ',', '.'); ?> km</td>
                        <?php endforeach; ?>
                    </tr>
                     <tr>
                        <th>Color</th>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <td><?php echo htmlspecialchars($vehiculo['color'] ?? 'N/A'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                     <tr>
                        <th>Descripción</th>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <td class="description-cell"><?php echo !empty($vehiculo['descripcion']) ? nl2br(htmlspecialchars($vehiculo['descripcion'])) : 'N/A'; ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>