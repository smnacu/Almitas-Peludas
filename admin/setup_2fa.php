<?php
/**
 * Almitas Peludas - Configurar 2FA (Admin)
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/GoogleAuthenticator.php';

requireAdmin();
$user = $_SESSION['user'];
$ga = new GoogleAuthenticator();
$error = '';
$success = '';

// Paso 1: Generar secreto si no existe en sesión (para no cambiarlo en cada recarga)
if (!isset($_SESSION['2fa_setup_secret'])) {
    $_SESSION['2fa_setup_secret'] = $ga->createSecret();
}
$secret = $_SESSION['2fa_setup_secret'];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    
    if ($ga->verifyCode($secret, $code, 2)) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE usuarios SET secret_2fa = ? WHERE id = ?");
            $stmt->execute([$secret, $user['id']]);
            
            $success = "¡Autenticación de dos pasos habilitada correctamente!";
            unset($_SESSION['2fa_setup_secret']); // Limpiar secreto temporal
        } catch (Exception $e) {
            $error = "Error al guardar configuración.";
        }
    } else {
        $error = "El código ingresado es incorrecto. Intente nuevamente.";
    }
}

// Verificar si ya tiene 2FA activo
$has2FA = false;
try {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT secret_2fa FROM usuarios WHERE id = ?");
    $stmt->execute([$user['id']]);
    $currentSecret = $stmt->fetchColumn();
    if ($currentSecret) $has2FA = true;
} catch (Exception $e) {}

$qrCodeUrl = $ga->getQRCodeGoogleUrl('Almitas Peludas (' . $user['email'] . ')', $secret);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar 2FA | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="/admin/" class="logo">
                <span class="logo-icon">🐾</span>
                <span class="logo-text">Admin Panel</span>
            </a>
            <nav class="nav">
                <a href="/admin/" class="nav-link">Dashboard</a>
                <a href="/admin/logout.php" class="nav-link">Salir</a>
            </nav>
        </div>
    </header>

    <main class="main">
        <section class="section">
            <div class="container" style="max-width: 600px;">
                <h1 class="section-title">Seguridad <span class="text-gradient">2FA</span></h1>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= e($success) ?>
                        <br><a href="/admin/">Volver al Dashboard</a>
                    </div>
                <?php else: ?>

                    <?php if ($has2FA): ?>
                        <div class="card" style="border-left: 4px solid var(--success);">
                            <h3>✅ 2FA Activado</h3>
                            <p>Tu cuenta ya está protegida con autenticación de dos pasos.</p>
                            <p style="margin-top: 1rem; color: var(--text-muted);">
                                Si necesitás reconfigurarlo, contactá al soporte técnico o base de datos.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <h3>Configurar Autenticador</h3>
                            <p style="margin-bottom: 1.5rem; color: var(--text-secondary);">
                                Escaneá el código QR con tu aplicación de autenticación (Google Authenticator, Authy, etc.) e ingresá el código generado.
                            </p>

                            <div style="text-align: center; margin-bottom: 2rem;">
                                <img src="<?= $qrCodeUrl ?>" alt="QR Code" style="border: 5px solid white; border-radius: 8px;">
                                <p style="font-family: monospace; margin-top: 0.5rem; color: var(--text-muted);">
                                    Secreto: <?= $secret ?>
                                </p>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-error"><?= e($error) ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="form-group">
                                    <label class="form-label">Código de Verificación (6 dígitos)</label>
                                    <input type="text" name="code" class="form-control" placeholder="123456" autofocus autocomplete="off" style="font-size: 1.5rem; text-align: center; letter-spacing: 5px;">
                                </div>
                                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                                    Verificar y Activar
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
