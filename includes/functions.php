<?php
/**
 * Almitas Peludas - Funciones Globales
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Obtiene la URL base del sitio
 */
function getBaseUrl(): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim("$protocol://$host$path", '/');
}

/**
 * Escapa HTML para prevenir XSS
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige a otra página
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Obtiene todos los servicios de peluquería activos
 */
function getServicios(): array {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT * FROM peluqueria_servicios WHERE activo = 1 ORDER BY nombre");
    return $stmt->fetchAll();
}

/**
 * Obtiene todos los productos activos
 */
function getProductos(): array {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT * FROM shop_productos WHERE activo = 1 ORDER BY categoria, nombre");
    return $stmt->fetchAll();
}

/**
 * Obtiene las categorías de productos
 */
function getCategorias(): array {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT DISTINCT categoria FROM shop_productos WHERE activo = 1 ORDER BY categoria");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Formatea precio en pesos argentinos
 */
function formatPrecio(float $precio): string {
    return '$ ' . number_format($precio, 2, ',', '.');
}

/**
 * Mapeo de zonas por día de la semana
 */
function getZonasPorDia(): array {
    return [
        1 => ['zona' => 'Oeste', 'dia' => 'Lunes'],
        3 => ['zona' => 'Centro', 'dia' => 'Miércoles'],
        5 => ['zona' => 'Norte', 'dia' => 'Viernes'],
    ];
}

/**
 * Verifica si el usuario está logueado como admin
 */
function isAdmin(): bool {
    return isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin';
}

/**
 * Verifica si el usuario está logueado como cliente
 */
function isCliente(): bool {
    return isset($_SESSION['user']);
}

/**
 * Obtiene el usuario actual de la sesión
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Requiere autenticación de admin
 */
function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isAdmin()) {
        redirect('/admin/login.php');
    }
}

/**
 * Requiere autenticación de cliente (o admin)
 */
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isCliente()) {
        redirect('/login.php');
    }
}

