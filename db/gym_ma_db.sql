/*
Navicat MySQL Data Transfer

Source Server         : 10.10.179.4
Source Server Version : 50505
Source Host           : 10.10.179.4:3306
Source Database       : gym_ma_db

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2026-03-26 14:03:50
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `caja_egresos`
-- ----------------------------
DROP TABLE IF EXISTS `caja_egresos`;
CREATE TABLE `caja_egresos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) NOT NULL,
  `monto_salida` decimal(10,2) NOT NULL,
  `fecha_egreso` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `categoria` varchar(50) DEFAULT 'GENERAL',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of caja_egresos
-- ----------------------------
INSERT INTO `caja_egresos` VALUES ('1', 'PAGO MANTENIMIENTO ', '1200.00', '2026-03-25 11:56:08', '1', 'MANTENIMIENTO');

-- ----------------------------
-- Table structure for `cajas`
-- ----------------------------
DROP TABLE IF EXISTS `cajas`;
CREATE TABLE `cajas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `nota` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of cajas
-- ----------------------------
INSERT INTO `cajas` VALUES ('1', '4', '2026-03-03 13:56:40', '2026-03-03 14:05:40', '500.00', '0.00', '515.00', '0.00', '515.00', 'CERRADA', '36.6243', '');
INSERT INTO `cajas` VALUES ('2', '4', '2026-03-03 14:06:34', '2026-03-03 14:22:57', '515.00', '0.00', '815.00', '0.00', '815.00', 'CERRADA', '36.6243', '');
INSERT INTO `cajas` VALUES ('3', '1', '2026-03-03 15:05:51', '2026-03-03 15:39:33', '815.00', '0.00', '935.00', '0.00', '935.00', 'CERRADA', '36.6243', 'gracias');
INSERT INTO `cajas` VALUES ('4', '1', '2026-03-05 08:15:10', '2026-03-05 08:17:37', '935.00', '0.00', '935.00', '0.00', '935.00', 'CERRADA', '36.6243', '');
INSERT INTO `cajas` VALUES ('5', '1', '2026-03-05 08:18:16', '2026-03-05 08:19:50', '935.00', '0.00', '935.00', '0.00', '935.00', 'CERRADA', '36.6243', '');
INSERT INTO `cajas` VALUES ('6', '1', '2026-03-19 08:24:30', '2026-03-19 11:01:00', '1035.00', '0.00', '1065.00', '0.00', '0.00', 'CERRADA', '36.6243', '');
INSERT INTO `cajas` VALUES ('7', '1', '2026-03-25 10:05:32', '2026-03-25 10:05:58', '0.00', '0.00', '0.00', '0.00', '0.00', 'CERRADA', '36.6243', '');
INSERT INTO `cajas` VALUES ('8', '1', '2026-03-25 10:15:09', '2026-03-25 10:15:56', '0.00', '0.00', '40.00', '0.00', '40.00', 'CERRADA', '36.6243', '');
INSERT INTO `cajas` VALUES ('9', '1', '2026-03-25 11:50:03', '2026-03-25 11:50:55', '0.00', '0.00', '40.00', '0.00', '40.00', 'CERRADA', '36.6243', '');
INSERT INTO `cajas` VALUES ('10', '1', '2026-03-25 13:00:22', '2026-03-25 13:01:12', '40.00', '0.00', '115.00', '0.00', '115.00', 'CERRADA', '36.6243', '');

-- ----------------------------
-- Table structure for `configuracion`
-- ----------------------------
DROP TABLE IF EXISTS `configuracion`;
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
  `tema` varchar(20) DEFAULT 'default',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of configuracion
-- ----------------------------
INSERT INTO `configuracion` VALUES ('1', 'GIMNASIO SPARTANS', 'Córdoba Nicaragüense', 'NIO', 'C$', '36.6243', '2026-03-25 13:11:16', 'Managua Nicaragua LINDA VISTA, Gasolinera Puma 1 c al S. ', '88888888', 'logo_principal.png', 'darkblue');

-- ----------------------------
-- Table structure for `inventario`
-- ----------------------------
DROP TABLE IF EXISTS `inventario`;
CREATE TABLE `inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(150) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of inventario
-- ----------------------------
INSERT INTO `inventario` VALUES ('1', 'Botella Agua  Litro', '20.00', '73');
INSERT INTO `inventario` VALUES ('2', 'Coca Cola 12 OZ', '15.00', '22');

-- ----------------------------
-- Table structure for `planes`
-- ----------------------------
DROP TABLE IF EXISTS `planes`;
CREATE TABLE `planes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_plan` varchar(100) NOT NULL,
  `duracion_dias` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of planes
-- ----------------------------
INSERT INTO `planes` VALUES ('1', 'Mensualidad Pesas', '30', '300.00', 'ACTIVO');

-- ----------------------------
-- Table structure for `socios`
-- ----------------------------
DROP TABLE IF EXISTS `socios`;
CREATE TABLE `socios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `id_plan` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_socio_plan_gym` (`id_plan`),
  CONSTRAINT `fk_socio_plan` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_socio_plan_gym` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of socios
-- ----------------------------
INSERT INTO `socios` VALUES ('11', 'Juan', 'Lopez', 'N/A', '18', '89900000', 'Ninguna', '2026-03-03', 'Jonas 8888800', 'default.png', 'ACTIVO', '2026-04-02', null, '1');

-- ----------------------------
-- Table structure for `usuarios`
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('ADMIN','CAJA') NOT NULL DEFAULT 'CAJA',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `two_factor_pin` varchar(10) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of usuarios
-- ----------------------------
INSERT INTO `usuarios` VALUES ('1', 'Administrador', 'General', 'admin', '001-000000-0000A', '$2y$10$AdGxvloNGYnjjj55Dnpli.edXvnGs5R6OgAjvoOzQ5VdW/91PX7PW', '88888888', 'ADMIN', '2026-02-26 16:50:35', null, '0', null);
INSERT INTO `usuarios` VALUES ('4', 'Briana', 'Lopez', 'blopez', 'N/A', '$2y$10$OhjxGqP9YS3qmF5v3EHRTesWc2M6uXkHVsNBSyquYdJWbXAR3tBN6', '888', 'CAJA', '2026-02-27 10:26:48', null, '0', null);
INSERT INTO `usuarios` VALUES ('7', 'Pedro', 'Perez', 'pperez', '001 010189 000Q', '$2y$10$7Hoh3uLEAgJvSV8xXf5/GezjxTEzsMMSXuew2WkRprNFCQxENFE7e', null, 'CAJA', '2026-03-25 13:04:39', null, '0', null);

-- ----------------------------
-- Table structure for `ventas`
-- ----------------------------
DROP TABLE IF EXISTS `ventas`;
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `estado` varchar(20) DEFAULT 'COMPLETADO',
  PRIMARY KEY (`id`),
  KEY `fk_venta_usuario` (`id_usuario`),
  KEY `fk_venta_socio` (`id_socio`),
  CONSTRAINT `fk_venta_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_venta_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of ventas
-- ----------------------------
INSERT INTO `ventas` VALUES ('1', '1', null, null, '20.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:10:09', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('2', '1', null, null, '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', null, '1', 'EFECTIVO', '2026-02-27 13:11:12', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('3', '1', null, null, '80.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x4)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:41:33', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('4', '1', null, null, '20.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:41:35', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('5', '1', null, null, '20.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:41:38', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('6', '1', null, null, '20.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:41:39', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('7', '1', null, null, '60.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x3)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:42:26', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('8', '1', null, null, '45.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x3)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:54:44', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('9', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:55:06', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('10', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:55:10', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('11', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:55:14', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('12', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:55:18', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('13', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:56:05', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('14', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:56:40', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('15', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:56:49', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('16', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:58:37', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('17', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:58:47', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('18', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 13:59:02', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('19', '1', null, null, '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', null, '1', 'TRANSFERENCIA', '2026-02-27 13:59:32', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('20', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:22:52', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('21', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:23:17', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('22', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:24:07', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('23', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:28:13', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('24', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:28:56', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('25', '1', null, null, '60.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x4)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:29:25', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('26', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:30:03', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('27', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:55:44', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('28', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:55:52', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('29', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:59:07', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('30', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', 'EFECTIVO', '2026-02-27 14:59:09', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('31', '1', null, null, '30.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x2)', '1', 'PRODUCTO', null, '1', null, '2026-02-27 15:01:51', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('32', '1', null, null, '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', null, '1', null, '2026-02-27 15:02:33', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('33', '1', null, null, '0.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x0)', '1', 'PRODUCTO', null, '1', null, '2026-02-27 15:07:26', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('34', '1', null, null, '75.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x5)', '1', 'PRODUCTO', null, '1', null, '2026-02-27 15:15:12', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('35', '1', null, null, '60.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x4)', '1', 'PRODUCTO', null, '1', null, '2026-02-27 15:18:58', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('36', '1', null, null, '60.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x4)', '1', 'PRODUCTO', null, '1', null, '2026-02-27 15:20:11', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('37', '1', null, null, '30.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x2)', '1', 'PRODUCTO', null, '1', null, '2026-02-27 15:21:56', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('38', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', null, '2026-03-02 09:02:49', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('39', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', null, '2026-03-02 09:03:04', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('40', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', null, '2026-03-02 09:04:04', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('41', '1', null, null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', null, '1', null, '2026-03-03 07:52:04', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('42', '4', null, null, '75.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x5)', '1', 'PRODUCTO', null, '1', null, '2026-03-03 08:55:55', 'ANULADO');
INSERT INTO `ventas` VALUES ('43', '4', null, null, '75.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x5)', '1', 'PRODUCTO', null, '1', null, '2026-03-03 08:56:07', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('44', '1', null, null, '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', '1', '1', null, '2026-03-03 09:51:06', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('45', '1', null, null, '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', '1', '1', null, '2026-03-03 09:53:22', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('46', '4', null, null, '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', '1', '1', null, '2026-03-03 10:02:08', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('47', '4', null, null, '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', '1', '1', null, '2026-03-03 11:00:42', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('48', '4', null, '11', '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', '1', '1', null, '2026-03-03 11:55:29', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('49', '4', '1', null, '15.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x1)', '1', 'PRODUCTO', '2', '1', 'EFECTIVO', '2026-03-03 13:57:01', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('50', '4', '2', null, '300.00', '36.6243', 'NIO', 'Plan: Mensualidad Pesas', '1', 'PLAN', '1', '1', 'EFECTIVO', '2026-03-03 14:06:58', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('51', '1', '3', null, '60.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x4)', '1', 'PRODUCTO', '2', '4', 'EFECTIVO', '2026-03-03 15:06:15', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('52', '1', '3', null, '60.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x3)', '1', 'PRODUCTO', '1', '3', 'EFECTIVO', '2026-03-03 15:22:40', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('53', '1', '6', null, '30.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x2)', '1', 'PRODUCTO', '2', '2', 'EFECTIVO', '2026-03-19 08:24:45', 'ANULADO');
INSERT INTO `ventas` VALUES ('54', '1', '8', null, '40.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x2)', '1', 'PRODUCTO', '1', '2', 'EFECTIVO', '2026-03-25 10:15:31', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('55', '1', '9', null, '40.00', '36.6243', 'NIO', 'Venta Art: Botella Agua  Litro (x2)', '1', 'PRODUCTO', '1', '2', 'EFECTIVO', '2026-03-25 11:50:12', 'COMPLETADO');
INSERT INTO `ventas` VALUES ('56', '1', '10', null, '75.00', '36.6243', 'NIO', 'Venta Art: Coca Cola 12 OZ (x5)', '1', 'PRODUCTO', '2', '5', 'EFECTIVO', '2026-03-25 13:00:47', 'COMPLETADO');
