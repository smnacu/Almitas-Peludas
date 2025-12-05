<?php
/**
 * Almitas Peludas - Carrito de Compras
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Carrito';

include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Tu <span class="text-gradient">Carrito</span></h1>
        
        <div id="alert-container"></div>
        
        <div class="grid-2">
            <!-- Lista de Productos -->
            <div>
                <div id="cart-items">
                    <!-- Se llena con JavaScript -->
                </div>
                
                <div id="cart-empty" style="display: none; text-align: center; padding: 3rem;">
                    <p style="font-size: 4rem;">🛒</p>
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">Tu carrito está vacío</p>
                    <a href="/tienda.php" class="btn btn-primary">Ir a la Tienda</a>
                </div>
            </div>
            
            <!-- Resumen -->
            <div>
                <div class="cart-summary" id="cart-summary">
                    <h3 style="margin-bottom: 1rem;">Resumen del Pedido</h3>
                    
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--text-secondary);">Productos:</span>
                            <span id="summary-count">0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-secondary);">Subtotal:</span>
                            <span id="summary-subtotal">$ 0</span>
                        </div>
                    </div>
                    
                    <div class="cart-total">
                        <span>Total:</span>
                        <span id="summary-total" style="color: var(--secondary);">$ 0</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📍 Dirección de entrega</label>
                        <input type="text" id="direccion-entrega" class="form-control" 
                               placeholder="Ej: Av. San Martín 1234">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📝 Notas</label>
                        <textarea id="notas-pedido" class="form-control" rows="2" 
                                  placeholder="Indicaciones especiales"></textarea>
                    </div>
                    
                    <button onclick="checkout()" class="btn btn-primary" style="width: 100%;">
                        ✅ Confirmar Pedido
                    </button>
                    
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem; text-align: center;">
                        💬 Te contactaremos por WhatsApp para coordinar el pago y la entrega.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', renderCart);

function renderCart() {
    const items = Cart.getItems();
    const container = document.getElementById('cart-items');
    const empty = document.getElementById('cart-empty');
    const summary = document.getElementById('cart-summary');
    
    if (items.length === 0) {
        container.style.display = 'none';
        summary.style.display = 'none';
        empty.style.display = 'block';
        return;
    }
    
    container.style.display = 'block';
    summary.style.display = 'block';
    empty.style.display = 'none';
    
    container.innerHTML = items.map(item => `
        <div class="cart-item">
            <div class="cart-item-image">📦</div>
            <div class="cart-item-info">
                <div class="cart-item-name">${item.nombre}</div>
                <div class="cart-item-price">${formatPrice(item.precio)}</div>
            </div>
            <div class="cart-item-qty">
                <button class="qty-btn" onclick="updateQty(${item.id}, ${item.cantidad - 1})">-</button>
                <span>${item.cantidad}</span>
                <button class="qty-btn" onclick="updateQty(${item.id}, ${item.cantidad + 1})">+</button>
            </div>
            <button onclick="removeFromCart(${item.id})" style="background: none; border: none; color: var(--error); cursor: pointer; font-size: 1.2rem;">
                🗑️
            </button>
        </div>
    `).join('');
    
    // Actualizar resumen
    document.getElementById('summary-count').textContent = Cart.getCount();
    document.getElementById('summary-subtotal').textContent = formatPrice(Cart.getTotal());
    document.getElementById('summary-total').textContent = formatPrice(Cart.getTotal());
}

function updateQty(id, qty) {
    if (qty < 1) {
        removeFromCart(id);
        return;
    }
    Cart.updateQuantity(id, qty);
    renderCart();
}

function removeFromCart(id) {
    Cart.removeItem(id);
    renderCart();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
