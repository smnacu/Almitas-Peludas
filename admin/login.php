<?php
/**
 * Almitas Peludas - Admin Login
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Si ya está logueado, redirigir
if (isAdmin()) {
    redirect('/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $code2fa = $_POST['code_2fa'] ?? '';
    
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND rol = 'admin' AND activo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Verificar si tiene 2FA activado
            if (!empty($user['secret_2fa'])) {
                require_once __DIR__ . '/../includes/GoogleAuthenticator.php';
                $ga = new GoogleAuthenticator();
                
                if (empty($code2fa)) {
                    // Pedir código 2FA
                    $ask2fa = true;
                } else {
                    // Validar código
                    if ($ga->verifyCode($user['secret_2fa'], $code2fa)) {
                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'nombre' => $user['nombre'],
                            'rol' => $user['rol']
                        ];
                        redirect('/admin/');
                    } else {
                        $error = 'Código de verificación incorrecto.';
                        $ask2fa = true; 
                    }
                }
            } else {
                // Login normal sin 2FA
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'nombre' => $user['nombre'],
                    'rol' => $user['rol']
                ];
                redirect('/admin/');
            }
        } else {
            $error = 'Email o contraseña incorrectos';
        }
    } catch (Exception $e) {
        $error = 'Error de conexión';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Almitas Peludas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="card" style="width: 100%; max-width: 400px; margin: 1rem;">
        <div class="text-center mb-3">
            <span style="font-size: 3rem;">🐾</span>
            <h1 style="font-size: 1.5rem; margin-top: 0.5rem;">Admin Login</h1>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <?php if (isset($ask2fa) && $ask2fa): ?>
                <!-- 2FA Form -->
                <input type="hidden" name="email" value="<?= e($email) ?>">
                <input type="hidden" name="password" value="<?= e($password) ?>">
                
                <div class="text-center mb-3">
                    <p>Ingresá el código de 6 dígitos de tu autenticador</p>
                </div>

                <div class="form-group">
                    <label class="form-label text-center">Código 2FA</label>
                    <input type="text" name="code_2fa" class="form-control" required autofocus autocomplete="off"
                           style="text-align: center; letter-spacing: 5px; font-size: 1.2rem;">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Verificar
                </button>
                <div class="text-center mt-2">
                     <a href="/admin/login.php" style="font-size: 0.9rem;">Cancelar</a>
                </div>

            <?php else: ?>
                <!-- Normal Login Form -->
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required 
                           placeholder="admin@almitaspeludas.com"
                           value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Ingresar
                </button>
            <?php endif; ?>
        </form>
        
        <p class="text-center mt-3" style="color: var(--text-muted); font-size: 0.85rem;">
            <a href="/" style="color: var(--primary-light);">← Volver al sitio</a>
        </p>
    </div>
</body>
</html>
