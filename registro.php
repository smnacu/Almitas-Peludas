<?php
/**
 * Almitas Peludas - Registro de Clientes
 */
require_once __DIR__ . '/includes/functions.php';

session_start();
if (isCliente()) {
    redirect('/');
}

$pageTitle = 'Registro';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $barrio = trim($_POST['barrio'] ?? '');

    if (empty($nombre) || empty($email) || empty($password) || empty($telefono)) {
        $error = 'Por favor complete todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } else {
        try {
            $db = Database::getConnection();
            
            // Verificar si existe email
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Ya existe una cuenta con ese email.';
            } else {
                // Crear usuario
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    INSERT INTO usuarios (nombre, email, password, telefono, direccion, barrio, rol, activo)
                    VALUES (?, ?, ?, ?, ?, ?, 'cliente', 1)
                ");
                $stmt->execute([$nombre, $email, $hash, $telefono, $direccion, $barrio]);
                
                // Auto-login
                $_SESSION['user'] = [
                    'id' => $db->lastInsertId(),
                    'email' => $email,
                    'nombre' => $nombre,
                    'rol' => 'cliente'
                ];
                
                redirect('/');
            }
        } catch (Exception $e) {
            error_log("Error registro: " . $e->getMessage());
            $error = 'Ocurrió un error al registrarse. Intente nuevamente.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container" style="max-width: 500px;">
        <div class="text-center mb-3">
            <h1 class="section-title">Crear <span class="text-gradient">Cuenta</span></h1>
            <p style="color: var(--text-secondary);">Unite para agendar turnos y comprar productos</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="card">
            <div class="form-group">
                <label class="form-label">Nombre Completo *</label>
                <input type="text" name="nombre" class="form-control" required value="<?= e($_POST['nombre'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña *</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>

            <div class="form-group">
                <label class="form-label">Teléfono *</label>
                <input type="tel" name="telefono" class="form-control" required value="<?= e($_POST['telefono'] ?? '') ?>" placeholder="Ej: 11 1234-5678">
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="direccion" class="form-control" value="<?= e($_POST['direccion'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Barrio</label>
                    <select name="barrio" class="form-control">
                        <option value="">Seleccionar...</option>
                        <option value="Oeste" <?= ($_POST['barrio'] ?? '') === 'Oeste' ? 'selected' : '' ?>>Oeste</option>
                        <option value="Centro" <?= ($_POST['barrio'] ?? '') === 'Centro' ? 'selected' : '' ?>>Centro</option>
                        <option value="Norte" <?= ($_POST['barrio'] ?? '') === 'Norte' ? 'selected' : '' ?>>Norte</option>
                    </select>
                </div>
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Registrarse</button>
            </div>
            
            <p class="text-center mt-2" style="font-size: 0.9rem;">
                ¿Ya tenés cuenta? <a href="/login.php" style="color: var(--primary-light);">Iniciar Sesión</a>
            </p>
        </form>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
