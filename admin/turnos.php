<?php
/**
 * Almitas Peludas - Admin Turnos
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Verificar autenticación
requireAdmin();

$user = $_SESSION['user'];
$filtroEstado = $_GET['estado'] ?? '';

// Construir query
$sql = "
    SELECT t.*, s.nombre as servicio_nombre, u.nombre as cliente_nombre, u.telefono, u.email
    FROM peluqueria_turnos t
    JOIN peluqueria_servicios s ON t.servicio_id = s.id
    JOIN usuarios u ON t.cliente_id = u.id
";

$params = [];
if ($filtroEstado) {
    $sql .= " WHERE t.estado = ?";
    $params[] = $filtroEstado;
}

$sql .= " ORDER BY t.fecha DESC, t.hora DESC";

try {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $turnos = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error al cargar turnos";
    $turnos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        .admin-table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); }
        th { color: var(--text-secondary); font-weight: 500; font-size: 0.9rem; }
        .btn-action { padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem; }
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
                <a href="/admin/turnos.php" class="nav-link" style="color: var(--primary);">Turnos</a>
                <a href="/admin/pedidos.php" class="nav-link">Pedidos</a>
                <a href="/admin/lista_compras.php" class="nav-link">Lista de Compras</a>
                <a href="/admin/logout.php" class="nav-link" style="color: var(--error);">Salir</a>
            </nav>
        </div>
    </header>
    
    <main class="main">
        <section class="section">
            <div class="container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h1 class="section-title" style="margin-bottom: 0;">Gestión de Turnos</h1>
                    
                    <!-- Filtros -->
                    <form method="GET" style="display: flex; gap: 0.5rem;">
                        <select name="estado" class="form-control" onchange="this.form.submit()" style="padding: 0.5rem; width: auto;">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" <?= $filtroEstado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="confirmado" <?= $filtroEstado === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                            <option value="completado" <?= $filtroEstado === 'completado' ? 'selected' : '' ?>>Completado</option>
                            <option value="cancelado" <?= $filtroEstado === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </form>
                </div>
                
                <div id="alert-container"></div>

                <div class="card admin-table-container">
                    <?php if (empty($turnos)): ?>
                    <p class="text-center" style="color: var(--text-muted);">No se encontraron turnos.</p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Zona</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turnos as $t): ?>
                            <tr id="row-<?= $t['id'] ?>">
                                <td>
                                    <?= date('d/m/Y', strtotime($t['fecha'])) ?><br>
                                    <small style="color: var(--text-secondary);"><?= date('H:i', strtotime($t['hora'])) ?> hs</small>
                                </td>
                                <td>
                                    <?= e($t['cliente_nombre']) ?><br>
                                    <small style="color: var(--text-secondary);"><?= e($t['telefono']) ?></small>
                                </td>
                                <td><?= e($t['servicio_nombre']) ?></td>
                                <td>
                                    <?= e($t['barrio']) ?><br>
                                    <small style="color: var(--text-muted); font-size: 0.8rem;"><?= e($t['direccion']) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= strtolower(str_replace(' ', '_', $t['estado'])) ?>">
                                        <?= ucfirst($t['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($t['estado'] === 'pendiente'): ?>
                                        <button onclick="updateEstado(<?= $t['id'] ?>, 'confirmado')" class="btn btn-sm btn-primary btn-action">✓</button>
                                        <button onclick="updateEstado(<?= $t['id'] ?>, 'cancelado')" class="btn btn-sm btn-secondary btn-action" style="color: var(--error);">✗</button>
                                    <?php elseif ($t['estado'] === 'confirmado'): ?>
                                        <button onclick="updateEstado(<?= $t['id'] ?>, 'completado')" class="btn btn-sm btn-primary btn-action">★</button>
                                        <button onclick="updateEstado(<?= $t['id'] ?>, 'cancelado')" class="btn btn-sm btn-secondary btn-action">✗</button>
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
        if (!confirm(`¿Cambiar estado a ${nuevoEstado}?`)) return;

        try {
            const response = await fetch('/api/admin/actualizar_estado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    tipo: 'turno',
                    id: id,
                    estado: nuevoEstado
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Recargar para ver cambios (MVP simple)
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
