-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-06-2025 a las 13:08:31
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
-- Estructura de tabla para la tabla `seguimiento_mascotas`
--

CREATE TABLE `seguimiento_mascotas` (
  `id` int(11) NOT NULL,
  `mascota_id` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `fecha` date NOT NULL,
  `descripcion` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seguimiento_mascotas`
--

INSERT INTO `seguimiento_mascotas` (`id`, `mascota_id`, `titulo`, `fecha`, `descripcion`, `foto`) VALUES
(1, 4, 'Seguimiento de Junio', '2025-06-23', 'Hola.', 'uploads/seguimientos/seg_685918b2837210.15562614.jpeg');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `seguimiento_mascotas`
--
ALTER TABLE `seguimiento_mascotas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mascota_id` (`mascota_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `seguimiento_mascotas`
--
ALTER TABLE `seguimiento_mascotas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `seguimiento_mascotas`
--
ALTER TABLE `seguimiento_mascotas`
  ADD CONSTRAINT `seguimiento_mascotas_ibfk_1` FOREIGN KEY (`mascota_id`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
