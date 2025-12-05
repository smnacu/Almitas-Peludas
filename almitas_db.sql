-- ============================================
-- ALMITAS PELUDAS - Base de Datos MySQL
-- Versión: 1.0
-- Fecha: 2025-12-05
-- ============================================

CREATE DATABASE IF NOT EXISTS almitas_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE almitas_db;

-- ============================================
-- TABLA: usuarios
-- Roles: 'admin', 'cliente'
-- ============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    barrio VARCHAR(100),
    rol ENUM('admin', 'cliente') DEFAULT 'cliente',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: peluqueria_servicios
-- Catálogo de servicios de peluquería
-- ============================================
CREATE TABLE peluqueria_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    duracion_minutos INT NOT NULL DEFAULT 60,
    precio DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: peluqueria_turnos
-- Turnos a domicilio con validación de zonas
-- Estados: 'pendiente', 'confirmado', 'en_camino', 'completado', 'cancelado'
-- ============================================
CREATE TABLE peluqueria_turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    servicio_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    barrio VARCHAR(100) NOT NULL,
    notas TEXT,
    estado ENUM('pendiente', 'confirmado', 'en_camino', 'completado', 'cancelado') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES peluqueria_servicios(id) ON DELETE RESTRICT,
    INDEX idx_fecha (fecha),
    INDEX idx_barrio (barrio),
    INDEX idx_estado (estado),
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: shop_productos
-- Productos a pedido (dropshipping interno)
-- No manejamos stock, solo referencia a proveedor
-- ============================================
CREATE TABLE shop_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio_estimado DECIMAL(10,2) NOT NULL,
    proveedor_ref VARCHAR(100) NOT NULL COMMENT 'Nombre o código del proveedor',
    categoria VARCHAR(50),
    imagen_url VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_proveedor (proveedor_ref),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: shop_pedidos
-- Órdenes de compra con flujo de estados
-- ============================================
CREATE TABLE shop_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM(
        'pendiente_aprobacion',
        'pedido_a_proveedor', 
        'en_poder_repartidor', 
        'entregado',
        'cancelado'
    ) DEFAULT 'pendiente_aprobacion',
    total DECIMAL(10,2) DEFAULT 0.00,
    notas TEXT,
    direccion_entrega VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: shop_detalle_pedido
-- Relación N:M entre pedidos y productos
-- ============================================
CREATE TABLE shop_detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (pedido_id) REFERENCES shop_pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES shop_productos(id) ON DELETE RESTRICT,
    INDEX idx_pedido (pedido_id),
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB;

-- ============================================
-- DATOS INICIALES DE PRUEBA
-- ============================================

-- Usuario admin por defecto (password: admin123 - hasheado con password_hash)
INSERT INTO usuarios (email, password, nombre, telefono, rol) VALUES
('admin@almitaspeludas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', '1234567890', 'admin');

-- Cliente de prueba (password: cliente123)
INSERT INTO usuarios (email, password, nombre, telefono, direccion, barrio, rol) VALUES
('cliente@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cliente Test', '0987654321', 'Av. Siempre Viva 742', 'Centro', 'cliente');

-- Servicios de peluquería
INSERT INTO peluqueria_servicios (nombre, descripcion, duracion_minutos, precio) VALUES
('Baño y Secado', 'Baño completo con shampoo hipoalergénico y secado', 60, 2500.00),
('Corte Completo', 'Corte de pelo según raza y preferencias', 90, 4500.00),
('Baño + Corte', 'Servicio completo de baño y corte', 120, 6000.00),
('Corte de Uñas', 'Corte y limado de uñas', 20, 800.00),
('Limpieza de Oídos', 'Limpieza profunda de oídos', 15, 600.00);

-- Productos de ejemplo
INSERT INTO shop_productos (nombre, descripcion, precio_estimado, proveedor_ref, categoria) VALUES
('Alimento Premium 15kg', 'Alimento balanceado para perros adultos', 25000.00, 'Royal Canin', 'Alimentos'),
('Alimento Cachorro 8kg', 'Alimento especial para cachorros', 18000.00, 'Royal Canin', 'Alimentos'),
('Shampoo Antipulgas 500ml', 'Shampoo medicado antipulgas', 3500.00, 'Bayer', 'Higiene'),
('Collar Antipulgas', 'Collar antipulgas 6 meses de duración', 4200.00, 'Bayer', 'Accesorios'),
('Cama Ortopédica Grande', 'Cama para perros grandes con espuma memory', 15000.00, 'PetComfort', 'Accesorios');

-- ============================================
-- VISTA: Lista de compras pendientes por proveedor
-- ============================================
CREATE OR REPLACE VIEW v_lista_compras_proveedor AS
SELECT 
    sp.proveedor_ref AS proveedor,
    sp.nombre AS producto,
    SUM(sdp.cantidad) AS cantidad_total,
    GROUP_CONCAT(DISTINCT sped.id) AS pedidos_ids
FROM shop_pedidos sped
JOIN shop_detalle_pedido sdp ON sped.id = sdp.pedido_id
JOIN shop_productos sp ON sdp.producto_id = sp.id
WHERE sped.estado = 'pendiente_aprobacion'
GROUP BY sp.proveedor_ref, sp.id, sp.nombre
ORDER BY sp.proveedor_ref, sp.nombre;
