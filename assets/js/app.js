/**
 * Almitas Peludas - JavaScript Principal
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar componentes
    initNavToggle();
    initCart();
    initAnimations();
});

// ============================================
// NAVEGACIÓN MÓVIL
// ============================================
function initNavToggle() {
    const toggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.nav');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('active');
            toggle.classList.toggle('active');
        });
    }
}

// ============================================
// CARRITO DE COMPRAS (LocalStorage)
// ============================================
const Cart = {
    key: 'almitas_cart',

    getItems() {
        const data = localStorage.getItem(this.key);
        return data ? JSON.parse(data) : [];
    },

    saveItems(items) {
        localStorage.setItem(this.key, JSON.stringify(items));
        this.updateCounter();
    },

    addItem(product) {
        const items = this.getItems();
        const existing = items.find(item => item.id === product.id);

        if (existing) {
            existing.cantidad++;
        } else {
            items.push({ ...product, cantidad: 1 });
        }

        this.saveItems(items);
        this.showNotification(`${product.nombre} agregado al carrito`);
    },

    removeItem(productId) {
        let items = this.getItems();
        items = items.filter(item => item.id !== productId);
        this.saveItems(items);
    },

    updateQuantity(productId, cantidad) {
        const items = this.getItems();
        const item = items.find(i => i.id === productId);

        if (item) {
            item.cantidad = Math.max(1, cantidad);
            this.saveItems(items);
        }
    },

    getTotal() {
        const items = this.getItems();
        return items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    },

    getCount() {
        const items = this.getItems();
        return items.reduce((sum, item) => sum + item.cantidad, 0);
    },

    clear() {
        localStorage.removeItem(this.key);
        this.updateCounter();
    },

    updateCounter() {
        const counter = document.getElementById('cart-count');
        if (counter) {
            counter.textContent = this.getCount();
        }
    },

    showNotification(message) {
        // Crear notificación toast
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `<span>✓</span> ${message}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #7FA968, #5D7E4C);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }
};

function initCart() {
    Cart.updateCounter();
}

// Función global para agregar al carrito
function addToCart(id, nombre, precio) {
    Cart.addItem({ id, nombre, precio });
}

// ============================================
// FORMULARIO DE TURNOS
// ============================================
function initTurnoForm() {
    const form = document.getElementById('turno-form');
    const fechaInput = document.getElementById('fecha');
    const barrioSelect = document.getElementById('barrio');

    if (!form || !fechaInput) return;

    // Validar fecha al cambiar
    fechaInput.addEventListener('change', () => {
        const fecha = new Date(fechaInput.value + 'T12:00:00');
        const dia = fecha.getDay(); // 0=Dom, 1=Lun, 2=Mar, 3=Mié, 4=Jue, 5=Vie, 6=Sáb

        const zonas = {
            1: 'Oeste',    // Lunes
            3: 'Centro',   // Miércoles
            5: 'Norte'     // Viernes
        };

        if (!zonas[dia]) {
            showAlert('Solo atendemos Lunes (Oeste), Miércoles (Centro) y Viernes (Norte)', 'warning');
            fechaInput.value = '';
            return;
        }

        // Auto-seleccionar barrio según día
        if (barrioSelect) {
            barrioSelect.value = zonas[dia];
            showAlert(`Día ${getDayName(dia)} - Zona ${zonas[dia]}`, 'success');
        }
    });

    // Submit del formulario
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // data.cliente_id se toma de la sesión en backend
        data.direccion = data.direccion || 'Por confirmar';

        try {
            const response = await fetch('/api/peluqueria/agendar_turno.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showAlert('¡Turno agendado exitosamente! Te contactaremos para confirmar.', 'success');
                form.reset();
            } else {
                if (response.status === 401) {
                    window.location.href = '/login.php';
                    return;
                }
                showAlert(result.message || 'Error al agendar turno', 'error');
            }
        } catch (error) {
            showAlert('Error de conexión. Intenta nuevamente.', 'error');
        }
    });
}

function getDayName(dayIndex) {
    const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return days[dayIndex];
}

// ============================================
// CHECKOUT
// ============================================
async function checkout() {
    const items = Cart.getItems();

    if (items.length === 0) {
        showAlert('El carrito está vacío', 'warning');
        return;
    }

    const productos = items.map(item => ({
        producto_id: item.id,
        cantidad: item.cantidad
    }));

    try {
        const response = await fetch('/api/shop/crear_orden.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                // cliente_id se toma de sesión
                productos: productos
            })
        });

        const result = await response.json();

        if (result.success) {
            Cart.clear();
            showAlert('¡Pedido realizado! Te contactaremos para coordinar la entrega.', 'success');
            setTimeout(() => window.location.href = '/', 2000);
        } else {
            if (response.status === 401) {
                window.location.href = '/login.php';
                return;
            }
            showAlert(result.message || 'Error al procesar pedido', 'error');
        }
    } catch (error) {
        showAlert('Error de conexión. Intenta nuevamente.', 'error');
    }
}

// ============================================
// UTILIDADES
// ============================================
function showAlert(message, type = 'info') {
    const alertDiv = document.getElementById('alert-container');

    if (alertDiv) {
        alertDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        // Fallback: crear alerta flotante
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.style.cssText = `
            position: fixed;
            top: 90px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            text-align: center;
        `;
        alert.textContent = message;
        document.body.appendChild(alert);

        setTimeout(() => alert.remove(), 4000);
    }
}

function formatPrice(price) {
    return '$ ' + new Intl.NumberFormat('es-AR').format(price);
}

// ============================================
// ANIMACIONES
// ============================================
function initAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.card, .service-card, .product-card').forEach(el => {
        observer.observe(el);
    });
}

// CSS para animaciones toast
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
