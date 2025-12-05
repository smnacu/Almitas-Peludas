<?php
/**
 * Almitas Peludas - API Pet Shop
 * Endpoint: Crear Orden
 * 
 * POST /api/shop/crear_orden.php
 * 
 * Crea una orden en estado 'pendiente_aprobacion'.
 * No valida stock (modelo dropshipping interno).
 * 
 * @package AlmitasPeludas
 */

require_once __DIR__ . '/../../config/database.php';

// Configurar CORS
setCorsHeaders();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido. Use POST.', 405);
}

// Obtener datos del request
$data = getJsonInput();

// Validar campos requeridos
if (empty($data['cliente_id'])) {
    jsonError("El campo 'cliente_id' es requerido.", 400);
}

if (empty($data['productos']) || !is_array($data['productos'])) {
    jsonError("El campo 'productos' es requerido y debe ser un array.", 400);
}

$clienteId = (int) $data['cliente_id'];
$productos = $data['productos'];
$notas = $data['notas'] ?? null;
$direccionEntrega = $data['direccion_entrega'] ?? null;

// Validar que hay al menos un producto
if (count($productos) === 0) {
    jsonError("Debe incluir al menos un producto en la orden.", 400);
}

try {
    $db = Database::getConnection();

    // Verificar que el cliente existe
    $stmt = $db->prepare("SELECT id, direccion FROM usuarios WHERE id = ? AND activo = 1");
    $stmt->execute([$clienteId]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        jsonError('Cliente no encontrado.', 404);
    }

    // Usar dirección del cliente si no se proporciona una
    if (empty($direccionEntrega)) {
        $direccionEntrega = $cliente['direccion'];
    }

    // Validar todos los productos y calcular total
    $productosValidos = [];
    $total = 0;

    foreach ($productos as $index => $item) {
        if (empty($item['producto_id']) || empty($item['cantidad'])) {
            jsonError("El producto en posición $index debe tener 'producto_id' y 'cantidad'.", 400);
        }

        $productoId = (int) $item['producto_id'];
        $cantidad = (int) $item['cantidad'];

        if ($cantidad <= 0) {
            jsonError("La cantidad del producto en posición $index debe ser mayor a 0.", 400);
        }

        // Verificar que el producto existe y está activo
        $stmt = $db->prepare("
            SELECT id, nombre, precio_estimado 
            FROM shop_productos 
            WHERE id = ? AND activo = 1
        ");
        $stmt->execute([$productoId]);
        $producto = $stmt->fetch();

        if (!$producto) {
            jsonError("El producto con ID $productoId no existe o no está disponible.", 404);
        }

        $subtotal = $producto['precio_estimado'] * $cantidad;
        $total += $subtotal;

        $productosValidos[] = [
            'producto_id' => $productoId,
            'nombre' => $producto['nombre'],
            'cantidad' => $cantidad,
            'precio_unitario' => $producto['precio_estimado'],
            'subtotal' => $subtotal
        ];
    }

    // ============================================
    // CREAR LA ORDEN (Transacción)
    // ============================================
    $db->beginTransaction();

    try {
        // Insertar el pedido
        $stmt = $db->prepare("
            INSERT INTO shop_pedidos 
            (cliente_id, estado, total, notas, direccion_entrega)
            VALUES (?, 'pendiente_aprobacion', ?, ?, ?)
        ");
        $stmt->execute([$clienteId, $total, $notas, $direccionEntrega]);
        $pedidoId = $db->lastInsertId();

        // Insertar los detalles del pedido
        $stmt = $db->prepare("
            INSERT INTO shop_detalle_pedido 
            (pedido_id, producto_id, cantidad, precio_unitario)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($productosValidos as $prod) {
            $stmt->execute([
                $pedidoId,
                $prod['producto_id'],
                $prod['cantidad'],
                $prod['precio_unitario']
            ]);
        }

        $db->commit();

        // Respuesta exitosa
        jsonResponse([
            'success' => true,
            'message' => 'Orden creada exitosamente. Pendiente de aprobación.',
            'data' => [
                'pedido_id' => (int) $pedidoId,
                'estado' => 'pendiente_aprobacion',
                'total' => number_format($total, 2, '.', ''),
                'productos' => $productosValidos,
                'direccion_entrega' => $direccionEntrega
            ]
        ], 201);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("Error al crear orden: " . $e->getMessage());
    jsonError('Error interno del servidor al procesar la orden.', 500);
}
