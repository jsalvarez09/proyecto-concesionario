-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 21:29:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `concesionario_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `admin_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`admin_id`, `nombre`, `email`, `password`) VALUES
(1, 'Admin', 'admin@concesionario.com', '$2y$10$AvXKRRiAuCe4SvVuTXLtk.XOrQo4ni0yty0ByF9lY5T4X9FidsaLu');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `cliente_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`cliente_id`, `nombre`, `apellido`, `telefono`, `email`, `direccion`, `password`) VALUES
(1, 'Carlos', 'Sánchez', '3101234567', 'carlos.s@email.com', 'Calle 10 #43A-20', ''),
(2, 'Ana', 'Gómez', '3209876543', 'ana.g@email.com', 'Carrera 70 #1-150', ''),
(3, 'Caliche', 'Huertas', '3012346734', 'caliche@gmail.com', NULL, '$2y$10$BsaMw8suQCTOfjxaW81Ugu9DfDeJwSTeF9ZipnB.rb8HvSDy2DegG'),
(4, 'prueba', 'cliente', '3026384498', 'pruebacliente@gmail.com', NULL, '$2y$10$mt4AMi4usDYFfcR9I.n5W.MFgEdZpDI.dBx1WImGYOQuQiZgKg3Qm');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `vehiculo_id` int(11) NOT NULL,
  `placa` varchar(6) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `anio` int(11) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `kilometraje` int(11) DEFAULT 0,
  `precio_lista` decimal(15,2) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Disponible',
  `imagen_url` varchar(255) NOT NULL,
  `vendedor_id` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`vehiculo_id`, `placa`, `marca`, `modelo`, `anio`, `color`, `kilometraje`, `precio_lista`, `estado`, `imagen_url`, `vendedor_id`, `descripcion`) VALUES
(2, 'Z9Y8X7', 'Mazda', '3', 2023, 'Rojo', 12000, 89000000.00, 'Vendido', '', NULL, NULL),
(5, '1V4S5C', 'Ferrari', 'F8 Tributo', 2019, NULL, 0, 45000000.00, 'Disponible', 'uploads/68e05f0751c2d-Ferrari_F8_Tributo_DSC_7013.jpg', NULL, NULL),
(6, '1V8S5C', 'Chevrolet', '0', 2018, NULL, 0, 90000000.00, 'Disponible', 'uploads/68e18c2d3bf2f-images.jpg', NULL, NULL),
(13, 'FMD302', 'Porshe', '911 GT3RS', 2025, NULL, 0, 70000000.00, 'Disponible', '', 4, 'El Porsche 911 GT3 es un superdeportivo diseñado para entregar sensaciones puras de competición. Equipado con un motor 4.0 litros Bóxer de 6 cilindros, naturalmente aspirado, genera 502 hp y alcanza hasta 9,000 rpm, ofreciendo una respuesta inmediata y una aceleración explosiva. Su transmisión PDK de 7 velocidades (o manual de 6 opcional) permite cambios ultra rápidos, mientras que su sistema de suspensión Porsche Active Suspension Management (PASM) y la dirección en el eje trasero garantizan estabilidad y agarre incluso en curvas cerradas.'),
(15, 'AMG676', 'Mercedes Benz', 'CLE 53 AMG COUPE 4MATIC', 2026, NULL, 0, 99999999.99, 'Vendido', '', 4, '0'),
(16, 'DFW231', 'BMW', 'X4 M40i', 2025, NULL, 1000, 200000000.00, 'Disponible', '', 4, 'BMW X4 M40i 2025 Garantía hasta Julio 2027 Servicios incluidos hasta Julio 2030 ó 60.000 KM Motor: 6 cilindros en línea TwinPower Turbo Cilindrada: 2.998 CC Potencia: 387 CV @ 5.800 rpm ParMotor: 500 Nm @ 1.900 rpm Transmisión: Steptronic de 8 velocidades Tracción: 4X4 xDrive'),
(17, 'GMH077', ' Lamborghini ', 'SVJ', 2025, 'Negro', 0, 300000000.00, 'Disponible', '', 4, 'El Lamborghini Aventador SVJ (Super Veloce Jota) es uno de los superdeportivos más extremos jamás creados. Monta un impresionante motor V12 de 6.5 litros, capaz de producir 770 hp y 720 Nm de torque. Con su tracción AWD y una transmisión ISR de 7 velocidades, logra un arranque brutal capaz de impulsarlo de 0 a 100 km/h en 2.8 segundos, alcanzando una velocidad máxima de 350 km/h.\r\n\r\nEl SVJ incorpora el sistema Aero Vectoring ALA 2.0, una tecnología aerodinámica activa que ajusta el flujo de aire en tiempo real para maximizar el agarre y mejorar el desempeño en curva.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculo_imagenes`
--

