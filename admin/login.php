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
    
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND rol = 'admin' AND activo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol']
            ];
            redirect('/admin/');
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
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required 
                       placeholder="admin@almitaspeludas.com">
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Ingresar
            </button>
        </form>
        
        <p class="text-center mt-3" style="color: var(--text-muted); font-size: 0.85rem;">
            <a href="/" style="color: var(--primary-light);">← Volver al sitio</a>
        </p>
    </div>
</body>
</html>
