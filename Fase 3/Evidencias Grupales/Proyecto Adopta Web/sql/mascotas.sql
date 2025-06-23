-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-06-2025 a las 13:08:21
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
-- Base de datos: `adoptaweb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascotas`
--

CREATE TABLE `mascotas` (
  `id` int(11) NOT NULL,
  `estado_salud` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `sexo` varchar(20) NOT NULL,
  `raza` varchar(100) NOT NULL,
  `especie` varchar(100) NOT NULL,
  `edad` int(11) NOT NULL,
  `nro_chip` varchar(50) NOT NULL,
  `tamaño` varchar(50) NOT NULL,
  `refugio_id` int(11) NOT NULL,
  `adoptante_id` int(11) DEFAULT NULL,
  `region_id` int(11) NOT NULL,
  `comuna_id` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'No adoptada',
  `foto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mascotas`
--

INSERT INTO `mascotas` (`id`, `estado_salud`, `nombre`, `sexo`, `raza`, `especie`, `edad`, `nro_chip`, `tamaño`, `refugio_id`, `adoptante_id`, `region_id`, `comuna_id`, `estado`, `foto`) VALUES
(1, 'Saludable.', 'Coffy', 'Macho', 'Quiltro', 'Perro', 13, '1234567123', 'Pequeño', 1, NULL, 7, 84, 'No Adoptado', 'fotos/20250613_154116.jpg'),
(2, 'Saludable.', 'Piojito', 'Macho', 'Pequines', 'Perro', 4, '1234567124', 'Grande', 1, NULL, 11, 218, 'No Adoptado', 'fotos/20250613_154937.jpg'),
(3, 'Saludable.', 'Ruffa', 'Hembra', 'Gata albina', 'Gata', 1, '1234567125', 'Pequeña', 3, NULL, 11, 214, 'No Adoptado', 'fotos/20250613_155739.jpg'),
(4, 'Saludable', 'Frufru', 'Macho', 'Cocker', 'Perro', 26, '987654321', 'Mediano', 3, 2, 11, 216, 'Adoptado', 'fotos/20250623_021536.jpeg');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refugio_id` (`refugio_id`),
  ADD KEY `adoptante_id` (`adoptante_id`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `comuna_id` (`comuna_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD CONSTRAINT `mascotas_ibfk_1` FOREIGN KEY (`refugio_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `mascotas_ibfk_2` FOREIGN KEY (`adoptante_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `mascotas_ibfk_3` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`),
  ADD CONSTRAINT `mascotas_ibfk_4` FOREIGN KEY (`comuna_id`) REFERENCES `comuna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
