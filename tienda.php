<?php
/**
 * Almitas Peludas - Tienda Pet Shop
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tienda';
$productos = getProductos();
$categorias = getCategorias();

include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Pet Shop <span class="text-gradient">a Pedido</span></h1>
        
        <p class="text-center mb-3" style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 2rem;">
            🛒 Modalidad "a pedido": Elegí los productos que necesitás, nosotros los conseguimos 
            con nuestros proveedores y te los llevamos a casa.
        </p>
        
        <!-- Filtros -->
        <div class="text-center mb-3">
            <button class="btn btn-sm btn-secondary filter-btn active" data-category="all">
                Todos
            </button>
            <?php foreach ($categorias as $cat): ?>
            <button class="btn btn-sm btn-secondary filter-btn" data-category="<?= e($cat) ?>">
                <?= e($cat) ?>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Grid de Productos -->
        <div class="products-grid" id="products-grid">
            <?php foreach ($productos as $prod): 
                // Iconos por categoría
                $iconos = [
                    'Alimentos' => '🍖',
                    'Higiene' => '🧴',
                    'Accesorios' => '🎾',
                ];
                $icono = $iconos[$prod['categoria']] ?? '📦';
            ?>
            <div class="card product-card" data-category="<?= e($prod['categoria']) ?>">
                <div class="product-image">
                    <?= $icono ?>
                </div>
                <div class="product-info">
                    <span class="product-category"><?= e($prod['categoria']) ?></span>
                    <h3 class="product-name"><?= e($prod['nombre']) ?></h3>
                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem;">
                        <?= e($prod['descripcion'] ?? '') ?>
                    </p>
                    <div class="product-price"><?= formatPrecio($prod['precio_estimado']) ?></div>
                    <button class="btn btn-primary btn-sm" style="width: 100%;" 
                            onclick="addToCart(<?= $prod['id'] ?>, '<?= e($prod['nombre']) ?>', <?= $prod['precio_estimado'] ?>)">
                        🛒 Agregar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($productos)): ?>
        <div class="text-center" style="padding: 3rem;">
            <p style="font-size: 3rem;">📦</p>
            <p style="color: var(--text-secondary);">Próximamente agregaremos productos</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Filtro de productos
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Actualizar botón activo
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        btn.style.background = 'var(--primary)';
        document.querySelectorAll('.filter-btn:not(.active)').forEach(b => {
            b.style.background = '';
        });
        
        const category = btn.dataset.category;
        
        document.querySelectorAll('.product-card').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
