<?php
/**
 * Script de migración: Crear tabla almitas
 */
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS almitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        tipo VARCHAR(50) NOT NULL COMMENT 'Perro, Gato, Otro',
        fecha_nacimiento DATE NULL,
        notas_cuidados TEXT NULL,
        foto_url VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        video_presentacion VARCHAR(255) NULL, 
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    echo "Tabla 'almitas' creada correctamente.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
