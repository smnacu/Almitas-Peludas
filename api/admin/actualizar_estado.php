<?php
/**
 * Almitas Peludas - API Admin
 * Endpoint: Actualizar Estado
 * 
 * POST /api/admin/actualizar_estado.php
 */

require_once __DIR__ . '/../../includes/functions.php';

// Configurar CORS
setCorsHeaders();

// Verificar auth admin
requireAdmin();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido.', 405);
}

$data = getJsonInput();

if (empty($data['tipo']) || empty($data['id']) || empty($data['estado'])) {
    jsonError('Faltan parámetros requeridos (tipo, id, estado).', 400);
}

$tipo = $data['tipo']; // 'turno' o 'pedido'
$id = (int) $data['id'];
$estado = $data['estado'];

// Validar tipos y estados permitidos
$validos = [
    'turno' => ['pendiente', 'confirmado', 'en_camino', 'completado', 'cancelado'],
    'pedido' => ['pendiente_aprobacion', 'pedido_a_proveedor', 'en_poder_repartidor', 'entregado', 'cancelado']
];

if (!isset($validos[$tipo])) {
    jsonError('Tipo inválido.', 400);
}

if (!in_array($estado, $validos[$tipo])) {
    jsonError('Estado inválido para este tipo.', 400);
}

try {
    $db = Database::getConnection();
    $tabla = $tipo === 'turno' ? 'peluqueria_turnos' : 'shop_pedidos';
    
    $stmt = $db->prepare("UPDATE $tabla SET estado = ? WHERE id = ?");
    $result = $stmt->execute([$estado, $id]);
    
    if ($stmt->rowCount() > 0) {
        jsonResponse([
            'success' => true, 
            'message' => 'Estado actualizado correctamente'
        ]);
    } else {
        // Puede que el ID no exista o el estado sea el mismo
        jsonResponse([
            'success' => true, 
            'message' => 'Estado actualizado (sin cambios)'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error actualizando estado admin: " . $e->getMessage());
    jsonError('Error de base de datos.', 500);
}
