-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-03-2025 a las 23:14:12
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
-- Base de datos: `facebook2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `foto_url` varchar(255) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `likes` int(11) DEFAULT 0,
  `dislikes` int(11) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id`, `usuario_id`, `titulo`, `contenido`, `foto_url`, `nombre_usuario`, `likes`, `dislikes`, `fecha`) VALUES
(1, 1, 'Ejemplo 1', 'Nada en especial', 'https://imagen', 'Marco', 2, 1, '2025-03-15 02:10:26'),
(2, 2, 'Qlo rico de Luna', 'Hoy me detoné al Agustín, Me encanta ese qlito :3', 'https://imagen2', 'Alitzel', 1, 0, '2025-03-15 02:14:06'),
(3, 2, 'Prueba navegadores', 'THUS', 'https://imagen2', 'alitzel', 0, 0, '2025-03-19 01:47:53'),
(4, 2, 'Prueba navegadores', 'THUS', 'https://imagen2', 'alitzel', 0, 0, '2025-03-19 01:48:11'),
(5, 2, 'Prueba navegadores2', 'ASD', 'https://imagen2', 'alitzel', 0, 0, '2025-03-19 01:48:36'),
(6, 1, 'hola', 'ali', 'https://images.daznservices.com/di/library/DAZN_News/b1/4d/2024-02-15-verstappen-red-bull-f1-formula-1_7fjp61rzs151lwx4kbayfej8.jpg?t=-1264930179&amp;w=800&amp;quality=100', 'Marco', 0, 0, '2025-03-19 02:07:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reacciones`
--

CREATE TABLE `reacciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `publicacion_id` int(11) NOT NULL,
  `reaccion` enum('like','dislike') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reacciones`
--

INSERT INTO `reacciones` (`id`, `usuario_id`, `publicacion_id`, `reaccion`) VALUES
(2, 1, 2, 'like');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `foto_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `usuario`, `pwd`, `foto_url`) VALUES
(1, 'Marco', '$2y$10$fc6pUAb.8dqxw/553HHqx.HhIGcQx/BZ0duH5ZgG8hiw74RGqeXQm', 'https://images.daznservices.com/di/library/DAZN_News/b1/4d/2024-02-15-verstappen-red-bull-f1-formula-1_7fjp61rzs151lwx4kbayfej8.jpg?t=-1264930179&amp;w=800&amp;quality=100'),
(2, 'Alitzel', '$2y$10$QSqCqLsZi8X3LfI/F2O9w.8sgj5e1q0ms4SPe.8wZFT.fQC7Xef5C', 'https://imagen2');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `reacciones`
--
ALTER TABLE `reacciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`publicacion_id`),
  ADD KEY `publicacion_id` (`publicacion_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `reacciones`
--
ALTER TABLE `reacciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `publicaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `reacciones`
--
ALTER TABLE `reacciones`
  ADD CONSTRAINT `reacciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `reacciones_ibfk_2` FOREIGN KEY (`publicacion_id`) REFERENCES `publicaciones` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
