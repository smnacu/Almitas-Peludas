<?php
/**
 * Almitas Peludas - Login de Clientes
 */
require_once __DIR__ . '/includes/functions.php';

session_start();
if (isCliente()) {
    redirect('/');
}

$pageTitle = 'Iniciar Sesión';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Ingrese email y contraseña.';
    } else {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1 AND rol = 'cliente'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'nombre' => $user['nombre'],
                    'rol' => 'cliente'
                ];
                redirect('/');
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        } catch (Exception $e) {
            error_log("Error login: " . $e->getMessage());
            $error = 'Error de conexión.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container" style="max-width: 400px;">
        <div class="text-center mb-3">
            <h1 class="section-title">Iniciar <span class="text-gradient">Sesión</span></h1>
            <p style="color: var(--text-secondary);">Accedé a tu cuenta</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="card">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Ingresar</button>
            </div>
            
            <p class="text-center mt-2" style="font-size: 0.9rem;">
                ¿No tenés cuenta? <a href="/registro.php" style="color: var(--primary-light);">Registrate aquí</a>
            </p>
        </form>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
