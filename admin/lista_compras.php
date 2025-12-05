<?php
/**
 * Almitas Peludas - Admin Lista de Compras
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Verificar autenticación
if (!isAdmin()) {
    redirect('/admin/login.php');
}

// Obtener lista de compras
try {
    $db = Database::getConnection();
    
    $stmt = $db->query("
        SELECT 
            sp.proveedor_ref AS proveedor,
            sp.id AS producto_id,
            sp.nombre AS producto_nombre,
            sp.precio_estimado,
            SUM(sdp.cantidad) AS cantidad_total,
            COUNT(DISTINCT sped.id) AS cantidad_pedidos
        FROM shop_pedidos sped
        INNER JOIN shop_detalle_pedido sdp ON sped.id = sdp.pedido_id
        INNER JOIN shop_productos sp ON sdp.producto_id = sp.id
        WHERE sped.estado = 'pendiente_aprobacion'
        GROUP BY sp.proveedor_ref, sp.id, sp.nombre, sp.precio_estimado
        ORDER BY sp.proveedor_ref ASC, sp.nombre ASC
    ");
    $resultados = $stmt->fetchAll();
    
    // Agrupar por proveedor
    $proveedores = [];
    foreach ($resultados as $row) {
        $prov = $row['proveedor'];
        if (!isset($proveedores[$prov])) {
            $proveedores[$prov] = ['productos' => [], 'total' => 0];
        }
        $subtotal = $row['precio_estimado'] * $row['cantidad_total'];
        $proveedores[$prov]['productos'][] = $row;
        $proveedores[$prov]['total'] += $subtotal;
    }
    
    // Stats
    $stmt = $db->query("
        SELECT COUNT(DISTINCT id) as total, COALESCE(SUM(total), 0) as monto
        FROM shop_pedidos WHERE estado = 'pendiente_aprobacion'
    ");
    $stats = $stmt->fetch();
    
} catch (Exception $e) {
    $proveedores = [];
    $stats = ['total' => 0, 'monto' => 0];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Compras | Almitas Peludas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="/admin/" class="logo">
                <span class="logo-icon">🐾</span>
                <span class="logo-text">Admin Panel</span>
            </a>
            <nav class="nav">
                <a href="/admin/" class="nav-link">Dashboard</a>
                <a href="/admin/lista_compras.php" class="nav-link">Lista de Compras</a>
                <a href="/" class="nav-link" target="_blank">Ver Sitio</a>
                <a href="/admin/logout.php" class="nav-link" style="color: var(--error);">Salir</a>
            </nav>
        </div>
    </header>
    
    <main class="main">
        <section class="section">
            <div class="container">
                <h1 style="margin-bottom: 0.5rem;">
                    Lista de <span class="text-gradient">Compras</span> 🛒
                </h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Productos a pedir a proveedores (pedidos en estado "pendiente_aprobacion")
                </p>
                
                <!-- Resumen -->
                <div class="card" style="margin-bottom: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
                    <div>
                        <span style="color: var(--text-secondary);">Pedidos pendientes:</span>
                        <strong style="margin-left: 0.5rem; font-size: 1.25rem;"><?= $stats['total'] ?></strong>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary);">Monto total:</span>
                        <strong style="margin-left: 0.5rem; font-size: 1.25rem; color: var(--secondary);">
                            <?= formatPrecio($stats['monto']) ?>
                        </strong>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary);">Proveedores:</span>
                        <strong style="margin-left: 0.5rem; font-size: 1.25rem;"><?= count($proveedores) ?></strong>
                    </div>
                </div>
                
                <?php if (empty($proveedores)): ?>
                <div class="card text-center" style="padding: 3rem;">
                    <p style="font-size: 4rem;">✅</p>
                    <p style="color: var(--text-secondary);">No hay pedidos pendientes de aprobación</p>
                </div>
                <?php else: ?>
                
                <!-- Lista por Proveedor -->
                <?php foreach ($proveedores as $nombre => $data): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <h2 style="margin: 0;">
                            <span style="color: var(--primary-light);">📦</span> <?= e($nombre) ?>
                        </h2>
                        <span style="color: var(--secondary); font-weight: 700;">
                            Total: <?= formatPrecio($data['total']) ?>
                        </span>
                    </div>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <th style="padding: 0.75rem; text-align: left;">Producto</th>
                                <th style="padding: 0.75rem; text-align: center;">Cantidad</th>
                                <th style="padding: 0.75rem; text-align: right;">Precio Unit.</th>
                                <th style="padding: 0.75rem; text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['productos'] as $prod): 
                                $subtotal = $prod['precio_estimado'] * $prod['cantidad_total'];
                            ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 0.75rem;"><?= e($prod['producto_nombre']) ?></td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <span style="background: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px;">
                                        <?= $prod['cantidad_total'] ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: right; color: var(--text-secondary);">
                                    <?= formatPrecio($prod['precio_estimado']) ?>
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-weight: 600;">
                                    <?= formatPrecio($subtotal) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endforeach; ?>
                
                <!-- Botón para imprimir -->
                <div class="text-center mt-3">
                    <button onclick="window.print()" class="btn btn-secondary">
                        🖨️ Imprimir Lista
                    </button>
                </div>
                
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <style>
        @media print {
            .header, .nav, button { display: none !important; }
            body { background: white; color: black; }
            .card { border: 1px solid #ddd; background: white; }
        }
    </style>
</body>
</html>
