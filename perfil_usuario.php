<?php
/**
 * Almitas Peludas - Perfil de Usuario
 */
session_start();
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$user = $_SESSION['user'];
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $db = Database::getConnection();

    if ($action === 'update_info') {
        $nombre = trim($_POST['nombre']);
        $telefono = trim($_POST['telefono']);
        $direccion = trim($_POST['direccion']);

        if (empty($nombre) || empty($telefono)) {
            $error = "Nombre y teléfono son obligatorios.";
        } else {
            try {
                $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?");
                $stmt->execute([$nombre, $telefono, $direccion, $user['id']]);
                
                // Actualizar sesión
                $_SESSION['user']['nombre'] = $nombre;
                $user['nombre'] = $nombre;
                
                $success = "Datos actualizados correctamente.";
            } catch (Exception $e) {
                $error = "Error al actualizar datos.";
            }
        }
    } elseif ($action === 'change_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (empty($current_pass) || empty($new_pass)) {
            $error = "Complete todos los campos de contraseña.";
        } elseif ($new_pass !== $confirm_pass) {
            $error = "Las nuevas contraseñas no coinciden.";
        } else {
            // Verificar contraseña actual
            $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->execute([$user['id']]);
            $real_pass = $stmt->fetchColumn();

            if (password_verify($current_pass, $real_pass)) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmt->execute([$new_hash, $user['id']]);
                $success = "Contraseña cambiada exitosamente.";
            } else {
                $error = "La contraseña actual es incorrecta.";
            }
        }
    }
}

// Obtener datos frescos
try {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch();
} catch (Exception $e) {
    $userData = [];
}

$pageTitle = 'Mi Cuenta';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Mi <span class="text-gradient">Cuenta</span></h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            
            <!-- Datos Personales -->
            <div class="card">
                <h3>Datos Personales</h3>
                <form method="POST" style="margin-top: 1rem;">
                    <input type="hidden" name="action" value="update_info">
                    
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?= e($userData['nombre']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= e($userData['email']) ?>" disabled style="opacity: 0.7;">
                        <small style="color: var(--text-muted);">El email no se puede cambiar.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control" value="<?= e($userData['telefono']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?= e($userData['direccion']) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
                </form>
            </div>

            <!-- Seguridad -->
            <div class="card">
                <h3>Seguridad</h3>
                <form method="POST" style="margin-top: 1rem;">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-secondary mt-3">Cambiar Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
