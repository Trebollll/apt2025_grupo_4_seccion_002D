-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-06-2025 a las 13:08:42
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
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rut` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `edad` int(11) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `direccion` text NOT NULL,
  `numero_contacto` varchar(20) NOT NULL,
  `representante` varchar(100) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `especies` text DEFAULT NULL,
  `redes_sociales` text DEFAULT NULL,
  `mapa_url` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `region_id` int(11) NOT NULL,
  `comuna_id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `rut`, `email`, `contrasena`, `edad`, `fecha_nacimiento`, `genero`, `direccion`, `numero_contacto`, `representante`, `horario`, `descripcion`, `especies`, `redes_sociales`, `mapa_url`, `foto`, `fecha_creacion`, `region_id`, `comuna_id`, `tipo`) VALUES
(1, 'Fundación Quiltro', '20.487.624-k', 'die.aguayo@duocuc.cl', '$2y$10$tVVHuqHBtz.NxVeOMPFqBOYGWsdgGOLXJB8H0nyxhY3Ukh4q6iFN6', NULL, NULL, NULL, 'Av. del Valle Nte. 961, 8580710 Huechuraba, Región Metropolitana', '+569 12345678', 'Diego Aguayo', 'Lunes a viernes: 08:00 - 18:00\r\nSábados: 10:00 - 14:00', 'Somos un refugio enfocado en los animales callejeros y centralizado en perros.', 'Perros.', 'Instagram: @fundacionquiltro', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3331.330209983223!2d-70.6198570227761!3d-33.38854832222601!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9662c8ab8c69c9f5%3A0xd42e24a88020feb!2sAgencia%20los%20Quiltros!5e0!3m2!1ses!2scl!4v1749821437294!5m2!1ses!2scl\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 'fotos/20250613_153414_20487624k.jpg', '2020-07-14 00:00:00', 7, 84, 'Refugio'),
(2, 'Guillermo Saavedra', '19.835.368-k', 'gu.saavedrag@duocuc.cl', '$2y$10$wkAe6/rFwTIiOYUgiY9VbevOstLIyY7BP8Dfvq02c8pvqTsLmtq7u', 27, '1998-04-25', 'Masculino', 'Bio Bio #18', '+569 65987456', NULL, NULL, NULL, NULL, NULL, NULL, 'fotos/20250613_154637_19835368k.png', '2025-06-13 09:46:38', 11, 216, 'Adoptante'),
(3, 'Patitas Sin Hogar', '10.492.481-6', 'patitassinhogar@gmail.com', '$2y$10$VW2QM3BdoD5UKXrGq..SY.dvDP4g1FanqNEh4.Ee1/jMEZgBjsyVO', NULL, NULL, NULL, 'Dirección: Serrano 311 (Esquina San Martin)', '+569 12345699', 'César Jiménez', 'De lunes a sábado: 08:00 - 19:00', 'Somos un refugio ubicado en la región de Concepción que busca darle un hogar a los animales callejeros.', 'Perros, gatos y hamsters.', 'Instagram: @patitassinhogar', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3193.3695926637733!2d-73.01625876549005!3d-36.833623597384985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9669b7ec14a81c99%3A0x359add3ce3c6c688!2sRefugio%20Del%20Clan%20Hern%C3%A1ndez!5e0!3m2!1ses!2scl!4v1749822813713!5m2!1ses!2scl\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 'fotos/20250613_155606_104924816.jpg', '2022-10-29 00:00:00', 11, 214, 'Refugio'),
(4, 'César Jiménez', '20.057.454-0', 'ce.jimenez.le@gmail.com', '$2y$10$qseecE14feHIoSogegmseO109AgtUs5hVo/NR3BmPB1KFJu08g4Aq', NULL, NULL, NULL, 'Sin dirección', '+56900000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-17 17:15:04', 11, 216, 'Administrador');

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `before_usuario_delete` BEFORE DELETE ON `usuarios` FOR EACH ROW BEGIN
  INSERT INTO eliminados (rut) VALUES (OLD.rut);
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rut` (`rut`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `comuna_id` (`comuna_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`comuna_id`) REFERENCES `comuna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
