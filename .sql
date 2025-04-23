-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-08-2024 a las 20:09:14
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `eventos_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionarios`
--

INSERT INTO `funcionarios` (`id`, `nombre`, `correo`, `cargo`, `telefono`, `contrasena`) VALUES
(1, 'jaider', 'jaider@jaider', 'funcionario', '3123123', ''),
(2, 'cindy', 'cindy@cindy', 'logistica', '21312', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3'),
(3, 'DRAWEN', 'dra@dra', 'aseador', '3123123', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3'),
(4, 'cindy2', 'cindy2@cindy', 'logistica', '231', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto` decimal(10,2) NOT NULL,
  `confirmado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `evento` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `invitados` int(11) NOT NULL,
  `confirmado` tinyint(1) DEFAULT 0,
  `fecha_reserva` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `usuario_id`, `evento`, `fecha`, `invitados`, `confirmado`, `fecha_reserva`) VALUES
(1, 2, 'cumpleaños', '2024-08-07', 0, 1, '2024-08-07 11:04:11'),
(2, 6, '15 años', '2024-08-07', 0, 1, '2024-08-07 11:04:11'),
(3, 6, '15 años', '2024-08-07', 0, 1, '2024-08-07 11:04:11'),
(4, 6, '15 años', '2024-08-07', 0, 1, '2024-08-07 11:04:11'),
(5, 6, 'despedidas de soltero', '2024-08-07', 0, 1, '2024-08-07 11:04:11'),
(6, 6, 'cumpleaños', '2024-08-07', 123, 1, '2024-08-07 11:04:11'),
(7, 7, 'despedidas de soltero', '2024-08-07', 1234, 1, '2024-08-07 11:32:08'),
(10, 2, 'cumpleaños', '2024-08-13', 1000, 1, '2024-08-13 08:39:19'),
(13, 7, 'Fiesta Tropical', '2024-08-19', 123, 1, '2024-08-19 15:51:02'),
(17, 2, 'Tu Cumple', '2024-08-20', 3213, 0, '2024-08-20 12:56:00'),
(18, 13, 'La Noche de los Sueños', '2024-08-20', 3232, 0, '2024-08-20 13:03:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id` int(11) NOT NULL,
  `evento` varchar(255) NOT NULL,
  `tarifa` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id`, `evento`, `tarifa`) VALUES
(15, 'Nigth Party', 1500000.00),
(16, 'Elefantito Azul', 1000000.00),
(17, 'Unicornio Mágico', 1000000.00),
(18, 'Osito Dulce', 1000000.00),
(19, 'Mariposas Coloridas', 1000000.00),
(20, 'economic pack', 300000.00),
(21, 'Tu Cumple', 800000.00),
(22, 'Parque de juegos', 500000.00),
(23, 'Super Personajes', 800000.00),
(24, 'Noche de lujo y diversión', 1200000.00),
(25, 'Tema de Mascarada Elegante', 3000000.00),
(26, 'Baile de Princesas de Cuento de Hadas', 4000000.00),
(27, 'Fiesta Tropical', 2500000.00),
(28, 'Graduación', 2000000.00),
(29, '\"Amor en el Horizonte\"', 8000000.00),
(30, '\"Romance en el Jardín\"', 6000000.00),
(31, 'Noche de Caballeros', 3000000.00),
(35, 'Escape Night: Fiesta de Solteros para Chicas', 3000000.00),
(36, 'La Noche de los Sueños', 3000000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('funcionario','usuario') DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `rol`) VALUES
(2, 'Mauro', 'AJA@AJA', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(3, 'Mauro', 'JAJA@KQK', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(4, 'Prueba', 'prueba@prueba', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(5, 'prueba 1', 'prueba1@prueba1', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(6, 'cantiflas', '123@123', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(7, 'coco', 'co@co', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(9, 'Mauro', 'pepe@pepe', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(10, 'prueba3', 'pru@pru', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(11, 'wey', 'we@we', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(12, 'Breiny', 'bre@bre', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario'),
(13, 'Frank', 'fran@fran', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'usuario');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reserva_id` (`reserva_id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id_reserva`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
