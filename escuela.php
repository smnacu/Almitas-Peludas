<?php
/**
 * Almitas Peludas - Escuela de Cuidado y Derechos
 */
require_once __DIR__ . '/includes/functions.php';
$config = require __DIR__ . '/config/app.php';
$pageTitle = 'Escuela de Cuidado';

include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="section-title">Escuela de <span class="text-gradient">Cuidado</span></h1>
            <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
                Creemos que el amor se demuestra con respeto y conocimiento. Aquí encontrarás guías para garantizar el bienestar de tu compañero.
            </p>
        </div>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            
            <?php
            // Obtener artículos dinámicos
            $articulos = [];
            try {
                $db = Database::getConnection();
                $stmt = $db->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
                $articulos = $stmt->fetchAll();
            } catch (Exception $e) {
                // Si falla (tabla no existe), usaremos contenido default vacío o mostraremos error controlado
            }
            ?>

            <?php if (!empty($articulos)): ?>
                <?php foreach ($articulos as $post): ?>
                <article class="card h-100" style="display: flex; flex-direction: column;">
                    <?php if ($post['imagen_url']): ?>
                        <div style="height: 200px; overflow: hidden; margin: -2rem -2rem 1.5rem -2rem; border-radius: var(--radius) var(--radius) 0 0;">
                            <img src="<?= e($post['imagen_url']) ?>" alt="<?= e($post['titulo']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <h3><?= e($post['titulo']) ?></h3>
                    <div style="flex-grow: 1; color: var(--text-secondary); line-height: 1.6;">
                        <?= nl2br(e(substr($post['contenido'], 0, 150))) ?>...
                    </div>
                    <!-- Leer más (futura expansión) -->
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Contenido por Defecto (Fallback si no hay posts DB) -->
                <div class="card" style="border-left: 4px solid var(--primary);">
                    <h3>📜 Derechos de los Animales</h3>
                    <ul style="margin-top: 1rem; padding-left: 1.2rem; line-height: 1.6;">
                        <li>Derecho a una alimentación adecuada.</li>
                        <li>Derecho a la atención sanitaria.</li>
                        <li>Derecho a expresar su comportamiento natural.</li>
                    </ul>
                </div>
                <div class="card">
                     <div style="font-size: 2rem; mb-2">✂️</div>
                     <h3>El Cepillado es Salud</h3>
                     <p>Activa la circulación y fortalece el vínculo con tu compañero.</p>
                </div>
            <?php endif; ?>

        </div>
        
        <div class="card mt-4 text-center" style="background: rgba(255,255,255,0.05);">
            <h3>¿Tenés dudas específicas?</h3>
            <p style="margin-bottom: 1rem;">Escribinos, nos encanta ayudar a familias multiespecie.</p>
            <a href="https://wa.me/<?= $config['whatsapp'] ?>" class="btn btn-primary">Consultar al Experto</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
