-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-03-2026 a las 20:20:08
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gym_ma_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_apertura` datetime DEFAULT current_timestamp(),
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_apertura` decimal(10,2) NOT NULL,
  `monto_apertura_usd` decimal(10,2) DEFAULT 0.00,
  `monto_cierre` decimal(10,2) DEFAULT 0.00,
  `monto_cierre_usd` decimal(10,2) DEFAULT 0.00,
  `monto_esperado` decimal(10,2) DEFAULT 0.00,
  `estado` enum('ABIERTA','CERRADA') DEFAULT 'ABIERTA',
  `tasa_apertura` decimal(10,4) DEFAULT 36.6243,
  `nota` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cajas`
--

INSERT INTO `cajas` (`id`, `id_usuario`, `fecha_apertura`, `fecha_cierre`, `monto_apertura`, `monto_apertura_usd`, `monto_cierre`, `monto_cierre_usd`, `monto_esperado`, `estado`, `tasa_apertura`, `nota`) VALUES
(1, 4, '2026-03-03 13:56:40', '2026-03-03 14:05:40', 500.00, 0.00, 515.00, 0.00, 515.00, 'CERRADA', 36.6243, ''),
(2, 4, '2026-03-03 14:06:34', '2026-03-03 14:22:57', 515.00, 0.00, 815.00, 0.00, 815.00, 'CERRADA', 36.6243, ''),
(3, 1, '2026-03-03 15:05:51', '2026-03-03 15:39:33', 815.00, 0.00, 935.00, 0.00, 935.00, 'CERRADA', 36.6243, 'gracias'),
(4, 1, '2026-03-05 08:15:10', '2026-03-05 08:17:37', 935.00, 0.00, 935.00, 0.00, 935.00, 'CERRADA', 36.6243, ''),
(5, 1, '2026-03-05 08:18:16', '2026-03-05 08:19:50', 935.00, 0.00, 935.00, 0.00, 935.00, 'CERRADA', 36.6243, ''),
(6, 1, '2026-03-19 08:24:30', '2026-03-19 11:01:00', 1035.00, 0.00, 1065.00, 0.00, 0.00, 'CERRADA', 36.6243, ''),
(7, 1, '2026-03-25 10:05:32', '2026-03-25 10:05:58', 0.00, 0.00, 0.00, 0.00, 0.00, 'CERRADA', 36.6243, ''),
(8, 1, '2026-03-25 10:15:09', '2026-03-25 10:15:56', 0.00, 0.00, 40.00, 0.00, 40.00, 'CERRADA', 36.6243, ''),
(9, 1, '2026-03-25 11:50:03', '2026-03-25 11:50:55', 0.00, 0.00, 40.00, 0.00, 40.00, 'CERRADA', 36.6243, ''),
(10, 1, '2026-03-25 13:00:22', '2026-03-25 13:01:12', 40.00, 0.00, 115.00, 0.00, 115.00, 'CERRADA', 36.6243, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_egresos`
--

CREATE TABLE `caja_egresos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `monto_salida` decimal(10,2) NOT NULL,
  `fecha_egreso` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `categoria` varchar(50) DEFAULT 'GENERAL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caja_egresos`
--

