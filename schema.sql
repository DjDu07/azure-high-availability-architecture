-- ====================================================================
-- ESQUEMA DE BASE DE DATOS - TIENDA DE CERVEZAS
-- ====================================================================
-- Archivo: schema.sql
-- Descripción: Estructura completa de la base de datos para tienda de cervezas
-- Actualizado: 2025-12-01
-- Cambios:
--   - Eliminado campo stock de Producto (trabajamos sin control de inventario).
--   - Eliminado campo estado de Pedido (simplificación de flujo de pedido).
-- ====================================================================

-- CREACIÓN DE LA BASE DE DATOS
-- ====================================================================
CREATE DATABASE IF NOT EXISTS mysql_beerboost;
USE mysql_beerboost;

-- ELIMINACIÓN DE TABLAS EXISTENTES (ORDEN CRÍTICO)
-- ====================================================================
DROP TABLE IF EXISTS Detalle_Pedido;
DROP TABLE IF EXISTS Pedido;
DROP TABLE IF EXISTS Producto;
DROP TABLE IF EXISTS Cliente;

-- TABLA: CLIENTE
-- ====================================================================
-- Almacena información de los clientes de la tienda
CREATE TABLE Cliente (
    id_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    distrito VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uq_cliente_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: PRODUCTO
-- ====================================================================
-- Almacena información de los productos (cervezas).
-- OJO: sin columna stock, porque trabajamos con productos estáticos.
CREATE TABLE Producto (
    id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: PEDIDO
-- ====================================================================
-- Almacena información de los pedidos realizados.
-- OJO: sin columna estado, porque no manejamos flujo de estados.
CREATE TABLE Pedido (
    id_pedido INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNSIGNED NOT NULL,
    fecha_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    direccion_entrega VARCHAR(200) NOT NULL,
    distrito_entrega VARCHAR(100) NOT NULL,
    codigo_postal_entrega VARCHAR(10) NOT NULL,
    departamento_entrega VARCHAR(100) NOT NULL,

    FOREIGN KEY (id_cliente) REFERENCES Cliente(id_cliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA: DETALLE_PEDIDO
-- ====================================================================
-- Almacena los detalles de cada producto en cada pedido
CREATE TABLE Detalle_Pedido (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_pedido) REFERENCES Pedido(id_pedido)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_producto) REFERENCES Producto(id_producto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================================================
-- CARGA INICIAL DE PRODUCTOS (DEBE COINCIDIR CON LOS data-id DEL HTML)
-- ====================================================================
INSERT INTO Producto (id_producto, nombre, descripcion, precio, activo) VALUES
(1,  'IPA Andina', 'Intensa y aromática, con notas cítricas y un amargor marcado al estilo West Coast.', 14.90, 1),
(2,  'Pale Ale Cusqueña', 'Equilibrada y refrescante, con notas florales y de caramelo ligero.', 12.50, 1),
(3,  'Porter Limeña', 'Oscura y tostada, con notas a chocolate y café, cuerpo medio y final suave.', 15.90, 1),
(4,  'Wheat Beer del Valle', 'Cerveza de trigo suave, ligeramente turbia, con toques frutales y especiados.', 13.40, 1),
(5,  'Red Ale Arequipeña', 'Roja y maltosa, con notas acarameladas y tostadas, ideal para maridar con carnes.', 13.90, 1),
(6,  'Stout Barranco', 'Stout cremosa, con notas intensas de malta tostada y cacao.', 16.50, 1),
(7,  'Lager Limeña', 'Lager clara, muy refrescante, de amargor suave y final limpio.', 11.90, 1),
(8,  'Sour Maracuyá', 'Cerveza ácida con maracuyá, muy fresca y frutada.', 14.50, 1),
(9,  'Belgian Tripel Andina', 'Tripel belga de alta graduación, compleja, especiada y con final seco.', 18.90, 1),
(10, 'Session IPA Costa', 'IPA ligera en alcohol, muy aromática y fácil de beber.', 13.20, 1);

-- ====================================================================
-- FIN DEL ESQUEMA
-- Cliente (1) --> (N) Pedido
-- Pedido (1) --> (N) Detalle_Pedido
-- Producto (1) --> (N) Detalle_Pedido
-- ====================================================================