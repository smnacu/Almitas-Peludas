<?php
/**
 * Almitas Peludas - Admin Blog
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Verificar autenticación
requireAdmin();
$user = $_SESSION['user'];
$error = '';
$success = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Artículo eliminado.";
        } catch (Exception $e) {
            $error = "Error al eliminar.";
        }
    } else {
        // Crear Post
        $titulo = trim($_POST['titulo']);
        $contenido = trim($_POST['contenido']);
        $imagen_url = null;
        
        // Manejar subida de imagen si existe
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            // Usar la lógica de upload aquí o llamar a una función helper
            // Por simplicidad en este MVP monolítico, duplicamos lógica básica o usamos la API
            // Aquí haremos un move_uploaded_file directo para el admin
            $uploadDir = __DIR__ . '/../assets/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('blog_') . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $filename)) {
                $imagen_url = '/assets/uploads/' . $filename;
            }
        }

        if (empty($titulo) || empty($contenido)) {
            $error = "Título y contenido son obligatorios.";
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare("INSERT INTO blog_posts (titulo, slug, contenido, imagen_url) VALUES (?, ?, ?, ?)");
                $stmt->execute([$titulo, $slug, $contenido, $imagen_url]);
                $success = "Artículo publicado exitosamente.";
            } catch (Exception $e) {
                $error = "Error al publicar: " . $e->getMessage();
            }
        }
    }
}

// Obtener posts
try {
    $db = Database::getConnection();
    $posts = $db->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $posts = [];
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $error = "La tabla 'blog_posts' no existe. Ejecuta install_blog.php primero.";
    }
}

$pageTitle = 'Gestión Blog';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Admin | Almitas Peludas</title>
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
                <a href="/admin/blog.php" class="nav-link active" style="color: var(--primary);">Blog</a>
                <a href="/admin/logout.php" class="nav-link" style="color: var(--error);">Salir</a>
            </nav>
        </div>
    </header>

    <main class="main section">
        <div class="container">
            <h1 class="section-title">Gestión de la Escuela (Blog)</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                <!-- Formulario -->
                <div class="card">
                    <h3>✍️ Nuevo Artículo</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">Título</label>
                            <input type="text" name="titulo" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Imagen</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contenido</label>
                            <textarea name="contenido" class="form-control" rows="10" required></textarea>
                            <small class="text-secondary">Acepta HTML básico (p, b, ul, li).</small>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Publicar</button>
                    </form>
                </div>

                <!-- Lista -->
                <div class="card">
                    <h3>📚 Artículos Publicados</h3>
                    <?php if (empty($posts)): ?>
                        <p class="text-center text-secondary py-5">No hay artículos aún.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($posts as $p): ?>
                                <div style="display: flex; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                                    <?php if ($p['imagen_url']): ?>
                                        <img src="<?= e($p['imagen_url']) ?>" alt="img" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    <?php endif; ?>
                                    <div style="flex: 1;">
                                        <h4 style="margin-bottom: 0.5rem;"><?= e($p['titulo']) ?></h4>
                                        <small class="text-secondary"><?= date('d/m/Y', strtotime($p['created_at'])) ?></small>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('¿Borrar?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-secondary" style="color: var(--error); border-color: var(--error);">🗑️</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