CREATE TABLE `vehiculo_imagenes` (
  `imagen_id` int(11) NOT NULL,
  `vehiculo_id` int(11) NOT NULL,
  `imagen_url` varchar(255) NOT NULL,
  `es_principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculo_imagenes`
--

INSERT INTO `vehiculo_imagenes` (`imagen_id`, `vehiculo_id`, `imagen_url`, `es_principal`) VALUES
(3, 13, 'uploads/68e56e00791ed-descarga.jpg', 1),
(5, 15, 'uploads/68e57adc373d9-18C0091_025-600x330.jpg', 1),
(7, 16, 'uploads/68e590c4ea884-68e4387af3b64.webp', 1),
(8, 16, 'uploads/68e590c4eb430-68e4387246fb0.webp', 0),
(9, 16, 'uploads/68e590c4eba34-68e4386bc4879.webp', 0),
(10, 16, 'uploads/68e590c4ec4a4-68e4386ec1562.webp', 0),
(26, 17, 'uploads/6920e88556af1-Lambo2.jpg', 1),
(28, 17, 'uploads/6920e92560ecc-Lambo1.jpg', 0),
(29, 17, 'uploads/6920e92fc4938-Lambo4.jpg', 0),
(31, 17, 'uploads/6920e945c81fe-Lambo3.jpg', 0),
(32, 13, 'uploads/6920e9cd843ce-p17-1158-a4-rgb.jpg', 0),
(33, 13, 'uploads/6920e9cd849ed-descarga (1).jpg', 0),
(35, 15, 'uploads/6920e9f3d29bf-AMG 53 GT 2022-19.jpeg', 0),
(37, 15, 'uploads/6920e9f3d3d56-images (3).jpg', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vendedores`
--

CREATE TABLE `vendedores` (
  `vendedor_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vendedores`
--

INSERT INTO `vendedores` (`vendedor_id`, `nombre`, `apellido`, `telefono`, `email`, `fecha_contratacion`, `password`) VALUES
(1, 'Juan', 'Pérez', NULL, 'juan.perez@concesionario.com', '2024-01-10', ''),
(2, 'Maria', 'López', NULL, 'maria.lopez@concesionario.com', '2023-03-15', ''),
(3, 'Caliche', 'Huertas', '3012346734', 'caliche@gmail.com', '2025-10-03', '$2y$10$1o09vUlsbUp7FWJ/N0hYK.FxwQQH89laaBpIyaseyxEO1/5bmjzxm'),
(4, 'prueba', 'vendedor', '3214564432', 'pruebavendedor@gmail.com', '2025-10-03', '$2y$10$M5xAMiRonTU.lbqQ3KGbEe07Da4dq7kIC88jen2oTSUpfKiXe/GpK'),
(5, 'Juan Carlos', 'Alvarez', '3052246638', 'juan@gmail.com', '2025-10-04', '$2y$10$yV6GrpqH5KBdzkdkAr8x4.oXFU9PnHEWIc7vSHGeMfTJ/ErfCeJJ.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `venta_id` int(11) NOT NULL,
  `vehiculo_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `vendedor_id` int(11) NOT NULL,
  `fecha_venta` date NOT NULL,
  `precio_final` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`venta_id`, `vehiculo_id`, `cliente_id`, `vendedor_id`, `fecha_venta`, `precio_final`) VALUES
(1, 2, 2, 1, '2025-10-02', 88500000.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cliente_id`),
  ADD UNIQUE KEY `telefono` (`telefono`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`vehiculo_id`),
  ADD UNIQUE KEY `vin` (`placa`),
  ADD UNIQUE KEY `placa` (`placa`);

--
-- Indices de la tabla `vehiculo_imagenes`
--
ALTER TABLE `vehiculo_imagenes`
  ADD PRIMARY KEY (`imagen_id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`);

--
-- Indices de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`vendedor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`venta_id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `vendedor_id` (`vendedor_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cliente_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `vehiculo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `vehiculo_imagenes`
--
ALTER TABLE `vehiculo_imagenes`
  MODIFY `imagen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `vendedor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `venta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `vehiculo_imagenes`
--
ALTER TABLE `vehiculo_imagenes`
  ADD CONSTRAINT `vehiculo_imagenes_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`vehiculo_id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`vehiculo_id`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`cliente_id`),
  ADD CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`vendedor_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
