<?php
/**
 * Almitas Peludas - Landing Page
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Peluquería Canina a Domicilio';
$servicios = getServicios();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">
            Tu mascota, <span class="highlight">hermosa</span><br>
            en la comodidad de tu hogar 🐶
        </h1>
        <p class="hero-subtitle">
            Servicio de peluquería canina a domicilio. Atendemos por zonas: 
            Oeste (Lun) • Centro (Mié) • Norte (Vie)
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="/agendar.php" class="btn btn-primary">📅 Agendar Turno</a>
            <a href="/tienda.php" class="btn btn-secondary">🛒 Ver Tienda</a>
        </div>
    </div>
</section>

<!-- Servicios -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Nuestros <span class="text-gradient">Servicios</span></h2>
        
        <div class="services-grid">
            <?php foreach ($servicios as $servicio): ?>
            <div class="card service-card">
                <div class="service-icon">
                    <?php
                    // Iconos según servicio
                    $iconos = [
                        'baño' => '🛁',
                        'corte' => '✂️',
                        'uñas' => '💅',
                        'oídos' => '👂',
                    ];
                    $icono = '🐕';
                    foreach ($iconos as $keyword => $emoji) {
                        if (stripos($servicio['nombre'], $keyword) !== false) {
                            $icono = $emoji;
                            break;
                        }
                    }
                    echo $icono;
                    ?>
                </div>
                <h3 class="service-name"><?= e($servicio['nombre']) ?></h3>
                <p class="service-description"><?= e($servicio['descripcion'] ?? '') ?></p>
                <div class="service-price"><?= formatPrecio($servicio['precio']) ?></div>
                <p class="text-muted" style="font-size: 0.85rem; margin-top: 0.5rem;">
                    ⏱️ <?= $servicio['duracion_minutos'] ?> minutos
                </p>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="/agendar.php" class="btn btn-primary">Agendar Ahora</a>
        </div>
    </div>
</section>

<!-- Zonas de Atención -->
<section class="section" style="background: var(--bg-card);">
    <div class="container">
        <h2 class="section-title">Zonas de <span class="text-gradient">Atención</span></h2>
        
        <div class="zonas-info">
            <div class="zona-card">
                <div class="zona-dia">🗓️ Lunes</div>
                <div class="zona-nombre">Zona Oeste</div>
            </div>
            <div class="zona-card">
                <div class="zona-dia">🗓️ Miércoles</div>
                <div class="zona-nombre">Zona Centro</div>
            </div>
            <div class="zona-card">
                <div class="zona-dia">🗓️ Viernes</div>
                <div class="zona-nombre">Zona Norte</div>
            </div>
        </div>
        
        <p class="text-center" style="color: var(--text-secondary);">
            📱 ¿Tenés dudas sobre tu zona? <a href="https://wa.me/5491112345678" style="color: var(--primary-light);">Consultanos por WhatsApp</a>
        </p>
    </div>
</section>

<!-- Pet Shop Preview -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Pet Shop <span class="text-gradient">a Pedido</span></h2>
        <p class="text-center mb-3" style="color: var(--text-secondary);">
            Productos de calidad para tu mascota. Hacemos el pedido por vos y te lo llevamos a casa.
        </p>
        <div class="text-center">
            <a href="/tienda.php" class="btn btn-primary">Ver Productos</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
