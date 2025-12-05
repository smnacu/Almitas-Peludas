<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Almitas Peludas - Peluquería canina a domicilio y Pet Shop en tu ciudad">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Almitas Peludas</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="/" class="logo">
                <span class="logo-icon">🐾</span>
                <span class="logo-text">Almitas Peludas</span>
            </a>
            <nav class="nav">
                <a href="/" class="nav-link">Inicio</a>
                <a href="/agendar.php" class="nav-link">Agendar Turno</a>
                <a href="/tienda.php" class="nav-link">Tienda</a>
                <a href="/carrito.php" class="nav-link cart-link">
                    🛒 <span id="cart-count">0</span>
                </a>
            </nav>
            <button class="nav-toggle" aria-label="Menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <main class="main">
