<?php
/**
 * Almitas Peludas - Mis Compañeros
 */
session_start();
require_once __DIR__ . '/includes/functions.php';

// Verificar login
requireLogin();

$user = $_SESSION['user'];
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Eliminar almita
        $id = (int) $_POST['id'];
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM almitas WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $user['id']]);
            $success = "Perfil eliminado.";
        } catch (Exception $e) {
            $error = "Error al eliminar.";
        }
    } else {
        // Crear almita
        $nombre = trim($_POST['nombre']);
        $tipo = $_POST['tipo'];
        $notas = trim($_POST['notas']);
        $fecha = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
        $foto_url = null;

        // Manejo de imagen
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/assets/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('almita_') . '.' . $ext;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                $foto_url = '/assets/uploads/' . $filename;
            }
        }
        
        if (empty($nombre) || empty($tipo)) {
            $error = "Nombre y especie son obligatorios.";
        } else {
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare("INSERT INTO almitas (usuario_id, nombre, tipo, fecha_nacimiento, notas_cuidados, foto_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user['id'], $nombre, $tipo, $fecha, $notas, $foto_url]);
                $success = "¡Bienvenido/a $nombre a la familia!";
            } catch (Exception $e) {
                $error = "Error al guardar: " . $e->getMessage();
            }
        }
    }
}

// Obtener almitas
try {
    $db = Database::getConnection();
    // Asegurar compatibilidad si la columna foto_url no existe aun (aunque el script install_almitas la crea)
    $stmt = $db->prepare("SELECT * FROM almitas WHERE usuario_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $almitas = $stmt->fetchAll();
} catch (Exception $e) {
    $almitas = [];
}

$pageTitle = 'Mis Compañeros';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Mis <span class="text-gradient">Compañeros</span></h1>
        <p class="text-center" style="color: var(--text-secondary); margin-bottom: 2rem;">
            Administrá los perfiles de tu familia multiespecie 🐾
        </p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Formulario de Agregado -->
            <div class="card">
                <h3>Nuevo Integrante ❤️</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Luna">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Foto de Perfil</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Especie</label>
                        <select name="tipo" class="form-control" required>
                            <option value="Perro">Perro 🐶</option>
                            <option value="Gato">Gato 🐱</option>
                            <option value="Otro">Otro 🐰</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fecha de Nacimiento (Aprox)</label>
                        <input type="date" name="fecha_nacimiento" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cuidados Especiales</label>
                        <textarea name="notas" class="form-control" placeholder="Alergias, miedos, gustos..."></textarea>
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Agregar Perfil</button>
                    </div>
                </form>
            </div>

            <!-- Lista de Almitas -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php if (empty($almitas)): ?>
                    <div class="card text-center" style="padding: 3rem;">
                        <p style="font-size: 3rem; margin-bottom: 1rem;">🐶🐱</p>
                        <p style="color: var(--text-secondary);">Todavía no registraste a nadie.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($almitas as $almita): ?>
                    <div class="card" style="display: flex; gap: 1rem; align-items: start;">
                        <?php if (!empty($almita['foto_url'])): ?>
                            <img src="<?= e($almita['foto_url']) ?>" alt="<?= e($almita['nombre']) ?>" 
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 2px solid var(--primary);">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; background: var(--surface-alt); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                <?= $almita['tipo'] == 'Gato' ? '🐱' : '🐶' ?>
                            </div>
                        <?php endif; ?>
                        
                        <div style="flex: 1;">
                            <h3 style="margin-bottom: 0.25rem;">
                                <?= e($almita['nombre']) ?> 
                                <small style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 400;">
                                    (<?= e($almita['tipo']) ?>)
                                </small>
                            </h3>
                            <?php if ($almita['fecha_nacimiento']): ?>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                                🎂 <?= date('d/m/Y', strtotime($almita['fecha_nacimiento'])) ?>
                            </p>
                            <?php endif; ?>
                            <?php if ($almita['notas_cuidados']): ?>
                            <p style="font-size: 0.9rem; margin-top: 0.5rem; background: rgba(0,0,0,0.03); padding: 0.5rem; border-radius: 4px;">
                                📝 <?= e($almita['notas_cuidados']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" onsubmit="return confirm('¿Borrar perfil?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $almita['id'] ?>">
                            <button type="submit" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; opacity: 0.7;">
                                🗑️
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
