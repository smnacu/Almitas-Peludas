<?php
/**
 * API Upload de Imágenes
 * Retorna JSON { success: true, url: '/assets/uploads/xyz.jpg' }
 */
session_start();
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Solo usuarios logueados pueden subir archivos
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir archivo']);
    exit;
}

$file = $_FILES['imagen'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validaciones
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Formato no soportado (Solo JPG, PNG, WEBP)']);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'El archivo supera los 5MB']);
    exit;
}

// Crear directorio si no existe
$uploadDir = __DIR__ . '/../../assets/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generar nombre único
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_') . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode([
        'success' => true,
        'url' => '/assets/uploads/' . $filename,
        'message' => 'Imagen subida correctamente'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar archivo en el servidor']);
}
