<?php
/**
 * Almitas Peludas - API Admin Dashboard
 * Endpoint: Lista de Compras por Proveedor
 * 
 * GET /api/admin/lista_compras.php
 * 
 * Devuelve todos los productos de órdenes en estado 'pendiente_aprobacion'
 * agrupados por proveedor, para saber qué pedir.
 * 
 * @package AlmitasPeludas
 */

require_once __DIR__ . '/../../config/database.php';

// Configurar CORS
setCorsHeaders();

// Solo aceptar GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Método no permitido. Use GET.', 405);
}

try {
    $db = Database::getConnection();

    // Consulta para obtener productos agrupados por proveedor
    // de órdenes pendientes de aprobación
    $stmt = $db->prepare("
        SELECT 
            sp.proveedor_ref AS proveedor,
            sp.id AS producto_id,
            sp.nombre AS producto_nombre,
            sp.precio_estimado,
            SUM(sdp.cantidad) AS cantidad_total,
            COUNT(DISTINCT sped.id) AS cantidad_pedidos,
            GROUP_CONCAT(DISTINCT sped.id ORDER BY sped.id ASC) AS pedidos_ids
        FROM shop_pedidos sped
        INNER JOIN shop_detalle_pedido sdp ON sped.id = sdp.pedido_id
        INNER JOIN shop_productos sp ON sdp.producto_id = sp.id
        WHERE sped.estado = 'pendiente_aprobacion'
        GROUP BY sp.proveedor_ref, sp.id, sp.nombre, sp.precio_estimado
        ORDER BY sp.proveedor_ref ASC, sp.nombre ASC
    ");
    $stmt->execute();
    $resultados = $stmt->fetchAll();

    // Agrupar por proveedor para la respuesta
    $proveedores = [];
    foreach ($resultados as $row) {
        $proveedorKey = $row['proveedor'];
        
        if (!isset($proveedores[$proveedorKey])) {
            $proveedores[$proveedorKey] = [
                'proveedor' => $proveedorKey,
                'productos' => [],
                'total_estimado' => 0
            ];
        }

        $subtotalProducto = $row['precio_estimado'] * $row['cantidad_total'];
        
        $proveedores[$proveedorKey]['productos'][] = [
            'producto_id' => (int) $row['producto_id'],
            'nombre' => $row['producto_nombre'],
            'cantidad_total' => (int) $row['cantidad_total'],
            'precio_unitario' => number_format($row['precio_estimado'], 2, '.', ''),
            'subtotal_estimado' => number_format($subtotalProducto, 2, '.', ''),
            'en_pedidos' => array_map('intval', explode(',', $row['pedidos_ids']))
        ];

        $proveedores[$proveedorKey]['total_estimado'] += $subtotalProducto;
    }

    // Formatear totales
    foreach ($proveedores as &$prov) {
        $prov['total_estimado'] = number_format($prov['total_estimado'], 2, '.', '');
    }

    // Obtener también estadísticas generales
    $stmtStats = $db->prepare("
        SELECT 
            COUNT(DISTINCT id) as total_pedidos_pendientes,
            COALESCE(SUM(total), 0) as monto_total_pendiente
        FROM shop_pedidos 
        WHERE estado = 'pendiente_aprobacion'
    ");
    $stmtStats->execute();
    $stats = $stmtStats->fetch();

    // Respuesta
    jsonResponse([
        'success' => true,
        'resumen' => [
            'pedidos_pendientes' => (int) $stats['total_pedidos_pendientes'],
            'monto_total' => number_format($stats['monto_total_pendiente'], 2, '.', ''),
            'proveedores_involucrados' => count($proveedores)
        ],
        'lista_compras' => array_values($proveedores)
    ]);

} catch (PDOException $e) {
    error_log("Error en lista de compras: " . $e->getMessage());
    jsonError('Error interno del servidor al obtener la lista de compras.', 500);
}
