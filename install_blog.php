<?php
/**
 * Script de migración: Crear tabla blog_posts
 */
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getConnection();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        contenido TEXT NOT NULL,
        imagen_url VARCHAR(255) NULL,
        autor VARCHAR(100) DEFAULT 'Admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    echo "Tabla 'blog_posts' creada correctamente.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