INSERT INTO `caja_egresos` (`id`, `descripcion`, `monto_salida`, `fecha_egreso`, `id_usuario`, `categoria`) VALUES
(1, 'PAGO MANTENIMIENTO ', 1200.00, '2026-03-25 11:56:08', 1, 'MANTENIMIENTO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL DEFAULT 1,
  `nombre_gym` varchar(100) DEFAULT 'GYM MA DB',
  `moneda_nombre` varchar(50) DEFAULT 'Córdoba Nicaragüense',
  `moneda_iso` varchar(3) DEFAULT 'NIO',
  `moneda_simbolo` varchar(5) DEFAULT 'C$',
  `tipo_cambio_bcn` decimal(10,4) DEFAULT 36.6243,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `direccion_gym` text DEFAULT NULL,
  `telefono_gym` varchar(20) DEFAULT NULL,
  `logo_ruta` varchar(255) DEFAULT 'logo_default.png',
  `tema` varchar(20) DEFAULT 'default'
) ;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_gym`, `moneda_nombre`, `moneda_iso`, `moneda_simbolo`, `tipo_cambio_bcn`, `ultima_actualizacion`, `direccion_gym`, `telefono_gym`, `logo_ruta`, `tema`) VALUES
(1, 'GIMNASIO SPARTANS', 'Córdoba Nicaragüense', 'NIO', 'C$', 36.6243, '2026-03-25 19:11:16', 'Managua Nicaragua LINDA VISTA, Gasolinera Puma 1 c al S. ', '88888888', 'logo_principal.png', 'darkblue');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id`, `descripcion`, `precio`, `cantidad`) VALUES
(1, 'Botella Agua  Litro', 20.00, 73),
(2, 'Coca Cola 12 OZ', 15.00, 22);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id` int(11) NOT NULL,
  `nombre_plan` varchar(100) NOT NULL,
  `duracion_dias` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `nombre_plan`, `duracion_dias`, `precio`, `estado`) VALUES
(1, 'Mensualidad Pesas', 30, 300.00, 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `edad` int(3) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `enfermedad` text DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `emergencia_contacto` varchar(100) DEFAULT NULL,
  `foto_ruta` varchar(255) DEFAULT 'default.png',
  `estado` enum('ACTIVO','INACTIVO','DEUDOR') DEFAULT 'ACTIVO',
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_renovacion` date DEFAULT NULL,
  `id_plan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `socios`
--

INSERT INTO `socios` (`id`, `nombre`, `apellido`, `cedula`, `edad`, `telefono`, `enfermedad`, `fecha_ingreso`, `emergencia_contacto`, `foto_ruta`, `estado`, `fecha_vencimiento`, `fecha_renovacion`, `id_plan`) VALUES
(11, 'Juan', 'Lopez', 'N/A', 18, '89900000', 'Ninguna', '2026-03-03', 'Jonas 8888800', 'default.png', 'ACTIVO', '2026-04-02', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('ADMIN','CAJA') NOT NULL DEFAULT 'CAJA',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `usuario`, `cedula`, `password`, `telefono`, `rol`, `fecha_creacion`) VALUES
(1, 'Administrador', 'General', 'admin', '001-000000-0000A', '$2y$10$AdGxvloNGYnjjj55Dnpli.edXvnGs5R6OgAjvoOzQ5VdW/91PX7PW', '88888888', 'ADMIN', '2026-02-26 22:50:35'),
(4, 'Briana', 'Lopez', 'blopez', 'N/A', '$2y$10$OhjxGqP9YS3qmF5v3EHRTesWc2M6uXkHVsNBSyquYdJWbXAR3tBN6', '888', 'CAJA', '2026-02-27 16:26:48'),
(7, 'Pedro', 'Perez', 'pperez', '001 010189 000Q', '$2y$10$7Hoh3uLEAgJvSV8xXf5/GezjxTEzsMMSXuew2WkRprNFCQxENFE7e', NULL, 'CAJA', '2026-03-25 19:04:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_caja` int(11) DEFAULT NULL,
  `id_socio` int(11) DEFAULT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `tasa_cambio_momento` decimal(10,4) DEFAULT 36.6243,
  `moneda_original` varchar(3) DEFAULT 'NIO',
  `concepto` varchar(255) NOT NULL,
  `cantidad` int(11) DEFAULT 1,
  `tipo_item` enum('PLAN','PRODUCTO') NOT NULL,
  `id_item_referencia` int(11) DEFAULT NULL,
  `cantidad_item` int(11) DEFAULT 1,
  `metodo_pago` enum('EFECTIVO','TRANSFERENCIA') DEFAULT 'EFECTIVO',
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(20) DEFAULT 'COMPLETADO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `id_usuario`, `id_caja`, `id_socio`, `monto_total`, `tasa_cambio_momento`, `moneda_original`, `concepto`, `cantidad`, `tipo_item`, `id_item_referencia`, `cantidad_item`, `metodo_pago`, `fecha_venta`, `estado`) VALUES
(1, 1, NULL, NULL, 20.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:10:09', 'COMPLETADO'),
(2, 1, NULL, NULL, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', NULL, 1, 'EFECTIVO', '2026-02-27 19:11:12', 'COMPLETADO'),
(3, 1, NULL, NULL, 80.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x4)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:41:33', 'COMPLETADO'),
(4, 1, NULL, NULL, 20.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:41:35', 'COMPLETADO'),
(5, 1, NULL, NULL, 20.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:41:38', 'COMPLETADO'),
(6, 1, NULL, NULL, 20.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:41:39', 'COMPLETADO'),
(7, 1, NULL, NULL, 60.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x3)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:42:26', 'COMPLETADO'),
(8, 1, NULL, NULL, 45.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x3)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:54:44', 'COMPLETADO'),
(9, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:55:06', 'COMPLETADO'),
(10, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:55:10', 'COMPLETADO'),
(11, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:55:14', 'COMPLETADO'),
(12, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:55:18', 'COMPLETADO'),
(13, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:56:05', 'COMPLETADO'),
(14, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:56:40', 'COMPLETADO'),
(15, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:56:49', 'COMPLETADO'),
(16, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:58:37', 'COMPLETADO'),
(17, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:58:47', 'COMPLETADO'),
(18, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 19:59:02', 'COMPLETADO'),
(19, 1, NULL, NULL, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', NULL, 1, 'TRANSFERENCIA', '2026-02-27 19:59:32', 'COMPLETADO'),
(20, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:22:52', 'COMPLETADO'),
(21, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:23:17', 'COMPLETADO'),
(22, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:24:07', 'COMPLETADO'),
(23, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:28:13', 'COMPLETADO'),
(24, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:28:56', 'COMPLETADO'),
(25, 1, NULL, NULL, 60.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x4)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:29:25', 'COMPLETADO'),
(26, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:30:03', 'COMPLETADO'),
(27, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:55:44', 'COMPLETADO'),
(28, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:55:52', 'COMPLETADO'),
(29, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:59:07', 'COMPLETADO'),
(30, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, 'EFECTIVO', '2026-02-27 20:59:09', 'COMPLETADO'),
(31, 1, NULL, NULL, 30.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x2)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-02-27 21:01:51', 'COMPLETADO'),
(32, 1, NULL, NULL, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', NULL, 1, NULL, '2026-02-27 21:02:33', 'COMPLETADO'),
(33, 1, NULL, NULL, 0.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x0)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-02-27 21:07:26', 'COMPLETADO'),
(34, 1, NULL, NULL, 75.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x5)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-02-27 21:15:12', 'COMPLETADO'),
(35, 1, NULL, NULL, 60.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x4)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-02-27 21:18:58', 'COMPLETADO'),
(36, 1, NULL, NULL, 60.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x4)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-02-27 21:20:11', 'COMPLETADO'),
(37, 1, NULL, NULL, 30.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x2)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-02-27 21:21:56', 'COMPLETADO'),
(38, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-03-02 15:02:49', 'COMPLETADO'),
(39, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-03-02 15:03:04', 'COMPLETADO'),
(40, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-03-02 15:04:04', 'COMPLETADO'),
(41, 1, NULL, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-03-03 13:52:04', 'COMPLETADO'),
(42, 4, NULL, NULL, 75.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x5)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-03-03 14:55:55', 'ANULADO'),
(43, 4, NULL, NULL, 75.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x5)', 1, 'PRODUCTO', NULL, 1, NULL, '2026-03-03 14:56:07', 'COMPLETADO'),
(44, 1, NULL, NULL, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', 1, 1, NULL, '2026-03-03 15:51:06', 'COMPLETADO'),
(45, 1, NULL, NULL, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', 1, 1, NULL, '2026-03-03 15:53:22', 'COMPLETADO'),
(46, 4, NULL, NULL, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', 1, 1, NULL, '2026-03-03 16:02:08', 'COMPLETADO'),
(47, 4, NULL, NULL, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', 1, 1, NULL, '2026-03-03 17:00:42', 'COMPLETADO'),
(48, 4, NULL, 11, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', 1, 1, NULL, '2026-03-03 17:55:29', 'COMPLETADO'),
(49, 4, 1, NULL, 15.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', 1, 'PRODUCTO', 2, 1, 'EFECTIVO', '2026-03-03 19:57:01', 'COMPLETADO'),
(50, 4, 2, NULL, 300.00, 36.6243, 'NIO', 'Plan: Mensualidad Pesas', 1, 'PLAN', 1, 1, 'EFECTIVO', '2026-03-03 20:06:58', 'COMPLETADO'),
(51, 1, 3, NULL, 60.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x4)', 1, 'PRODUCTO', 2, 4, 'EFECTIVO', '2026-03-03 21:06:15', 'COMPLETADO'),
(52, 1, 3, NULL, 60.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x3)', 1, 'PRODUCTO', 1, 3, 'EFECTIVO', '2026-03-03 21:22:40', 'COMPLETADO'),
(53, 1, 6, NULL, 30.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x2)', 1, 'PRODUCTO', 2, 2, 'EFECTIVO', '2026-03-19 14:24:45', 'ANULADO'),
(54, 1, 8, NULL, 40.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x2)', 1, 'PRODUCTO', 1, 2, 'EFECTIVO', '2026-03-25 16:15:31', 'COMPLETADO'),
(55, 1, 9, NULL, 40.00, 36.6243, 'NIO', 'Venta Art: Botella Agua  Litro (x2)', 1, 'PRODUCTO', 1, 2, 'EFECTIVO', '2026-03-25 17:50:12', 'COMPLETADO'),
(56, 1, 10, NULL, 75.00, 36.6243, 'NIO', 'Venta Art: Coca Cola 12 OZ (x5)', 1, 'PRODUCTO', 2, 5, 'EFECTIVO', '2026-03-25 19:00:47', 'COMPLETADO');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_egresos`
--
ALTER TABLE `caja_egresos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_socio_plan_gym` (`id_plan`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_venta_usuario` (`id_usuario`),
  ADD KEY `fk_venta_socio` (`id_socio`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `caja_egresos`
--
ALTER TABLE `caja_egresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `socios`
--
ALTER TABLE `socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `socios`
--
ALTER TABLE `socios`
  ADD CONSTRAINT `fk_socio_plan` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_socio_plan_gym` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_venta_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_venta_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
