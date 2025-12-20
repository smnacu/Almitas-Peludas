<?php
/**
 * Almitas Peludas - Admin Dashboard
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Verificar autenticación
if (!isAdmin()) {
    redirect('/admin/login.php');
}

$user = $_SESSION['user'];

// Obtener estadísticas
try {
    $db = Database::getConnection();
    
    // Turnos pendientes
    $stmt = $db->query("SELECT COUNT(*) FROM peluqueria_turnos WHERE estado = 'pendiente'");
    $turnosPendientes = $stmt->fetchColumn();
    
    // Pedidos pendientes
    $stmt = $db->query("SELECT COUNT(*) FROM shop_pedidos WHERE estado = 'pendiente_aprobacion'");
    $pedidosPendientes = $stmt->fetchColumn();
    
    // Total pedidos hoy
    $stmt = $db->query("SELECT COUNT(*) FROM shop_pedidos WHERE DATE(fecha) = CURDATE()");
    $pedidosHoy = $stmt->fetchColumn();
    
    // Últimos turnos
    $stmt = $db->query("
        SELECT t.*, s.nombre as servicio_nombre, u.nombre as cliente_nombre
        FROM peluqueria_turnos t
        JOIN peluqueria_servicios s ON t.servicio_id = s.id
        JOIN usuarios u ON t.cliente_id = u.id
        ORDER BY t.fecha DESC, t.hora DESC
        LIMIT 5
    ");
    $ultimosTurnos = $stmt->fetchAll();
    
} catch (Exception $e) {
    $turnosPendientes = 0;
    $pedidosPendientes = 0;
    $pedidosHoy = 0;
    $ultimosTurnos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Almitas Peludas</title>
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
                <a href="/admin/turnos.php" class="nav-link">Turnos</a>
                <a href="/admin/pedidos.php" class="nav-link">Pedidos</a>
                <a href="/admin/lista_compras.php" class="nav-link">Lista de Compras</a>
                <a href="/admin/logout.php" class="nav-link" style="color: var(--error);">Salir</a>
            </nav>
        </div>
    </header>
    
    <main class="main">
        <section class="section">
            <div class="container">
                <h1 style="margin-bottom: 2rem;">
                    Hola, <span class="text-gradient"><?= e($user['nombre']) ?></span> 👋
                </h1>
                
                <!-- Stats Cards -->
                <div class="services-grid" style="margin-bottom: 3rem;">
                    <div class="card" style="text-align: center;">
                        <div style="font-size: 3rem;">📅</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                            <?= $turnosPendientes ?>
                        </div>
                        <div style="color: var(--text-secondary);">Turnos Pendientes</div>
                    </div>
                    
                    <div class="card" style="text-align: center;">
                        <div style="font-size: 3rem;">🛒</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--secondary);">
                            <?= $pedidosPendientes ?>
                        </div>
                        <div style="color: var(--text-secondary);">Pedidos por Aprobar</div>
                        <?php if ($pedidosPendientes > 0): ?>
                        <a href="/admin/lista_compras.php" class="btn btn-sm btn-primary mt-2">
                            Ver Lista de Compras
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card" style="text-align: center;">
                        <div style="font-size: 3rem;">🔐</div>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Seguridad</div>
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">Protégete con 2FA</div>
                        <a href="/admin/setup_2fa.php" class="btn btn-sm btn-secondary mt-2">
                            Configurar
                        </a>
                    </div>

                    <div class="card" style="text-align: center;">
                        <div style="font-size: 3rem;">📦</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--success);">
                            <?= $pedidosHoy ?>
                        </div>
                        <div style="color: var(--text-secondary);">Pedidos Hoy</div>
                    </div>
                </div>
                
                <!-- Últimos Turnos -->
                <h2 style="margin-bottom: 1rem;">Últimos Turnos</h2>
                <div class="card">
                    <?php if (empty($ultimosTurnos)): ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                        No hay turnos registrados
                    </p>
                    <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <th style="padding: 1rem; text-align: left;">Fecha</th>
                                    <th style="padding: 1rem; text-align: left;">Hora</th>
                                    <th style="padding: 1rem; text-align: left;">Cliente</th>
                                    <th style="padding: 1rem; text-align: left;">Servicio</th>
                                    <th style="padding: 1rem; text-align: left;">Barrio</th>
                                    <th style="padding: 1rem; text-align: left;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosTurnos as $turno): ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td style="padding: 1rem;"><?= date('d/m/Y', strtotime($turno['fecha'])) ?></td>
                                    <td style="padding: 1rem;"><?= date('H:i', strtotime($turno['hora'])) ?></td>
                                    <td style="padding: 1rem;"><?= e($turno['cliente_nombre']) ?></td>
                                    <td style="padding: 1rem;"><?= e($turno['servicio_nombre']) ?></td>
                                    <td style="padding: 1rem;"><?= e($turno['barrio']) ?></td>
                                    <td style="padding: 1rem;">
                                        <span style="padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.8rem;
                                            background: <?= $turno['estado'] === 'pendiente' ? 'var(--warning)' : 
                                                        ($turno['estado'] === 'completado' ? 'var(--success)' : 'var(--primary)') ?>20;
                                            color: <?= $turno['estado'] === 'pendiente' ? 'var(--warning)' : 
                                                   ($turno['estado'] === 'completado' ? 'var(--success)' : 'var(--primary)') ?>;">
                                            <?= ucfirst($turno['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
