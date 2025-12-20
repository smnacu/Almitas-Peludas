<?php
/**
 * Almitas Peludas - Admin Pedidos
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Verificar autenticación
requireAdmin();

$user = $_SESSION['user'];
$filtroEstado = $_GET['estado'] ?? '';

// Construir query recuperando resumen de productos
$sql = "
    SELECT p.*, u.nombre as cliente_nombre, u.email, u.telefono, u.direccion as direccion_cliente,
           GROUP_CONCAT(CONCAT(sp.nombre, ' (x', sdp.cantidad, ')') SEPARATOR ', ') as productos_resumen
    FROM shop_pedidos p
    JOIN usuarios u ON p.cliente_id = u.id
    JOIN shop_detalle_pedido sdp ON p.id = sdp.pedido_id
    JOIN shop_productos sp ON sdp.producto_id = sp.id
";

$params = [];
if ($filtroEstado) {
    $sql .= " WHERE p.estado = ?";
    $params[] = $filtroEstado;
}

$sql .= " GROUP BY p.id ORDER BY p.fecha DESC";

try {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error al cargar pedidos: " . $e->getMessage();
    $pedidos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        .admin-table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); vertical-align: top; }
        th { color: var(--text-secondary); font-weight: 500; font-size: 0.9rem; }
        .btn-action { padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem; margin-bottom: 0.25rem; }
        .product-list { font-size: 0.9rem; color: var(--text-secondary); }
    </style>
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
                <a href="/admin/turnos.php" class="nav-link">Turnos</a>
                <a href="/admin/pedidos.php" class="nav-link" style="color: var(--primary);">Pedidos</a>
                <a href="/admin/lista_compras.php" class="nav-link">Lista de Compras</a>
                <a href="/admin/logout.php" class="nav-link" style="color: var(--error);">Salir</a>
            </nav>
        </div>
    </header>
    
    <main class="main">
        <section class="section">
            <div class="container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h1 class="section-title" style="margin-bottom: 0;">Gestión de Pedidos</h1>
                    
                    <form method="GET" style="display: flex; gap: 0.5rem;">
                        <select name="estado" class="form-control" onchange="this.form.submit()" style="padding: 0.5rem; width: auto;">
                            <option value="">Todos los estados</option>
                            <option value="pendiente_aprobacion" <?= $filtroEstado === 'pendiente_aprobacion' ? 'selected' : '' ?>>Pendiente Aprobación</option>
                            <option value="pedido_a_proveedor" <?= $filtroEstado === 'pedido_a_proveedor' ? 'selected' : '' ?>>Pedido a Proveedor</option>
                            <option value="en_poder_repartidor" <?= $filtroEstado === 'en_poder_repartidor' ? 'selected' : '' ?>>En Reparto</option>
                            <option value="entregado" <?= $filtroEstado === 'entregado' ? 'selected' : '' ?>>Entregado</option>
                            <option value="cancelado" <?= $filtroEstado === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </form>
                </div>
                
                <div class="card admin-table-container">
                    <?php if (empty($pedidos)): ?>
                    <p class="text-center" style="padding: 2rem; color: var(--text-muted);">No se encontraron pedidos. <?= isset($error) ? e($error) : '' ?></p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha/ID</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Entrega</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $p): ?>
                            <tr>
                                <td>
                                    #<?= $p['id'] ?><br>
                                    <small style="color: var(--text-secondary);"><?= date('d/m/Y', strtotime($p['fecha'])) ?></small>
                                </td>
                                <td>
                                    <?= e($p['cliente_nombre']) ?><br>
                                    <small style="color: var(--text-secondary);"><?= e($p['telefono']) ?></small>
                                </td>
                                <td style="max-width: 300px;">
                                    <div class="product-list">
                                        <?= e($p['productos_resumen']) ?>
                                    </div>
                                    <?php if ($p['notas']): ?>
                                    <small style="display: block; margin-top: 0.5rem; color: var(--warning);">
                                        📝 <?= e($p['notas']) ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= e($p['direccion_entrega'] ?: $p['direccion_cliente']) ?>
                                </td>
                                <td>
                                    <?= formatPrecio($p['total']) ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $p['estado'] ?>">
                                        <?= str_replace('_', ' ', ucfirst($p['estado'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($p['estado'] === 'pendiente_aprobacion'): ?>
                                        <button onclick="updateEstado(<?= $p['id'] ?>, 'pedido_a_proveedor')" class="btn btn-sm btn-primary btn-action">Aprobar</button>
                                        <button onclick="updateEstado(<?= $p['id'] ?>, 'cancelado')" class="btn btn-sm btn-secondary btn-action" style="color: var(--error);">Cancelar</button>
                                    <?php elseif ($p['estado'] === 'pedido_a_proveedor'): ?>
                                        <button onclick="updateEstado(<?= $p['id'] ?>, 'en_poder_repartidor')" class="btn btn-sm btn-primary btn-action">En Reparto</button>
                                    <?php elseif ($p['estado'] === 'en_poder_repartidor'): ?>
                                        <button onclick="updateEstado(<?= $p['id'] ?>, 'entregado')" class="btn btn-sm btn-success btn-action" style="background: var(--success);">Entregado</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <script>
    async function updateEstado(id, nuevoEstado) {
        if (!confirm(`¿Cambiar estado del pedido #${id}?`)) return;

        try {
            const response = await fetch('/api/admin/actualizar_estado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    tipo: 'pedido',
                    id: id,
                    estado: nuevoEstado
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.message || 'Error al actualizar');
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
        }
    }
    </script>
</body>
</html>
