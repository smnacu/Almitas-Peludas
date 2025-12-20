<?php
/**
 * Almitas Peludas - Admin Productos
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Verificar autenticación
requireAdmin();

$user = $_SESSION['user'];
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int) $_POST['id'];
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM shop_productos WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Producto eliminado.";
        } catch (Exception $e) {
            $error = "Error al eliminar.";
        }
    } else {
        // Crear/Editar Producto
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = (float) $_POST['precio'];
        $categoria = $_POST['categoria'];
        $proveedor = $_POST['proveedor'];
        $imagen_url = null;

        // Upload imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('prod_') . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $filename)) {
                $imagen_url = '/assets/uploads/' . $filename;
            }
        }

        if (empty($nombre) || $precio <= 0) {
            $error = "Nombre y precio válidos obligatorios.";
        } else {
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare("INSERT INTO shop_productos (nombre, descripcion, precio_estimado, proveedor_ref, categoria, imagen_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $precio, $proveedor, $categoria, $imagen_url]);
                $success = "Producto agregado exitosamente.";
            } catch (Exception $e) {
                $error = "Error al guardar: " . $e->getMessage();
            }
        }
    }
}

// Obtener productos
try {
    $db = Database::getConnection();
    $productos = $db->query("SELECT * FROM shop_productos ORDER BY categoria, nombre")->fetchAll();
} catch (Exception $e) {
    $productos = [];
}

$pageTitle = 'Gestión Productos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Admin | Almitas Peludas</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="/admin/" class="logo">
                <span class="logo-icon">🐾</span> <span class="logo-text">Admin Panel</span>
            </a>
            <nav class="nav">
                <a href="/admin/" class="nav-link">Dashboard</a>
                <a href="/admin/turnos.php" class="nav-link">Turnos</a>
                <a href="/admin/pedidos.php" class="nav-link">Pedidos</a>
                <a href="/admin/productos.php" class="nav-link active" style="color: var(--primary);">Productos</a>
                <a href="/admin/logout.php" class="nav-link" style="color: var(--error);">Salir</a>
            </nav>
        </div>
    </header>

    <main class="main section">
        <div class="container">
            <h1 class="section-title">Gestión de Productos</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                <!-- Formulario -->
                <div class="card">
                    <h3>📦 Nuevo Producto</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Precio ($)</label>
                            <input type="number" step="0.01" name="precio" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" class="form-control">
                                <option value="Alimentos">Alimentos</option>
                                <option value="Higiene">Higiene</option>
                                <option value="Accesorios">Accesorios</option>
                                <option value="Juguetes">Juguetes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Proveedor</label>
                            <input type="text" name="proveedor" class="form-control" placeholder="Ej: Royal Canin">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Imagen</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar Producto</button>
                    </form>
                </div>

                <!-- Lista -->
                <div class="card table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Img</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Proveedor</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['imagen_url']): ?>
                                        <img src="<?= e($p['imagen_url']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: var(--surface-alt); border-radius: 4px; display: flex; align-items: center; justify-content: center;">📦</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= e($p['nombre']) ?></strong><br>
                                    <small class="text-secondary"><?= e($p['categoria']) ?></small>
                                </td>
                                <td><?= formatPrecio($p['precio_estimado']) ?></td>
                                <td><?= e($p['proveedor_ref']) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('¿Borrar?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-secondary" style="color: var(--error);">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
