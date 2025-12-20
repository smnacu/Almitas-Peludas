<?php
/**
 * Almitas Peludas - Landing Page
 */
require_once __DIR__ . '/includes/functions.php';

$config = require __DIR__ . '/config/app.php';
$pageTitle = 'Estética y Salud Animal a Domicilio';
$servicios = getServicios();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Tu <span class="text-gradient">Compañero</span> merece lo mejor</h1>
        <p class="hero-subtitle">Estética y salud animal en la comodidad de tu hogar. Tratamos a cada almita con el respeto y amor que es parte de tu familia.</p>
        <div class="hero-buttons">
            <a href="agendar.php" class="btn btn-primary">Reservar Turno</a>
            <a href="tienda.php" class="btn btn-secondary">Tienda</a>
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
            📱 ¿Tenés dudas sobre tu zona? <a href="https://wa.me/<?= $config['whatsapp'] ?>" target="_blank" style="color: var(--primary-light);">Consultanos por WhatsApp</a>
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
